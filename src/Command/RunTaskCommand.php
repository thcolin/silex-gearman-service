<?php

  namespace thcolin\Gearman\Command;

  use Knp\Command\Command;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;
  use thcolin\Gearman\Job;
  use Exception;

  class RunTaskCommand extends Command{

    protected function configure(){
      $this
        -> setName('run-task')
        -> setDescription('Run the task passed in argument')
        -> addArgument('uuid', InputArgument::REQUIRED, 'UUID of the task to be run');
    }

    protected function execute(InputInterface $input, OutputInterface $output){
      $uuid = $input -> getArgument('uuid');
      $app = $this -> getSilexApplication();

      $job = $app['gearman.jobs'] -> job($uuid);

      // save the jobHandler to monitor the job
      $app['gearman.client'] -> setCreatedCallback(function($task) use ($app, $job, $output){
        $output -> writeln('Task <info>['.$job -> getUUID().']</info> added and runned : <comment>'.$task -> jobHandle().'</comment>');
        $job -> setJobHandler($task -> jobHandle());
        $app['gearman.jobs'] -> save($job);
      });

      // save the result of the job
      $app['gearman.client'] -> setCompleteCallback(function($task) use ($app, $job, $output){
        $output -> writeln('Task <info>['.$job -> getUUID().']</info> done : <comment>'.$task -> data().'</comment>');
        $job -> setResult($task -> data());
        $app['gearman.jobs'] -> save($job);
      });

      // add the task & run it
      $app['gearman.client'] -> addTask($job -> getTask(), $job -> getWorkload(Job::WORKLOAD_JSON));
      $app['gearman.client'] -> runTasks();
    }

  }

?>
