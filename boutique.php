<?php
require_once("inc/init.inc.php");
$titre_page = "T-shirts Apéro";
//APERO - Felicia Cuneo - 12/2015//
$resultat_categorie = executeRequete("SELECT DISTINCT categorie FROM produit ORDER BY categorie");
$resultat_couleur = executeRequete("SELECT DISTINCT couleur FROM produit ORDER BY couleur");
$resultat_taille = executeRequete("SELECT DISTINCT taille FROM produit ORDER BY taille DESC");


require_once("inc/header.inc.php");

  
echo '<div class="box_info col1">';
// debug($_GET);
$req = "";

echo "<p>";
while ($ligne = $resultat_categorie->fetch_assoc()) 
{
	echo '<a class="button" style="margin-bottom: 20px;" href="?action=tri_categorie&categorie='. $ligne['categorie'] .'" >'. $ligne['categorie'] .'</a> | ';
}
echo '<a class="button" style="margin-bottom: 20px;" href="?action=order&tri=tous">Tous les articles</a>
</ul>';


// COULEUR

echo '<form class="form" method="get" action="?action=tri">
			<label for="couleur">Trier par couleur</label>
			<select class="form-control" id="couleur" name="couleur">
				<option value="" >Séléctionnez une couleur</option>';
	while ($ligne = $resultat_couleur->fetch_assoc()) 
	{
		echo '<option value="'. $ligne['couleur'] .'" >'. $ligne['couleur'] .'</option>' ;
	}
	echo '</select>';
	
	//TAILLE

	echo '<label for="taille">Ou par taille </label>
		<select class="form-control" id="taille" name="taille">
			<option value="" >Séléctionnez une taille</option>';

	while ($ligne = $resultat_taille->fetch_assoc()) 
	{
		echo '<option value="'. $ligne['taille'] .'" >'. $ligne['taille'] .'</option>' ;
	}
	echo '</select>';

	//PRIX
	echo '<input type="submit" class="btn" style="margin-top: 20px; margin-bottom: 40px" name="action" value="Trier">
	</form>
	<a class="btn " style="margin-bottom: 20px;" href="?action=order&tri=min" >- de 15€</a> |  
	<a class="btn " style="margin-bottom: 20px;" href="?action=order&tri=mid">  de 15€ à 25€</a> |  
	<a class="btn " style="margin-bottom: 20px;" href="?action=order&tri=max">  + de 25€</a> | 
	<a class="btn " style="margin-bottom: 20px;" href="?action=order&tri=tous">Tous les articles</a> | 
</div>	


<div class="col2">';

echo $msg;

if(isset($_GET['action']))
{ 
	$req .= "SELECT * FROM produit WHERE stock > 0 ";
//AFFICHAGE CATEGORIE
	if(isset($_GET['categorie']))
	{
		$req .= "AND categorie ='$_GET[categorie]' ";
	}
// AFFICHAGE COULEUR
	if(isset($_GET['couleur']))
	{
		$req .= "AND couleur = '$_GET[couleur]' ";
	}
	
// AFFICHAGE TAILLE
	if(isset($_GET['taille']))
	{
		$req .= "AND taille = '$_GET[taille]' ";
	}	
	if(isset($_GET['tri']))      
	{
		switch($_GET['tri'])
		{
			// AFFICHAGE PRIX MIN(ORDER)
			case 'min':
				$req .= "AND prix < 15 ";
			break;

			// AFFICHAGE PRIX MID(ORDER)
			case 'mid':
			
				$req .= "AND (prix BETWEEN 15 AND 25) ";
				break;
			// AFFICHAGE PRIX MAX(ORDER)
			case 'max':
				$req .= "AND prix > 25 ";
			break;
			
			case 'tous':
				$req .= "";
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
	$req .= "SELECT * FROM produit WHERE stock > 0 ";
	
}	
echo '<div class="box _info">';		
paginationRecherche(5, $req);
afficheProduits($req);
affichagePaginationRecherche(5, $req);
echo '</div>					
</div>';

require_once("inc/footer.inc.php");
  
  ?>