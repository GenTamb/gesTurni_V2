<?php

require_once 'require_all.php';

$trigger = $_GET['richiediListaDipendenti_Turni'];

if(!is_null($trigger)){
	try{
		$listaDipendenti = new ListaDipendenti();
		$listaDipendenti->recuperaListaInDB();
		echo json_encode($listaDipendenti);
	}
	catch(PDOException $e){
		echo json_encode($e->getMessage());
	}

}

?>