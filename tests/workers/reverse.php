<?php

# Code du worker

echo "Début\n";

# Création de notre objet worker.
$gmworker= new GearmanWorker();

# Ajout du serveur par défaut (localhost).
$gmworker->addServer();

# Enregistre la fonction "reverse" avec le serveur.
$gmworker->addFunction("reverse", "reverse_fn");

print "Attente d'une tâche...\n";
while($gmworker->work())
{
  if ($gmworker->returnCode() != GEARMAN_SUCCESS)
  {
    echo "return_code : " . $gmworker->returnCode() . "\n";
    break;
  }
}

function reverse_fn($job)
{
  echo "Tâche reçue : " . $job->handle() . "\n";

  $workload = json_decode($job->workload(), true);
  $workload = $workload['string'];
  $workload_size = strlen($workload);

  echo "Workload : $workload ($workload_size)\n";

  # This status loop is not needed, just showing how it works
  for ($x= 0; $x < $workload_size; $x++)
  {
    echo "Envoi du statut : " + $x + 1 . "/$workload_size terminé\n";
    $job->sendStatus($x+1, $workload_size);
    #$job->sendData(substr($workload, $x, 1));
    sleep(1);
  }

  $result= strrev($workload);
  echo "Résultat : $result\n";

  # Retourne ce que l'on souhaite retourner au client.
  return $result;
}

?>
