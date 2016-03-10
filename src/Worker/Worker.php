<?php

  namespace thcolin\Gearman\Worker;

  class Worker{

    protected $id;
    protected $fd;
    protected $server;
    protected $abilities;

    public function __construct($data){
      if($data['id'] == '-'){
        throw new Exception('Non valid worker submited');
      }

      $this -> setId($data['id']);
      $this -> setFd($data['fd']);
      $this -> setServer($data['ip']);
      $this -> setAbilities($data['abilities']);
    }

    public function getId(){
      return $this -> id;
    }

    public function setId($id){
      $this -> id = $id;
    }

    public function getPid(){
      return explode('-', $this -> getId())[1];
    }

    public function getFd(){
      return $this -> fd;
    }

    public function setFd($fd){
      $this -> fd = $fd;
    }

    public function getServer(){
      return $this -> server;
    }

    public function setServer($server){
      $this -> server = $server;
    }

    public function getAbilities(){
      return $this -> abilities;
    }

    public function setAbilities(array $abilities){
      $this -> abilities = $abilities;
    }

  }

?>
