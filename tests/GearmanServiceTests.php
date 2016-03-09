<?php

  namespace thcolin\Gearman\Tests;

  use PHPUnit_Framework_TestCase;

  use Exception;

  class GearmanServiceTests extends PHPUnit_Framework_TestCase{

    public function setUp(){
      $this -> app = require __DIR__.'/bootstrap.php';
    }

    public function testRegister(){
      $this -> assertArrayHasKey('gearman.jobs', $this -> app);
      $this -> assertArrayHasKey('gearman.workers', $this -> app);
    }

    public function testAddJobSuccess(){
      $job = $this -> app['gearman.jobs'] -> add('test', ['string' => 'hello']);
      $this -> assertInstanceOf('thcolin\Gearman\Job', $job);
    }

    public function testGetJobsSuccess(){
      $jobs = $this -> app['gearman.jobs'] -> jobs();
      var_dump($jobs);
    }

    public function testGetWorkersSuccess(){
      $workers = $this -> app['gearman.workers'] -> workers();
      var_dump($workers);
    }

    public function testHireWorkerSuccess(){
      $worker = $this -> app['gearman.workers'] -> hire();
    }

  }

?>
