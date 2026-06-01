<?php

namespace Goralys\Kernel\Data;

/**
 * DTO used to configure an error response with a message, redirect path, and HTTP status code.
 */
final readonly class ErrorMessageConfig
{
    /**
     * @param string $message The error message.
     * @param string $redirect The url redirect.
     * @param int $code The HTTP response code to send.
     */
    public function __construct(
        public string $message,
        public string $redirect = "/",
        public int $code = 500,
    ) {
    }
}
