<?php
require_once("inc/init.inc.php");



require_once("inc/header.inc.php");


?>
     
<div class="box_info col1">
<h4> Catégories</h4>
				
<?php

//recup la liste des categories en BDD et les afficher ici dans l'ordre alpha dans une list ul li
// debug($_GET);
echo "<br /><ul>";
$resultat = executeRequete('SELECT DISTINCT categorie FROM produit ORDER BY categorie ');


while ($ligne = $resultat->fetch_assoc()) 
{
	echo '<li><a class="btn btn-warning col-md-12" style="margin-bottom: 20px;" href="?action=tri_categorie&categorie='. $ligne['categorie'] .'" >'. $ligne['categorie'] .'</a></li>';
}
echo '<li><a class="btn btn-warning col-md-12" style="margin-bottom: 20px;" href="?action=order&tri=tous">Tous les articles</a></li>';

echo '</ul>';


// COULEUR
$resultat = executeRequete('SELECT DISTINCT couleur FROM produit ORDER BY couleur');

echo '<h4><i class="glyphicon glyphicon-star"></i> Préférences</h4>
	<form class="form" method="get" action="?action=tri">
		<label for="couleur">Trier par couleur</label><br />
		<select class="form-control" id="couleur" name="couleur">
			<option value="" >Séléctionnez une couleur</option>';

	while ($ligne = $resultat->fetch_assoc()) 
	{
		echo '<option value="'. $ligne['couleur'] .'" >'. $ligne['couleur'] .'</option>' ;
	}
	echo '</select><br />';
// echo '<input type="submit" class="btn btn-success" name="action" value="Trier">';
// echo '</form>';

//TAILLE

$resultat = executeRequete('SELECT DISTINCT taille FROM produit ORDER BY taille DESC');

// echo '<h4><i class="glyphicon glyphicon-star"></i> Taille</h4>';
// echo '<form class="form" method="get" action="?action=tri_taille&couleur='. $ligne['taille'] .'">';
echo '<label for="taille">Ou par taille </label><br />
	<select class="form-control" id="taille" name="taille">
		<option value="" >Séléctionnez une taille</option>';

while ($ligne = $resultat->fetch_assoc()) 
{
	echo '<option value="'. $ligne['taille'] .'" >'. $ligne['taille'] .'</option>' ;
}
echo '</select><br />';
// echo '<input type="submit" class="btn btn-success" name="action" value="Trier">';
// echo '</form>';

//PRIX

$resultat = executeRequete('SELECT DISTINCT prix FROM produit ORDER BY prix ASC');

// echo '<h4><i class="glyphicon glyphicon-star"></i> Prix</h4>';
// echo '<form class="form" method="get" action="?action=tri_prix&prix='. $ligne['prix'] .'">';
	echo '<label for="prix">Ou par prix</label><br />
	<select class="form-control" id="triprix" name="triprix">
		<option value="" >Séléctionnez un prix</option>';

		while ($ligne = $resultat->fetch_assoc()) 
		{
			echo '<option value="'. $ligne['prix'] .'" >'. $ligne['prix'] .'</option>' ;
		}
		echo '</select>
			<input type="submit" class="btn" style="margin-top: 20px; margin-bottom: 40px" name="action" value="Trier">
		</form>
		<a class="btn " style="margin-bottom: 20px;" href="?action=order&tri=min" >- de 20€</a><br /> 
		<a class="btn " style="margin-bottom: 20px;" href="?action=order&tri=mid">  de 20€ à 50€</a><br /> 
		<a class="btn " style="margin-bottom: 20px;" href="?action=order&tri=max">  + de 50€</a><br />
		<a class="btn " style="margin-bottom: 20px;" href="?action=order&tri=tous">Tous les articles</a><br /><br />';



?>
</div>	
<div class="box_info col2">
<?php
	echo $msg;
	
	if(isset($_GET['action']))
	{
	//AFFICHAGE CATEGORIE
		if(isset($_GET['categorie']))
		{
			$resultat = executeRequete("SELECT * FROM produit WHERE categorie ='$_GET[categorie]'");
			afficheProduit($resultat);
		}
	// AFFICHAGE COULEUR
		if(isset($_GET['couleur']))
		{
			$resultat = executeRequete("SELECT * FROM produit WHERE couleur = '$_GET[couleur]' ORDER BY categorie");
			afficheProduit($resultat);
		}
		
	// AFFICHAGE TAILLE

		if(isset($_GET['taille']))
		{
			$resultat = executeRequete("SELECT * FROM produit WHERE taille = '$_GET[taille]' ORDER BY categorie");
			afficheProduit($resultat);
		}
	// AFFICHAGE PRIX
		if(isset($_GET['triprix']))
		{
			$resultat = executeRequete("SELECT * FROM produit WHERE prix = '$_GET[triprix]' ORDER BY categorie");
			afficheProduit($resultat);
		}
		
		if(isset($_GET['tri']))      
		{
			switch($_GET['tri'])
			{
				// AFFICHAGE PRIX MIN(ORDER)
				case 'min':
					$resultat = executeRequete("SELECT * FROM produit WHERE prix < 20 ORDER BY categorie");
					afficheProduit($resultat);
				break;
				
				
				// AFFICHAGE PRIX MID(ORDER)
				case 'mid':
				
					$resultat = executeRequete("SELECT * FROM produit WHERE (prix BETWEEN 20 AND 50) ORDER BY categorie");
					afficheProduit($resultat);
					break;
				
				case 'max':
					$resultat = executeRequete("SELECT * FROM produit WHERE prix > 50 ORDER BY categorie");
					afficheProduit($resultat);
				break;
				
				case 'tous':
					$resultat = executeRequete('SELECT * FROM produit ORDER BY categorie ');
					afficheProduit($resultat);
				break;
				
				default: //PAR DEFAUT AFFICHAGE DE TOUs les
				$resultat = executeRequete('SELECT * FROM produit ORDER BY categorie ');
				afficheProduit($resultat);
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
	$resultat = executeRequete('SELECT * FROM produit ORDER BY categorie ');
	afficheProduit($resultat);

}				
				
?>
			
		</div>
  <?php
	require_once("inc/footer.inc.php");
  
  ?>