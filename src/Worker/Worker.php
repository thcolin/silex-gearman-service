<?php

  namespace thcolin\Gearman\Worker;

  class Worker{

    const LOCAL = 'local';
    const SCALEWAY = 'scaleway';

    protected $id;
    protected $fd;
    protected $address;
    protected $abilities;

    public function __construct($data){
      if($data['id'] == '-'){
        $data['id'] = self::SCALEWAY;
      }

      $this -> setId($data['id']);
      $this -> setFd($data['fd']);
      $this -> setAddress($data['ip']);
      $this -> setAbilities($data['abilities']);
    }

    public function getId(){
      return $this -> id;
    }

    public function setId($id){
      $this -> id = $id;
    }

    public function getType(){
      return ($this -> getAddress() == '127.0.0.1' ? self::LOCAL:self::SCALEWAY);
    }

    public function getPid(){
      return $this -> getId();
    }

    public function getFd(){
      return $this -> fd;
    }

    public function setFd($fd){
      $this -> fd = $fd;
    }

    public function getAddress(){
      return $this -> address;
    }

    public function setAddress($address){
      $this -> address = $address;
    }

    public function getAbilities(){
      return $this -> abilities;
    }

    public function setAbilities(array $abilities){
      $this -> abilities = $abilities;
    }

  }

?>
