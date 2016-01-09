<?php
require_once("inc/init.inc.php");

require_once("inc/header.inc.php");

echo $msg;

echo '<div class="box_info">
	<h1>Fiche Article</h1>';

if(isset($_GET['id_produit']))
{
	$resultat = executeRequete("SELECT * FROM produit INNER JOIN taille_stock ON produit.id_produit = taille_stock.id_produit WHERE produit.id_produit = '$_GET[id_produit]'");
	if($resultat -> num_rows < 1)
	{
		header("location:boutique.php");
		exit;
	}
	$mon_produit = $resultat->fetch_assoc();
	
	echo '<div style="text-align: center;">';
	
	echo '<div><img  src="images/apero_logo.png" width="300"></div>';
	
	echo '<img class="make-it-slow make-it-fast box" src="'. $mon_produit['photo'].'" style=" width: 300px; max-width: 100%;" />';
	echo '<h3>'. $mon_produit['titre'] .'</h3>';
	echo '<p>'. $mon_produit['prix']*1.2 .' €</p>';
	echo '<hr />';

	//AJOUTER AU PANIER	
	echo '<form method="post" action="'.RACINE_SITE.'panier.php" class="form-inline">';
	
	$res_taille_stock = executeRequete("SELECT id_taille_stock, id_produit AS produit, stock AS stock, id_taille FROM taille_stock  WHERE id_produit = '$mon_produit[id_produit]'");
	
	echo '<label for="taille">Taille </label>
			<select name="taille" required>';
		
			while($ligne_taille_stock = $res_taille_stock -> fetch_assoc())
			{
				$res_taille= executeRequete("SELECT * FROM taille WHERE id_taille = '$ligne_taille_stock[id_taille]'");
				$taille= $res_taille-> fetch_assoc();
				//foreach($ligne_taille_stock AS $indice => $valeur)
				//{
					if($ligne_taille_stock['stock'] > 0)
					{
						echo '<option value="'.$ligne_taille_stock['id_taille'].'">'.$taille['taille'].' | en stock: '.$ligne_taille_stock['stock'].' </option>';
					}
				//}
			}
				
	//echo '<input type="hidden" name="id_taille_stock" value="'.$ligne_taille_stock['id_taille_stock'].'" />';
	
	
	
	
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
	
	echo '<p><br /><strong>Référence:</strong> '. $mon_produit['reference'].' - <strong>Catégorie:</strong> '. $mon_produit['categorie'].'</p>';		
	echo '<p><strong>Couleur:</strong> '. $mon_produit['couleur'].'</p>';


	echo '<p><strong>Description:</strong> '. $mon_produit['description'].'</p>';

	echo '<hr />';
	//lien de retour vers la categorie du produit
	echo '<br /><a href="boutique.php?action=tri_categorie&categorie='.str_replace('#', '',$mon_produit['categorie']).'" class="btn btn-warning">Retour à '.$mon_produit['categorie'] .'</a> ';

	echo '</div>';
}
else
{
	echo "OUPS ! Il y a un petit soucis !";
}
?>
	</div>
	  
	  
  <?php
	require_once("inc/footer.inc.php");
  
  ?>