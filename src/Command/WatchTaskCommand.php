<?php

  namespace thcolin\Gearman\Command;

  use Knp\Command\Command;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;
  use Symfony\Component\Console\Helper\ProgressBar;
  use thcolin\Gearman\Job;
  use Exception;

  class WatchTaskCommand extends Command{

    protected function configure(){
      $this
        -> setName('watch-task')
        -> setDescription('Watch the task passed in argument')
        -> addArgument('uuid', InputArgument::REQUIRED, 'UUID of the task to be watched');
    }

    protected function execute(InputInterface $input, OutputInterface $output){
      $uuid = $input -> getArgument('uuid');
      $app = $this -> getSilexApplication();

      $job = $app['gearman.jobs'] -> job($uuid);
      $output -> writeln('Watching task <info>['.$job -> getUUID().']</info> :');

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
      } while($status['running']);

      $progress -> finish();
      $output -> writeln("");
    }

  }

?>
