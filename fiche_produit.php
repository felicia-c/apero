<?php
require_once("inc/init.inc.php");
$titre_page = "T-shirts Apéro";

require_once("inc/header.inc.php");

echo $msg;

echo '<div class="box_info fiche_produit">';
if(isset($_GET['id_produit']))
{
	$resultat = executeRequete("SELECT * FROM produit INNER JOIN taille_stock ON produit.id_produit = taille_stock.id_produit WHERE produit.id_produit = '$_GET[id_produit]'");
	if($resultat -> num_rows < 1)
	{
		header("location:boutique.php");
		exit;
	}
	
	$mon_produit = $resultat->fetch_assoc();
	echo '<h1><a href="'.RACINE_SITE.'boutique.php">T-shirts</a> / <a href="'.RACINE_SITE.'boutique.php?action=tri_categorie&categorie='. $mon_produit['categorie'] .'">'.$mon_produit['categorie'].'</a> / '.$mon_produit['titre'].'</h1><br />';
	
		
	echo '<div class="text-center"><img src="'.RACINE_SITE.'images/apero_logo.png" width="300"></div>';
	echo '<div class="bloc_fiche block_inline text-center">';
	echo '<img class="make-it-slow make-it-fast box" src="'.$mon_produit['photo'].'" style=" width: 300px; max-width: 100%;" />';
	echo '</div>';
	echo '<div class="bloc_fiche block_inline text-center"">';	
	echo '<h2>'. $mon_produit['titre'] .'</h2>';
	echo '<p>'. round($mon_produit['prix']*1.2, 2) .' €</p>';
	echo '<hr />';

	//AJOUTER AU PANIER	
	echo '<p><strong>Couleur:</strong> '. $mon_produit['couleur'].'</p><br />';
	echo '<form method="post" action="'.RACINE_SITE.'panier.php" class="form-inline">';
	
	$res_taille_stock = executeRequete("SELECT id_taille_stock, id_produit AS produit, stock AS stock, id_taille FROM taille_stock  WHERE id_produit = '$mon_produit[id_produit]'");
	
	echo '<label for="taille_stock">Taille </label>
			<select name="taille_stock" required>';
		
	while($ligne_taille_stock = $res_taille_stock -> fetch_assoc())
	{
		$res_taille= executeRequete("SELECT * FROM taille WHERE id_taille = '$ligne_taille_stock[id_taille]'");
		$taille= $res_taille-> fetch_assoc();
		//foreach($ligne_taille_stock AS $indice => $valeur)
		//{
			if($ligne_taille_stock['stock'] > 0)
			{
				echo '<option value="'.$ligne_taille_stock['id_taille_stock'].'">'.$taille['taille'].' | en stock: '.$ligne_taille_stock['stock'].' </option>';
			}
		//}
	}
		
	//echo '<input type="hidden" name="taille_stock" value="'.$ligne_taille_stock['id_taille_stock'].'" />';
	
	echo '</select>';

	echo '<label for="quantite">Quantité </label>';
	echo '<select id="quantite" name="quantite" class="form-control"  >';
	for($i=1; $i<= 10 ; $i++)
	{
		echo '<option value="'.$i.'">'. $i .'</option>';
	}
	echo '</select>';
	
	echo '<input type="submit" name="ajout_panier" value="Ajouter au panier" class="button"  >';
	echo '</form><br />';
	echo '</div>';
	echo '<p><br /><strong>Référence:</strong> '. $mon_produit['reference'].' - <strong>Collection:</strong> '. $mon_produit['categorie'].'</p>';		
	echo '<p><strong>Couleur:</strong> '. $mon_produit['couleur'].'</p>';


	echo '<p><strong>Description:</strong> '. $mon_produit['description'].'</p><br /><br /><hr />
	<h3 class="orange">Apéros proposés avec ce T-shirt:</h3>';
	$req="SELECT bar.id_bar, bar.nom_bar, bar.cp, promo_bar.description, promo_bar.date_debut, promo_bar.date_fin, promo_bar.id_bar, promo_bar.categorie_produit, bar.statut FROM promo_bar INNER JOIN bar ON bar.id_bar=promo_bar.id_bar WHERE promo_bar.categorie_produit='$mon_produit[categorie]' AND (promo_bar.date_fin > NOW() AND bar.statut!='0') ORDER BY promo_bar.date_debut";
	
	affichePromoBar($req);
	
	//lien de retour vers la categorie du produit
	echo '<br /><a href="'.RACINE_SITE.'boutique.php?action=tri_categorie&categorie='.str_replace('#', '',$mon_produit['categorie']).'">Retour à '.$mon_produit['categorie'] .'</a> ';
}
else
{
	echo "OUPS ! Il y a un petit soucis !";
}
?>
	</div>
	<br />
	<br />
	  
  <?php
	require_once("inc/footer.inc.php");
  
  ?>