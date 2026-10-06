<?php

namespace Goralys\App\Cron\Scheduler;

use Goralys\App\Cron\Data\CronJob;
use Goralys\App\Cron\Scheduler\Data\Date;
use Goralys\App\Cron\Scheduler\Data\JobSchedule;

final class CronScheduler
{
    /** @var int This constant represents the interval between two calls to the scheduler.
     * It is in minutes and should not exeeds 59 minutes.
     */
    private const int TIME_INTERVAL = 5;

    /**
     * Filters the jobs to only run the due ones.
     * @param JobSchedule[] $jobs The registered and scheduled jobs.
     * @return CronJob[] The list of jobs to run.
     */
    public static function getDue(array $jobs): array
    {
        $now = Date::now();
        return array_map(fn (JobSchedule $js) => $js->job, array_filter($jobs, function (JobSchedule $js) use ($now) {
            $s = $js->getSchedule();
            if (
                in_array($now->month->value, $s->months)
                && in_array($now->day, $s->days)
                && in_array($now->hour, $s->hours)
            ) {
                $diff = abs($now->minute - $s->minute);
                if ($diff > 30) {
                    $diff = 60 - $diff; // take shortest path around the full circle
                }

                return $diff <= (self::TIME_INTERVAL / 2);
            }

            return false;
        }));
    }
}
