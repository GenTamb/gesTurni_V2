<?php
require_once '_costanti.php';
require_once 'class_Giorno.php';
require_once 'class_Dipendente.php';
require_once 'class_ListaDipendenti.php';

class Mese implements JsonSerializable{
	private $anno;
	private $nomeMese;
	private $numMese;
	private $numGiorniMese;
	private $numGiorniUtili;
	private $numGiorniFestivi;
	private $calendarioFestivi = array();
	private $calendarioGiorni = array();
	
	public function InitMese($anno,$nomeMese,$numGiorniMese,$primoGiornoDelMese,$calendarioFestivi,$listaDipendenti){
		$keyMese = array_search($nomeMese,mesi);
		if($keyMese){
			$this->setAnno($anno);
			$this->setNomeMese($nomeMese);
			$this->setNumMese($keyMese);
			$this->setNumGiorniMese($numGiorniMese);
			$keySettimana = array_search($primoGiornoDelMese,settimana);
			if($keySettimana){
				for ($i = 1; $i <= $this->numGiorniMese; $i++) {
					$festivo=0;
					if(in_array($i,$calendarioFestivi)) $festivo=1;
					$giorno = new Giorno();
					$giorno->InitGiorno(settimana[$keySettimana],$i,$keyMese,$anno,$festivo);
					$this->calendarioGiorni[$i] = $giorno;
					if($giorno->getFestivo()) $this->calendarioFestivi[] = $i;
					$keySettimana++;
					if($keySettimana==8) $keySettimana=1;
				}
				$this->numGiorniFestivi=count($this->calendarioFestivi);
				$this->numGiorniUtili=$numGiorniMese-$this->numGiorniFestivi;
				
			}
		}
	}
	public function getAnno(){return $this->anno;}
	public function getNomeMese(){return $this->nomeMese;}
	public function getNumMese(){return $this->numMese;}
	public function getNumGiorniMese(){return $this->numGiorniMese;}
	public function getNumGiorniUtiliMese(){return $this->numGiorniUtili;}
	public function getNumGiorniFestiviMese(){return $this->numGiorniFestivi;}
	public function getCalendarioGiorni(){return $this->calendarioGiorni;}
	public function getCalendarioTurni(){return $this->calendarioTurni;}
	
	public function setAnno($anno){ $this->anno=$anno;}
	public function setNomeMese($nomeMese){ $this->nomeMese=$nomeMese;}
	public function setNumMese($numMese){ $this->numMese=$numMese;}
	public function setNumGiorniMese($numGiorniMese){ $this->numGiorniMese=$numGiorniMese;}
	public function setNumGiorniUtiliMese($numGiorniUtili){ $this->numGiorniUtili=$numGiorniUtili;}
	public function setNumGiorniFestiviMese($numGiorniFestivi){ $this->numGiorniFestivi=$numGiorniFestivi;}
	
	
	public function generaTurni($listaDipendenti){
			foreach ($this->getCalendarioGiorni() as $giorno){
				$giorno->setTurniGiornata($listaDipendenti);
				$giorno->inserisciGiornataInDB();
			}
	}
	
	public function inserisciMeseInDB(){
		try{
			if(!$this->checkEsistenzaMese())
			{
				$db=connectDB();
				$sql=$db->prepare('INSERT INTO mesi (anno,numMese,nomeMese,numGiorniMese,numGiorniUtili,numGiorniFestivi,calendarioFestivi)
						VALUES (?,?,?,?,?,?,?)');
				try{
					$sql->execute([$this->anno,$this->numMese,$this->nomeMese,$this->numGiorniMese,$this->numGiorniUtili,$this->numGiorniFestivi,json_encode($this->calendarioFestivi)]);
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
	
	public function checkEsistenzaMese(){
		try{
			$db=connectDB();
			$sql=$db->prepare('SELECT COUNT(*) AS CONTO FROM mesi WHERE anno = ? AND numMese = ?');
			try{
				$sql->execute([$this->anno,$this->numMese]);
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
	
	public function aggiornaMeseInDB(){
		try{
			$db=connectDB();
			$sql=$db->prepare("UPDATE mesi SET anno = ? , numMese = ? , nomeMese = ? , numGiorniMese = ? , numGiorniUtili = ? ,
					numGiorniFestivi = ? , calendarioFestivi = ? WHERE anno = ? AND numMese = ?");
			try{
				$sql->execute([$this->anno,$this->numMese,$this->nomeMese,$this->numGiorniMese,$this->numGiorniUtili,$this->numGiorniFestivi,json_encode($this->calendarioFestivi),$this->anno,$this->numMese]);
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
	
	public function recuperaMeseInDB($anno,$keyMese){
		try{
			$db=connectDB();
			$sql=$db->prepare('SELECT * FROM mesi AS M JOIN giorni AS G 
							   ON M.anno = G.annoAppartenenza AND M.numMese = G.meseAppartenenza
					           WHERE M.anno = ? AND M.numMese = ?');
			try{
				$datiMese = false;
				$sql->execute([$anno,$keyMese]);
				while($res=$sql->fetch(PDO::FETCH_ASSOC))
				{
					if(!$datiMese){
						$this->anno = $res['anno'];
						$this->numMese = $res['numMese'];
						$this->nomeMese = $res['nomeMese'];
						$this->numGiorniMese = $res['numGiorniMese'];
						$this->numGiorniUtili = $res['numGiorniUtili'];
						$this->numGiorniFestivi = $res['numGiorniFestivi'];
						$this->calendarioFestivi = json_decode($res['calendarioFestivi']);
						$datiMese = true;
					}
					$giorno = new Giorno();
					$giorno->setNomeGiorno($res['nomeGiorno']);
					$giorno->setNumeroGiorno($res['numeroGiorno']);
					$giorno->setMeseAppartenenza($res['meseAppartenenza']);
					$giorno->setAnnoAppartenenza($res['annoAppartenenza']);
					$giorno->setFestivo($res['festivo']);
					$giorno->setTurniGiornalieri4(json_decode($res['turniGiornalieri4']));
					$giorno->setTurniGiornalieri6(json_decode($res['turniGiornalieri6']));
					$giorno->setTurniGiornalieri8(json_decode($res['turniGiornalieri8']));
					$this->calendarioGiorni[] = $giorno;
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
	
	public function jsonSerialize()
	{
		return get_object_vars($this);
	}
}



?>