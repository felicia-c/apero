<?php
require_once('inc/init.inc.php');
$titre_page = "Bars Apéro";
require_once('inc/header.inc.php');
$id_bar = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING ); 
if(isset($_GET['ville']))
{ 
	$ville = filter_input( INPUT_GET, 'ville', FILTER_SANITIZE_STRING );
	//echo $ville;
}
$req = "";
$table=" bar WHERE statut='1'";
if(isset($_GET['action']) && (isset($_GET['ville'])))
{ 
	$req .= "SELECT *, bar.id_bar AS bar_id_bar FROM $table AND ";
//AFFICHAGE CATEGORIE
	
		$req .= " ville ='$ville' ";
}
else
{	
	
	//$res_avg = executeRequete("SELECT  AS moyenne FROM avis WHERE id_bar='$id_bar' ");
	$req="SELECT *, bar.id_bar AS bar_id_bar FROM $table";
	
}
//echo '<h2><a href="'.RACINE_SITE.'bars_et_promos.php" >Bars</a> /</h2><div class="box_info no_border">';
echo '<div class="tri"><p>';
$resultat_ville = executeRequete("SELECT DISTINCT ville FROM bar ORDER BY ville");

while ($ligne = $resultat_ville->fetch_assoc()) 
{
	echo ' <a class="'; 
	if(isset($_GET['ville']) && $_GET['ville'] == $ligne['ville'])
	{
		echo ' actif ';
	}
	echo 'button" style="margin-bottom: 20px;" href="?action=tri&ville='. $ligne['ville'] .'" > '. $ligne['ville'] .' </a> | ';
}

$req = paginationGestion(6, $table, $req);
$lien = "";
//echo '<h2><a href="'.RACINE_SITE.'bars_et_promos.php" >Bars</a> /</h2> ';

echo '<div class="block_inline box_info no_border">';
//echo  '<h3> > <a href="'.RACINE_SITE.'bars_et_promos.php?action=promos">Voir les apéros</a></h3>';
afficheVignetteBar($req);
echo '</div>';
affichagePaginationGestion(6, $table, $lien);
echo '<br /><br />';

require_once('inc/footer.inc.php');