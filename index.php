<?php 
require_once("inc/init.inc.php");
//APERO  - Felicia Cuneo - 01/2016
$titre_page = "Accueil";
?>

<?php 
require_once("inc/header.inc.php"); 

$lien = "";
$req_tsh = "SELECT * FROM produit GROUP BY categorie DESC LIMIT 3";
echo '<div class="block_inline box_info no_border">
<h3 class="orange text-center no-margin no-padding">1 T-shirt + 1 Bar = 1 apéro offert </h3><br />

<p><a class="btn_index text-center" href="'.RACINE_SITE.'boutique.php">Choisir mon T-shirt apéro</a></p>';
afficheProduits($req_tsh);
echo '</div>';

echo '<div class="text_index text-center">
	<img src="images/apero_logo.png" alt="Les T-shirts apéro" width="300px" >
	<h2 class="orange">Prenez l\'apéro avec votre T-shirt</h2>
	<p class="block_inline ">Les T-shirts Apéro vous permettent de bénéficier de promotions dans les bars partenaires ! 
	<br /> - 
	<br/> Vous ne choisirez plus votre T-shirt par hasard</p>
	<p><a class="btn_index text-center" href="'.RACINE_SITE.'apero.php">En savoir plus</a></p></div>';

echo '<div class="block_inline box_info no_border">';
echo '<h3 class="orange">Les nouveaux bars</h3>';
//echo  '<h3> > <a href="'.RACINE_SITE.'bars_et_promos.php?action=promos">Voir les apéros</a></h3>';
$table_new="bar WHERE statut='1' ORDER BY id_bar DESC LIMIT 3";	
$req_bar="SELECT *, bar.id_bar AS bar_id_bar FROM $table_new";
afficheVignetteBar($req_bar);
echo '</div>';

echo '<br />
<p><a class="btn_index text-center" href="'.RACINE_SITE.'bars_et_promos.php">Découvrir les bars à apéro</a></p><br/>';
 

require_once("inc/footer.inc.php");