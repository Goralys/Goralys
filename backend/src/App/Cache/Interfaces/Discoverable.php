<?php

namespace Goralys\App\Cache\Interfaces;

interface Discoverable
{
    /**
     * Returns the name to use when discovering the class.
     * @return string The name of the class.
     */
    public static function name(): string;
}
