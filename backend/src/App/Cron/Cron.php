<?php

namespace Goralys\App\Cron;

use Closure;
use Goralys\App\Cron\Data\CronJob;
use Goralys\App\Cron\Scheduler\Data\JobSchedule;
use Goralys\Kernel\GoralysKernel;

final class Cron
{
    /** @var array<string, CronJob> $jobs */
    private static array $jobs = [];

    /**
     * Registers a new job.
     * @param string $name The name of the new cron job.
     * @param Closure(GoralysKernel $kernel, string $school): void $callback The actual job's logic.
     * @return CronJob The created job.
     */
    public static function job(string $name, Closure $callback): CronJob
    {
        $job = new CronJob(
            $name,
            $callback,
            onSchedule: fn (JobSchedule $js, string $name) => self::$jobs[$name] = $js
        );
        self::$jobs[$name] = $job;
        return $job;
    }

    /**
     * Returns all the registered jobs.
     * @return array<string, CronJob|JobSchedule> The array containing all the registered jobs, indexed by name.
     */
    public function getAll(): array
    {
        return self::$jobs;
    }
}
