<?php

  namespace thcolin\Gearman\Command;

  use Knp\Command\Command;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;
  use Symfony\Component\Console\Question\ChoiceQuestion;
  use thcolin\Gearman\Worker\WorkerService;
  use GearmanWorker;
  use Exception;

  class HireWorkerCommand extends Command{

    protected function configure(){
      $this
        -> setName('hire-worker')
        -> setDescription('Hire a worker and assign it to a Gearman server')
        -> addOption('type', null, InputOption::VALUE_REQUIRED, 'Type of worker : local or scaleway')
        -> addArgument('classes', (InputArgument::IS_ARRAY | InputArgument::REQUIRED), 'Tasks classes the worker will be able to execute (separated by space)');
    }

    public function execute(InputInterface $input, OutputInterface $output){
      $helper = $this -> getHelper('question');
      $type = $input -> getOption('type');
      $classes = $input -> getArgument('classes');
      $app = $this -> getSilexApplication();

      if(!in_array($type, [WorkerService::WORKER_LOCAL, WorkerService::WORKER_SCALEWAY])){
        $whichType = new ChoiceQuestion('Which worker do you want to hire ?', [WorkerService::WORKER_LOCAL, WorkerService::WORKER_SCALEWAY], 0);
        $type = $helper -> ask($input, $output, $whichType);
      }

      foreach($classes as $class){
        if(!class_exists($class)){
          throw new Exception('Class "'.$class.'" not found !');
        }
        $tasks[] = $class::WORK;
      }

      if($type == WorkerService::WORKER_SCALEWAY){
        $app['gearman.workers'] -> hire($classes, WorkerService::WORKER_SCALEWAY);
        $output -> writeln('Launching new Scaleway server...');
        $output -> writeln('Started scaleway worker : <info>'.implode('</info>, <info>', $tasks).'</info>');
      } else{
        $worker = new GearmanWorker();
        $worker -> setId(uniqid().'-'.getmypid());
        $worker -> addServers($app['gearman.options']['server']);

        foreach($classes as $class){
          $object = new $class();
          $worker -> addFunction($object::WORK, [$object, 'work']);
        }

        $output -> writeln('Starting local worker : <info>'.implode('</info>, <info>', $tasks).'</info>');
        $output -> writeln("Waiting job...");

        do{
          if($worker -> returnCode() != GEARMAN_SUCCESS){
            break;
          }
        } while($worker -> work());
      }
    }

  }

?>
