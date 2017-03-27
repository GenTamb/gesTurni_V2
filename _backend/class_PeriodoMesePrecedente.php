<?php

require_once 'class_Giorno.php';
require_once 'class_Mese.php';

Class PeriodoMesePrecedente implements JsonSerializable{
	private $periodo = array();
	
// 	function PeriodoMesePrecedente($meseAttuale,$anno){
// 		$keyMese = array_search($meseAttuale,mesi);
// 		if($keyMese==1) $keyMese =12;
// 		else $keyMese--;
// 		$mese = new Mese();
// 		$mese->getMeseDaDB($keyMese,$anno);
// 		//recupera mese da db
// 	}
	public function setPeriodo($periodo){$this->periodo=$periodo;}
	
	public function getPeriodo(){return $this->periodo;}
	
	
	public function mockaPeriodo($meseMock,$listaDipendenti){
		$maxPeriodo=max($listaDipendenti->getNumeroDipendentiTurno6(),$listaDipendenti->getNumeroDipendentiTurno8());
		$contatore=($meseMock->getNumGiorniMese()-$maxPeriodo);
		$indice=1;
		for ($i = $meseMock->getNumGiorniMese(); $i >= $contatore; $i--) {
				if(!$meseMock->getCalendarioGiorni()[$i]->getFestivo()){
					$this->periodo[] = $meseMock->getCalendarioGiorni()[$i];
					$i=0;
				}
			}
	}
	public function jsonSerialize()
	{
		return get_object_vars($this);
	}
	
}


?>