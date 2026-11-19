<?php

namespace App\Services\Duffel;

enum DuffelErrorType: string
{
    case Misconfigured = 'misconfigured';
    case Unauthorized = 'unauthorized';
    case Validation = 'validation';
    case RateLimited = 'rate_limited';
    case Unavailable = 'unavailable';
    case Connection = 'connection';
    case Unknown = 'unknown';

    /**
     * @param int $status
     * @return self
     */
    public static function fromStatus(int $status): self
    {
        return match (true) {
            $status === 401, $status === 403 => self::Unauthorized,
            $status === 400, $status === 422 => self::Validation,
            $status === 429 => self::RateLimited,
            $status >= 500 => self::Unavailable,
            default => self::Unknown,
        };
    }

    /**
     * Text for the end user; details go to the log only.
     *
     * @return string
     */
    public function userMessage(): string
    {
        return __('duffel.errors.'.$this->value);
    }
}
