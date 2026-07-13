<?php

$var = parse_ini_file(__DIR__ . "/Assets/schools_list", true);

echo print_r($var["0492061z"]["NAME"], true);
