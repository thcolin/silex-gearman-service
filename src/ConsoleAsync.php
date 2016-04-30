<?php

	namespace thcolin\Gearman;

	use Symfony\Component\Console\Command\Command;
	use Symfony\Component\Process\Process;

	use Exception;

	class ConsoleAsync{

		public function __construct($console){
			$this -> console = $console;
		}

		private function getConsole(){
			return $this -> console;
		}

		public function execute(Command $command, $args = [], $options = []){
			$process = new Process($this -> getCommandString($command, $args, $options));
			$process -> run();

			return $process;
		}

		public function getCommandString(Command $command, $args = [], $options = []){
			return(
				$this -> getConsole().' '.
				$command -> getName().' '.
				$this -> parseArgs($command -> getDefinition() -> getArguments(), $args).' '.
				$this -> parseOptions($command -> getDefinition() -> getOptions(), $options).' &> /dev/null &'
			);
		}

		private function parseArgs($expected, $values){
			$args = [];

			foreach($expected as $arg){
				$name = $arg -> getName();

				if($arg -> isRequired() AND !isset($values[$name])){
					throw new Exception('"'.$name.'" introuvable');
				}

				$value = $values[$name];

				if($arg -> isArray() && is_array($value)){
					$array = $value;

					foreach($array as $key => $value){
						if(is_array($value) OR is_object($value)){
							throw new Exception();
						}

						$args[] = escapeshellarg($value);
					}

				} else if(!$arg -> isArray() AND !is_array($value) AND !is_object($value)){
					$args[] = escapeshellarg($value);
				} else{
					throw new Exception();
				}
			}

			return implode(' ', $args);
		}

		private function parseOptions($expected, $values){
			$options = [];

			foreach($expected as $option){
				$name = $option -> getName();

				if(isset($values[$name])){
					$value = $values[$name];
					if(!is_array($value) && !is_object($value)){
						$options[] = '--'.$name.'='.escapeshellarg($value);
					} else{
						throw new Exception();
					}
				}
			}

			return implode(' ', $options);
		}

	}

?>
