<?php
require_once 'class_Dipendente.php';
require_once 'class_PeriodoMesePrecedente.php';
require_once 'class_DB.php';

Class ListaDipendenti implements JsonSerializable{
	private $listaDipendenti = array();
	private $numeroDipendentiTotale = 0;
	private $numeroDipendentiTurno8 = 0;
	private $numeroDipendentiTurno6 = 0;
	private $numeroDipendentiTurno4 = 0;
	private $turniGiornalieri4 = array();
	private $turniGiornalieri6 = array();
	private $turniGiornalieri8 = array();
	
	
	public function scriviPrimaVoltaListaInDB(){
		$this->aggiornaListaDipendentiInDB();
	}
	
	
	public function aggiungiDipendentiInListaSenzaDB($lista){
		$this->listaDipendenti=array_merge($this->listaDipendenti,$lista);
		$this->aggiornaContatoriLista();
		$this->inizializzaTurniStandard();
		$this->inserisciListaInDB();
		$this->aggiornaListaDipendentiInDB();
	}
	
// 	public function inizializzaTurniDaPeriodoPrecedente($periodo){
// 		if(!is_null($periodo) && count($periodo->getPeriodo()[0]->getTurniGiornalieri6())!=0 && count($periodo->getPeriodo()[0]->getTurniGiornalieri8())!=0){
// 			foreach ($this->getListaTurniGiornalieri6() as $keyTurno=>$descTurno){
// 				foreach ($this->getLista() as $dipendente){
// 					if($descTurno == $dipendente->getCognome()) $dipendente->setTurnoAttuale($keyTurno);
// 				}
// 			}
// 			foreach ($this->getListaTurniGiornalieri8() as $keyTurno=>$descTurno){
// 				foreach ($this->getLista() as $dipendente){
// 					if($descTurno == $dipendente->getCognome()) $dipendente->setTurnoAttuale($keyTurno);
// 				}
// 			}
// 		}
// 	}
	
	public function aggiornaContatoriLista(){
		$this->numeroDipendentiTotale = count($this->listaDipendenti);
		$this->numeroDipendentiTurno4 = 0;
		$this->numeroDipendentiTurno6 = 0;
		$this->numeroDipendentiTurno8 = 0;
		foreach ($this->getLista() as $dipendente){
			if($dipendente->getOrario()=='8') $this->numeroDipendentiTurno8++;
			if($dipendente->getOrario()=='6') $this->numeroDipendentiTurno6++;
			if($dipendente->getOrario()=='4') $this->numeroDipendentiTurno4++;
		}
	}
	
	public function inizializzaTurniStandard(){
			$this->cicloFunzioneInizializzaListaTurni("4");
			$this->cicloFunzioneInizializzaListaTurni("6");
			$this->cicloFunzioneInizializzaListaTurni("8");
			$this->cicloFunzioneInizializzaTurniDipendenti("4");
			$this->cicloFunzioneInizializzaTurniDipendenti("6");
			$this->cicloFunzioneInizializzaTurniDipendenti("8");
		}
		
	public function contaDipendentiByOrario($param){
		$counter = 0;
		foreach ($this->listaDipendenti as $dipendente) {
			if($param==$dipendente->getOrario()) $counter++;
		}
		return $counter;
	}
		
	public function getLista(){return $this->listaDipendenti;}
	public function getNumeroDipendentiTotale(){return $this->numeroDipendentiTotale;}
	public function getNumeroDipendentiTurno4(){return $this->numeroDipendentiTurno4;}
	public function getNumeroDipendentiTurno6(){return $this->numeroDipendentiTurno6;}
	public function getNumeroDipendentiTurno8(){return $this->numeroDipendentiTurno8;}
	public function getListaTurniGiornalieri4(){return $this->turniGiornalieri4;}
	public function getListaTurniGiornalieri6(){return $this->turniGiornalieri6;}
	public function getListaTurniGiornalieri8(){return $this->turniGiornalieri8;}
	
	public function aggiornaListaTurniDipendenti(){
		foreach ($this->getLista() as $dipendente) {
			$dipendente->aggiornaTurnoAttuale();
		}
	}
	
	public function recuperaListaInDB($forzaRicalcoloTurni=false){
		try{
			$db=connectDB();
			$sql=$db->prepare('SELECT * FROM listadipendenti');
			try{
				$sql->execute([]);
				while($res=$sql->fetch(PDO::FETCH_ASSOC))
				{
					$this->numeroDipendentiTotale = $res['numeroDipendentiTotale'];
					$this->numeroDipendentiTurno4 = $res['numeroDipendentiTurno4'];
					$this->numeroDipendentiTurno6 = $res['numeroDipendentiTurno6'];
					$this->numeroDipendentiTurno8 = $res['numeroDipendentiTurno8'];
				}
			}
			catch(PDOException $e){
				echo json_encode($e->getMessage());
			}
			
			$sql=$db->prepare('SELECT * FROM dipendenti');
			try{
				$sql->execute([]);
				while($res=$sql->fetch(PDO::FETCH_ASSOC))
				{
					$dipendente = new Dipendente($res['nome'],$res['cognome'],$res['orario'],$res['speciale']);
					$dipendente->setTurnoAttuale($res['turnoAttuale']);
					$dipendente->setContatoreRangeTurno($res['contatoreRangeTurno']);
					$dipendente->setNomeListaTurniEseguibili($res['nomeListaTurniEseguibili']);
					$dipendente->recuperaListaTurniEsebuibiliInDB();
					$this->listaDipendenti[]= $dipendente;
				}
			}
			catch(PDOException $e){
				echo json_encode($e->getMessage());
			}
		}
		catch(PDOException $e){ //exception per connectDB()
			echo json_encode($e->getMessage());
		}
		finally{
			$db=null;
		}
		if($forzaRicalcoloTurni || $this->numeroDipendentiTotale!=count($this->listaDipendenti) || $this->numeroDipendentiTurno4!=$this->contaDipendentiByOrario(4) 
				|| $this->numeroDipendentiTurno6!=$this->contaDipendentiByOrario(6) || $this->numeroDipendentiTurno8!=$this->contaDipendentiByOrario(8)){
			$this->aggiornaContatoriLista();
			$this->inizializzaTurniStandard();
		}
	}
	
	public function inserisciListaInDB(){
		foreach ($this->getLista() as $dipendente){
			$dipendente->inserisciDipendenteInDB();
		}
	}
	
	public function aggiornaListaInDB(){
		foreach ($this->getLista() as $dipendente){
			$dipendente->aggiornaDipendenteInDB();
		}
	}
	
	public function inserisciTurniInDB($param){
		try{
			$db=connectDB();
			if(!$this->checkEsistenzaListaTurni($param)){
				$sql=$db->prepare('INSERT INTO turni (nomeListaTurni,listaTurni) VALUES (?,?)');
				try{
					$sql->execute([('turniGiornalieri'.$param),json_encode($this->{'turniGiornalieri'.$param})]);
				}
				catch(PDOException $e){
					echo json_encode($e->getMessage());
				}
			}
			else{
				$this->aggiornaTurniInDB($param);
			}
			
		}
		catch(PDOException $e){ //exception per connectDB()
			echo json_encode($e->getMessage());
		}
		finally{
			$db=null;
		}
	}
	
	public function aggiornaTurniInDB($param){
		try{
			$db=connectDB();
			$sql=$db->prepare('UPDATE turni SET listaTurni = ? WHERE nomeListaTurni = ?');
			try{
				$sql->execute([json_encode($this->{'turniGiornalieri'.$param}),('turniGiornalieri'.$param)]);
			}
			catch(PDOException $e){
				echo json_encode($e->getMessage());
			}
				
		}
		catch(PDOException $e){ //exception per connectDB()
			echo json_encode($e->getMessage());
		}
		finally{
			$db=null;
		}
	}
	
	public function checkEsistenzaListaTurni($param){
		try{
			$db=connectDB();
			$sql=$db->prepare('SELECT COUNT(*) AS CONTO FROM turni WHERE nomeListaTurni = ?');
			try{
				$sql->execute([('turniGiornalieri'.$param)]);
				$res=$sql->fetch(PDO::FETCH_ASSOC);
				if($res['CONTO'] == 1) return true;
				else return false;
			}
			catch(PDOException $e){
				echo json_encode($e->getMessage());
			}
		}
		catch(PDOException $e){ //exception per connectDB()
			echo json_encode($e->getMessage());
		}
		finally{
			$db=null;
		}
	}
	
	public function recuperaListaTurni($param){
		try{
			$db=connectDB();
			$sql=$db->prepare('SELECT * FROM turni WHERE nomeListaTurni = ?');
			try{
				$sql->execute(['turniGiornalieri'.$param]);
				while($res=$sql->fetch(PDO::FETCH_ASSOC))
				{
					$this->{'turniGiornalieri'.$param} = json_decode($res['listaTurni']);
				}
			}
			catch(PDOException $e){
				echo json_encode($e->getMessage());
			}
		}
		catch(PDOException $e){ //exception per connectDB()
			echo json_encode($e->getMessage());
		}
		finally{
			$db=null;
		}
	}
	
	public function aggiornaListaDipendentiInDB(){
		try{
			$db=connectDB();
			if(!$this->checkEsistenzaListaDipendentiInDB()){
				$sql=$db->prepare('INSERT INTO listadipendenti (numeroDipendentiTotale,numeroDipendentiTurno4,numeroDipendentiTurno6,numeroDipendentiTurno8) VALUES (?,?,?,?)');
				try{
					$sql->execute([$this->numeroDipendentiTotale,$this->numeroDipendentiTurno4,$this->numeroDipendentiTurno6,$this->numeroDipendentiTurno8]);
				}
				catch(PDOException $e){
					echo json_encode($e->getMessage());
				}
			}
			else{
				$this->checkEsistenzaListaDipendentiInDB_MakeQuery();
			}
				
		}
		catch(PDOException $e){ //exception per connectDB()
			echo json_encode($e->getMessage());
		}
		finally{
			$db=null;
		}
	}
	
	public function checkEsistenzaListaDipendentiInDB_MakeQuery(){
		try{
			$db=connectDB();
			$sql=$db->prepare('UPDATE listadipendenti SET numeroDipendentiTotale = ?,  numeroDipendentiTurno4 = ? , numeroDipendentiTurno6 = ? , numeroDipendentiTurno8 = ?');
			try{
				$sql->execute([$this->numeroDipendentiTotale,$this->numeroDipendentiTurno4,$this->numeroDipendentiTurno6,$this->numeroDipendentiTurno8]);
			}
			catch(PDOException $e){
				echo json_encode($e->getMessage());
			}
		
		}
		catch(PDOException $e){ //exception per connectDB()
			echo json_encode($e->getMessage());
		}
		finally{
			$db=null;
		}
	}
	
	public function checkEsistenzaListaDipendentiInDB(){
		try{
			$db=connectDB();
			$sql=$db->prepare('SELECT COUNT(*) AS CONTO FROM listadipendenti');
			try{
				$sql->execute([]);
				$res=$sql->fetch(PDO::FETCH_ASSOC);
				if($res['CONTO'] == 1) return true;
				else return false;
			}
			catch(PDOException $e){
				echo json_encode($e->getMessage());
			}
		}
		catch(PDOException $e){ //exception per connectDB()
			echo json_encode($e->getMessage());
		}
		finally{
			$db=null;
		}
	}
	
	private function cicloFunzioneInizializzaListaTurni($param){
		$this->{'turniGiornalieri'.$param} = array();
		for ($i = 0; $i < $this->{'numeroDipendentiTurno'.$param}; $i++) {
			$this->{'turniGiornalieri'.$param}[] = ($param)."_turno".($i);
		}
		$this->inserisciTurniInDB($param);
		$this->aggiornaListaDipendentiInDB();
		
	}
	private function cicloFunzioneInizializzaTurniDipendenti($param){
		$i=0;
		foreach ($this->getLista() as $dipendente){
			if ($dipendente->getOrario()==$param) {
				$funzioneParametrica = 'getListaTurniGiornalieri'.$param;
				$dipendente->setNomeListaTurniEseguibili(("turniGiornalieri".$param));
				$dipendente->setTurnoAttuale($this->$funzioneParametrica()[$i]);
				$dipendente->inizializzaContatoreRangeTurno();
				$dipendente->aggiornaDipendenteInDB();
				$i++;
			}
		
		}
	}
	
		
	
	public function jsonSerialize()
	{
		return get_object_vars($this);
	}
	
}


?>