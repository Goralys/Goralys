<?php

use Goralys\App\Context\Data\CurrentSchool;
use Goralys\Kernel\Data\Enums\KernelType;
use Goralys\Platform\Loader\Services\HighSchoolsService;

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

// Load the jobs
(require __DIR__ . "/../Cron/jobs.php")();
$jobs->runDue();
exit(0);
