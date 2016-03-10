<?php

  namespace thcolin\Gearman\Command;

  use Knp\Command\Command;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;
  use Symfony\Component\Console\Helper\ProgressBar;
  use Symfony\Component\Console\Question\ChoiceQuestion;
  use thcolin\Gearman\Job\JobService;
  use thcolin\Gearman\Job\Job;
  use Exception;

  class WatchJobCommand extends Command{

    protected function configure(){
      $this
        -> setName('watch-job')
        -> setDescription('List the jobs, and watch the selected')
        -> addArgument('uuid', InputArgument::OPTIONAL, 'UUID of the job to watch');
    }

    protected function execute(InputInterface $input, OutputInterface $output){
      $uuid = $input -> getArgument('uuid');
      $app = $this -> getSilexApplication();

      if($uuid != null){
        $job = $app['gearman.jobs'] -> job($uuid);
      } else {
        $helper = $this -> getHelper('question');

        $jobs = [];

        $opportunities = [];
        foreach($app['gearman.jobs'] -> jobs(JobService::REFRESH) as $key => $job){
          if($job -> getStatus()['known']){
            $jobs[] = $job;
            $opportunities[] = '<comment>'.$job -> getUUID().'</comment> - <info>'.$job -> getTask().'</info> '.$job -> getWorkload(Job::WORKLOAD_JSON).($job -> getResult() ? ' : <question>'.$job -> getResult().'</question>':null);
          }
        }

        if(!$opportunities){
          throw new Exception('No jobs running to watch');
        }

        $whichJob = new ChoiceQuestion('Which job do you want to watch ?', $opportunities, null);
        $answer = $helper -> ask($input, $output, $whichJob);

        $key = array_search($answer, $opportunities);
        $job = $jobs[$key];
      }

      $output -> writeln('Watching job <info>['.$job -> getUUID().']</info> :');

      $progress = new ProgressBar($output, 100);
      $progress -> start();

      do{
        $job = $app['gearman.jobs'] -> refresh($job);
        $status = $job -> getStatus();

        if($status['denominator'] != 0){
          $multiplacator = (100 / $status['denominator']);
          $percent = $status['numerator'] * $multiplacator;
          $progress -> setProgress($percent);
        }

        sleep(1);
      } while($status['known']);

      $progress -> finish();
      $output -> writeln("");
    }

  }

?>
