<?php

require __DIR__ . '/../vendor/autoload.php';

$app = new \Logg\Application();

$app->add(new \Logg\Commands\Parse());

$app->run();
