<?php

require_once 'require_all.php';
$getTurni = $_GET['getTurni'];

if(!is_null($getTurni)){
	$lista = new ListaDipendenti();
	$lista->recuperaListaTurni(4);
	$lista->recuperaListaTurni(6);
	$lista->recuperaListaTurni(8);
	
	echo json_encode($lista);
}



?>