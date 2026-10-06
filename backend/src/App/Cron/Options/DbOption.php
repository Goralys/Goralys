<?php

namespace Goralys\App\Cron\Options;

use Goralys\Kernel\GoralysKernel;

final readonly class DbOption implements Interfaces\OptionInterface
{
    private const string NAME = "db";

    public function __construct(string ...$params)
    {
    }

    /**
     * The option's logic to run before the job.
     * @param string $school
     * @param GoralysKernel $kernel The kernel used to centralze the helpers.
     * @param callable $next The next option/step to run.
     * @return void
     */
    public function handle(string $school, GoralysKernel $kernel, callable $next): void
    {
        $kernel->db->connect(
            $kernel->highSchools->getDbForSchool( // resolve db name from token
                $kernel->highSchools->getTokenForSchool($school) // resolve token from school code
            )
        );

        $next($kernel);
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
    public static function connect(): array
    {
        return [self::NAME];
    }
}
