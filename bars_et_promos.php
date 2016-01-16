<?php
require_once('inc/init.inc.php');
$titre_page = "Bars Apéro";
require_once('inc/header.inc.php');
$id_bar = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING ); 

if(isset($_GET['action']) && $_GET['action'] == 'promos')
{
	echo '<h1><a href="'.RACINE_SITE.'bars_et_promos.php">Bars</a> / Promos</h1>';
	echo '<div class="block_inline">';
	echo '<h2>les apéros</h2>';
	$req_promo= "SELECT * FROM promo_bar INNER JOIN bar ON promo_bar.id_bar=bar.id_bar WHERE promo_bar.date_fin > NOW() GROUP BY promo_bar.id_bar ORDER BY promo_bar.date_debut ";
	affichePromoBar($req_promo);
	echo '</div>';
}
else
{	

	$table="bar WHERE statut='1' ORDER BY id_bar DESC  ";	
	$req="SELECT * FROM $table";
	$req = paginationGestion(6, $table, $req);
	$lien = "";
	echo '<div class="block_inline box_info no_border">';
	//echo  '<h3> > <a href="'.RACINE_SITE.'bars_et_promos.php?action=promos">Voir les apéros</a></h3>';
	afficheVignetteBar($req);
	echo '</div>';
	affichagePaginationGestion(6, $table, $lien);
}
echo '<br /><br />';

require_once('inc/footer.inc.php');