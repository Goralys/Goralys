<?php

use Goralys\App\Context\Data\CurrentSchool;
use Goralys\Kernel\Data\Enums\KernelType;
use Goralys\Platform\Loader\Services\HighSchoolsService;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../src/Kernel/bootstrap.php";


// Init before kernel
// This is the most barebone part, but it is necessary might need a refactor later.

$schoolsService = new HighSchoolsService();
$schools = $schoolsService->getAllSchools();

CurrentSchool::$CODE = array_key_first($schools);
CurrentSchool::$TOKEN = $schoolsService->getTokenForSchool(CurrentSchool::$CODE);

// Actual dispatch
$kernel = makeKernel(KernelType::CRON);
$jobs = $kernel->jobs;

if (count($argv) < 2) {
    $kernel->logger->fatal(
        LoggerInitiator::CRON,
        "Expected at least two arguments, got: " . count($argv)
    );
    exit(1);
}

// Load the jobs
(require __DIR__ . "/../Cron/jobs.php")();

$name = $argv[1];
$jobs->dispatch($name);
exit(0);
