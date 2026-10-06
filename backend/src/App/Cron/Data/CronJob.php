<?php

namespace Goralys\App\Cron\Data;

use Closure;
use Goralys\App\Cron\Scheduler\Data\JobSchedule;
use Goralys\Kernel\GoralysKernel;

/**
 * This DTO is used to represent a cron job. It contains the job's name and its callback.
 */
final class CronJob
{
    /**
     * @param string $name The name of the job.
     * @param Closure(GoralysKernel $kernel, string $school): void $callback The call back/code to execute when running
     * the job.
     * @param list<Option> $options The options for the job. An option is a tiny logical block that can be run before
     * the job (e.g. connect to the database).
     * @param ?Closure(JobSchedule $js, string $name): void $onSchedule An optional callback to run  when the job is
     * scheduled.
     */
    public function __construct(
        public readonly string $name,
        public readonly Closure $callback,
        private(set) array $options = [],
        private readonly ?Closure $onSchedule = null,
    ) {
    }

    /**
     * Adds an option to the job.
     * @param string $name The name of the option to add.
     * @param string ...$params The parameters of the option.
     * @return self
     */
    public function option(string $name, string ...$params): self
    {
        $this->options[] = new Option($name, $params);
        return $this;
    }

    /**
     * Converts the cron job into a job schedule, after the conversion, the job can no longer be modified or receive new
     * options.
     * @return JobSchedule The job schedule created from the original job.
     */
    public function schedule(): JobSchedule
    {
        $jobSchedule = new JobSchedule($this);
        if ($this->onSchedule) {
            ($this->onSchedule)($jobSchedule, $this->name);
        }
        return $jobSchedule;
    }
}
