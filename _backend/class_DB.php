<?php
// require_once 'class_Utente.php';

define('HOST','localhost');
define('DBNAME','db_gesturniv2');
define('USERNAME','root');
define('PASSWORD','');  

//for DB Connection
$concat='mysql:host='.HOST.';dbname='.DBNAME;
define('STRTOCNCT',$concat);

function connectDB()
{
	$db=new PDO(STRTOCNCT,USERNAME,PASSWORD);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $db;
}


?>