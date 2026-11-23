<?php

namespace Goralys\Core\RateLimiter\Repository;

use DateMalformedStringException;
use DateTimeImmutable;
use Goralys\Core\RateLimiter\Data\RateLimitInfo;
use Goralys\Core\RateLimiter\Repository\Interfaces\RateLimiterRepositoryInterface;
use Goralys\Platform\DB\Interfaces\DbContainerInterface;

class RateLimiterRepository implements RateLimiterRepositoryInterface
{
    private DbContainerInterface $db;

    private string $key;

    /**
     * @param DbContainerInterface $db The injected db container.
     */
    public function __construct(DbContainerInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Make the repository target a specific token and endpoint pair.
     * This pair is then reused for all the later operations.
     * @param string $token The token of the current client.
     * @param string $endpoint The endpoint that is currently hit.
     * @return void
     */
    public function select(string $token, string $endpoint): void
    {
        $this->key = $token . $endpoint;
    }

    /**
     * Retrieves the info for the current target.
     * @return RateLimitInfo The info of the target.
     * @throws DateMalformedStringException
     */
    public function getInfo(): RateLimitInfo
    {
        $row = $this->db->fetch(
            "
            select
                count, created, 
                greatest(0, timestampdiff(second, now(), ban_until)) AS ban_for,
                timestampdiff(second, now(), last_zero) as since_last_zero
            from rate_limits where token = ?
            limit 1",
            "s",
            $this->key
        )->fetch_assoc();

        return new RateLimitInfo(
            $row['count'],
            $row['ban_for'] > 0 ? $row['ban_for'] : null,
            $row['since_last_zero'],
            new DateTimeImmutable($row['created'])
        );
    }

    /**
     * Increments the current target count.
     * @param int $count The amount to increase the count by, the default is 1.
     * @return void
     */
    public function increment(int $count = 1): void
    {
        $this->db->run(
            "
            insert into rate_limits (token, ban_until) values (?, null)
            on duplicate key update count = greatest(count + ?, 0)
            ",
            "si",
            $this->key,
            $count
        );
    }

    /**
     * Sets the current target count.
     * @param int $count The amount to set the count to.
     * @param bool $refreshZero Wether to consider the rate limit has being zero at the current time.
     * @return void
     */
    public function set(int $count, bool $refreshZero = false): void
    {
        $this->db->run(
            "update rate_limits set count = ?, last_zero = if(?, current_timestamp, last_zero) where token = ?",
            "iis",
            $count,
            $refreshZero,
            $this->key
        );
    }

    /**
     * Bans the current target.
     * @param int $for The time to ban the target for in seconds.
     * @return void
     */
    public function ban(int $for): void
    {
        $this->db->run(
            "update rate_limits set ban_until = timestampadd(second, now(), ?) where token = ?",
            "is",
            $for,
            $this->key
        );
    }

    /**
     * Deletes all the rate limit entries that are outdated.
     * @param int $lifetime The maximum age, in seconds, of an entry. Entries with active bans, but pass that lifetime
     * are still preserved.
     * @return void
     */
    public function purge(int $lifetime): void
    {
        $this->db->runIgnoreNoOps(
            "
            delete from rate_limits
            where created <= now() - interval ? second and (ban_until is null or ban_until < now())
            ",
            "i",
            $lifetime
        );
    }
}
