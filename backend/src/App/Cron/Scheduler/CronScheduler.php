<?php

namespace Goralys\App\Cron\Scheduler;

use Goralys\App\Cron\Data\CronJob;
use Goralys\App\Cron\Scheduler\Data\Date;
use Goralys\App\Cron\Scheduler\Data\JobSchedule;
use Goralys\Shared\Exception\GoralysRuntimeException;

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
     * @throws GoralysRuntimeException
     */
    public static function getDue(array $jobs): array
    {
        $half =  intdiv(self::TIME_INTERVAL, 2);
        $w = Date::window($half, self::TIME_INTERVAL - $half - 1);

        return array_map(fn (JobSchedule $js) => $js->job, array_filter($jobs, function (JobSchedule $js) use ($w) {
            $s = $js->getSchedule();

            return array_any($w, fn($t) =>
                in_array($t->month->value, $s->months)
                && in_array($t->day, $s->days)
                && in_array($t->hour, $s->hours)
                && $t->minute === $s->minute);
        }));
    }
}
