<?php

require_once 'class_Mese.php';

Class ListaMesi implements JsonSerializable{
	private $mesi = array();
	
	public function recuperaListaMesiInDB(){
		try{
			$db=connectDB();
			$sql=$db->prepare('SELECT * FROM mesi ORDER BY anno,numMese DESC LIMIT 2');
			try{
				$sql->execute([]);
				while($res=$sql->fetch(PDO::FETCH_ASSOC))
				{
					$mese = new Mese();
					$mese->setAnno($res['anno']);
					$mese->setNumMese($res['numMese']);
					$mese->setNomeMese($res['nomeMese']);
					$mese->setNumGiorniMese($res['numGiorniMese']);
					$mese->setNumGiorniUtiliMese($res['numGiorniUtili']);
					$mese->setNumGiorniFestiviMese($res['numGiorniFestivi']);
					$this->mesi[] = $mese;
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
	
	public function getListaMesi(){return $this->mesi;}
	
	public function jsonSerialize()
	{
		return get_object_vars($this);
	}
	
}


?>