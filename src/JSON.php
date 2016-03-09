<?php

  namespace thcolin\Gearman;

  use Exception;

  class JSON{

    public function __construct($json){
      if(!is_file($json)){
        file_put_contents($json, json_encode([]));
      }

      if(!is_writable($json)){
        throw new Exception('Unable to write into "'.basename($json).'" to save jobs');
      }

      $this -> json = $json;
    }

    public function getJSON(){
      $content = file_get_contents($this -> json);

      return (is_null(json_decode($content)) ? '[]':$content);
    }

    public function writeJSON($jobs){
      file_put_contents($this -> json, json_encode($jobs));
    }

  }

?>
