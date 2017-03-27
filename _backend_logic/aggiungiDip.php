<?php

require_once 'require_all.php';

$json = file_get_contents('php://input');
$obj = json_decode($json);

if($obj->{'aggiundiDip'}){
	try{
		$dipendente = new Dipendente($obj->{'nome'},$obj->{'cognome'},intval($obj->{'orario'}),$obj->{'responsabile'});
		if($dipendente->inserisciDipendenteInDB()){
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