<?php

use Goralys\App\Context\Data\CurrentSchool;
use Goralys\Kernel\Data\Enums\KernelType;
use Goralys\Platform\Loader\Services\HighSchoolsService;
use Goralys\Platform\Logger\Data\Enums\LoggerInitiator;

require __DIR__ . "/../../vendor/autoload.php";
require __DIR__ . "/../../src/Kernel/bootstrap.php";

$schoolsService = new HighSchoolsService();
$schools = $schoolsService->getAllSchools();

CurrentSchool::$CODE = array_key_first($schools);
CurrentSchool::$TOKEN = $schoolsService->getTokenForSchool(CurrentSchool::$CODE);
$kernel = makeKernel(KernelType::CRON);
$router = $kernel->router;

foreach ($kernel->highSchools->getAllSchools() as $code => $_) {
    CurrentSchool::$CODE = $code;
    CurrentSchool::$TOKEN = $kernel->highSchools->getTokenForSchool(CurrentSchool::$CODE);

    $kernel->db->connect($kernel->highSchools->getDbForSchool(CurrentSchool::$TOKEN));
    if (!$kernel->users->syncUsernames()) {
        $kernel->logger->error(LoggerInitiator::CRON, "Failed to sync usernames");
    }
}
