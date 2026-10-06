<?php

namespace Goralys\App\Cron\Scheduler\Data;

use Goralys\App\Cron\Data\CronJob;
use Goralys\App\Cron\Scheduler\Data\Enums\Month;

/**
 * Data Transfer Object representing a scheduled cron job.
 *
 * This DTO encapsulates the timing constraints for a cron job. Non-scheduled jobs
 * (without schedule constraints) are skipped by the dispatcher.
 *
 * **Override Behavior:**
 * If two or more methods related to the same constraint/time component are called
 * (e.g., `month()` and `monthly()`), the last one called takes effect and overrides
 * all previous calls for that component.
 */
final class JobSchedule
{
    /**
     * @param CronJob $job The cron job to be scheduled.
     * @param list<int> $months The months (1-12) on which the job will run. Empty means all months.
     * @param list<int> $days The days of month (1-31) on which the job will run. Empty means all days.
     * @param list<int> $hours The hours (0-23) on which the job will run. Empty means all hours.
     * @param int $minute The minute (0-59) on which to run the job. Defaults to 0.
     */
    public function __construct(
        public readonly CronJob $job,
        private array $months = [],
        private array $days = [],
        private array $hours = [],
        private int $minute = 0,
    ) {
    }

    /**
     * Schedule the job to run on specific months.
     *
     * Overrides any previous month constraints (including `monthly()`).
     *
     * @param Month $first The first month to include.
     * @param Month ...$_m Additional months to include.
     * @return $this
     */
    public function month(Month $first, Month ...$_m): self
    {
        $this->months = array_map(fn (Month $m) => $m->value, [$first, ...$_m]);
        return $this;
    }

    /**
     * Schedule the job to run every month.
     *
     * Overrides any previous month constraints (including `month()`).
     *
     * @return $this
     */
    public function monthly(): self
    {
        $this->months = array_map(fn (Month $m) => $m->value, Month::cases());
        return $this;
    }

    /**
     * Schedule the job to run every week of the month.
     * To do so, it uses fixed days: 1, 8, 15, 22, 29
     *
     * Overrides any previous day constraints (including `day()`).
     *
     * @return $this
     */
    public function weekly(): self
    {
        $this->monthly();
        $this->days = range(1, 31, 7);
        return $this;
    }

    /**
     * Schedule the job to run on specific days of the month.
     *
     * Overrides any previous day constraints (including `daily()`).
     *
     * @param int $first The first day (1-31) to include.
     * @param int ...$_d Additional days (1-31) to include.
     * @return $this
     */
    public function day(int $first, int ...$_d): self
    {
        $this->days = [$first, ...$_d];
        return $this;
    }

    /**
     * Schedule the job to run every day of the month.
     *
     * Overrides any previous day constraints (including `day()`).
     *
     * @return $this
     */
    public function daily(): self
    {
        $this->monthly();
        $this->days = range(1, 31);
        return $this;
    }

    /**
     * Schedule the job to run on specific hours.
     *
     * Overrides any previous hour constraints (including `hourly()` and `at()`).
     *
     * @param int $first The first hour (0-23) to include.
     * @param int ...$_h Additional hours (0-23) to include.
     * @return $this
     */
    public function hour(int $first, int ...$_h): self
    {
        $this->hours = [$first, ...$_h];
        return $this;
    }

    /**
     * Schedule the job to run every hour (0-23).
     *
     * Overrides any previous hour constraints (including `hour()` and `at()`).
     *
     * @return $this
     */
    public function hourly(): self
    {
        $this->daily();
        $this->hours = range(0, 23);
        return $this;
    }

    /**
     * Set the minute (0-59) at which the job will run.
     *
     * Works in conjunction with hour constraints. Does not affect hour constraints.
     *
     * @param int $minute The minute (0-59) at which to run.
     * @return $this
     */
    public function atMinute(int $minute): self
    {
        $this->minute = $minute;
        return $this;
    }

    /**
     * Set a specific hour and minute at which the job will run.
     *
     * Overrides any previous hour constraints but preserves month/day constraints.
     * Sets the minute for the job.
     *
     * @param int $hour The hour (0-23) at which to run.
     * @param int $minute The minute (0-59) at which to run.
     * @return $this
     */
    public function at(int $hour, int $minute): self
    {
        $this->hours = [$hour];
        $this->minute = $minute;
        return $this;
    }

    public function getSchedule(): Schedule
    {
        return new Schedule($this->months, $this->days, $this->hours, $this->minute);
    }
}
