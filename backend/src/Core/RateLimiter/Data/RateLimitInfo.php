<?php

namespace Goralys\Core\RateLimiter\Data;

use DateTimeImmutable;

/**
 * This DTO is used to represent the information tied to a specific token and endpoint.
 */
final readonly class RateLimitInfo
{
    /**
     * @param int $count The number of times this endpoint has been hit with this token.
     * @param ?int $banFor The time until which the token is considered banned for this endpoint.
     * @param int $sinceLastZero The last time this token had a count of 0 for this endpoint.
     * @param DateTimeImmutable $created The time at which the token was created.
     */
    public function __construct(
        public int $count,
        public ?int $banFor,
        public int $sinceLastZero,
        public DateTimeImmutable $created,
    ) {
    }
}
