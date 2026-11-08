<?php

use Goralys\App\Cron\Cron;
use Goralys\App\Cron\Options\RunOption;
use Goralys\Shared\Config\GoralysConfig as Config;
use TemplateRenderer\Renderer;

Cron::job("build-template", function () {
    $raw = @file_get_contents(Config::SUBJECTS_EXPORT::TEMPLATE_DIR_PATH . "preset.json");
    $preset = $raw === false ? [] : json_decode($raw, true, flags: JSON_THROW_ON_ERROR);

    Renderer::buildMetadata($preset);
})
    ->option(...RunOption::once())
    ->schedule()
    ->daily()
    ->at(0, 30);
