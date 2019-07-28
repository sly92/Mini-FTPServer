<?php
// set some variables
$host = "127.0.0.1";
$port = 25010;
$nbMaxClient = 1;

// don't timeout!
set_time_limit(0);
ob_implicit_flush();

// create socket
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");

// bind socket to port
$result = socket_bind($socket, $host, $port) or die("Could not bind to socket\n");

$client=0;
// start listening for connections
$result = socket_listen($socket, $nbMaxClient) or die("Could not set up socket listener\n");

// accept incoming connections
// spawn another socket to handle communication

if($client!=$nbMaxClient ){
$spawn = socket_accept($socket) or die("Could not accept incoming connection\n");
$client++;
}

$mode=1;
$commande="copie";
$fichier=0;

shell_exec('reset');

echo "Bienvenue Client ".$client."\n";

do{

// read client input
$mode = socket_read($spawn, 1024) or die("Could not read input\n");



if($mode==2){

$input = socket_read($spawn, 1024) or die("Could not read input\n");


if(trim($input)==3){

	$source = socket_read($spawn, 1024) or die("Could not read input\n");


	$filename=trim($source);
	// echo "source : ".$filename;
	$handle = fopen($filename, "r");
	$output = fread($handle, filesize($filename));
	
	socket_write($spawn, $output, strlen ($output)) or die("Could not write output\n");
	fclose($handle);
}
else if(trim($input)==4){


	$dest= socket_read($spawn, 1024) or die("Could not read input\n");

		$filename=trim($dest);
		$handle = fopen($filename, "w");
		$result = socket_read ($spawn, 1024) or die("Could not read server response\n");
		echo "RESULT : ".$result;
		fwrite($handle, $result);
		fclose($handle);
}
else if(trim($input)==5){

	$source = socket_read($spawn, 1024) or die("Could not read input\n");
	echo 'SOURCE 1 : '.$source."\n";
	$source=trim($source);
	echo 'SOURCE 2 : '.$source."\n"; 
	$dir=scandir($source,1); //liste les fichier du repertoire courant

	foreach($dir as $k => $v){
	if($v!=".." && $v!="."){
	echo "SOURCE : ".trim($source)."/".$v;
	$filename=trim($v);
	echo "FILENAME : ".$filename;
	socket_write($spawn, $filename, strlen ($filename)) or die("Could not write output\n");

	$filename=trim($source)."/".trim($v);
	// echo "source : ".$filename;
	$handle = fopen($filename, "r");
	$output = fread($handle, filesize($filename));
	socket_write($spawn, $output, strlen ($output)) or die("Could not write output\n");
	fclose($handle);
	$filename="";
	$output="";
	}
	}
}
else{

echo "Client ".$client." : ".$input . "\n";

//recupere une commande shell
$output = shell_exec($input) . "\n";

//$output = strrev($input) . "\n";

//ecrit et execute la commande shell
socket_write($spawn, $output, strlen ($output)) or die("Could not write output\n");
}
}
else
{

do {
$input = socket_read($spawn, 1024) or die("Could not read input\n");

echo "Client ".$client." : ".$input . "\n";

//recupere une commande shell
$output = $input."\n";


//ecrit et execute la commande shell
socket_write($spawn, $output, strlen ($output)) or die("Could not write output\n");

}while(strcmp(trim($input),'stop')!=0);
}

}while($input!=4);



if($fichier!=null)
fclose($fichier);

// close sockets
socket_close($spawn);
socket_close($socket);

return 0;
?>
