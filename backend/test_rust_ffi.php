<?php

require __DIR__ . "/vendor/autoload.php";

use TemplateRenderer\Renderer;

$settings = [
        "max_threads" => 1,
        "template_dir" => __DIR__ . "/Assets/Template/",
        "base_font" => "Marianne",
    "pdfium" => "lib",
    "logo_file" => "logo-nantes.jpg"
];

Renderer::buildMetadata($settings);
