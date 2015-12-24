<?php
require_once("../inc/init.inc.php");

//APERO - Felicia Cuneo 12/2015

 if(!utilisateurEstConnecteEtEstAdmin())
 {
	 header("location:../connexion.php");
	exit(); // arrete l'execution du code 
 }
 
 // SUPPRESSION DES PRODUITS
 
 if(isset($_GET['action']) && $_GET['action'] == 'suppression')
{
	$resultat = executeRequete("SELECT * FROM produit WHERE id_produit= '$_GET[id_produit]'"); //on recupere les infos afin de connaitre son image  pour pouvoir la supprimer
	$produit_a_supprimer = $resultat->fetch_assoc();
	//Spécial pour la suppression de fichiers (et pas de données):
	$chemin_produit = RACINE_SERVER . $produit_a_supprimer['photo'] ; // Nous avons besoin du chemin du produit depuis la racine serveur pour supprimer la photo du serveur
	if(!empty($produit_a_supprimer['photo'] && file_exists($chemin_produit))) // FILE_EXISTS verifie si l'élément existe
	{
		unlink($chemin_produit); //UNLINK() va  SUPPRIMER le fichier du serveur
	}
	
	executeRequete("DELETE FROM produit WHERE id_produit='$_GET[id_produit]'");
	$msg .='<div class="msg_success" style="padding: 10px; text-align: center">Produit N°'. $_GET['id_produit'] .' supprimé avec succès!/div>';
	$_GET['action'] = 'affichage'; // Pour afficher le tableau d'affichage une fois qu'on a validé le formulaire on peut changer la valeur action dans $_GET

}
						
 
//ENREGISTREMENT DES PRODUITS
if(isset($_POST['enregistrement'])) //nom du bouton valider
	
{
	$reference= executeRequete("SELECT reference FROM produit WHERE reference='$_POST[reference]'");
	if($reference -> num_rows > 0 && isset($_GET['action']) && $_GET['action'] == 'ajout') //si la requete retourne un enregistrement, alors la reference est deja utilisée, on affiche un message (si on est bien dans un ajout, et pas une modif ! lors d'une modif on garde la reference de l'article, donc on ne rentrerait pas ds cette condition)
	{
		$msg .='<div class="msg_erreur" style="margin-top: 20px; padding: 10px; text-align: center">Cette référence est déjà utilisée !</div>';
	}
	else
	{  //sinon  la référence est valable, on enregistre le nouveau produit
		// $msg .= 'TEST';
		$photo_bdd =""; //evite une erreur lors de la requete INSERT si l'utilisateur ne charge pas de photo
		
		if(isset($_GET['action']) && $_GET['action'] == 'modification')
		{
			$photo_bdd = $_POST['photo_actuelle'];  // dans le cas d'une modif on recupere la photo actuelle avant de vérifier si l'utilisateur en charge une nouvelle (l'ancienne sera alors ecrasée)
		}
		if(!empty($_FILES['photo']['name']))//on verifie si photo a bien été postée (empty teste aussi par defaut si c'est isset')
		{
			if(verificationExtensionPhoto())
			{
				// $msg .= '<div class="bg-success" style="padding: 10px; text-align: center"><h4>OK !</h4></div>';
				$nom_photo = $_POST['reference'] . '_' . $_FILES['photo']['name']; //afin que chaque nom de photo soit unique
				
				$photo_bdd = RACINE_SITE . "images/produits/$nom_photo"; //chemin src que l'on va enregistrer ds la BDD
				
				$photo_dossier = RACINE_SERVER . RACINE_SITE . "images/produits/$nom_photo";// chemin pour l'enregistrement dans le dossier qui va servir dans la fonction copy()
				copy($_FILES['photo']['tmp_name'], $photo_dossier); // COPY() permet de copier un fichier depuis un endroit (1er argument) vers un autre endroit (2eme argument). 
				
			}
			else
			{
				$msg .= '<div class="msg_erreur" style="padding: 10px; text-align: center">L\' extension de la photo n\'est pas valide(jpg, jpeg, png, gif)</div>';
			}
		}

		if(empty($msg))// S'il n'y a pas de message...
		{
			$msg .='<div class="msg_success" style="padding: 10px; text-align: center">Produit enregistré avec succès!</div>';
			
			foreach($_POST AS $indice => $valeur )
			{
				$_POST[$indice] = htmlentities($valeur, ENT_QUOTES); 
			}
			extract($_POST); // EXTRACT marche sur un tableau array (si indices non-numerique)
			
			if(isset($_GET['action']) && $_GET['action'] == 'modification')
			{
				executeRequete("UPDATE produit SET categorie='$categorie', titre='$titre', description='$description', couleur='$couleur', taille='$taille', sexe='$sexe', photo='$photo_bdd', prix='$prix',stock='$stock', id_promo_produit = '$id_promo_produit' WHERE id_produit='$_POST[id_produit]'");
			}
			else{
				executeRequete("INSERT INTO produit (reference, categorie, titre, description, couleur, taille, sexe, photo, prix, stock, id_promo_produit) VALUES ( '$reference', '$categorie', '$titre', '$description', '$couleur', '$taille', '$sexe', '$photo_bdd', '$prix', '$stock', '$id_promo_produit')"); //requete d'inscription (pour la PHOTO on utilise le chemin src que l'on a enregistré ds $photo_bdd)
			}
			$_GET['action'] = 'affichage'; // Pour afficher le tableau d'affichage une fois qu'on a validé le formulaire on peut changer la valeur action dans $_GET
		}	
	}
}
// FIN ENREGISTREMENT DES PRODUITS

