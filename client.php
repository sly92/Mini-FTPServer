<?php //21 // shell_exec pour executer une commande shell
$host    = "127.0.0.1";
$port    = 25010;

set_time_limit(0);
ob_implicit_flush();
// create sophphcket
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");

// connect to server
$result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");

$input="";

echo "Bienvenue dans le serveur FTP \n\n Cliquer sur une touche pour continuer.\n\n";

$continue=fgets(STDIN);


shell_exec('reset');

$menu="----------------------------------- MENU -------------------------------------\n\n\n\n         
        1 - Discuter avec le serveur\n 
  	2 - Utiliser des commandes\n 
	3 - Interagir avec la base etudiant\n
	4 - Quitter\n\n\n";

$menu2="\n\n\n         
        1 - Lister les fichiers\n 
  	2 - Changer de dossier\n 
	3 - Copier un fichier vers le serveur\n 
  	4 - Recuperer un fichier sur le serveur\n 
	5 - Recuperer un dossier sur le serveur\n\n\n";

$menu3="\n\n\n         
        1 - Dossier parent\n 
  	2 - Dossier enfant\n 
	\n\n";

echo $menu;

do{

$choix=fgets(STDIN);



socket_write($socket, $choix, strlen($choix)) or die("Could not send data to server\n");

if($choix == 1){
shell_exec('reset');

echo "Choisissez votre pseudo : \n\n";
$client=trim(fgets(STDIN));

shell_exec('reset');

do{

echo "\n Ecrire 'stop' a tout moment pour revenir au menu principal\n".$client." :   ";

$input=fgets(STDIN);


// send cmd to server
socket_write($socket, $input ,strlen($input)) or die("Could not send result of commande line to server\n");


// get server response
$result = socket_read ($socket, 1024) or die("Could not read server response\n");

// echo "Reply from server  :".$result;

echo "\n";

}while(strcmp(trim($input),'stop')!=0);



}

if($choix == 2){
shell_exec('reset');

do{


echo "\n\n Ecrire 'stop' a tout moment pour revenir au menu principal".$menu2;

$input=fgets(STDIN);

$cmd="";
socket_write($socket, $input, strlen($input)) or die("Could not send data to server\n");

switch($input) {

    case 1:
        $cmd="ls";
        break;
    case 2:
    	do{
    	echo $menu3;
    	$choix=fgets(STDIN);
    	if($choix==2){
    	echo "Nom du dossier enfant : ";
    	$nomD=fgets(STDIN);
    	}
        $cmd="cd ..";
        $cmd="cd ".$nomD;

    	}while($cmd="");
        break;
        break;
    case 3:
        $cmd="cpcs";
        echo("fichier source :");
		$source=fgets(STDIN);
		echo "source : ".$source;

		echo("fichier de destination :");
		$dest=fgets(STDIN);	
		echo "destinataire : ".$dest;

		socket_write($socket, $source, strlen($source)) or die("Could not send data to server\n");

		$filename=trim($dest);
		$handle = fopen($filename, "w");
		$result = socket_read ($socket, 1024) or die("Could not read server response\n");
		echo "RESULT : ".$result;
		fwrite($handle, $result);
		fclose($handle);
        break;
    case 4:
        $cmd="cpsc";
        echo("fichier source :");
		$source=fgets(STDIN);
		echo "source : ".$source;

		echo("dossier de destination :");
		$dest=fgets(STDIN);	
		echo "destinataire : ".$dest;

		socket_write($socket, $dest, strlen($dest))
		or die("Could not send data to server\n");


		$filename=trim($source);
		$handle = fopen($filename, "r");
		$output = fread($handle, filesize($filename));
		socket_write($socket, $output, strlen ($output)) 
		or die("Could not write output\n");
		fclose($handle);
        break;
    case 5:
        $cmd="cpcsd";
        $fichier= array();
        
        echo("dossier source :");
		$source=fgets(STDIN);

		echo "source : ".$source;
		// $source='/'.$source;
		// echo "\n\ndossier source : ".$source;

		socket_write($socket, $source, strlen($source)) 
		or die("Could not send data to server\n");

        
        // echo("Fichiers dans dossier:\n\n".$ls);

  //       $lines = file($ls);
		
		// foreach($lines as $k => $v){
		// 	$fichier[$k]=$v;
		// }
		$source=$source."2";
		shell_exec("mkdir ".$source." && cd ".$source);

		while(true){
		fflush($dest);
		$dest = socket_read ($socket, 1024) or die("Could not read server response\n");
		echo "DEST : ".$dest;
		echo "\n\n".$source."/".trim($dest);
		$filename=trim($source)."/".trim($dest);
		$handle = fopen($filename, "w");
		$result = socket_read ($socket, 1024) or die("Could not read server response\n");
		echo "RESULT : ".$result;
		fwrite($handle, $result);
		fclose($handle);

	}
        break;
    default:
       echo "je n'ai pas compris votre choix.";
}

}while(strcmp(trim($input),'stop')!=0);

}


if($choix == 3){

$trouve=false;


do{

shell_exec('reset');

	echo "Entrez le numero de l'etudiant :  \n Ecrire 'stop' a tout moement pour revenir au menu principal\n\n";
	$input=trim(fgets(STDIN));

if(($fichier = fopen('base.txt','r+')) !== FALSE){

$row = 1;

	while(($data = fgetcsv($fichier, 1000, ",")) !== FALSE) {

		$num= count($data);

		if($data[0] == $input){
			echo "\nNom : ".$data[1]."\nPrenom : ".$data[2];	
			$trouve=true;
		}

		$row++;
	}
	if(!$trouve)
		echo "Aucun etudiant avec ce numero d'etudiant\n\n";
}

}while(strcmp(trim($input),'stop')!=0);
}



if($choix!=1 && $choix!=2 && $choix!=3 && $choix!=4)
echo "Cette valeur n'est pas presente dans le choix\n\nVeuillez saisir une valeur présente dans le menu : \n\n";

if(strcmp(trim($input),'stop')==0){
	shell_exec('reset');
	echo $menu;
	$choix=0;
	$input="";
}

}while($choix!=1 && $choix!=2 && $choix!=3 && $choix!=4);


echo "Au revoir :) ";

if($socket!=null)
socket_close($socket);
return 0;
?>
