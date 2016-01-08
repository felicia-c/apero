<?php
require_once("../inc/init.inc.php");

//APERO - Felicia Cuneo 12/2015

 if(!utilisateurEstConnecteEtEstAdmin() && !utilisateurEstConnecteEtEstGerantEtAdmin())
 {
	 header("location:../connexion.php");
	exit();
 }

foreach($_GET AS $indice => $valeur )
{
	$_GET[$indice] = htmlentities($valeur, ENT_QUOTES); 
}
//pagination liens
$page='';
$orderby=''; 
$asc_desc='';

if(isset($_GET['page']))
{
	$page.= '&page='.$_GET['page'];
}
if(isset($_GET['orderby']))
{
	$orderby.= '&orderby='.$_GET['orderby'];
}
if(isset($_GET['asc']))
{
	$asc_desc.= '&asc='.$_GET['asc'];
}
elseif(isset($_GET['desc']))
{
	$asc_desc.='&desc='.$_GET['desc'];
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
	$msg .='<div class="msg_success">Produit N°'. $_GET['id_produit'] .' supprimé !</div>';
	$_GET['affichage'] = 'affichage'; // afficher les produits une fois qu'on a validé le formulaire 

}

// MODIF STOCK

if(isset($_POST['modif_stock']) && $_POST['modif_stock'] == 'modifier le stock')
{
	executeRequete("UPDATE taille_stock SET stock ='$_POST[stock]' WHERE id_taille_stock = '$_POST[id_taille_stock]'");
	header('location:?affichage=affichage&mod=ok&id_produit='.$_POST['id_produit'].'');
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
		if(!empty($_FILES['photo']['name']))//on verifie si photo a bien été postée
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
			extract($_POST);
			
			if(isset($_GET['action']) && $_GET['action'] == 'modification')
			{
				executeRequete("UPDATE produit SET categorie='$categorie', titre='$titre', description='$description', couleur='$couleur', sexe='$sexe', photo='$photo_bdd', prix='$prix', id_promo_produit = '$id_promo_produit' WHERE id_produit='$_POST[id_produit]'");
				header('location:gestion_produit.php?affichage=affichage&mod=ok&id_produit='.$_GET['id_produit'].''.$page.''.$orderby.''.$asc_desc.'');
			}
			else
			{
				executeRequete("INSERT INTO produit (reference, categorie, titre, description, couleur, sexe, photo, prix, id_promo_produit) VALUES ( '$reference', '$categorie', '$titre', '$description', '$couleur', '$sexe', '$photo_bdd', '$prix', '$id_promo_produit')"); //requete d'inscription (pour la PHOTO on utilise le chemin src que l'on a enregistré ds $photo_bdd)
				header('location:gestion_produit.php?affichage=affichage&add=ok&'.$mysqli->insert_id.''.$page.''.$orderby.''.$asc_desc.'');
			}
			//$_GET['affichage'] = 'affichage'; // afficher les produits une fois qu'on a validé le formulaire
		}	
	}
}
// FIN ENREGISTREMENT DES PRODUITS

//MESSAGE DE VALIDATION AJOUT
if(isset($_GET['add']) && $_GET['add'] == 'ok')
{
	$msg .='<div class="msg_success">Produit enregistré !</div>';
}
//MESSAGE DE VALIDATION MODIF
if(isset($_GET['mod']) && $_GET['mod'] == 'ok')
{
	$msg .='<div class="msg_success">Produit modifié !</div>';
}


require_once("../inc/header.inc.php");


// debug($_SESSION);
// debug($_POST);
// debug($_FILES);
//debug($_SERVER);

echo '<div class="box_info" >';

