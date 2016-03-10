<?php

  namespace thcolin\Gearman\Command;

  use Knp\Command\Command;
  use Symfony\Component\Console\Input\InputArgument;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;
  use Symfony\Component\Console\Question\ChoiceQuestion;
  use Symfony\Component\Console\Question\ConfirmationQuestion;
  use GearmanWorker;
  use Exception;

  class FireWorkerCommand extends Command{

    protected function configure(){
      $this
        -> setName('fire-worker')
        -> setDescription('List the workers assigned to a Gearman server which you can fire');
    }

    public function execute(InputInterface $input, OutputInterface $output){
      $helper = $this -> getHelper('question');
      $app = $this -> getSilexApplication();

      $workers = $app['gearman.workers'] -> workers();

      if(!$workers){
        throw new Exception('No workers running');
      }

      $opportunities = [];
      foreach($workers as $worker){
        $opportunities[] = '<comment>'.$worker -> getId().'</comment> (<info>'.implode('</info>, <info>', $worker -> getAbilities()).'</info>)';
      }

      $whichWorker = new ChoiceQuestion('Which worker do you want to fire ?', $opportunities, null);
      $answer = $helper -> ask($input, $output, $whichWorker);
      $key = array_search($answer, $opportunities);

      $app['gearman.workers'] -> fire($workers[$key]);
      $output -> writeln('Worker fired : <info>'.$worker -> getId().'</info>');
    }

  }

?>
