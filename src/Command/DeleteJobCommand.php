<?php

  namespace thcolin\Gearman\Command;

  use Knp\Command\Command;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;
  use Symfony\Component\Console\Question\ChoiceQuestion;
  use thcolin\Gearman\Job\Job;
  use GearmanWorker;
  use Exception;

  class DeleteJobCommand extends Command{

    protected function configure(){
      $this
        -> setName('delete-job')
        -> setDescription('List the jobs which you can delete');
    }

    public function execute(InputInterface $input, OutputInterface $output){
      $helper = $this -> getHelper('question');
      $app = $this -> getSilexApplication();

      $jobs = $app['gearman.jobs'] -> jobs();
      $jobs = array_values($jobs);

      if(!$jobs){
        throw new Exception('No jobs');
      }

      $opportunities = [];
      foreach($jobs as $job){
        $opportunities[] = '<comment>'.$job -> getUUID().'</comment> - <info>'.$job -> getTask().'</info> '.$job -> getWorkload(Job::WORKLOAD_JSON).($job -> getResult() ? ' : <question>'.$job -> getResult().'</question>':null);
      }

      $whichJob = new ChoiceQuestion('Which job do you want to delete ? (separated by coma)', $opportunities, null);
      $whichJob -> setMultiselect(true);
      $answers = $helper -> ask($input, $output, $whichJob);

      foreach($answers as $answer){
        $key = array_search($answer, $opportunities);
        $app['gearman.jobs'] -> delete($jobs[$key] -> getUUID());
        $output -> writeln('Job deleted : <info>'.$jobs[$key] -> getUUID().'</info>');
      }
    }

  }

?>
