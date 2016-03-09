<?php

  namespace thcolin\Gearman\Command;

  use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;
  use Exception;

  class HireWorkerCommand extends ContainerAwareCommand{

    protected function configure(){
      $this
        -> setName('hire-worker')
        -> setDescription('Hire a worker and assign it to a Gearman server')
        -> addOption('server', null, InputOption::VALUE_REQUIRED, 'Gearman server to use (default: localhost)')
        -> addOption('worker', null, InputOption::VALUE_REQUIRED, 'Local/Scaleway (default: local)')
        -> addArgument('commands', (InputArgument::IS_ARRAY | InputArgument::REQUIRED), 'Command classes the worker will be able to execute (separated by space)');
    }

    public function execute(InputInterface $input, OutputInterface $output){
      $server = $input -> getOption('server');
      $commands = $input -> getArgument('commands');

      $worker = new \MHlavac\Gearman\Worker();

      if($server){
        $worker -> addServer($server);
      } else{
        $worker -> addServer();
      }

      foreach($commands as $command){
        $object = new $command();
        $worker -> addFunction($object -> getName(), [$object, 'work']);
        $worker -> attachCallback([$object, 'callback']);
      }

      $worker -> work();
    }

  }

?>
