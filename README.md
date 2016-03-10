# Gearman Silex Service

PHP library to facilitate the use of Gearman bin.

The library use two majors service **gearman.jobs** to manage jobs, and **gearman.workers** to manage workers.

## Installation
Install with composer :
```
composer require thcolin/silex-gearman-service
```

## Example :
Assuming a simple architecture :
* ```bootstrap.php``` who will create, configure and return our Silex ```$app```
* ```lifecycle.php``` who will just make the life cycle of a job and worker
* ```console.php``` who will be executable and able us to run job in background

```php
/*
 * bootstrap.php
 */
use Silex\Application;
use Knp\Provider\ConsoleServiceProvider;
use thcolin\Gearman\GearmanProvider;

require __DIR__.'/vendor/autoload.php';

$app = new Application();

// used to launch job in background
$app -> register(new ConsoleServiceProvider(), [
  'console.name'              => 'GearmanConsole',
  'console.version'           => '1.0.0',
  'console.project_directory' => __DIR__
]);

$app -> register(new GearmanProvider(), [
  'gearman.options' => [
    'server' => '127.0.0.1:4730', // gearman server
    'json' => __DIR__.'/jobs.json', // json file used to save jobs
    'console' => __DIR__.'/console' // console to launch background job
  ]
]);

return $app;
```

```php
/*
 * lifecycle.php : JobService & WorkerService usage
 */

use thcolin\Gearman\Job\Job;
use thcolin\Gearman\Job\JobService;

$app = require __DIR__.'/bootstrap.php';

// Fire olds workers
foreach($app['gearman.workers'] -> workers() as $worker){
  $app['gearman.workers'] -> fire($worker);
  echo "Worker <".$worker -> getId()."> fired.\n";
}

// Hire new <ReverseWorker> worker
$app['gearman.workers'] -> hire(['thcolin\Gearman\Worker\ReverseWorker']);
echo "Worker <ReverseWorker> hired.\n\n";

// RUN_NORMAL (will display the job with the result once finished)
$job = new Job('reverse', ['string' => 'Hello World !']);
echo "Running Job <".$job -> getUUID().">...\n";
$app['gearman.jobs'] -> run($job, JobService::RUN_NORMAL);
$job = $app['gearman.jobs'] -> refresh($job);
echo "Result Job <".$job -> getUUID()."> : ".$job -> getResult()."\n\n";

// RUN_BACKGROUND (check with the "WatchTaskCommand" on the console and the UUID of the job)
$job = new Job('reverse', ['string' => 'Hello World !']);
echo "Running Job <".$job -> getUUID()."> in background\n";
$app['gearman.jobs'] -> run($job, JobService::RUN_BACKGROUND);
```


```php
/*
 * console.php : Console example
 */

use Knp\Provider\ConsoleServiceProvider;
use thcolin\Gearman\GearmanProvider;

$app = require __DIR__.'/bootstrap.php';

$console = $app['console'];
$console -> add(new thcolin\Gearman\Command\FireWorkerCommand);
$console -> add(new thcolin\Gearman\Command\HireWorkerCommand);
$console -> add(new thcolin\Gearman\Command\AddJobCommand);
/* Without a console with "RunJobCommand" you will not be able to launch job with JobService::RUN_BACKGROUND */
$console -> add(new thcolin\Gearman\Command\RunJobCommand);
$console -> add(new thcolin\Gearman\Command\WatchJobCommand);
$console -> add(new thcolin\Gearman\Command\DeleteJobCommand);
$console -> run();
```

## Todo
* Scaleway worker integration
* Worker abstract class (actually just a ```ReverseWorker``` to test)
