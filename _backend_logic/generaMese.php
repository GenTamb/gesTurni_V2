<?php
require_once 'require_all.php';

$json = file_get_contents('php://input');
$obj = json_decode($json);

if($obj->{'generaMese'}){
	try{
		$listaDipendenti = new ListaDipendenti();
		$listaDipendenti->recuperaListaInDB($obj->{'forzaRigenerazione'});
		$mese = new Mese();
		$mese->InitMese($obj->{'anno'},$obj->{'nomeMese'},$obj->{'numeroGiorni'},$obj->{'primoGiornoMese'},$obj->{'listaFestivi'},$listaDipendenti);
		$mese->generaTurni($listaDipendenti);
		if($mese->inserisciMeseInDB()) echo json_encode('ok');
		else echo json_encode('Errore');
	}
	catch(PDOException $e){
		echo json_encode($e->getMessage());
	}
	
}


?>