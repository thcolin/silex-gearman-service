<?php

  namespace thcolin\Gearman;

  use Silex\Application;
  use Silex\ServiceProviderInterface;
  use Silex\ControllerProviderInterface;

  use GearmanClient;
  use MHlavac\Gearman\Manager as GearmanManager;

  class GearmanProvider implements ServiceProviderInterface, ControllerProviderInterface{

    /**
     * Define the services on the applications (should be registered)
     * @method register
     * @param  Application $app
     * @return void
     */
    public function register(Application $app){
      $app['gearman.options.init'] = $app -> protect(function() use ($app){
          $app['gearman.options'] = (isset($app['gearman.options']) ? $app['gearman.options']:[]);
          $app['gearman.options'] = array_replace_recursive([
            'server' => '127.0.0.1:4730',
            'json' => __DIR__.'/../jobs.json',
            'console' => __DIR__.'/../tests/console'
          ], $app['gearman.options']);
      });

      $app['gearman.client'] = $app -> share(function() use($app){
        $app['gearman.options.init']();
        $client = new GearmanClient();
        $client -> addServers(implode(',', [$app['gearman.options']['server']]));
        return $client;
      });

      $app['gearman.jobs'] = $app -> share(function() use ($app){
        $app['gearman.options.init']();
        $console = new ConsoleAsync($app['gearman.options']['console']);
        $json = new JSON($app['gearman.options']['json']);
        return new JobService($console, $app['gearman.client'], $json);
      });

      $app['gearman.workers'] = $app -> share(function() use ($app){
        $app['gearman.options.init']();
        $manager = new GearmanManager($app['gearman.options']['server']);
        return new WorkerService($manager);
      });
    }

    /**
     * Configure the application before it handle a request
     * @method boot
     * @param  Application $app
     * @return void
     */
    public function boot(Application $app){
      $app['gearman.options.init']();
    }

    /**
     * Define controllers routes (should be mounted)
     * @method connect
     * @param  Application $app
     * @return ControllerCollection
     */
    public function connect(Application $app){
      $controllers = $app['controllers_factory'];

      $accountController = 'thcolin\Gearman\GearmanController';
      $controllers -> get('/jobs', $accountController.'::listJobs') -> bind('gearman.list');

      return $controllers;
    }

  }

?>
