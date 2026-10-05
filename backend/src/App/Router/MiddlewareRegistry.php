<?php

namespace Goralys\App\Router;

use Goralys\App\HTTP\Middleware\Interface\MiddlewareInterface;
use ReflectionClass;
use Throwable;

class MiddlewareRegistry
{
    private const string CACHE_FILE = __DIR__ . "/../../../Assets/Cache/middlewares.php.cache";
    private const string MIDDLEWARE_PATH = __DIR__ . "/../HTTP/Middleware";
    private const string MIDDLEWARE_NAMESPACE = 'Goralys\App\HTTP\Middleware\\';

    /**
     * Returns the list of all middlewares associated with their name.
     * This operation is quite expensive, thus the result is cached in a specific file.
     * @param bool $skipCache An additionnal flag to skip cache resolution and force a synchronization.
     * @return array<string, class-string<MiddlewareInterface>> The map of the middlewraes indexed by name.
     */
    public static function discoverMiddlewares(bool $skipCache = false): array
    {
        if (!$skipCache && file_exists(self::CACHE_FILE)) {
            return require self::CACHE_FILE;
        }

        if (!is_dir(dirname(self::CACHE_FILE))) {
            mkdir(dirname(self::CACHE_FILE), 0o777, true);
        }

        $map = self::discover();
        file_put_contents(self::CACHE_FILE, "<?php return " . var_export($map, true) . "; ?>");
        return $map;
    }

    /**
     * This helper is in charge of actually discoverer the middleware. It scans the directory containing all middlewares
     * and finds the name of the classes using the PSR-4 naming scheme.
     * @return array<string, class-string<MiddlewareInterface>> The map of the middlewraes indexed by name.
     */
    private static function discover(): array
    {
        $map = [];
        $debugFile = __DIR__ . "/../../../Logs/middleware-discovery.log";
        if (!is_dir(dirname($debugFile))) {
            mkdir(dirname($debugFile), 0o777, true);
        }

        $debug = "=== Middleware Discovery Debug ===\n";
        $debug .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        $debug .= "Scanning: " . self::MIDDLEWARE_PATH . "\n\n";

        // Scan filesystem for middleware files
        $files = glob(self::MIDDLEWARE_PATH . "/*.php");
        $debug .= "PHP files found: " . count($files) . "\n";

        if (empty($files)) {
            $debug .= "⚠️ No PHP files in middleware directory!\n";
            file_put_contents($debugFile, $debug);
            return [];
        }

        $debug .= "\nFiles:\n";
        foreach ($files as $file) {
            $filename = basename($file, '.php');
            if ($filename === 'Interface') {
                continue; // Skip Interface directory
            }

            $class = self::MIDDLEWARE_NAMESPACE . $filename;
            $debug .= "  - $filename => $class\n";

            // Require the file to load the class
            require_once $file;

            if (!class_exists($class)) {
                $debug .= "    ✗ Class doesn't exist after require\n";
                continue;
            }

            try {
                $reflection = new ReflectionClass($class);

                if ($reflection->isAbstract() || $reflection->isInterface()) {
                    $debug .= "    ✗ Abstract/Interface\n";
                    continue;
                }

                if (!$reflection->implementsInterface(MiddlewareInterface::class)) {
                    $debug .= "    ✗ Doesn't implement MiddlewareInterface\n";
                    continue;
                }

                $name = $class::name();
                $debug .= "    ✓ Discovered as '$name'\n";
                $map[$name] = $class;
            } catch (Throwable $e) {
                $debug .= "    ✗ Error: " . $e->getMessage() . "\n";
            }
        }

        $debug .= "\nFinal map: " . count($map) . " middlewares\n";
        file_put_contents($debugFile, $debug);

        return $map;
    }
}
