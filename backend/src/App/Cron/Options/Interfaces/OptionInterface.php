<?php

namespace Goralys\App\Cron\Options\Interfaces;

use Goralys\Kernel\GoralysKernel;

interface OptionInterface
{
    /**
     * @param string ...$params The configurable parameters of the option (if applicable)
     */
    public function __construct(string ...$params);

    /**
     * The option's logic to run before the job.
     * @param string $school The code of the school that the job is currently running for.
     * @param GoralysKernel $kernel The kernel used to centralze the helpers.
     * @param callable $next The next option to run.
     * @return void
     */
    public function handle(string $school, GoralysKernel $kernel, callable $next): void;

    /**
     * Returns the name of the option.
     * @return string The name of the option.
     */
    public static function name(): string;
}
