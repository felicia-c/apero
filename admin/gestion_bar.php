<?php 
require_once("../inc/init.inc.php");
//APERO - Felicia Cuneo - 12/2015
$titre_page = "Gestion des bars";

//Redirection si l'utilisateur n'est pas admin
if(!utilisateurEstConnecteEtEstAdmin())
{
	header("location:../connexion.php");
}



if(!empty($_POST))
{
 // SECURITE 
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['nom']); 
	if(!$verif_caractere && !empty($_POST['nom']))
	{
		$msg .= '<div class="msg_erreur" >Nom - Caractères acceptés: _ - àâäçéèêëï A à Z et 0 à 9</div>';  
	}
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['prenom']); 
	if(!$verif_caractere && !empty($_POST['prenom']))
	{
		$msg .= '<div class="msg_erreur" >Prénom - caractères acceptés: _ -  àâäçéèêëï A à Z et 0 à 9</div>';  
	}
	$email = filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ); 
	if(!$email && !empty($_POST['email']))
	{
		$msg .= '<div class="msg_erreur" >Adresse e-mail invalide !</div>'; 
	} 
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['ville']); 
	if(!$verif_caractere && !empty($_POST['ville']))
	{
		$msg .= '<div class="msg_erreur" >Ville - Caractères acceptés: _ - àâäçéèêëïa A à Z, 0 à 9 -_</div>';  
	}
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['adresse']); 
	if(!$verif_caractere && !empty($_POST['adresse']))
	{
		$msg .= '<div class="msg_erreur">Adresse - Caractères acceptés: _ - àâäçéèêëïa A à Z et 0 à 9</div>';  
	}
	
	$verif_caractere = preg_match('#^[0-9]+$#', $_POST['cp']); 
	if(!$verif_caractere && !empty($_POST['cp']))      
	{
		$msg .= '<div class="msg_erreur" >Code postal - Caractères acceptés: 0 à 9</div>';  
	}

	//ENREGISTREMENT
	if(isset($_POST['ajouter']) && $_POST['ajouter'] == 'Ajouter') 
	{
		$siret= executeRequete("SELECT siret FROM bar WHERE siret='$_POST[siret]'");
		if($siret -> num_rows > 0 && isset($_GET['action']) && $_GET['action'] == 'ajout') //si le siret est deja enregistré
		{
			$msg .='<div class="msg_erreur" style="margin-top: 20px; padding: 10px; text-align: center">Ce numéro de SIRET est déjà utilisé !</div>';
		}
	 
		$photo_bdd ="";
		
		//if(isset($_GET['action']) && $_GET['action'] == 'modification')
	//	{
	//		$photo_bdd = $_POST['photo_actuelle'];  // dans le cas d'une modif on recupere la photo actuelle avant de vérifier si l'utilisateur en charge une nouvelle (l'ancienne sera alors ecrasée)
	//	}
		if(!empty($_FILES['photo']['name']))//on verifie si photo a bien été postée
		{

			if(verificationExtensionPhoto())
			{
				// $msg .= '<div class="bg-success"><h4>OK !</h4></div>';
				$nom_photo = $_POST['nom_bar'] . '_' . $_FILES['photo']['name']; //afin que chaque nom de photo soit unique
				
				$photo_bdd = RACINE_SITE . "images/bars/$nom_photo"; //chemin src que l'on va enregistrer ds la BDD
				
				$photo_dossier = RACINE_SERVER . RACINE_SITE . "images/bars/$nom_photo";// chemin pour l'enregistrement dans le dossier qui va servir dans la fonction copy()
				copy($_FILES['photo']['tmp_name'], $photo_dossier); // COPY() permet de copier un fichier depuis un endroit (1er argument) vers un autre endroit (2eme argument). 	
			}
			else
			{
				$msg .= '<div class="msg_erreur" style="padding: 10px; text-align: center">L\' extension de la photo n\'est pas valide(jpg, jpeg, png, gif)</div>';
			}
		}

		if(empty($msg))// S'il n'y a pas de message...
		{
			foreach($_POST AS $indice => $valeur )
			{
				$_POST[$indice] = htmlentities($valeur, ENT_QUOTES); 
			}
			extract($_POST);
			$resultat = executeRequete("SELECT statut FROM membre WHERE id_membre = '$id_membre' ");
			$statut = $resultat -> fetch_assoc();
			if($statut['statut'] != 3)
			{
				executeRequete("UPDATE membre SET statut = 3 WHERE id_membre='$_POST[id_membre]'");
			}
			
			executeRequete("INSERT INTO bar (id_membre, siret, nom_bar, photo, description, nom_gerant, prenom_gerant, ville, cp, adresse, telephone, email) VALUES ( '$id_membre', '$siret', '$nom_bar', '$photo_bdd', '$description', '$nom', '$prenom', '$ville', '$cp', '$adresse', '$telephone', '$email')"); //requete d'inscription (pour la PHOTO on utilise le chemin src que l'on a enregistré ds $photo_bdd)
			header('location:gestion_bar.php?add=ok&affichage=affichage');
		}	
	}
}
// FIN ENREGISTREMENT

