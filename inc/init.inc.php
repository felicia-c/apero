<?php
//header('Content-type: text/html; charset="UTF-8')

define ("RACINE_SITE", ""); 

define ("RACINE_SERVER", $_SERVER['DOCUMENT_ROOT'] ); 

require_once("connexion_bdd.inc.php");
require_once("fonction.inc.php");
if (session_id()=='')
{
	session_start();

}


$msg = ""; 