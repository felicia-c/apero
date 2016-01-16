<?php 
require_once("inc/init.inc.php");
//APERO  - Felicia Cuneo - 01/2016
$titre_page = "Accueil";
?>

<body>




<?php 
require_once("inc/header.inc.php"); 

$table="bar WHERE statut='1' ORDER BY id_bar DESC LIMIT 3";	
	$req_bar="SELECT * FROM $table";
	
	$lien = "";
	echo '<div class="block_inline box_info no_border">';
	//echo  '<h3> > <a href="'.RACINE_SITE.'bars_et_promos.php?action=promos">Voir les apéros</a></h3>';
	afficheVignetteBar($req_bar);
	echo '</div>';

echo '<div><div class="text_index"><p class="block_inline">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
</p></div>';
/*
echo '<div class="aperos_index">
<h3>Nouveaux apéros</h3>
';
$req_promo="SELECT bar.id_bar, bar.nom_bar, promo_bar.description, promo_bar.date_debut, promo_bar.date_fin, promo_bar.id_bar, promo_bar.categorie_produit FROM bar INNER JOIN promo_bar ON bar.id_bar=promo_bar.id_bar WHERE  promo_bar.date_fin > NOW() ORDER BY promo_bar.date_debut LIMIT 3";
affichePromoBar($req_promo);
echo '</div></div>
<br />';*/
$req_tsh = "SELECT * FROM produit ORDER BY id_produit DESC LIMIT 3";
afficheProduits($req_tsh);
 echo '<br />
  	<h2 class="text-center" ><img src="images/bouteilles_apero.png" alt="apéro" class="text-center"/></h2>
        
          <h3 class="text-center">en cours de préparation...</h3>';
	
 echo '<br />
 <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>';   

echo '<br /><br />';


require_once("inc/footer.inc.php");