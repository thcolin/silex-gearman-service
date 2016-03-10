<?php

  namespace thcolin\Gearman\Worker;

  use MHlavac\Gearman\Manager as GearmanManager;
  use thcolin\Gearman\Command\HireWorkerCommand;
  use thcolin\Gearman\ConsoleAsync;
  use thcolin\Gearman\JSON;
  use Exception;

  class WorkerService{

    const WORKER_LOCAL = 'local';
    const WORKER_SCALEWAY = 'scaleway';

    public function __construct(ConsoleAsync $console, GearmanManager $manager){
      $this -> console = $console;
      $this -> manager = $manager;
    }

    public function workers(){
      $workers = [];
      foreach($this -> manager -> workers() as $worker){
        if($worker['id'] != '-'){
          $workers[] = new Worker($worker);
        }
      }
      return $workers;
    }

    public function hire($classes, $worker = self::WORKER_LOCAL){
      if(is_string($classes)){
        $classes = [$classes];
      }

      if($worker == self::WORKER_LOCAL){
        $process = $this -> console -> execute(new HireWorkerCommand(), [
          'classes' => $classes
        ], [
          'type' => self::WORKER_LOCAL
        ]);
      } else{

      }
    }

    public function fire(Worker $worker){
      posix_kill($worker -> getPid(), SIGKILL);
    }
  }

?>
