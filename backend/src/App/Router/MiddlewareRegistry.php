<?php

namespace Goralys\App\Router;

use Goralys\App\Cache\ClassDiscoverer;
use Goralys\App\HTTP\Middleware\Interface\MiddlewareInterface;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;

final class MiddlewareRegistry
{
    private const string CACHE_FILE = __DIR__ . "/../../../Assets/Cache/middlewares.cache.php";
    private const string MIDDLEWARE_PATH = __DIR__ . "/../HTTP/Middleware";
    private const string MIDDLEWARE_NAMESPACE = 'Goralys\App\HTTP\Middleware\\';

    public static LoggerInterface $logger;

    /**
     * Returns the list of all middlewares associated with their name.
     * This operation is quite expensive, thus the result is cached in a specific file.
     * @param bool $skipCache An additionnal flag to skip cache resolution and force a synchronization.
     * @return array<string, class-string<MiddlewareInterface>> The map of the middlewraes indexed by name.
     */
    public static function discoverMiddlewares(bool $skipCache = false): array
    {
        return ClassDiscoverer::discoverCached(
            MiddlewareInterface::class,
            self::CACHE_FILE,
            self::MIDDLEWARE_PATH,
            self::MIDDLEWARE_NAMESPACE,
            $skipCache
        );
    }
}
