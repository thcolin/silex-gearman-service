<?php

	namespace thcolin\Gearman\Tests;

  use PHPUnit_Framework_TestCase;
  use thcolin\Gearman\Job\Job;
  use thcolin\Gearman\Job\JobService;

	class GearmanServiceTests extends PHPUnit_Framework_TestCase{

		public function setUp(){
      $this -> app = require __DIR__.'/bootstrap.php';
    }

    public function testRegister(){
      $this -> assertArrayHasKey('gearman.console', $this -> app);
      $this -> assertArrayHasKey('gearman.client', $this -> app);
      $this -> assertArrayHasKey('gearman.jobs', $this -> app);
      $this -> assertArrayHasKey('gearman.workers', $this -> app);
    }

    public function testAddJobSuccess(){
      $job = new Job('reverse', ['string' => 'Hello World !']);
      $this -> app['gearman.jobs'] -> save($job);

      $jobs = $this -> app['gearman.jobs'] -> jobs();
      $jobs = array_values($jobs);

      $this -> assertCount(1, $jobs);
      $this -> assertInstanceOf('thcolin\Gearman\Job\Job', $jobs[0]);
      $this -> assertEquals($job -> getUUID(), $jobs[0] -> getUUID());
    }

    public function testHireWorkerSuccess(){
      $this -> app['gearman.workers'] -> hire(['thcolin\Gearman\Worker\ReverseWorker']);
      sleep(2);

      $workers = $this -> app['gearman.workers'] -> workers();
      $this -> assertCount(1, $workers);
      $this -> assertInstanceOf('thcolin\Gearman\Worker\Worker', $workers[0]);
    }

    public function testRunJobSuccess(){
      $jobs = $this -> app['gearman.jobs'] -> jobs();

      foreach($jobs as $job){
        $this -> app['gearman.jobs'] -> run($job);
        $job = $this -> app['gearman.jobs'] -> refresh($job);
        $this -> assertEquals('! dlroW olleH', $job -> getResult());
      }
    }

    public function testRunJobBackgroundSuccess(){
      $jobs = $this -> app['gearman.jobs'] -> jobs();

      foreach($jobs as $job){
        $this -> app['gearman.jobs'] -> run($job, JobService::RUN_BACKGROUND);
        sleep(2);

        do{
          $job = $this -> app['gearman.jobs'] -> refresh($job);
          $status = $job -> getStatus();
          sleep(1);
        } while($status['known']);

        $this -> assertEquals('! dlroW olleH', $job -> getResult());
      }
    }

    public function testFireWorkerSuccess(){
      $workers = $this -> app['gearman.workers'] -> workers();

      foreach($workers as $worker){
        $this -> app['gearman.workers'] -> fire($worker);
        sleep(2);
      }

      $workers = $this -> app['gearman.workers'] -> workers();
      $this -> assertCount(0, $workers);
    }

    public function testDeleteJobSuccess(){
      $jobs = $this -> app['gearman.jobs'] -> jobs();

      foreach($jobs as $job){
        $this -> app['gearman.jobs'] -> delete($job -> getUUID());
      }

      $jobs = $this -> app['gearman.jobs'] -> jobs();
      $this -> assertCount(0, $jobs);
    }

	}

?>
