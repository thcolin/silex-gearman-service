<?php

  use Silex\Application;
  use Knp\Provider\ConsoleServiceProvider;
  use thcolin\Gearman\GearmanProvider;

  require __DIR__.'/../vendor/autoload.php';

  $app = new Application();

  $app -> register(new ConsoleServiceProvider(), [
    'console.name'              => 'GearmanConsole',
    'console.version'           => '1.0.0',
    'console.project_directory' => __DIR__
  ]);

  $app -> register(new GearmanProvider(), [
    'gearman.options' => []
  ]);

  return $app;

?>
