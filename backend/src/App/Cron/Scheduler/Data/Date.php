<?php

namespace Goralys\App\Cron\Scheduler\Data;

use Goralys\App\Cron\Scheduler\Data\Enums\Month;

/**
 * This class is used to more easily handle the scheduler's constraints.
 */
final readonly class Date
{
    public function __construct(
        public Month $month,
        public int $day,
        public int $hour,
        public int $minute
    ) {
    }

    /**
     * Returns the current date using a strictly typed {@see Date} object.
     * @return self
     */
    public static function now(): self
    {
        $parts = explode("|", date("m|d|H|i"));
        return new self(
            Month::from($parts[0]),
            (int) $parts[1],
            (int) $parts[2],
            (int) $parts[3],
        );
    }
}
