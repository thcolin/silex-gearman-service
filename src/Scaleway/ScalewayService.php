<?php

  namespace thcolin\Gearman\Scaleway;

  use thcolin\Gearman\ConsoleAsync;
  use thcolin\Gearman\Command\HireWorkerCommand;

  /*
   * TODO : Rewrite this class, not working actually
   * get a new HTTP client to call the scaleway api
   * or get a good PHP implementation of it
   */
  class ScalewayService{

    const API_URL = 'https://api.scaleway.com';
    const DEFAULT_USER = 'root';

    protected $key;
    protected $client;
    protected $organization;
    protected $image;

    protected $keys;
    protected $bootstrap;
    protected $console;

    public function __construct($key, $organization, $image, $keys, $bootstrap, $console){
      $this -> key = $key;
      $this -> organization = $organization;
      $this -> image = $image;

      $this -> keys = $keys;
      $this -> bootstrap = $bootstrap;
      $this -> console = $console;
    }

    public function boot($classes){
    	$res = $this -> client -> request('POST', self::API_URL.'/servers', [
        'http_errors' => false,
    			'json' => [
  					'organization' => $this -> organization,
  					'name' => uniqid().'-'.getmypid(),
  					'image' => $this -> image,
            'tags' => ['gearman']
    			]
    	]);

    	$raw = json_decode($res -> getBody(), true);
      $server = $raw['server'];

  		$res = $this -> client -> request('POST', self::API_URL.'/servers/'.$server['id'].'/action', [
        'http_errors' => false,
				'json' => [
					'action' => 'poweron'
				]
  		]);

      $raw = json_decode($res -> getBody(), true);
      $host = new Ping($server['public_ip']);

      do{
        $latency = $host -> ping();
        sleep(1);
      }
      while(!$latency);

      $consoleAsync = new ConsoleAsync($this -> console);

      $bootstrap = tempnam();
      file_put_contents($bootstrap, json_encode($this -> getBootstrap()));

      $connection = ssh2_connect($server['public_ip'], 22);
      ssh2_auth_pubkey_file($connection, self::DEFAULT_USER, $this -> keys['public'], $this -> keys['private']);
      ssh2_spc_send($connection, $tmp, $this -> bootstrap);
      ssh2_exec($session, $consoleAsync -> getCommandString(new HireWorkerCommand(), [
        'classes' => $classes
      ], [
        'type' => Worker::LOCAL
      ]));
    }

    private function getBootstrap(){
      // d'où je choper le serveur local ?
      return [
        'server' => $server
      ];
    }

    public function shutdown($ip){
      $res = $this -> client -> request('GET', self::API_URL.'/servers');
      $raw = json_decode($res -> getBody(), true);

      foreach($raw['servers'] as $server){
        if($server['public_ip'] == $ip){
          $res = $this -> client -> request('POST', self::API_URL.'/servers/'.$server['id'].'/action', [
            'http_errors' => false,
            'json' => [
              'action' => 'poweroff'
            ]
          ]);
        }
      }
    }

  }

?>
