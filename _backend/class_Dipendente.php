<?php

require_once 'class_PeriodoMesePrecedente.php';
require_once 'class_DB.php';

Class Dipendente implements JsonSerializable{
	private $nome;
	private $cognome;
	private $orario;
	private $speciale = 0;
	private $contatoreRangeTurno = 0;
	private $turnoAttuale;
	private $nomeListaTurniEseguibili;
	private $listaTurniEseguibili = array();
	
	function Dipendente($nome,$cognome,$orario,$speciale=0){
		$this->nome = $nome;
		$this->cognome = $cognome;
		$this->orario = $orario;
		$this->speciale=$speciale;
	}
	public function getNome(){return $this->nome;}
	public function getCognome(){return $this->cognome;}
	public function getOrario(){return $this->orario;}
	public function getContatoreRangeTurno(){return $this->contatoreRangeTurno;}
	public function getTurnoAttuale(){return $this->turnoAttuale;}
	public function getNomeListaTurniEseguibili(){return $this->nomeListaTurniEseguibili;}
	public function getListaTurniEseguibili(){return $this->listaTurniEseguibili;}
	
	public function setNome($nome){ $this->nome = $nome;}
	public function setCognome($cognome){ $this->cognome = $cognome;}
	public function setOrario($orario){ $this->orario = $orario;}
	public function setContatoreRangeTurno($contatoreRangeTurno){ $this->contatoreRangeTurno = $contatoreRangeTurno;}
	public function setTurnoAttuale($turnoAttuale){ $this->turnoAttuale = $turnoAttuale;}
	
	public function setNomeListaTurniEseguibili($nomeListaTurni){
		$this->nomeListaTurniEseguibili=$nomeListaTurni;
		$this->recuperaListaTurniEsebuibiliInDB();
	}
	public function setListaTurniEseguibili($listaTurni){$this->listaTurniEseguibili=$listaTurni;}
	
	public function inizializzaContatoreRangeTurno(){
		$numeroTurni=count($this->getListaTurniEseguibili());
		foreach ($this->getListaTurniEseguibili() as $key => $value) {
			if($this->getTurnoAttuale()==$value)
				$this->setContatoreRangeTurno($numeroTurni-$key);
		}
	}
	
	public function aggiornaTurnoAttuale(){
		$numeroTurni=count($this->getListaTurniEseguibili());
		foreach ($this->getListaTurniEseguibili() as $key => $value) {
			if($this->getTurnoAttuale()==$value){
				$indiceProxTurno = $key+1;
				if ($indiceProxTurno==$numeroTurni) $indiceProxTurno = 0;
				$this->setTurnoAttuale($this->getListaTurniEseguibili()[$indiceProxTurno]);
				$this->setContatoreRangeTurno($numeroTurni-$indiceProxTurno);
				$this->aggiornaDipendenteInDB();
				break;
			}
		}
	}
	
	public function recuperaListaTurniEsebuibiliInDB(){
		try{
			$db=connectDB();
			$sql=$db->prepare('SELECT listaTurni FROM turni WHERE nomeListaTurni = ?');
			try{
				$sql->execute([$this->nomeListaTurniEseguibili]);
				while($res=$sql->fetch(PDO::FETCH_ASSOC))
				{
					$this->setListaTurniEseguibili(json_decode($res['listaTurni']));
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
	
	public function inserisciDipendenteInDB(){
		try{
			if(!$this->checkEsistenzaDipendente())
			{
				$db=connectDB();
				$sql=$db->prepare('INSERT INTO dipendenti (cognome,nome,orario,speciale,contatoreRangeTurno,turnoAttuale,nomeListaTurniEseguibili) VALUES (?,?,?,?,?,?,?)');
				try{
					$sql->execute([$this->cognome,$this->nome,$this->orario,$this->speciale,$this->contatoreRangeTurno,$this->turnoAttuale,$this->nomeListaTurniEseguibili]);
					return true;
				}
				catch(PDOException $e){
					echo json_encode($e->getMessage());
				}
			}
			else{
				return false;
			}
			
		}
		catch(PDOException $e){ //exception per connectDB()
			echo json_encode($e->getMessage());
		}
		finally{
			$db=null;
		}
	}
	
	public function checkEsistenzaDipendente(){
		try{
			$db=connectDB();
			$sql=$db->prepare('SELECT COUNT(*) AS CONTO FROM dipendenti WHERE cognome = ? AND nome = ?');
			try{
				$sql->execute([$this->cognome,$this->nome]);
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
	
	public function aggiornaDipendenteInDB(){
		
		try{
			$db=connectDB();
				
			$sql=$db->prepare("UPDATE dipendenti SET cognome = ? , nome = ? ,
					                  orario = ? , speciale = ? , contatoreRangeTurno = ? , turnoAttuale = ? ,
					                  nomeListaTurniEseguibili = ? WHERE cognome = ?");
			try{
				$sql->execute([$this->cognome,$this->nome,$this->orario,$this->speciale,$this->contatoreRangeTurno,$this->turnoAttuale,$this->nomeListaTurniEseguibili,$this->cognome]);
				return true;
			}
			catch(PDOException $e){
				echo $e->getMessage();
				return false;
				}
			}
			catch(PDOException $e){ //exception per connectDB()
				echo $e->getMessage();
				return false;
			}
			finally{
				$db=null;
			}
		}
	
	public function aggiornaDipendenteInDBdaFE($nome='',$cognome='',$orario='',$speciale=''){
	
		try{
			$db=connectDB();
	
			$sql=$db->prepare("UPDATE dipendenti SET cognome = ? , nome = ? ,
				                  orario = ? , speciale = ? , contatoreRangeTurno = ? , turnoAttuale = ? ,
				                  nomeListaTurniEseguibili = ? WHERE cognome = ?");
			try{
				$sql->execute([$cognome,$nome,$orario,$speciale,$this->contatoreRangeTurno,$this->turnoAttuale,$this->nomeListaTurniEseguibili,$this->cognome]);
				return true;
			}
			catch(PDOException $e){
				echo $e->getMessage();
				return false;
			}
		}
		catch(PDOException $e){ //exception per connectDB()
			echo $e->getMessage();
			return false;
		}
		finally{
			$db=null;
		}
	}
		
	public function cancellaDipendenteInDB(){
		try{
			$db=connectDB();
			$sql=$db->prepare("DELETE FROM dipendenti WHERE cognome = ?");
			try{
				$sql->execute([$this->cognome]);
				return true;
			}
			catch(PDOException $e){
				echo $e->getMessage();
				return false;
			}
		}
		catch(PDOException $e){ //exception per connectDB()
			echo $e->getMessage();
		}
		finally{
			$db=null;
		}
	}
	
	public function jsonSerialize()
	{
		return get_object_vars($this);
	}
}


?>