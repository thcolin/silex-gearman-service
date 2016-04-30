<?php

  namespace thcolin\Gearman\Worker;

  use MHlavac\Gearman\Manager as GearmanManager;
  use thcolin\Gearman\Command\HireWorkerCommand;
  use thcolin\Gearman\Scaleway\ScalewayService;
  use thcolin\Gearman\ConsoleAsync;
  use thcolin\Gearman\JSON;
  use Exception;

  class WorkerService{

    protected $console;
    protected $manager;
    protected $scaleway;

    public function __construct(ConsoleAsync $console, ScalewayService $scaleway, GearmanManager $manager){
      $this -> console = $console;
      $this -> scaleway = $scaleway;
      $this -> manager = $manager;
    }

    public function workers(){
      $workers = [];
      foreach($this -> manager -> workers() as $worker){
        if(count($worker['abilities'])){
          $workers[] = new Worker($worker);
        }
      }
      return $workers;
    }

    public function hire($classes, $worker = Worker::LOCAL){
      if(is_string($classes)){
        $classes = [$classes];
      }

      if($worker == Worker::LOCAL){
        $process = $this -> console -> execute(new HireWorkerCommand(), [
          'classes' => $classes
        ], [
          'type' => Worker::LOCAL
        ]);
      } else{
        $this -> scaleway -> boot($classes);
      }
    }

    public function fire(Worker $worker){
      switch($worker -> getType()){
        case Worker::LOCAL:
          posix_kill($worker -> getPid(), SIGKILL);
        break;
        case Worker::SCALEWAY:
          $this -> scaleway -> shutdown($worker -> getAddress());
        break;
      }
    }
  }

?>
