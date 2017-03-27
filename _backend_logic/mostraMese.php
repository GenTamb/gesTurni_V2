<?php

require_once 'require_all.php';
$anno = $_GET['anno'];
$numMese = $_GET['mese'];

if(!is_null($anno) && !is_null($numMese)){
	$mese = new Mese();
	$mese->recuperaMeseInDB($anno,$numMese);
	echo json_encode($mese);
}


?>