require_once("../inc/header.inc.php");


// debug($_SESSION);
// debug($_POST);
// debug($_FILES);
//debug($_SERVER);

echo '<div class="box_info" >';

// STATS
$resultat = executeRequete("SELECT SUM(montant) AS total,
										COUNT(id_commande) AS nbre_commandes,
										ROUND(AVG(montant),0) AS panier_moyen,
										MAX(date) AS der_commande 
									FROM commande");
$commandes = $resultat -> fetch_assoc();
echo '<h3>CA Total : '. $commandes['total'] .'€  |  Nombre de commandes: '. $commandes['nbre_commandes'].' | Commande moyenne : '.$commandes['panier_moyen'].'€</h3>';
$resultat= executeRequete("SELECT COUNT(id_produit) AS nbre_produits,
									SUM(prix * stock) AS valeur_stock,
									ROUND(AVG(prix),0) AS prix_moyen,
									MAX(prix) AS prix_max,
									SUM(stock) AS stock_total
								FROM produit");

$donnees = $resultat -> fetch_assoc();

echo '<p>Vous avez actuellement '. $donnees['stock_total'].' produits en stock<br /> Prix moyen des articles en stock: '.$donnees['prix_moyen'].'€<br />
	Valeur totale de votre stock: '. $donnees['valeur_stock'].'€</p>';

if(isset($_GET['affichage']) && $_GET['affichage'] == 'affichage')
{	

echo '<h2><a href="?affichage=affichage" class="button active" >Tous les produits ('. $donnees['nbre_produits'].')</a></h2>
<a href="?action=ajout" class="button">Ajouter des produits</a><br />';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'ajout')
{
echo '<h2><a href="?action=ajout" class="button active">Ajouter des produits</a></h2>
<a href="?affichage=affichage" class="button" >Tous les produits</a>';
}
else
{
echo '<h2><a href="?affichage=affichage" class="button" >Tous les produits</a></h2>
	<h2><a href="?action=ajout" class="button">Ajouter des produits</a></h2><br />';
}


echo $msg;

if(isset($_GET['affichage']) && $_GET['affichage'] == 'affichage')
{
$req = "SELECT * FROM produit";
$resultat = executeRequete($req); 
$req = paginationGestion(10, 'produit', $req);
$nbcol = $resultat->field_count; 
echo '<br />
	<br />

	<table class="large_table">
		<tr>'; 
for($i= 0; $i < $nbcol; $i++) 
{
	$colonne= $resultat->fetch_field(); 
	if($colonne->name == 'photo')
	{
		echo '<th class="text-center">'. ucfirst($colonne->name).'</th>'; 
	}
	elseif($colonne->name == 'description')
	{
		echo '<th colspan="3" class="text-center">'. ucfirst($colonne->name).'</th>'; 
	}
	else
	{
		echo '<th class="text-center"><a href="?affichage=affichage&orderby='. $colonne->name ; 
		if(isset($_GET['asc']))
		{
			echo '&desc=desc';
		}
		else
		{
			echo '&asc=asc';
		}
		
		echo '"'; 
		if(isset($_GET['orderby']) && ($_GET['orderby'] == $colonne->name))
		{
			echo ' class="actif" ';
		}
		if($colonne->name == 'id_promo_produit')
		{
			echo '>Promo</a></th>'; 
		}
		elseif($colonne->name == 'id_produit')
		{
			echo '>Id</a></th>'; 
		}
		else
		{
			echo '>'. ucfirst($colonne->name).'</a></th>'; 
		}			
	}		
}
echo'</tr>';

while ($ligne = $resultat->fetch_assoc()) // = tant qu'il y a une ligne de resultat, on en fait un tableau 
{
	echo '<tr>';
		foreach($ligne AS $indice => $valeur) // foreach = pour chaque element du tableau
		{
			if($indice == 'photo')
			{
				echo '<td ><img src="'.$valeur.'" alt="'.$ligne['titre'].'" title="'.$ligne['titre'].'" class="thumbnail_tableau" width="80px" /></td>';
			}
			elseif($indice == 'description')
			{
				echo '<td colspan="3">' . substr($valeur, 0, 70) . '...</td>'; //Pour couper la description (affiche une description de 70 caracteres maximum)
			}
			else
			{
				echo '<td >'.$valeur.'</td>';
			}
		}
		echo '<td><a href="?action=suppression&id_produit='.$ligne['id_produit'] .'" class="btn_delete" onClick="return(confirm(\'En êtes-vous certain ?\'));">X</a></td>';
		
		echo '<td><a href="?action=modification&id_produit='.$ligne['id_produit'] .'" class="btn_edit">éditer</a></td>';
		
		
	echo '</tr>';
}						
echo '</table><br />';
echo '</div>';
$lien = "";
affichagePaginationGestion(10, 'produit', $lien);
}

/******** FORMULAIRE ENREGISTREMENT / MODIFICATION PRODUITSS *******/ 

if(isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'modification') )
{
	
// SI on clique sur MODIFIER ou AJOUTER  -> FORMULAIRE D'AJOUT (pré-rempli si modif)
	if(isset($_GET['id_produit']))
	{
		$resultat = executeREquete("SELECT * FROM produit WHERE id_produit ='$_GET[id_produit]'") ; // on recupere les infos de l'article à partir de l'id_article récupéré dans l'URL
		$produit_actuel = $resultat ->fetch_assoc();
		//on transforme la ligne de resultat en tableau array
		// debug($produit_actuel);
		
	}

	echo '<form  class="form" method="post" action="" enctype="multipart/form-data">
	 <fieldset>
		 <legend >';
	if(isset($_GET['id_produit']) && ($_GET['action']=='modifier'))
	{	
		echo 'Modifier le produit n°';				
		if(isset($produit_actuel['id_produit'])) // n° du produit ds le titre
		{ 
			echo $produit_actuel['id_produit'];
		} 
		elseif(isset($_POST['id_produit']))
		{ 
			echo $_POST['id_produit'];
		}
	}
	else
	{
		echo 'Ajouter un produit';	
	}
		?>
			</legend>
			 <input type="hidden" name="id_produit" id="id_produit" value="<?php if(isset($produit_actuel['id_produit'])){ echo $produit_actuel['id_produit']; }?>" /><!-- On met un input caché pour pouvoir identifier l'article lors de la modification (REPLACE se base sur l'id uniquement(PRIMARY KEY)) /!\SECURITE : On est ici dans un back-office, on peut donc se permettre une certaine confiance en l'utilisateur, mais les champs cachés ne sont pas sécurisés pour l'acces public il faut faire des controles securités sur les url -->
			 <label for="reference">Réference </label>
			 <input type="text" id="reference" name="reference" value="<?php if(isset($_POST['reference'])) {echo $_POST['reference'];} if(isset($produit_actuel['reference'])){ echo $produit_actuel['reference']; }?>" />
			
			<label for="categorie">Catégorie </label>
			 <input type="text" id="categorie" name="categorie"  value="<?php if(isset($_POST['categorie'])) {echo $_POST['categorie'];} if(isset($produit_actuel['categorie'])){ echo $produit_actuel['categorie']; }?>"/>
			
			<label for="titre">Titre </label>
			 <input class="form-control" type="text" id="titre" name="titre" value="<?php if(isset($_POST['titre'])) {echo $_POST['titre'];} if(isset($produit_actuel['titre'])){ echo $produit_actuel['titre']; }?>"/>
			
			<label for="description">Description </label><br />
			 <textarea id="description" name="description" class="description_form" ><?php if(isset($_POST['description'])) {echo $_POST['description'];} if(isset($produit_actuel['description'])){ echo $produit_actuel['description']; }?></textarea>
			
			<label for="couleur">Couleur </label>
			 <input type="text" id="couleur" name="couleur"  value="<?php if(isset($_POST['couleur'])) {echo $_POST['couleur'];} if(isset($produit_actuel['couleur'])){ echo $produit_actuel['couleur']; }?>" />
			
			<label for="taille">Taille </label>
			 <select id="taille" name="taille" >
				<option >S</option>
				<option <?php if((isset($_POST['taille']) && $_POST['taille'] == "M") ||(isset($produit_actuel['taille'])&& $produit_actuel['taille'] == "M")) { echo 'selected';} ?> >M</option>
				<option <?php if((isset($_POST['taille']) && $_POST['taille'] == "L") ||(isset($produit_actuel['taille'])&& $produit_actuel['taille'] == "L")) { echo 'selected';} ?> >L</option>
				<option <?php if((isset($_POST['taille']) && $_POST['taille'] == "XL") ||(isset($produit_actuel['taille'])&& $produit_actuel['taille'] == "XL")) { echo 'selected';} ?> >XL</option>
			</select>
			<br />
			
			<label for="sexe">Sexe </label><br /> <!--cas par défaut + une valeur checkée si le formulaire a dejà été rempli-->
				<input   type="radio" name="sexe" value="m"  class="inline" <?php if((isset($_POST['sexe']) && $_POST['sexe'] == "m") ||(isset($produit_actuel['sexe'])&& $produit_actuel['sexe'] == "m")) { echo 'checked';} elseif(!isset($_POST['sexe']) && !isset($produit_actuel['sexe'])){echo 'checked';} ?> /> Homme &nbsp;
				<input  <?php if((isset($_POST['sexe']) && $_POST['sexe'] == "f") ||(isset($produit_actuel['sexe'])&& $produit_actuel['sexe'] == "f")) { echo 'checked';} ?> type="radio" name="sexe" value="f"  class="inline" /> Femme<br /><br />
			
			<label for="photo">Photo </label>
			<input type="file" name="photo" id="photo"><br />
			<?php 
			if(isset($produit_actuel)) // on affiche la photo actuelle par defaut
			{
					echo '<label>Photo actuelle</label><br />';
					echo '<img src="'. $produit_actuel['photo'].'" width="140"/><br />';
					echo '<input type="hidden" name="photo_actuelle" value="'. $produit_actuel['photo'].'" /><br />';
			}
			
			?>
			<label for="id_promo_produit">Code promo</label><br />
				<select id="id_promo_produit" name="id_promo_produit"  >
					<option value="" >Pas de code promo</option>
				<?php
					$resultat = executeRequete('SELECT * FROM promo_produit ORDER BY reduction ASC');

					while ($ligne = $resultat->fetch_assoc()) 
					{
						echo '<option value="'. $ligne['id_promo_produit'] .'" ';
						if((isset($_POST['id_promo_produit']) && ($_POST['id_promo_produit'] == $ligne['id_promo_produit'])) ||(isset($produit_actuel['id_promo_produit'])&& ($produit_actuel['id_promo_produit'] == $ligne['id_promo_produit']))) 
						{ 
							echo ' selected ';
						} 
				
						echo '>'. $ligne['id_promo_produit'] .' - '. $ligne['code_promo'] .' (- '. $ligne['reduction'] .' %)</option>' ;	
					}
				?>					
				</select>
			<label for="prix">Prix </label>
			<input type="text" id="prix" name="prix"  value="<?php if(isset($_POST['prix'])) {echo $_POST['prix'];} if(isset($produit_actuel['prix'])){ echo $produit_actuel['prix']; }?>" /><br />
			
			<label for="stock">Stock </label>
			<input type="text" id="stock" name="stock"  value="<?php if(isset($_POST['stock'])) {echo $_POST['stock'];} if(isset($produit_actuel['stock'])){ echo $produit_actuel['stock']; }?>" /><br />
			
			<input type="submit" id="enregistrement" name="enregistrement" value="<?php echo ucfirst($_GET['action']); ?>" class="btn" />
			
		
		</fieldset>
	 </form> 
 </div>
 <?php
}

echo '</div>'; //fin box_info : tableau	 
 ?>
	 <br />
	 <br />
  <?php
	require_once("../inc/footer.inc.php");
  
  ?>