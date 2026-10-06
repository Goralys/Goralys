<?php

namespace Goralys\App\Cron;

use Goralys\App\Cache\ClassDiscoverer;
use Goralys\App\Cron\Options\Interfaces\OptionInterface;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;

final class OptionsRegistry
{
    private const string CACHE_FILE = __DIR__ . "/../../../Assets/Cache/options.cache.php";
    private const string OPTION_PATH = __DIR__ . "/../Cron/Options";
    private const string OPTION_NAMESPACE = 'Goralys\App\Cron\Options\\';

    public static LoggerInterface $logger;

    /**
     * Returns the list of all middlewares associated with their name.
     * This operation is quite expensive, thus the result is cached in a specific file.
     * @param bool $skipCache An additionnal flag to skip cache resolution and force a synchronization.
     * @return array<string, class-string<OptionInterface>> The map of the middlewraes indexed by name.
     */
    public static function discoverOptions(bool $skipCache = false): array
    {
        return ClassDiscoverer::discoverCached(
            OptionInterface::class,
            self::CACHE_FILE,
            self::OPTION_PATH,
            self::OPTION_NAMESPACE,
            $skipCache
        );
    }
}
