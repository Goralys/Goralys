<?php

namespace TemplateRenderer;

final class Renderer
{
    /**
     * Builds the metadata (preprocessing) for the given template.
     * @param array $settings The template configuration's settings. See the README.md file on github for more info
     * about the expected structure.
     * @return void
     * @throws RendererException If the renderer cannot find the binary to execute, or if the execution fails.
     * @link https://github.com/Goralys/Goralys
     */
    public static function buildMetadata(array $settings): void
    {
        self::run("build", $settings + ["jobs" => []]);
    }

    /**
     * Renders a batch of jobs.
     * @param array $batch The jobs to render along with their settings:
     * @return void
     * @throws RendererException If the renderer cannot find the binary to execute, or if the execution fails.
     * @link https://github.com/Goralys/Goralys
     */
    public static function render(array $batch): void
    {
        self::run("render", $batch);
    }

    /**
     * Runs the renderer executable with the given options.
     * @param string $cmd The command to execute.
     * @param array $payload The batch to run the renderer on.
     * @return void
     * @throws RendererException If the renderer execution fails.
     */
    private static function run(string $cmd, array $payload): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), "goralys-");

        try {
            file_put_contents(
                $tmpFile,
                json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            );
        } catch (\Exception $e) {
            throw new RendererException("Error during json encoding: " . $e->getMessage());
        }

        try {
            $proc = proc_open(
                [self::binary(), $cmd, $tmpFile],
                [1 => ["pipe", "w"], 2 => ["pipe", "w"]],
                $pipes
            );
            if (!is_resource($proc)) {
                throw new RendererException("Cannot start: " . self::binary());
            }

            $out = stream_get_contents($pipes[1]);
            $err = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            if (($code = proc_close($proc)) !== 0) {
                throw new RendererException(($err ?: $out) ?: "Renderer terminated with exit code: $code");
            }
        } finally {
            @unlink($tmpFile);
        }
    }

    /**
     * Figures out which binary to use for your machine.
     * @return string The path to the exact binary.
     * @throws RendererException If your machine is not supported (e.g. Apple).
     */
    private static function binary(): string
    {
        $os = match (PHP_OS_FAMILY) {
            "Linux" => "linux",
            "Windows" => "windows",
            default => throw new RendererException("Unsupported OS: " . PHP_OS_FAMILY)
        };

        $arch = in_array(php_uname("m"), ["x86_64", "AMD64"])
                ? "x64"
                : throw new RendererException("Unsupported CPU architecture: " . php_uname("m"));

        $bin = dirname(__DIR__) . "/bin/$os/$arch/goralys-template-renderer" . ($os === "linux" ? ".so" : ".exe");

        if ($os === "linux" && !is_executable($bin)) {
            @chmod($bin, 0755);
        }

        return $bin;
    }
}