//MESSAGE DE VALIDATION AJOUT
if(isset($_GET['add']) && $_GET['add'] == 'ok')
{
	$msg .='<div class="msg_success" style="padding: 10px; text-align: center">Bar enregistré avec succès!</div>';
}


// SUPPRESSION
 
 if(isset($_GET['action']) && $_GET['action'] == 'suppression')
{
	$resultat = executeRequete("SELECT * FROM bar WHERE id_bar= '$_GET[id_bar]'"); //on recupere les infos afin de connaitre son image  pour pouvoir la supprimer
	$produit_a_supprimer = $resultat->fetch_assoc();
	//Spécial pour la suppression de fichiers (et pas de données):
	$chemin_produit = RACINE_SERVER . $produit_a_supprimer['photo'] ; // Nous avons besoin du chemin du produit depuis la racine serveur pour supprimer la photo du serveur
	if(!empty($produit_a_supprimer['photo'] && file_exists($chemin_produit))) // FILE_EXISTS verifie si l'élément existe
	{
		unlink($chemin_produit); //UNLINK() va  SUPPRIMER le fichier du serveur
	}
	
	executeRequete("DELETE FROM bar WHERE id_bar='$_GET[id_bar]'");
	$msg .='<div class="msg_success" style="padding: 10px; text-align: center">Bar N°'. $_GET['id_bar'] .' supprimé avec succès!</div>';
	$_GET['affichage'] = 'affichage'; 	

}
//FIN SUPPRESSION


$req = "";
require_once("../inc/header.inc.php");

echo '<div="box_info">';
		
