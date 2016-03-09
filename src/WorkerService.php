<?php

  namespace thcolin\Gearman;

  use MHlavac\Gearman\Manager as GearmanManager;
  use Exception;

  class WorkerService{

    const WORKER_LOCAL = 1;
    const WORKER_SCALEWAY = 2;

    public function __construct(GearmanManager $manager){
      $this -> manager = $manager;
    }

    public function workers(){
      return $this -> manager -> workers();
    }

    public function hire($args, $worker = Worker::WORKER_LOCAL){
      var_dump($args);
    }

    //public function fire(){}

  }

?>
