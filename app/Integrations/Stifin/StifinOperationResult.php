<?php

namespace App\Integrations\Stifin;

use App\Enums\StifinOperationOutcome;

class StifinOperationResult
{
    public function __construct(
        public readonly bool $isSuccess,
        public readonly ?int $httpCode,
        public readonly string $rawBody,
        public readonly ?array $parsedData,
        public readonly bool $isAmbiguousTimeout = false,
        public readonly bool $isAmbiguousTransport = false,
        public readonly bool $isParseError = false,
        public readonly ?string $errorMessage = null,
    ) {
    }

    public function outcome(): StifinOperationOutcome
    {
        if ($this->isAmbiguousTimeout) {
            return StifinOperationOutcome::AmbiguousTimeout;
        }
        if ($this->isAmbiguousTransport) {
            return StifinOperationOutcome::AmbiguousTransport;
        }
        if ($this->isParseError) {
            return StifinOperationOutcome::Unparseable;
        }
        if ($this->isSuccess) {
            return StifinOperationOutcome::Success;
        }
        if ($this->httpCode >= 400 && $this->httpCode < 500) {
            return StifinOperationOutcome::Fail4xx;
        }
        if ($this->httpCode >= 500) {
            return StifinOperationOutcome::Fail5xx;
        }
        return StifinOperationOutcome::PreflightError;
    }

    public function isAmbiguous(): bool
    {
        return $this->isAmbiguousTimeout || $this->isAmbiguousTransport || $this->isParseError;
    }
}
