<?php

require_once 'require_all.php';

$json = file_get_contents('php://input');
$obj = json_decode($json);

if($obj->{'modificaDip'}){
	try{
		$dipendente = new Dipendente($obj->{'origNome'},$obj->{'origCognome'},$obj->{'origOrario'},$obj->{'origResponsabile'});
		if($dipendente->aggiornaDipendenteInDBdaFE($obj->{'nome'},$obj->{'cognome'},$obj->{'orario'},$obj->{'responsabile'})){
			$listaDipendenti = new ListaDipendenti();
			$listaDipendenti->recuperaListaInDB(true);
			echo json_encode('OK');
		}
		else json_encode('Errore');
	}
	catch(PDOException $e){
		echo json_encode($e->getMessage());
	}

}

?>