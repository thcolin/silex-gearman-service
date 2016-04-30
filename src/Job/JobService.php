<?php

  namespace thcolin\Gearman\Job;

  use GearmanClient;
  use thcolin\Gearman\Command\RunJobCommand;
  use thcolin\Gearman\ConsoleAsync;
  use thcolin\Gearman\JSON;
  use Exception;

  class JobService{

    const REFRESH = true;

    const RUN_NORMAL = 'normal';
    const RUN_BACKGROUND = 'background';

    public function __construct(ConsoleAsync $console, GearmanClient $client, JSON $json){
      $this -> console = $console;
      $this -> client = $client;
      $this -> json = $json;
    }

    public function jobs($refresh = false){
      $json = $this -> json -> getJSON();
      $array = json_decode($json, true);
      $jobs = [];

      foreach($array as $value){
        $job = Job::unserialize($value);
        if($refresh){
          $job = $this -> refresh($job);
        }
        $jobs[$job -> getUUID()] = $job;
      }

      $this -> json -> writeJSON($jobs);

      return $jobs;
    }

    public function job($uuid){
      $jobs = $this -> jobs();

      foreach($jobs as $key => $job){
        if($job -> getUUID() == $uuid){
          return $job;
        }
      }

      throw new Exception('Unknown job with UUID "'.$uuid.'"');
    }

    public function refresh(Job $job){
      // refresh "result" too
      $job = $this -> job($job -> getUUID());
      $status = $this -> status($job);
      $job -> setStatus($status);
      $this -> save($job);
      return $job;
    }

    private function status(Job $job){
      $status = $this -> client -> jobStatus($job -> getJobHandler());

      return [
        'known' => ($status[0] ? true:false),
        'running' => ($status[1] ? true:false),
        'numerator' => $status[2],
        'denominator' => $status[3]
      ];
    }

    public function save(Job $job){
      $jobs = $this -> jobs();
      $jobs[$job -> getUUID()] = $job;
      $this -> json -> writeJSON($jobs);
    }

    public function delete($uuid){
      $jobs = $this -> jobs();
      if(isset($jobs[$uuid])){
        unset($jobs[$uuid]);
      }
      $this -> json -> writeJSON($jobs);
    }

    public function run(Job $job, $run = self::RUN_NORMAL, $args = [], $options = []){
      try{
        $this -> job($job -> getUUID());
      } catch(Exception $e){
        $this -> save($job);
      }

      if($run == self::RUN_BACKGROUND){
        // the command run and save the job after his creation in gearman
        // with the jobhandler to, next, get the status
        $args['uuid'] = $job -> getUUID();
        $process = $this -> console -> execute(new RunJobCommand(), $args, $options);
      } else{
        $result = $this -> client -> doNormal($job -> getTask(), $job -> getWorkload(Job::WORKLOAD_JSON));
        $job -> setResult($result);
        $this -> save($job);
      }
    }

  }

?>
