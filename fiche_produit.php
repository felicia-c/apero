<?php
require_once("inc/init.inc.php");

require_once("inc/header.inc.php");

echo $msg;

?>
      
		<div class="box_info">
			<h1>Fiche Article</h1>
<?php

	if(isset($_GET['id_produit']))
	{
	$resultat = executeRequete("SELECT * FROM produit WHERE id_produit = '$_GET[id_produit]'");
		if($resultat -> num_rows <= 0)
		{
			header("location:boutique.php");
			exit;
		}
		$mon_produit = $resultat->fetch_assoc();
		
			echo '<div style="text-align: center;">';
			
			echo '<div><img src="images/apero_logo.png" width="300"></div>';
			
			echo '<img src="'. $mon_produit['photo'].'" style=" width: 300px; max-width: 100%;" />';
			echo '<h3>'. $mon_produit['titre'] .'</h3>';
			echo '<p>'. $mon_produit['prix'].' €</p>';
			echo '<hr />';
			if($mon_produit['stock'] > 0)
			{
				echo '<p><strong>'. $mon_produit['stock'].' en stock</strong></p>';
				
				
			//AJOUTER AU PANIER	
				echo '<form method="post" action="'.RACINE_SITE.'panier.php" class="form-inline">';
				echo '<input type="hidden" name="id_produit" value="'. $mon_produit['id_produit'].'" />';
			
				echo '<label for="quantite">Quantité </label>';
				echo '<select id="quantite" name="quantite" class="form-control" style="width: 200" >';
				for($i=1; $i <= $mon_produit['stock'] && $i<=5 ; $i++)
				{
					echo '<option>'. $i .'</option>';
				}
				echo '</select>';
				echo '<input type="submit" name="ajout_panier" value="Ajouter au panier" class="button"  >';
				echo '</form><br />';
			}
			else
			{
				echo "<p><strong>Cet article n'est plus en stock ! </strong></p>";
			}
			echo '<p><br /><strong>Référence:</strong> '. $mon_produit['reference'].' - <strong>Catégorie:</strong> '. $mon_produit['categorie'].'</p>';		
			echo '<p><strong>Couleur:</strong> '. $mon_produit['couleur'].'</p>';
			echo '<p><strong>Taille:</strong> '. $mon_produit['taille'].'</p>';
	
			echo '<p><strong>Description:</strong> '. $mon_produit['description'].'</p>';
			
			echo '<hr />';
			//lien de retour vers la categorie du produit
			echo '<br /><a href="boutique.php?action=tri_categorie&categorie='.$mon_produit['categorie'].'" class="btn btn-warning">Retour à '.$mon_produit['categorie'] .'</a> ';
			
			echo '</div>';
	}
else echo "OUPS ! Il y a un petit soucis !"
?>
	</div>
	  
	  
  <?php
	require_once("inc/footer.inc.php");
  
  ?>