// STATS	
$resultat_stock = executeRequete("SELECT SUM(stock) AS stock_total FROM taille_stock");
$donnees_stock = $resultat_stock -> fetch_assoc();
$stock= $donnees_stock['stock_total'];
$resultat = executeRequete("SELECT SUM(montant) AS total, COUNT(id_commande) AS nbre_commandes, ROUND(AVG(montant),0) AS panier_moyen, MAX(date) AS der_commande FROM commande");
$commandes = $resultat -> fetch_assoc();
echo '<h3>CA Total : '. $commandes['total'] .'€  |  Nombre de commandes: '. $commandes['nbre_commandes'].' | Commande moyenne : '.$commandes['panier_moyen'].'€</h3>';
$resultat= executeRequete("SELECT COUNT(id_produit) AS nbre_produits,
									SUM(prix * $stock) AS valeur_stock,
									ROUND(AVG(prix),0) AS prix_moyen,
									MAX(prix) AS prix_max	
								FROM produit");

$donnees = $resultat -> fetch_assoc();

echo '<p class="orange">Vous avez '. $donnees_stock['stock_total'].' articles en stock | Prix HT moyen des articles en stock: '.$donnees['prix_moyen'].'€ 
		<br />Valeur du stock : '. $donnees['valeur_stock'].'€</p>';

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
	echo '<br />
		<br />
		<table class="large_table" id="details">'; 
	$req = "SELECT * FROM produit";

	$req = paginationGestion(5, 'produit', $req);
	$resultat = executeRequete($req); 
	$dont_link = null; // entete du tablau sans order by
	$dont_show = 'description'; // colonne non affichée
	enteteTableau($resultat, $dont_show, $dont_link); //entete tableau

	while ($ligne = $resultat->fetch_assoc())
	{
		echo '<tr '; 
		if(isset($_GET['id_produit']) && ($_GET['id_produit'] == $ligne['id_produit']))
		{
			echo ' class="tr_active" ';
		}
		echo '>';
		foreach($ligne AS $indice => $valeur)
		{
			if($indice == 'photo')
			{
				echo '<td ><img src="'.$valeur.'" alt="'.$ligne['titre'].'" title="'.$ligne['titre'].'" class="thumbnail_tableau" width="80px" /></td>';
			}
			elseif($indice == 'id_promo_produit')
			{
				if(!empty($valeur))
				{
					$resultat_promo = executeRequete("SELECT * FROM promo_produit WHERE id_promo_produit = '$ligne[id_promo_produit]'");
					$promo = $resultat_promo -> fetch_assoc();
					echo '<td >'.$promo['code_promo'].' ('.$promo['id_promo_produit'].') <br /> -'. $promo['reduction'].'%</td>';
				}
				else
				{
					echo '<td >PAS DE PROMO</td>';
				}	
			}

		
			elseif($indice == 'prix')
			{
				echo '<td >'.$valeur.'€</td>';
			}
			elseif($indice != 'description')
			{
				echo '<td >'.$valeur.'</td>';
			}
		}
		echo '<td><ul >';
		$res_taille_stock = executeRequete("SELECT taille_stock.id_taille_stock, taille_stock.id_produit AS produit, taille_stock.stock AS stock, taille.taille AS taille FROM taille_stock INNER JOIN taille ON taille_stock.id_taille = taille.id_taille WHERE id_produit = '$ligne[id_produit]'");
		while($ligne_taille_stock = $res_taille_stock -> fetch_assoc())
		{
			//$res_taille = executeRequete("SELECT taille FROM taille WHERE id_taille = '$valeur_taille_stock'");
			//$taille = $res_taille -> fetch_assoc();
			echo '<li style="width: 100%">';
			echo '<form method="post" action="">
				<input type="hidden" name="id_taille_stock" value="'.$ligne_taille_stock['id_taille_stock'].'" />
				<input type="hidden" name="id_produit" value="'.$ligne_taille_stock['produit'].'" />
				<p style="font-weight: bold; font-size: 2em; width: 40%; float: left;">'.$ligne_taille_stock['taille'].' x </p>
				<input type="number" style="float: left; width: 30%" name="stock" value="'.$ligne_taille_stock['stock'].'" />
				<input type="submit" style="width: 80%; padding: 1%;" name="modif_stock" value="modifier le stock" />
			</form>';
			echo '</li><br/>';
			
		}

		echo '</ul></td>';
		echo '<td><a href="?action=suppression&id_produit='.$ligne['id_produit'].$page.''.$orderby.''.$asc_desc.'" class="btn_delete" onClick="return(confirm(\'En êtes-vous certain ?\'));">X</a></td>';
	
		echo '<td><a href="?action=modification&id_produit='.$ligne['id_produit'] .$page.''.$orderby.''.$asc_desc.'" class="btn_edit">éditer</a></td>';
		echo '</tr>';
	}
						
	echo '</table><br />';
	echo '</div>';

	affichagePaginationGestion(5, 'produit', '');
}



// FORMULAIRE AJOUT / MODIF 

