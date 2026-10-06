<?php

namespace Goralys\App\Cron;

use Goralys\App\Context\Data\CurrentSchool;
use Goralys\App\Cron\Data\CronJob;
use Goralys\App\Cron\Options\DbOption;
use Goralys\App\Cron\Options\Interfaces\OptionInterface;
use Goralys\Kernel\GoralysKernel;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;

class JobDispatcher
{
    private array $optionsMap = [
            "db" => DbOption::class
    ];
    private GoralysKernel $kernel;

    /**
     * @param GoralysKernel $kernel The injected kernel.
     */
    public function __construct(GoralysKernel $kernel)
    {
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
     * Runs a given job.
     * @param string $name The name of the job to run.
     * @return void
     */
    public function dispatch(string $name): void
    {
        $jobs = new Cron()->getAll();
        $j = $jobs[$name] ?? null;
        if ($j === null) {
            $this->kernel->logger->fatal(
                LoggerInitiator::CRON,
                "Unknown job name: " . $name
            );
        }


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
