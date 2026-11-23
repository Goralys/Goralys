<?php

namespace Goralys\Core\RateLimiter\Repository\Interfaces;

use Goralys\Core\RateLimiter\Data\RateLimitInfo;

interface RateLimiterRepositoryInterface
{
    /**
     * Make the repository target a specific token and endpoint pair.
     * This pair is then reused for all the later operations.
     * @param string $token The token of the current client.
     * @param string $endpoint The endpoint that is currently hit.
     * @return void
     */
    public function select(string $token, string $endpoint): void;

    /**
     * Retrieves the info for the current target.
     * @return RateLimitInfo The info of the target.
     */
    public function getInfo(): RateLimitInfo;

    /**
     * Increments the current target count.
     * @param int $count The amount to increase the count by, the default is 1.
     * @return void
     */
    public function increment(int $count = 1): void;

    /**
     * Sets the current target count.
     * @param int $count The amount to set the count to.
     * @param bool $refreshZero Wether to consider the rate limit has being zero at the current time.
     * @return void
     */
    public function set(int $count, bool $refreshZero = false): void;

    /**
     * Bans the current target.
     * @param int $for The time to ban the target for in seconds.
     * @return void
     */
    public function ban(int $for): void;

    /**
     * Deletes all the rate limit entries that are outdated.
     * @param int $lifetime The maximum age, in seconds, of an entry.
     * @return void
     */
    public function purge(int $lifetime): void;
}
