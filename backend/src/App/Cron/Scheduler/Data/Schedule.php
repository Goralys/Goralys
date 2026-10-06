<?php

namespace Goralys\App\Cron\Scheduler\Data;

/**
 * This DTO represents a simple schedule.
 */
final readonly class Schedule
{
    /**
     * @param list<int> $months The months (1-12) on which the job will run. Empty means all months.
     * @param list<int> $days The days of month (1-31) on which the job will run. Empty means all days.
     * @param list<int> $hours The hours (0-23) on which the job will run. Empty means all hours.
     * @param int $minute The minute (0-59) on which to run the job. Defaults to 0.
     */
    public function __construct(
        public array $months,
        public array $days,
        public array $hours,
        public int $minute,
    ) {
    }
}
