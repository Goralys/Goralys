<?php

use Goralys\App\Cron\Cron;
use Goralys\App\Cron\Options\DbOption;
use Goralys\Kernel\GoralysKernel;

Cron::job("sync-usernames", fn (GoralysKernel $kernel) => $kernel->users->syncUsernames())
    ->option(...DbOption::connect())
    ->schedule()
    ->weekly()
    ->at(0, 30);
