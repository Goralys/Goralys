<?php

namespace Goralys\App\Cron\Data;

final readonly class Option
{
    public function __construct(
        public string $name,
        public array $params,
    ) {
    }
}
