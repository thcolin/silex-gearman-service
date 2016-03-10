<?php

  namespace thcolin\Gearman\Command;

  use Knp\Command\Command;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;
  use Symfony\Component\Console\Question\ChoiceQuestion;
  use thcolin\Gearman\Job\Job;
  use Exception;

  class RunJobCommand extends Command{

    protected function configure(){
      $this
        -> setName('run-job')
        -> setDescription('Run the job passed in argument')
        -> addArgument('uuid', InputArgument::OPTIONAL, 'UUID of the job to be run');
    }

    protected function execute(InputInterface $input, OutputInterface $output){
      $uuid = $input -> getArgument('uuid');
      $app = $this -> getSilexApplication();

      if($uuid != null){
        $job = $app['gearman.jobs'] -> job($uuid);
      } else {
        $helper = $this -> getHelper('question');

        $jobs = $app['gearman.jobs'] -> jobs();
        $jobs = array_values($jobs);

        $opportunities = [];
        foreach($jobs as $job){
          $opportunities[] = '<comment>'.$job -> getUUID().'</comment> - <info>'.$job -> getTask().'</info> '.$job -> getWorkload(Job::WORKLOAD_JSON).($job -> getResult() ? ' : <question>'.$job -> getResult().'</question>':null);
        }

        if(!$opportunities){
          throw new Exception('No jobs running to watch');
        }

        $whichJob = new ChoiceQuestion('Which job do you want to watch ?', $opportunities, null);
        $answer = $helper -> ask($input, $output, $whichJob);

        $key = array_search($answer, $opportunities);
        $job = $jobs[$key];
      }

      // save the jobHandler to monitor the job
      $app['gearman.client'] -> setCreatedCallback(function($task) use ($app, $job, $output){
        $output -> writeln('Job <info>['.$job -> getUUID().']</info> runned : <comment>'.$task -> jobHandle().'</comment>');
        $job -> setJobHandler($task -> jobHandle());
        $app['gearman.jobs'] -> save($job);
      });

      // save the result of the job
      $app['gearman.client'] -> setCompleteCallback(function($task) use ($app, $job, $output){
        $output -> writeln('Job <info>['.$job -> getUUID().']</info> done : <comment>'.$task -> data().'</comment>');
        $job -> setResult($task -> data());
        $app['gearman.jobs'] -> save($job);
      });

      // add the task & run it
      $app['gearman.client'] -> addTask($job -> getTask(), $job -> getWorkload(Job::WORKLOAD_JSON));
      $app['gearman.client'] -> runTasks();
    }

  }

?>
