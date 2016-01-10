<?php
require_once('inc/init.inc.php');

require_once('inc/header.inc.php');
$id_bar = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING ); 

if(isset($_GET['action']) && $_GET['action'] == 'promos')
{
	echo '<div class="block_inline">';
	echo '<h2>les apéros</h2>';
	$req_promo= "SELECT * FROM promo_bar WHERE id_bar='$id_bar' AND date_fin > NOW() ORDER BY date_debut";
	affichePromoBar($req_promo);
	echo '</div>';
}
else
{	
	$table='bar';	
	$req="SELECT * FROM $table";
	$req = paginationGestion(6, $table, $req);
	$lien = "";
	echo '<div class="block_inline box_info">
			<h2>les bars</h2>';
	afficheVignetteBar($req);
	echo '</div>';
	affichagePaginationGestion(6, $table, $lien);
}
echo '<br /><br />';

require_once('inc/footer.inc.php');