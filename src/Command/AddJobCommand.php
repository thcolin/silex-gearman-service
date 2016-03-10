<?php

  namespace thcolin\Gearman\Command;

  use Knp\Command\Command;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\ArrayInput;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;
  use Symfony\Component\Console\Question\Question;
  use Symfony\Component\Console\Question\ChoiceQuestion;
  use Symfony\Component\Console\Question\ConfirmationQuestion;
  use thcolin\Gearman\Job\JobService;
  use thcolin\Gearman\Job\Job;
  use Exception;

  class AddJobCommand extends Command{

    protected function configure(){
      $this
        -> setName('add-job')
        -> setDescription('Run the job passed in argument')
        -> addArgument('task', InputArgument::OPTIONAL, 'Task the job will execute')
        -> addArgument('workload', InputArgument::OPTIONAL, 'JSON workload the job will use to execute the task');
    }

    protected function execute(InputInterface $input, OutputInterface $output){
      $helper = $this -> getHelper('question');
      $task = $input -> getArgument('task');
      $workload = $input -> getArgument('workload');
      $helper = $this -> getHelper('question');
      $app = $this -> getSilexApplication();

      if($task == null){
        $whichTask = new Question('Which task the job will execute ? ', null);
        $task = $helper -> ask($input, $output, $whichTask);
      }

      if($workload == null){
        $whichWorkload = new Question('Enter the workload of the job (JSON) : ', null);
        do{
          $json = $helper -> ask($input, $output, $whichWorkload);
        } while(!json_decode($json, true));
        $workload = json_decode($json, true);
      }

      $job = new Job($task, $workload);
      $app['gearman.jobs'] -> save($job);
      $output -> writeln('Job added : <comment>'.$job -> getUUID().'</comment> - <info>'.$job -> getTask().'</info> '.$job -> getWorkload(Job::WORKLOAD_JSON));

      $howRunIt = new ChoiceQuestion('How do you want to run it ?', [JobService::RUN_NORMAL, JobService::RUN_BACKGROUND], 0);
      $how = $helper -> ask($input, $output, $howRunIt);

      $args = new ArrayInput(['uuid' => $job -> getUUID()]);

      if($how == JobService::RUN_NORMAL){
        $command = new RunJobCommand();
        $command -> setApplication($app['console']);
        $command -> run($args, $output);
      } else if($how == JobService::RUN_BACKGROUND){
        $app['gearman.jobs'] -> run($job, JobService::RUN_BACKGROUND);
        $showBackground = new ConfirmationQuestion('Do you want to watch his progress ? [Y/n] ');
        if($helper -> ask($input, $output, $showBackground)){
          $command = new WatchJobCommand();
          $command -> setApplication($app['console']);
          $command -> run($args, $output);
        }
      }    
    }

  }

?>
