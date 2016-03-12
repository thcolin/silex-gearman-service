<?php

  use Silex\Application;
  use Knp\Provider\ConsoleServiceProvider;
  use thcolin\Gearman\GearmanProvider;

  require __DIR__.'/../vendor/autoload.php';
  require __DIR__.'/vars.php';

  $app = new Application();

  $app -> register(new ConsoleServiceProvider(), [
    'console.name'              => 'GearmanConsole',
    'console.version'           => '1.0.0',
    'console.project_directory' => __DIR__
  ]);

  $app -> register(new GearmanProvider(), [
    'gearman.options' => [
    		"scaleway_key"			=>SCALEWAY_KEY,
    		"scaleway_organization"	=>SCALEWAY_ORGANIZATION,
    		"scaleway_image"		=>SCALEWAY_IMAGE
    ]
  ]);

  return $app;

?>
