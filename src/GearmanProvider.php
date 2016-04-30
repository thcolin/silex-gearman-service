<?php

  namespace thcolin\Gearman;

  use Silex\Application;
  use Silex\ServiceProviderInterface;
  use Silex\ControllerProviderInterface;

  use GearmanClient;
  use MHlavac\Gearman\Manager as GearmanManager;

  use thcolin\Gearman\Scaleway\ScalewayService;
  use thcolin\Gearman\Job\JobService;
  use thcolin\Gearman\Worker\WorkerService;
  use thcolin\Gearman\ConsoleAsync;

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
            'scaleway' => [
              'key' => 'YOUR_API_KEY',
              'organization' => 'YOUR_ORGANIZATION_KEY',
              'image' => 'YOUR_IMAGE_KEY'
            ],
            'workers' => [
              'local' => [
                'console' => __DIR__.'/../tests/console'
              ],
              'scaleway' => [
                'keys' => [
                  'public' => __DIR__.'/../tests/id_rsa.pub',
                  'private' => __DIR__.'/../tests/ida_rsa'
                ],
                'bootstrap' => '/var/www/silex-gearman-service/tests/bootstrap.json',
                'console' => '/var/www/silex-gearman-service/tests/console'
              ]
            ]
          ], $app['gearman.options']);
      });

      $app['gearman.console'] = $app -> share(function() use ($app){
        $console = new ConsoleAsync($app['gearman.options']['workers']['local']['console']);
        return $console;
      });

      $app['gearman.scaleway'] = $app -> share(function() use ($app){
        $scaleway = new ScalewayService(
          $app['gearman.options']['scaleway']['key'],
          $app['gearman.options']['scaleway']['organization'],
          $app['gearman.options']['scaleway']['image'],
          $app['gearman.options']['workers']['scaleway']['keys'],
          $app['gearman.options']['workers']['scaleway']['bootstrap'],
          $app['gearman.options']['workers']['scaleway']['console']
        );
        return $scaleway;
      });

      $app['gearman.client'] = $app -> share(function() use ($app){
        $app['gearman.options.init']();
        $client = new GearmanClient();
        $client -> addServers(implode(',', [$app['gearman.options']['server']]));
        return $client;
      });

      $app['gearman.jobs'] = $app -> share(function() use ($app){
        $app['gearman.options.init']();
        $json = new JSON($app['gearman.options']['json']);
        return new JobService($app['gearman.console'], $app['gearman.client'], $json);
      });

      $app['gearman.workers'] = $app -> share(function() use ($app){
        $app['gearman.options.init']();
        $manager = new GearmanManager($app['gearman.options']['server']);
        return new WorkerService($app['gearman.console'], $app['gearman.scaleway'], $manager);
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
