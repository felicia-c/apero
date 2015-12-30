<?php
require_once('inc/init.inc.php');

require_once('inc/header.inc.php');

if(isset($_GET['id_bar']))
{
	$id_bar = filter_input( INPUT_GET, 'id_bar', FILTER_SANITIZE_NUMBER_INT ); 
	$req="SELECT * FROM bar WHERE id_bar = '$id_bar'";
	afficheBar($req);
	echo '<h2>Apéros proposés par ce bar</h2>';
	$req_promo= "SELECT * FROM promo_bar WHERE id_bar='$id_bar' AND date_fin > NOW() ORDER BY date_debut";
	affichePromoBar($req_promo);
}



echo '<br /><br />';

require_once('inc/footer.inc.php');