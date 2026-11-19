<?php

namespace App\Services\Duffel;

use RuntimeException;
use Throwable;

class DuffelException extends RuntimeException
{
    /**
     * @param DuffelErrorType $type
     * @param string $message
     * @param int|null $status
     * @param Throwable|null $previous
     */
    public function __construct(
        public readonly DuffelErrorType $type,
        string $message = '',
        public readonly ?int $status = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status ?? 0, $previous);
    }

    /**
     * @return string
     */
    public function userMessage(): string
    {
        return $this->type->userMessage();
    }
}
