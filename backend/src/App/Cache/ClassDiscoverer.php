<?php

namespace Goralys\App\Cache;

use Goralys\App\Cache\Interfaces\Discoverable;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;
use Goralys\Platform\Logger\Interfaces\LoggerInterface;
use ReflectionClass;
use Throwable;

class ClassDiscoverer
{
    public static LoggerInterface $logger;

    /**
     * @template T of Discoverable
     * Returns the list of all classes implementing an interface that extends @{@see Discoverable} associated with their
     * name.
     * This operation is quite expensive, thus the result is cached in a specific file.
     * @param class-string<T> $interface The interface to discover implementations of.
     * @param string $cacheFile The file to the file used to cache the result.
     * @param string $basePath The path (folder) that contains all implementations of the interface.
     * @param string $baseNamespace The namespace that contains all implementations of the interface.
     * @param bool $skipCache An additionnal flag to skip cache resolution and force a synchronization.
     * @return array<string, class-string<T>> The map of the classes indexed by name.
     */
    public static function discoverCached(
        string $interface,
        string $cacheFile,
        string $basePath,
        string $baseNamespace,
        bool $skipCache = false
    ): array {
        if (!$skipCache && file_exists($cacheFile)) {
            return require $cacheFile;
        }

        if (!is_dir(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0o777, true);
        }

        $map = self::discover($interface, $basePath, $baseNamespace);
        file_put_contents($cacheFile, "<?php return " . var_export($map, true) . "; ?>");
        return $map;
    }

    /**
     * @template T of Discoverable
     * This helper is in charge of actually discoverer the middleware. It scans the directory containing all middlewares
     * and finds the name of the classes using the PSR-4 naming scheme.
     * @param class-string<T> $interface The interface to check for.
     * @param string $basePath The path (folder) that contains all implementations of the interface.
     * @param string $baseNamespace The namespace that contains all implementations of the interface.
     * @return array<string, class-string<T>> The map of the classes indexed by name.
     */
    private static function discover(string $interface, string $basePath, string $baseNamespace): array
    {
        $map = [];

        // Scan filesystem for middleware files
        $files = glob($basePath . "/*.php");

        if (empty($files)) {
            self::$logger->warning(LoggerInitiator::APP, "No middlewares found.");
            return [];
        }

        foreach ($files as $file) {
            $filename = basename($file, '.php');
            if ($filename === 'Interface') {
                continue; // Skip Interface directory
            }

            $class = $baseNamespace . $filename;

            // Require the file to load the class
            require_once $file;

            if (!class_exists($class)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($class);

                if ($reflection->isAbstract() || $reflection->isInterface()) {
                    continue;
                }

                if (!$reflection->implementsInterface($interface)) {
                    continue;
                }

                $name = $class::name();
                $map[$name] = $class;
            } catch (Throwable $e) {
                self::$logger->warning(LoggerInitiator::APP, "Error during interface check: {$e->getMessage()}.");
            }
        }

        return $map;
    }
}
