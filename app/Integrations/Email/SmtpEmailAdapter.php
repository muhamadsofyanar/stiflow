<?php

namespace App\Integrations\Email;

use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mime\Email as SymfonyEmail;

class SmtpEmailAdapter implements EmailProviderInterface
{
    private ?string $host;

    private ?int $port;

    private ?string $username;

    private ?string $password;

    private ?string $encryption;

    private ?string $fromAddress;

    private ?string $fromName;

    public function __construct(
        ?string $host = null,
        ?int $port = null,
        ?string $username = null,
        ?string $password = null,
        ?string $encryption = null,
        ?string $fromAddress = null,
        ?string $fromName = null,
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->encryption = $encryption;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
    }

    public function withCredentials(array $credentials): self
    {
        $clone = clone $this;
        $clone->host = $credentials['host'] ?? $credentials['smtp_host'] ?? $this->host;
        $clone->port = isset($credentials['port']) ? (int) $credentials['port'] : (isset($credentials['smtp_port']) ? (int) $credentials['smtp_port'] : $this->port);
        $clone->username = $credentials['username'] ?? $credentials['user'] ?? $credentials['smtp_user'] ?? $this->username;
        $clone->password = $credentials['password'] ?? $credentials['pass'] ?? $credentials['smtp_pass'] ?? $this->password;
        $clone->encryption = $credentials['encryption'] ?? $credentials['auth_mode'] ?? $this->encryption;
        $clone->fromAddress = $credentials['from_address'] ?? $credentials['sender_email'] ?? $this->fromAddress;
        $clone->fromName = $credentials['from_name'] ?? $credentials['sender_name'] ?? $this->fromName;

        return $clone;
    }

    public function testConnection(array $credentials): array
    {
        $adapter = $this->withCredentials($credentials);

        try {
            $host = $adapter->host;
            $port = $adapter->port ?? 587;
            if (! $host) {
                return ['success' => false, 'error' => 'SMTP host tidak diset'];
            }

            $encryption = strtolower($adapter->encryption ?? 'tls');
            $transport = new EsmtpTransport($host, $port, $encryption === 'ssl');

            if ($adapter->username && $adapter->password) {
                $transport->setUsername($adapter->username);
                $transport->setPassword($adapter->password);
            }

            $mailer = new SymfonyMailer($transport);
            $transport->start();

            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendTransactional(string $to, string $subject, string $body, array $options = []): array
    {
        try {
            $from = $this->fromAddress ?? config('mail.from.address', 'no-reply@stifin.id');
            $fromName = $this->fromName ?? config('mail.from.name', 'STIFIN');

            $host = $this->host ?? config('mail.mailers.smtp.host');
            $port = $this->port ?? (int) config('mail.mailers.smtp.port', 587);
            $encryption = strtolower($this->encryption ?? config('mail.mailers.smtp.encryption', 'tls'));

            $transport = new EsmtpTransport($host, $port, $encryption === 'ssl');

            if ($this->username && $this->password) {
                $transport->setUsername($this->username);
                $transport->setPassword($this->password);
            }

            $mailer = new SymfonyMailer($transport);

            $email = (new SymfonyEmail())
                ->from(new \Symfony\Component\Mime\Address($from, $fromName))
                ->to($to)
                ->subject($subject);

            $replyTo = $options['reply_to'] ?? null;
            if ($replyTo) {
                $email->replyTo($replyTo);
            }

            $isHtml = (bool) ($options['is_html'] ?? true);
            if ($isHtml) {
                $email->html($body);
            } else {
                $email->text($body);
            }

            $attachments = $options['attachments'] ?? [];
            foreach ($attachments as $att) {
                if (is_array($att) && isset($att['path'])) {
                    $email->attachFromPath($att['path'], $att['name'] ?? null, $att['mime'] ?? null);
                } elseif (is_string($att)) {
                    $email->attachFromPath($att);
                }
            }

            $mailer->send($email);

            return ['success' => true, 'to' => $to, 'message_id' => bin2hex(random_bytes(16))];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendBatch(array $recipients, string $subject, string $bodyTemplate, array $options = []): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];

        foreach ($recipients as $recipient) {
            $email = is_string($recipient) ? $recipient : ($recipient['email'] ?? null);
            if (! $email) {
                $results['failed']++;
                $results['errors'][] = 'invalid_recipient';
                continue;
            }

            $mergeData = is_array($recipient) ? $recipient : [];
            $body = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function ($m) use ($mergeData) {
                $key = $m[1];
                if (array_key_exists($key, $mergeData)) {
                    return (string) $mergeData[$key];
                }
                if (array_key_exists($key, $options)) {
                    return (string) $options[$key];
                }

                return '';
            }, $bodyTemplate);

            $sent = $this->sendTransactional($email, $subject, $body, $options);
            if ($sent['success'] ?? false) {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][] = $sent['error'] ?? 'unknown';
            }
        }

        return $results;
    }

    public function parseWebhook(array $payload, array $headers = []): array
    {
        $event = $payload['event'] ?? $payload['type'] ?? $payload['eventType'] ?? 'unknown';
        $recipient = $payload['recipient'] ?? $payload['email'] ?? $payload['to'] ?? null;
        $messageId = $payload['message_id'] ?? $payload['sg_message_id'] ?? $payload['id'] ?? null;
        $reason = $payload['reason'] ?? $payload['error'] ?? $payload['bounce_reason'] ?? null;

        return [
            'provider' => 'smtp',
            'event' => $event,
            'recipient' => $recipient,
            'message_id' => $messageId,
            'reason' => $reason,
            'timestamp' => $payload['timestamp'] ?? now()->toIso8601String(),
            'raw' => $payload,
            'headers' => $headers,
        ];
    }
}
