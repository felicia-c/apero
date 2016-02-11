<?php
require_once("inc/init.inc.php");
$titre_page = "T-shirts Apéro";
//APERO - Felicia Cuneo - 12/2015//
$resultat_categorie = executeRequete("SELECT DISTINCT categorie FROM produit ORDER BY categorie");
$resultat_couleur = executeRequete("SELECT DISTINCT couleur FROM produit ORDER BY couleur");
//$resultat_taille = executeRequete("SELECT DISTINCT id_taille FROM taille_stock INNER JOIN produit ON taille_stock.id_produit = produit.id_produit ORDER BY id_taille DESC");
//REQUETE TRI
$req = "";
if(isset($_GET['action']))
{ 
	$req .= "SELECT * FROM produit WHERE";
//AFFICHAGE CATEGORIE
	if(isset($_GET['categorie']))
	{
		$req .= " categorie ='$_GET[categorie]' ";
	}
// AFFICHAGE COULEUR
//	if(isset($_GET['couleur']))
//	{
//		$req .= "AND couleur = '$_GET[couleur]' ";
//	}
	
// TRI PRIX
if(isset($_GET['tri']))      
{
	switch($_GET['tri'])
	{
		// AFFICHAGE PRIX MIN(ORDER)
		case 'min':
			$req .= " prix < 15 ";
		break;

		// AFFICHAGE PRIX MID(ORDER)
		case 'mid':
		
			$req .= " (prix BETWEEN 15 AND 25) ";
			break;
		// AFFICHAGE PRIX MAX(ORDER)
		case 'max':
			$req .= " prix > 25 ";
		break;
		
		case 'tous':
			$req = "SELECT * FROM produit";
		break;
		
		default: //PAR DEFAUT AFFICHAGE DE TOUS les Produits
			$req .= "";
		break;
	}		
}

// else
// {
	// header("location:boutique.php");
	// exit;
	
// }
}	
else // PAR DEFAUT
{
	$req .= "SELECT * FROM produit ";
	
}	


require_once("inc/header.inc.php");

  
//echo '<div class="box_info col1 ">';
// debug($_GET);


echo '<br /><div class="tri"><p>';
while ($ligne = $resultat_categorie->fetch_assoc()) 
{
	echo ' <a class="'; 
	if(isset($_GET['categorie']) && $_GET['categorie'] == $ligne['categorie'])
	{
		echo ' actif ';
	}
	echo 'button" style="margin-bottom: 20px;" href="?action=tri_categorie&categorie='. $ligne['categorie'] .'" > '. $ligne['categorie'] .' </a> | ';
}


	//PRIX
	echo '<a  class="'; 
	if(isset($_GET['tri']) && $_GET['tri'] == 'min')
	{
		echo ' actif ';
	}
	echo 'btn" style="margin-bottom: 20px;" href="?action=order&tri=min" >- de 15€</a> |  
	<a class="'; 
	if(isset($_GET['tri']) && $_GET['tri'] == 'mid')
	{
		echo ' actif ';
	}
	echo 'btn" style="margin-bottom: 20px;" href="?action=order&tri=mid">  de 15€ à 25€</a> |  
	<a class="'; 
	if(isset($_GET['tri']) && $_GET['tri'] == 'max')
	{
		echo ' actif ';
	}
	echo 'btn" style="margin-bottom: 20px;" href="?action=order&tri=max">  + de 25€</a> | 
	<a class="'; 
	if(isset($_GET['tri']) && $_GET['tri'] == 'tous')
	{
		echo ' actif ';
	}
	echo 'btn" style="margin-bottom: 20px;" href="?action=order&tri=tous">Tous les articles</a> 
</div>';

echo $msg;


//AFFICHAGE

echo '<div class="box_info no_border">';	

//echo '<h1><a href="'.RACINE_SITE.'boutique.php" >T-shirts </a>/ ';
if(isset($_GET['categorie']))
{ 
	$categorie = filter_input( INPUT_GET, 'categorie', FILTER_SANITIZE_STRING );
	echo $categorie;
}
echo '</h1>';
//paginationRecherche(5, $req); // pagination /tri
afficheProduits($req); // affichage Produit
//affichagePaginationRecherche(5, $req); // pagination / tri 2
echo '</div>					
</div>
<br />
<br />';

require_once("inc/footer.inc.php");