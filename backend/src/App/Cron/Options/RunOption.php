<?php

namespace Goralys\App\Cron\Options;

use Goralys\App\Context\Context;
use Goralys\Kernel\GoralysKernel;

final readonly class RunOption implements Interfaces\OptionInterface
{
    private const string NAME = "run";

    public function __construct(string ...$params)
    {
    }

    public function handle(string $school, GoralysKernel $kernel, callable $next): void
    {
    }

    /**
     * Sets the jobs to run only once.
     * @return void
     */
    public function onResolve(): void
    {
        Context::$CURRENT->jobs->runOnce = true;
    }

    /**
     * Returns the name of the option.
     * @return string The name of the option.
     */
    public static function name(): string
    {
        return self::NAME;
    }

    /**
     * Returns a preconfigured set of params and the name of the option (db) to connect to the database before running
     * the job.
     * @return string[] The preconfigured name and param to connect to the databse.
     */
    public static function once(): array
    {
        return [self::NAME];
    }
}