if(isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'modification') )
{
	// MODIFIER ou AJOUTER  -> FORMULAIRE D'AJOUT (pré-rempli si modif)
	if(isset($_GET['id_produit']))
	{
		$resultat = executeREquete("SELECT * FROM produit WHERE id_produit ='$_GET[id_produit]'") ;
		$produit_actuel = $resultat ->fetch_assoc();
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
			 <input required type="text" id="reference" name="reference" value="<?php if(isset($_POST['reference'])) {echo $_POST['reference'];} if(isset($produit_actuel['reference'])){ echo $produit_actuel['reference']; }?>" />
			
			<label for="categorie">Catégorie </label>
			 <input required type="text" id="categorie" name="categorie"  value="<?php if(isset($_POST['categorie'])) {echo $_POST['categorie'];} if(isset($produit_actuel['categorie'])){ echo $produit_actuel['categorie']; }?>"/>
			
			<label for="titre">Titre </label>
			 <input required class="form-control" type="text" id="titre" name="titre" value="<?php if(isset($_POST['titre'])) {echo $_POST['titre'];} if(isset($produit_actuel['titre'])){ echo $produit_actuel['titre']; }?>"/>
			
			<label for="description">Description </label><br />
			 <textarea required id="description" name="description" class="description_form" ><?php if(isset($_POST['description'])) {echo $_POST['description'];} if(isset($produit_actuel['description'])){ echo $produit_actuel['description']; }?></textarea>
			
			<label for="couleur">Couleur </label>
			 <input required type="text" id="couleur" name="couleur"  value="<?php if(isset($_POST['couleur'])) {echo $_POST['couleur'];} if(isset($produit_actuel['couleur'])){ echo $produit_actuel['couleur']; }?>" />
		
		<!--	<label for="taille">Taille </label>
			 <select required id="taille" name="taille" >
				<option >S</option>
				<option <?php if((isset($_POST['taille']) && $_POST['taille'] == "M") ||(isset($produit_actuel['taille'])&& $produit_actuel['taille'] == "M")) { echo 'selected';} ?> >M</option>
				<option <?php if((isset($_POST['taille']) && $_POST['taille'] == "L") ||(isset($produit_actuel['taille'])&& $produit_actuel['taille'] == "L")) { echo 'selected';} ?> >L</option>
				<option <?php if((isset($_POST['taille']) && $_POST['taille'] == "XL") ||(isset($produit_actuel['taille'])&& $produit_actuel['taille'] == "XL")) { echo 'selected';} ?> >XL</option>
			</select>
			<br /> -->
			
			<label for="sexe">Sexe </label><br /> <!--cas par défaut + une valeur checkée si le formulaire a dejà été rempli-->
				<input type="radio" name="sexe" value="m"  class="inline" <?php if((isset($_POST['sexe']) && $_POST['sexe'] == "m") ||(isset($produit_actuel['sexe'])&& $produit_actuel['sexe'] == "m")) { echo 'checked';} elseif(!isset($_POST['sexe']) && !isset($produit_actuel['sexe'])){echo 'checked';} ?> /> Homme &nbsp;
				<input  <?php if((isset($_POST['sexe']) && $_POST['sexe'] == "f") ||(isset($produit_actuel['sexe'])&& $produit_actuel['sexe'] == "f")) { echo 'checked';} ?> type="radio" name="sexe" value="f"  class="inline" /> Femme<br /><br />
			
			<label for="photo">Photo </label>
			<input type="file" name="photo" id="photo"><br />
			<?php 
			if(isset($produit_actuel)) // on affiche la photo actuelle par defaut
			{
					echo '<label>Photo actuelle</label><br />';
					echo '<img src="'.$produit_actuel['photo'].'" width="140"/><br />';
					echo '<input type="hidden" name="photo_actuelle" value="'.$produit_actuel['photo'].'" /><br />';
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
			<input required type="text" id="prix" name="prix"  value="<?php if(isset($_POST['prix'])) {echo $_POST['prix'];} if(isset($produit_actuel['prix'])){ echo $produit_actuel['prix']; }?>" /><br />
			
			<!-- <label for="stock">Stock </label>
			<input required type="text" id="stock" name="stock"  value="<?php if(isset($_POST['stock'])) {echo $_POST['stock'];} if(isset($produit_actuel['stock'])){ echo $produit_actuel['stock']; }?>" /><br /> -->
			
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