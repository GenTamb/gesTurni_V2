<?php

Class Giorno implements JsonSerializable{
	private $nomeGiorno;
	private $numeroGiorno;
	private $meseAppartenenza;
	private $annoAppartenenza;
	private $festivo;
	private $turniGiornalieri4 = array();
	private $turniGiornalieri6 = array();
	private $turniGiornalieri8 = array();
	
	
	public function InitGiorno($nomeGiorno,$numeroGiorno,$meseAppartenenza,$annoAppartenenza,$festivo=0){
		$this->setNomeGiorno($nomeGiorno);
		$this->setNumeroGiorno($numeroGiorno);
		$this->setMeseAppartenenza($meseAppartenenza);
		$this->setAnnoAppartenenza($annoAppartenenza);
		$this->setFestivo($festivo);
		if ($nomeGiorno=='Sab' || $nomeGiorno=='Dom') $this->setFestivo(true);
	}
	public function setNomeGiorno($nomeGiorno){$this->nomeGiorno=$nomeGiorno;}
	public function setNumeroGiorno($numeroGiorno){$this->numeroGiorno=$numeroGiorno;}
	public function setMeseAppartenenza($meseAppartenenza){$this->meseAppartenenza=$meseAppartenenza;}
	public function setAnnoAppartenenza($annoAppartenenza){$this->annoAppartenenza=$annoAppartenenza;}
	
	public function setFestivo($festivo){$this->festivo =$festivo;}
	public function setTurniGiornalieri4($turniGiornalieri){$this->turniGiornalieri4 =$turniGiornalieri;}
	public function setTurniGiornalieri6($turniGiornalieri){$this->turniGiornalieri6 =$turniGiornalieri;}
	public function setTurniGiornalieri8($turniGiornalieri){$this->turniGiornalieri8 =$turniGiornalieri;}
	
	public function getNomeGiorno(){return $this->nomeGiorno;}
	public function getNumeroGiorno(){return $this->numeroGiorno;}
	public function getMeseAppartenenza(){return $this->meseAppartenenza;}
	public function getAnnoAppartenenza(){return $this->annoAppartenenza;}
	
	public function getFestivo(){return $this->festivo;}
	public function getTurniGiornalieri4(){return $this->turniGiornalieri6;}
	public function getTurniGiornalieri6(){return $this->turniGiornalieri6;}
	public function getTurniGiornalieri8(){return $this->turniGiornalieri8;}
	
	public function setTurniGiornata($listaDipendenti){
		if(!$this->getFestivo()){
			$this->cicloSetTurniGiornata($listaDipendenti,4);
			$this->cicloSetTurniGiornata($listaDipendenti,6);
			$this->cicloSetTurniGiornata($listaDipendenti,8);
		}
		
	}
	
	public function inserisciGiornataInDB(){
		try{
			if(!$this->checkEsistenzaGiornata())
			{
				$db=connectDB();
				$sql=$db->prepare('INSERT INTO giorni (annoAppartenenza,meseAppartenenza,numeroGiorno,nomeGiorno,festivo,turniGiornalieri4,turniGiornalieri6,turniGiornalieri8) 
						VALUES (?,?,?,?,?,?,?,?)');
				try{
					$sql->execute([$this->annoAppartenenza,$this->meseAppartenenza,$this->numeroGiorno,$this->nomeGiorno,$this->festivo,json_encode($this->turniGiornalieri4),json_encode($this->turniGiornalieri6),json_encode($this->turniGiornalieri8)]);
				}
				catch(PDOException $e){
					echo json_encode($e->getMessage());
				}
			}
			else{
				$this->aggiornaGiornataInDB();
			}
				
		}
		catch(PDOException $e){ //exception per connectDB()
			echo json_encode($e->getMessage());
		}
		finally{
			$db=null;
		}
	}
	
	public function checkEsistenzaGiornata(){
		try{
			$db=connectDB();
			$sql=$db->prepare('SELECT COUNT(*) AS CONTO FROM giorni WHERE annoAppartenenza = ? AND meseAppartenenza = ? AND numeroGiorno = ?');
			try{    
				$sql->execute([$this->annoAppartenenza,$this->meseAppartenenza,$this->numeroGiorno]);
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
	
	public function aggiornaGiornataInDB(){
		try{
			$db=connectDB();
			$sql=$db->prepare("UPDATE giorni SET festivo = ? , turniGiornalieri4 = ? , turniGiornalieri6 = ? , turniGiornalieri8 = ? 
					WHERE annoAppartenenza = ? AND meseAppartenenza = ? AND numeroGiorno = ?");
			try{
				$sql->execute([$this->festivo,json_encode($this->turniGiornalieri4),json_encode($this->turniGiornalieri6),json_encode($this->turniGiornalieri8),$this->annoAppartenenza,$this->meseAppartenenza,$this->numeroGiorno]);
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		}
		catch(PDOException $e){ //exception per connectDB()
			echo $e->getMessage();
		}
		finally{
			$db=null;
		}
	}
	
	private function cicloSetTurniGiornata($listaDipendenti,$param){
		foreach ($listaDipendenti->getLista() as $dipendente) {
			if($dipendente->getOrario()==$param){
				$this->{'turniGiornalieri'.$param}[] = array_fill_keys([$dipendente->getCognome()],$dipendente->getTurnoAttuale());
				$dipendente->aggiornaTurnoAttuale();
			}
		}
	}
	
	public function jsonSerialize()
	{
		return get_object_vars($this);
	}
	
}


?>