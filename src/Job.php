<?php

  namespace thcolin\Gearman;

  use JsonSerializable;
  use Exception;

  class Job implements JsonSerializable{

    const WORKLOAD_ORIGINAL = 1;
    const WORKLOAD_JSON = 2;

    protected $uuid;
    protected $task;
    protected $workload;
    protected $jobhandler = null;
    protected $status = null;
    protected $result = null;

    public function __construct($task, $workload){
      $this -> setTask($task);
      $this -> setWorkload($workload);
      $this -> setUUID();
    }

    public function getUUID(){
      return $this -> uuid;
    }

    public function setUUID($uuid = null){
      $this -> uuid = ($uuid ? $uuid:uniqid());
    }

    public function getJobHandler(){
      return $this -> jobhandler;
    }

    public function setJobHandler($jobhandler){
      $this -> jobhandler = $jobhandler;
    }

    public function setTask($task){
      $this -> task = $task;
    }

    public function getTask(){
      return $this -> task;
    }

    public function setWorkload($workload){
      $this -> workload = $workload;
    }

    public function getWorkload($encode = Job::WORKLOAD_ORIGINAL){
      switch($encode){
        case Job::WORKLOAD_JSON:
          return json_encode($this -> workload);
        break;
        default:
          return $this -> workload;
        break;
      }
    }

    public function setStatus($status){
      $this -> status = $status;
    }

    public function getStatus(){
      return $this -> status;
    }

    public function setResult($result){
      $this -> result = $result;
    }

    public function getResult(){
      return $this -> result;
    }

    public function jsonSerialize(){
      return [
        'uuid' => $this -> getUUID(),
        'task' => $this -> getTask(),
        'jobhandler' => $this -> getJobHandler(),
        'workload' => $this -> getWorkload(),
        'status' => $this -> getStatus(),
        'result' => $this -> getResult()
      ];
    }

    public static function unserialize($array){
      $job = new Job($array['task'], $array['workload']);

      $job -> setUUID($array['uuid']);

      if(isset($array['jobhandler'])){
        $job -> setJobHandler($array['jobhandler']);
      }

      if(isset($array['status'])){
        $job -> setStatus($array['status']);
      }

      if(isset($array['result'])){
        $job -> setResult($array['result']);
      }

      return $job;
    }

  }

?>
