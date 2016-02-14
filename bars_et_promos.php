<?php
require_once('inc/init.inc.php');
$titre_page = "Bars Apéro";
require_once('inc/header.inc.php');



if(isset($_GET['tri']))
{ 
	$tri = filter_input( INPUT_GET, 'tri', FILTER_SANITIZE_STRING );
	//echo $ville;
}
if(isset($_GET['ville']))
{ 
	$ville = filter_input( INPUT_GET, 'ville', FILTER_SANITIZE_STRING );
	//echo $ville;
}
$req = "";
$table=" bar WHERE statut='1'";
if(isset($tri) && $tri == 'ville' )
{ 
//AFFICHAGE VILLES
	$table .= " AND ville ='$ville' ";
}
$orderby = "";

//AFFICHAGE ORDRE ALPHA
if((isset($tri) && $tri == 'alpha') && isset($_GET['asc']))
{
	$orderby .= " ORDER BY nom_bar";
}
if((isset($tri) && $tri == 'alpha') && isset($_GET['desc']))
{
	$orderby .= " ORDER BY nom_bar";
}
$req = "SELECT *, bar.id_bar AS bar_id_bar FROM $table $orderby";

echo '<div class="tri"><p>';
$resultat_ville = executeRequete("SELECT DISTINCT ville FROM bar WHERE statut='1' ORDER BY ville");

while ($ligne = $resultat_ville->fetch_assoc()) 
{
	echo ' <a class="'; 
	if(isset($ville) && $ville == $ligne['ville'])
	{
		echo ' actif ';
	}
	echo 'button" style="margin-bottom: 20px;" href="?tri=ville&ville='. $ligne['ville'] .'" > '. $ligne['ville'] .' </a> | ';
}
echo '<br /><a class="'; 
if((isset($tri) && $tri =='all'))
{
	echo ' actif ';
	}
echo 'button" style="margin-bottom: 20px;" href="?tri=all" >Tous les bars</a>';
echo '<br /><a class="'; 
if((isset($tri) && $tri =='alpha') && isset($_GET['asc']))
{
	echo ' actif ';
}
echo 'button" style="margin-bottom: 20px;" href="?tri=alpha&asc" >A-Z</a>';
echo ' | <a class="'; 
if((isset($tri) && $tri =='alpha') && isset($_GET['desc']))
{
	echo ' actif ';
	}
echo 'button" style="margin-bottom: 20px;" href="?tri=alpha&desc" >Z-A</a>';



$req = paginationGestion(9, $table, $req);
$lien = "";


echo '<div class="block_inline box_info no_border">';

afficheVignetteBar($req);
echo '</div>';
affichagePaginationGestion(9, $table, $lien);
echo '<br /><br />';

require_once('inc/footer.inc.php');