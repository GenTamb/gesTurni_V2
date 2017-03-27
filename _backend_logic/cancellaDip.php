<?php

require_once 'require_all.php';

$json = file_get_contents('php://input');
$obj = json_decode($json);

if($obj->{'cancellaDip'}){
	try{
		$dipendente = new Dipendente('',$obj->{'cognome'},'');
		if($dipendente->cancellaDipendenteInDB()){
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