<?php

namespace App\Integrations\Email;

use Illuminate\Support\Facades\Mail;

class GenericEmailAdapter implements EmailProviderInterface
{
    private ?string $fromAddress;

    private ?string $fromName;

    public function __construct(?string $fromAddress = null, ?string $fromName = null)
    {
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
    }

    public function withCredentials(array $credentials): self
    {
        $clone = clone $this;
        $clone->fromAddress = $credentials['from_address'] ?? $credentials['sender_email'] ?? $this->fromAddress;
        $clone->fromName = $credentials['from_name'] ?? $credentials['sender_name'] ?? $this->fromName;

        return $clone;
    }

    public function testConnection(array $credentials): array
    {
        try {
            $transport = Mail::getSymfonyTransport();

            return ['success' => true, 'default_mailer' => config('mail.default')];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendTransactional(string $to, string $subject, string $body, array $options = []): array
    {
        try {
            $from = $this->fromAddress ?? config('mail.from.address', 'no-reply@stifin.id');
            $fromName = $this->fromName ?? config('mail.from.name', 'STIFIN');

            $isHtml = (bool) ($options['is_html'] ?? true);

            $mailable = new class($subject, $body, $isHtml, $from, $fromName) extends \Illuminate\Mail\Mailable {
                public function __construct(
                    private string $msgSubject,
                    private string $msgBody,
                    private bool $isHtml,
                    private string $fromAddr,
                    private string $fromNm,
                ) {
                }

                public function build()
                {
                    $mail = $this->from($this->fromAddr, $this->fromNm)
                        ->subject($this->msgSubject);

                    if ($this->isHtml) {
                        return $mail->html($this->msgBody);
                    }

                    return $mail->text($this->msgBody);
                }
            };

            Mail::to($to)->send($mailable);

            return ['success' => true, 'to' => $to, 'via' => config('mail.default')];
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
                continue;
            }

            $mergeData = is_array($recipient) ? $recipient : [];
            $body = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function ($m) use ($mergeData, $options) {
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
        $event = $payload['event'] ?? $payload['type'] ?? 'unknown';
        $recipient = $payload['recipient'] ?? $payload['email'] ?? $payload['to'] ?? null;
        $messageId = $payload['message_id'] ?? $payload['id'] ?? null;

        return [
            'provider' => 'generic_laravel',
            'event' => $event,
            'recipient' => $recipient,
            'message_id' => $messageId,
            'timestamp' => $payload['timestamp'] ?? now()->toIso8601String(),
            'raw' => $payload,
            'headers' => $headers,
        ];
    }
}
