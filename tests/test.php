<?php

  use thcolin\Gearman\Job;
  use thcolin\Gearman\JobService;

  $app = require __DIR__.'/bootstrap.php';

  // RUN_NORMAL (will display the job with the result once finished)
  $job = new Job('reverse', ['string' => 'Hello World !']);
  $app['gearman.jobs'] -> run($job, JobService::RUN_NORMAL);
  $job = $app['gearman.jobs'] -> refresh($job);
  print_r($job);

  // RUN_BACKGROUND (check with the "WatchTaskCommand" on the console and the UUID of the job)
  $job = new Job('reverse', ['string' => 'Hello World !']);
  $app['gearman.jobs'] -> run($job, JobService::RUN_BACKGROUND);
  print_r($job);

?>
