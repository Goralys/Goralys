<?php

namespace Goralys\App\Cron;

use Goralys\App\Context\Data\CurrentSchool;
use Goralys\App\Cron\Data\CronJob;
use Goralys\App\Cron\Options\Interfaces\OptionInterface;
use Goralys\App\Cron\Scheduler\CronScheduler;
use Goralys\App\Cron\Scheduler\Data\JobSchedule;
use Goralys\Kernel\GoralysKernel;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;

final class JobDispatcher
{
    private array $optionsMap;
    private GoralysKernel $kernel;

    /**
     * @param GoralysKernel $kernel The injected kernel.
     */
    public function __construct(GoralysKernel $kernel)
    {
        $this->optionsMap = OptionsRegistry::discoverOptions();
        $this->kernel = $kernel;
    }

    /**
     * Resolves the list of options for a given job.
     * @param CronJob $job The job to resolve the options for.
     * @return array
     */
    public function resolveOptions(CronJob $job): array
    {
        /** @var list<OptionInterface> $resolved */
        $resolved = [];
        foreach ($job->options as $option) {
            $class = $this->optionsMap[$option->name] ?? null;
            if ($class === null) {
                $this->kernel->logger->warning(
                    LoggerInitiator::CRON,
                    "Unknown option: " . $option->name
                );
                continue;
            }
            $resolved[] = new $class($option->name, ...$option->params);
        }

        return $resolved;
    }

    /**
     * Runs all due jobs.
     * @return void
     */
    public function runDue(): void
    {
        $jobs = new Cron()->getAll();
        foreach ($jobs as $n => $job) {
            if (!is_a($job, JobSchedule::class)) {
                unset($jobs[$n]);
                $this->kernel->logger->warning(
                    LoggerInitiator::CRON,
                    "Found unscheduled job: " . $n . ", this job will be skipped"
                );
            }
        }
        $jobs = CronScheduler::getDue($jobs);

        foreach ($jobs as $j) {
            $opt = $this->resolveOptions($j);
            $schools = $this->kernel->highSchools->getAllSchools();
            foreach ($schools as $school => $_) {
                CurrentSchool::$CODE = $school;
                CurrentSchool::$TOKEN = $this->kernel->highSchools->getTokenForSchool(CurrentSchool::$CODE);

                $dest = function () use ($school, $opt, $j) {
                    $this->kernel->run(function () use ($school, $opt, $j) {
                        ($j->callback)($this->kernel, $school);
                    });
                };
                $this->pipeline($school, $opt, $dest);
            }
        }
    }

    /**
     * Returns the final function to run for a given job.
     * @param string $school The current school the job is running for.
     * @param list<OptionInterface> $options The options to run.
     * @param callable $dest The job's logic to encapsulate.
     * @return void The final pipeline.
     */
    private function pipeline(string $school, array $options, callable $dest): void
    {
        $p = array_reduce($options, function ($next, $opt) use ($school) {
            return function () use ($next, $opt, $school) {
                $opt->handle($school, $this->kernel, $next);
            };
        }, $dest);
        $p();
    }
}