//STATS
$resultat = executeRequete("SELECT SUM(montant) AS total,
								COUNT(id_commande) AS nbre_commandes,
								ROUND(AVG(montant),0) AS panier_moyen,
								MAX(date) AS der_commande 
							FROM commande");
$commandes = $resultat -> fetch_assoc();
echo '<h3>CA Total : '. $commandes['total'] .'€  |  Nombre de commandes: '. $commandes['nbre_commandes'].' | Commande moyenne : '.$commandes['panier_moyen'].'€</h3><br />';
// FIN STATS

// LIENS
$resultat = executeRequete("SELECT COUNT(id_bar) AS nbre_bar FROM bar");
$donnees =$resultat -> fetch_assoc();	

if(isset($_GET['affichage']) && $_GET['affichage'] == 'affichage')
{	
	
	echo '<h2><a href="?affichage=affichage" class="button active" >Tous les bars ('. $donnees['nbre_bar'].')</a></h2>
	<a href="?action=ajout" class="button"> > Ajouter un bar</a><br />
	<a href="'.RACINE_SITE.'admin/gestion_promos_bar.php"> > Offres Apéro</a>';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'ajout')
{
	echo '<h2><a href="?action=ajout" class="button active">Ajouter un bar</a></h2>
	<a href="?affichage=affichage" class="button" > > Tous les bars</a><br />
	<a href="'.RACINE_SITE.'admin/gestion_promos_bar.php"> > Offres Apéro</a>';
}
else
{
	echo '<h2><a href="?affichage=affichage" class="button" >Tous les bars</a></h2>
		<h2><a href="?action=ajout" class="button">Ajouter un bar</a></h2>
		<h2><a href="'.RACINE_SITE.'admin/gestion_promos_bar.php">Offres Apéro</a><h2>';
}

// FIN LIENS

echo $msg;
if(isset($_GET['affichage']) && $_GET['affichage'] == 'affichage')
{
	echo '<br />
		<br />
		<table class="large_table">
			<tr>';
	$req = "SELECT * FROM bar";

	$req = paginationGestion(7, 'bar', $req);
	$resultat = executeRequete($req); 
	$nbcol = $resultat->field_count; 

	for($i= 0; $i < $nbcol; $i++) 
	{
		$colonne= $resultat->fetch_field(); 
		if($colonne->name == 'photo')
		{
				echo '<th class="text-center" width="150">'. ucfirst($colonne->name).'</th>'; 
		}
		elseif($colonne->name == 'email')
		{
			echo '<th class="text-center" colspan="3">E-mail</a></th>'; 
		}
		//elseif($colonne->name == 'description')
	//	{
		//	echo '<th colspan="3" class="text-center">'. ucfirst($colonne->name).'</th>'; 
	//	}
		

		elseif((($colonne->name != 'description') && ($colonne->name != 'photo')) && $colonne->name != 'prenom_gerant')
		{

			if($colonne->name == 'nom_gerant')
			{
				echo '<th class="text-center" colspan="2"><a href="?affichage=affichage&orderby='. $colonne->name ; 
			}	

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
				echo ' class="active" ';
			}
			elseif($colonne->name == 'id_bar') 
			{
				echo '>Id</a></th>'; 
			}
			elseif($colonne->name == 'id_membre')
			{
				echo '>Membre</a></th>';
			}
			elseif($colonne->name == 'nom_gerant')
			{
				echo '>Gérant</th>'; 
			}
			else
			{
				echo '>'. ucfirst($colonne->name).'</a></th>'; 		
			}
		}		
	}
	echo'<th></th></tr>';

	while ($ligne = $resultat->fetch_assoc())
	{
		echo '<tr>';
		foreach($ligne AS $indice => $valeur)
		{
				if($indice == 'photo')
			{
				echo '<td ><img src="'.$valeur.'" alt="'.$ligne['nom_bar'].'" title="'.$ligne['nom_bar'].'" class="thumbnail_tableau" width="80px" /></td>';
			}
			//elseif($indice == 'description')
			//{
		//		echo '<td colspan="3">' . substr($valeur, 0, 70) . '...</td>'; //Pour couper la description (affiche une description de 70 caracteres maximum)
		//	}
			elseif($indice == 'nom_gerant')
			{
				echo '<td colspan="2">' . ucfirst($valeur).' ';	
			}
			elseif($indice == 'prenom_gerant')
			{
				echo ucfirst($valeur) .'</td>';
			}
			elseif($indice != 'description')
			{
				echo '<td >'.$valeur.'</td>';
			}
		}
		echo '<td><a href="?action=suppression&id_bar='.$ligne['id_bar'] .'" class="btn_delete" onClick="return(confirm(\'En êtes-vous certain ?\'));">X</a></td>';
		
		//echo '<td><a href="?action=modification&id_produit='.$ligne['id_bar'] .'" class="btn_edit">éditer</a></td>';
		echo '</tr>';
	}						
	echo '</table><br />';

	affichagePaginationGestion(7, 'produit', '');
	echo '</div>';
}

//FORM AJOUT

if(isset($_GET['action']) &&  $_GET['action']=='ajout') 
{

?>		
	<form class="form" method="post" action="" enctype="multipart/form-data"> <!--enctype pour ajout eventuel d'un champs photo -->
	<fieldset>
		<legend>Nouveau bar</legend>
		 
		<input type="hidden" name="id_bar" id="id_bar" value="<?php if(isset($bar_actuel['id_bar'])){ echo $bar_actuel['id_bar']; }?>" /><!-- On met un input caché pour pouvoir identifier le membre lors de la modification (REPLACE se base sur l'id uniquement(PRIMARY KEY)) /!\SECURITE : On est ici dans un back-office, on peut donc se permettre une certaine confiance en l'utilisateur, mais les champs cachés ne sont pas sécurisés pour l'acces public il faut faire des controles securités sur les url -->
			<label for="nom_bar">Nom de l'établissement</label>
			<input required type="text" id="nom_bar" name="nom_bar"  maxlength="50" value="<?php if(isset($_POST['nom_bar'])) {echo $_POST['nom_bar'];}?>" placeholder="Puzzle Bar" /><br />

			<label for="siret">SIRET</label>
			<input required type="siret" id="siret" name="siret"  maxlength="14" /><br /> 
				
			<label for="id_membre">Compte membre lié au bar</label>
			<select required id="id_membre" name="id_membre">
			<?php
				$req = "SELECT id_membre, pseudo, nom, prenom FROM membre ORDER BY nom";
				$resultat = executeRequete($req);
				//$nb_ligne = count($resultat)
				while($ligne = $resultat -> fetch_assoc())
				{
					echo '<option value="'.$ligne['id_membre'].'"';
					if(isset($_GET['id_membre']) && $_GET['id_membre'] == $ligne['id_membre'])
					{
						echo 'selected';
					}
					if(isset($_POST['id_membre']) && isset($_POST['id_membre']) == $ligne['id_membre'])
					{
						echo 'selected';
					}
					echo ' >'.$ligne['id_membre'].' - '.$ligne['prenom'].' '.$ligne['nom'].' | '.$ligne['pseudo'].'</option>';
				}
						
			echo '</select>
			<a href="'.RACINE_SITE.'admin/gestion_membre.php?action=ajout" >Nouveau compte membre</a><br /><br />';	
			
			if(isset($_GET['id_membre']))
			{
				$req = "SELECT id_membre, nom, prenom FROM membre WHERE id_membre = '$_GET[id_membre]' ORDER BY nom";
				$resultat = executeRequete($req);
				$membre = $resultat -> fetch_assoc();
			}

			?>
			<label for="photo">Photo </label>
			<input type="file" name="photo" id="photo"><br />
			<?php 
			if(isset($bar_actuel)) // on affiche la photo actuelle par defaut
			{
				echo '<label>Photo actuelle</label><br />';
				echo '<img src="'. $_POST['photo'].'" width="140"/><br />';
				echo '<input type="hidden" name="photo_actuelle" value="'. $_POST['photo'].'" /><br />';
			}
			?>	
			<label for="description">Description </label><br />
			<textarea id="description" name="description" maxlength="200" class="description_form" ><?php if(isset($_POST['description'])) {echo $_POST['description'];} ?></textarea>
			
			<label for="nom">Nom du gérant</label>
			<input required type="text" id="nom" name="nom" maxlength="45" value="<?php if(isset($_POST['nom'])) {echo $_POST['nom'];}  if(isset($_GET['id_membre'])){ echo $membre['nom'];}?>" placeholder="Durand" required /><br />
			
			<label for="prenom">Prénom du gérant</label>
			<input required  type="text" id="prenom"  maxlength="30" name="prenom" value="<?php if(isset($_POST['prenom'])) {echo $_POST['prenom'];} if(isset($_GET['id_membre'])){ echo $membre['prenom'];}?>" placeholder="Jean"  required /><br />
			
			<h3>Coordonnées de l'établissement</h3>
			<label for="email">Email</label>
			<input required  type="email" id="email" name="email" maxlength="60" value="<?php if(isset($_POST['email'])) {echo $_POST['email'];}?>" placeholder="monmail@mail.com"  required /><br />

			<label for="telephone">Téléphone</label>
			<input required  type="text" id="telephone" name="telephone" maxlength="10" value="<?php if(isset($_POST['telephone'])) {echo $_POST['telephone'];}?>" placeholder="O111223344" required/><br />


			<label for="ville">Ville</label>
			<input required  type="text" id="ville" name="ville" value="<?php if(isset($_POST['ville'])) {echo $_POST['ville'];}?>" placeholder="Maville" required /><br />
		
			<label for="cp">Code Postal</label>
			<input required  type="text" id="cp" name="cp" minlength="5" maxlength="5" value="<?php if(isset($_POST['cp'])) {echo $_POST['cp'];}?>" placeholder="99999" required/><br />
			
			<label for="adresse">Adresse</label>
			<textarea required type="text" id="adresse" name="adresse" maxlength="100" placeholder="86 rue de la Ville" required><?php if(isset($_POST['adresse'])) {echo $_POST['adresse'];}?></textarea><br />
			
			<br />
			<input type="submit" id="ajouter" name="ajouter" value="Ajouter" class="button" /><br />
			<br />
			<a class="button " href="<?php echo RACINE_SITE; ?>admin/gestion_bar.php?affichage=affichage">Retour aux bars</a><br />
			<br />
			</fieldset>
		</form>		
	
	
	<br />
	<br />
<?php
}
echo '</div>

<br /><br /><br />';
	
require_once("../inc/footer.inc.php");	
	
?>