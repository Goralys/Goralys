<?php

use Goralys\App\Cron\Cron;
use Goralys\App\Cron\OptionsRegistry;
use Goralys\App\Router\MiddlewareRegistry;
use Goralys\Kernel\GoralysKernel;

Cron::job("sync-discovery-cache", function (GoralysKernel $kernel) {
    MiddlewareRegistry::discoverMiddlewares(true);
    OptionsRegistry::discoverOptions(true);
})
        ->schedule()
        ->daily()
        ->at(0, 30);
