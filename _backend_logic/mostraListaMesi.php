<?php

require_once 'require_all.php';
$mostraTutti = $_GET['mostraTutti'];

if(!is_null($mostraTutti)){
	$listaMesi = new ListaMesi();
	$listaMesi->recuperaListaMesiInDB();
	echo json_encode($listaMesi);
}


?>