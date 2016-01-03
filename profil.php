<?php
require_once("inc/init.inc.php");
$titre_page = "Profil";
//APERO - Felicia Cuneo - 12/2015//

if(!utilisateurEstConnecte()) //Si l'utilisateur n'est PAS connecté (SECURITE)
{
	header("location:connexion.php");
	exit();
}
if(isset($_GET['modif'])&& $_GET['modif'] == 'ok')
{
	$msg .= '<div class="msg_success"><p>Votre profil a été modifié</p></div>';
}
if(!empty($_POST))
{
 // SECURITE 
	$verif_caractere = preg_match('#^[0-9]+$#', $_POST['siret']); 
	if(!$verif_caractere && !empty($_POST['siret']))      
	{
		$msg .= '<div class="msg_erreur" >N° SIRET - Caractères acceptés: 0 à 9</div>';  
	}
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9. _\'!?-]+$#', $_POST['nom_bar']); 
	if(!$verif_caractere && !empty($_POST['nom_bar']))
	{
		$msg .= '<div class="msg_erreur" >Nom du bar- Caractères acceptés: _ - \' ?! àâäçéèêëï A à Z et 0 à 9</div>';  
	}
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['nom']); 
	if(!$verif_caractere && !empty($_POST['nom']))
	{
		$msg .= '<div class="msg_erreur" >Nom - Caractères acceptés: _ - àâäçéèêëï - \' A à Z et 0 à 9</div>';  
	}
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['prenom']); 
	if(!$verif_caractere && !empty($_POST['prenom']))
	{
		$msg .= '<div class="msg_erreur" >Prénom - caractères acceptés: _ -  àâäçéèêëï - A à Z et 0 à 9</div>';  
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
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ - \']+$#', $_POST['adresse']); 
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
	if(isset($_POST['enregistrer']) && $_POST['enregistrer'] == 'Enregistrer') 
	{
		$siret= executeRequete("SELECT siret FROM bar WHERE siret='$_POST[siret]'");
		if($siret -> num_rows > 0 && isset($_GET['action']) && $_GET['action'] == 'ajout') //si le siret est deja enregistré
		{
			$msg .='<div class="msg_erreur">Ce numéro de SIRET est déjà utilisé !</div>';
		}
	 
		$photo_bdd ="";
		
		if(isset($_GET['action']) && $_GET['action'] == 'modification')
		{
			$photo_bdd = $_POST['photo_actuelle'];  // dans le cas d'une modif on recupere la photo actuelle avant de vérifier si l'utilisateur en charge une nouvelle (l'ancienne sera alors ecrasée)
		}
		if(!empty($_FILES['photo']['name']))//on verifie si photo a bien été postée
		{
			if(verificationExtensionPhoto())
			{
				// $msg .= '<div class="bg-success" style="padding: 10px; text-align: center"><h4>OK !</h4></div>';
				$nom_photo = $_POST['nom_bar']. '_' .$_POST['nom'] . '_' . $_FILES['photo']['name']; //afin que chaque nom de photo soit unique
				
				$photo_bdd = RACINE_SITE . "images/bars/$nom_photo"; //chemin src que l'on va enregistrer ds la BDD
				
				$photo_dossier = RACINE_SERVER . RACINE_SITE . "images/bars/$nom_photo";// chemin pour l'enregistrement dans le dossier qui va servir dans la fonction copy()
				copy($_FILES['photo']['tmp_name'], $photo_dossier); // COPY() permet de copier un fichier depuis un endroit (1er argument) vers un autre endroit (2eme argument). 
				
			}
			else
			{
				$msg .= '<div class="msg_erreur">L\' extension de la photo n\'est pas valide(jpg, jpeg, png, gif)</div>';
			}
		}
	

		if(empty($msg))// S'il n'y a pas de message...
		{
			$id_membre = $_SESSION['utilisateur']['id_membre'];
			foreach($_POST AS $indice => $valeur )
			{
				$_POST[$indice] = htmlentities($valeur, ENT_QUOTES); 
			}
			extract($_POST);

			$resultat = executeRequete("SELECT statut FROM membre WHERE id_membre = '$id_membre' ");
			$statut = $resultat -> fetch_assoc();
			if($_GET['action'] == 'modification')
			{
				executeRequete("UPDATE bar SET siret ='$siret', nom_bar = '$nom_bar', photo = '$photo_bdd', description= '$description', nom_gerant = '$nom', prenom_gerant = '$prenom', ville = '$ville', cp = '$cp', adresse = '$adresse', telephone= '$telephone', email = '$email' WHERE id_bar = '$_GET[id_bar]'");
				header('location:profil.php?mod=ok&affichage=affichage');
			}
			else
			{
				executeRequete("INSERT INTO bar (id_membre, siret, nom_bar, photo, description, nom_gerant, prenom_gerant, ville, cp, adresse, telephone, email) VALUES ( '$id_membre', '$siret', '$nom_bar', '$photo_bdd', '$description', '$nom', '$prenom', '$ville', '$cp', '$adresse', '$telephone', '$email')"); 
			}
			unset($_POST);
			//executeRequete("INSERT INTO bar (id_membre, siret, nom_bar, photo, description, nom_gerant, prenom_gerant, ville, cp, adresse, telephone, email) VALUES ( '$id_membre', '$siret', '$nom_bar', '$photo_bdd', '$description', '$nom', '$prenom', '$ville', '$cp', '$adresse', '$telephone', '$email')"); 
			header('location:profil.php?add=ok&affichage=affichage');
			exit;
		}	
	}
}

// FIN ENREGISTREMENT
if($_GET)
{
	//MESSAGE DE VALIDATION AJOUT
	if(isset($_GET['add']) && $_GET['add'] == 'ok')
	{
		$msg .='<div class="msg_success" style="padding: 10px; text-align: center">Bar enregistré!</div>';
	}
	if(isset($_GET['mod']) && $_GET['mod'] == 'ok')
	{
		$msg .='<div class="msg_success" style="padding: 10px; text-align: center">Bar modifié!</div>';
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
}
//FIN SUPPRESSION
require_once("inc/header.inc.php");
//echo debug($_SESSION);
$membre_actuel = $_SESSION['utilisateur'];


echo '<div class="box_info" >';
echo $msg;
//echo debug($_SESSION);
if(isset($_SESSION['utilisateur']))
{
	echo '<h2>Bonjour <strong>'. ucfirst($membre_actuel['prenom']).' !</strong></h2>';
}
else
{
	header("location:".RACINE_SITE."connexion.php");
}
	

// INFOS UTILISATEUR

if(utilisateurEstConnecteEtEstAdmin() || utilisateurEstConnecteEtEstGerantEtAdmin())
{
	echo '<h3>Compte administrateur</h3>';
}
elseif(utilisateurEstConnecteEtEstGerant())
{
	echo '<h3>Compte barman</h3>';
}
else
{
	echo '<h3>Bienvenue sur votre profil</h3>' ;
} 
echo '<div class="float photo_profil">
		<img src="images/userpic_default.png" class="thumbnail float" alt="photo par défaut" >
	</div>
	<div class="infos_profil inline-block">';
if (isset($membre_actuel['sexe']) && $membre_actuel['sexe'] == 'f')
{
	echo '<p>Mme ';
}
else
{
	echo '<p>M. ';
}
echo ucfirst($membre_actuel['prenom']) .' '. ucfirst($membre_actuel['nom']) .'</p>';
// adresse de livraison
echo '<p><strong>'. ucfirst($membre_actuel['pseudo']) .'</strong></p>

	<p><strong>'. $membre_actuel['email'] .	'</strong></p>';

//LIEN MODIFIER		
if(isset($membre_actuel['id_membre']))
{
	$id_membre_actuel = $_SESSION['utilisateur']['id_membre'];
	echo '<a class="teal" href="'.RACINE_SITE.'modif_profil.php?id_membre='.$id_membre_actuel.'&action=Modifier" class="button" >Modifier</a>';		
}

echo '</div>
<div class="infos_profil inline-block">
	<h4 class="orange">Votre adresse de livraison</h4>
<p><strong>'. ucfirst($membre_actuel['prenom']) .' '. ucfirst($membre_actuel['nom']) .'</strong><br />'.$membre_actuel['adresse'] .'<br />'.$membre_actuel['cp'] .' '. ucfirst($membre_actuel['ville']) .'</p>';

echo '</div>
	</div>';

if(utilisateurEstConnecteEtEstGerant() || utilisateurEstConnecteEtEstGerantEtAdmin())
{
	echo '<div class="box_info">
			<h4 class=orange>Vos bars</h4>';
	$id_utilisateur = $_SESSION['utilisateur']['id_membre'];
	$req = "SELECT * FROM bar WHERE id_membre = '$id_utilisateur' ORDER BY id_membre DESC";

	$resultat = executeRequete($req);
	$nbcol = $resultat->field_count; 
	echo '<table>
			<tr>';

	$nb_bars = $resultat -> num_rows;

	if($nb_bars < 1)
	{
		echo '
				<td colspan="6">Vous n\'avez actuellement aucun compte Bar activé</td>	
			</tr>';
	}
	else
	{
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
			elseif((($colonne->name != 'description') && ($colonne->name != 'siret')) && ($colonne->name != 'prenom_gerant') && ($colonne->name != 'ville' && $colonne->name != 'adresse'))
			//elseif ($colonne->name != ('description' || 'siret' || 'prenom_gerant' || 'ville' || 'cp'))
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
		echo'<th></th><th></th></tr>';

		while ($ligne = $resultat->fetch_assoc()) // = tant qu'il y a une ligne de resultat, on en fait un tableau 
		{
			echo '<tr>';
			foreach($ligne AS $indice => $valeur) // foreach = pour chaque element du tableau
			{

				if($indice == 'photo')
				{
					echo '<td ><img src="'.$valeur.'" alt="'.$ligne['nom_bar'].'" title="'.$ligne['nom_bar'].'" class="thumbnail_tableau" width="80px" /></td>';
				}
				//elseif($indice == 'description')
				//{
			//		echo '<td colspan="3">' . substr($valeur, 0, 70) . '...</td>'; //Pour couper la description (affiche une description de 70 caracteres maximum)
			//	}
				elseif($indice == 'email')
				{
					echo '<td colspan="3">' . ucfirst($valeur).'</td>';	
				}
				elseif($indice == 'cp')
				{
					echo '<td>'. ucfirst($valeur) .'</td>';
				}
				elseif($indice == 'nom_gerant')
				{
					echo '<td colspan="2">' . ucfirst($valeur).' ';	
				}
				elseif($indice == 'prenom_gerant')
				{
					echo ucfirst($valeur) .'</td>';
				}
				elseif($indice == 'prenom_gerant')
				{
					echo ucfirst($valeur) .'</td>';
				}
				elseif(($indice != 'description') && ($indice != 'siret' && $indice != 'ville') && $indice != 'adresse')
				{
					echo '<td >'.$valeur.'</td>';
				}	
			}
			echo '<td><a href="?action=suppression&id_bar='.$ligne['id_bar'] .'" class="btn_delete" onClick="return(confirm(\'En êtes-vous certain ?\'));">X</a></td>';
			echo '<td><a href="?action=modification&id_bar='.$ligne['id_bar'] .'" class="btn_edit">éditer</a></td>';
			echo '</tr>';		
		}						
			
	}
	echo '</table><br />
	<a href="'.RACINE_SITE.'profil.php?action=ajouter" class="button" >Ajouter un bar</a>';	
	
	if($_GET)
	{
		if(isset($_GET['action']) &&  (($_GET['action']=='modification') || ($_GET['action']) == 'ajouter'))
		{
			if(isset($_GET['id_bar']))
			{
				$bar = executeRequete("SELECT * FROM bar WHERE id_bar = '$_GET[id_bar]' ");
				$bar_actuel = $bar->fetch_assoc();
			}

	?>		
	<form class="form" method="post" action="" enctype="multipart/form-data">
		<fieldset>
			<legend>Ajouter / Modifier un bar</legend>
			 
			<input type="hidden" name="id_bar" id="id_bar" value="<?php if(isset($bar_actuel['id_bar'])){ echo $bar_actuel['id_bar']; }?>" />
			<label for="nom_bar">Nom de l'établissement</label>
			<input required type="text" id="nom_bar" name="nom_bar"  maxlength="50" value="<?php if(isset($_POST['nom_bar'])) {echo $_POST['nom_bar'];} elseif(isset($bar_actuel['nom_bar'])){ echo $bar_actuel['nom_bar']; }?>" placeholder="Puzzle Bar" /><br />

			<label for="siret">SIRET</label>
			<input required type="siret" id="siret" name="siret"  maxlength="14" value="<?php if(isset($_POST['siret'])) {echo $_POST['siret'];} elseif(isset($bar_actuel['siret'])){ echo $bar_actuel['siret']; }?>"/><br /> 

			<label for="photo">Photo </label>
			<input type="file" name="photo" id="photo"><br />
			<?php 
		/*	if(isset($_POST['photo'])) // on affiche la photo actuelle par defaut
			{
				echo '<label>Photo actuelle</label><br />';
				echo '<img src="'. $_POST['photo'].'" width="140"/><br />';
				echo '<input type="hidden" name="photo_actuelle" value="'. $_POST['photo'].'" /><br />';
			} */
			if(isset($bar_actuel['photo'])) // on affiche la photo actuelle par defaut
			{
				echo '<label>Photo actuelle</label><br />';
				echo '<img src="'. $bar_actuel['photo'].'" width="140"/><br />';
				echo '<input type="hidden" name="photo_actuelle" id="photo_actuelle" value="'. $bar_actuel['photo'].'" /><br />';
			}
			?>	
			<label for="description">Description </label><br />
			<textarea id="description" name="description" maxlength="200" class="description_form" ><?php if(isset($_POST['description'])) {echo $_POST['description'];} elseif(isset($bar_actuel['description'])){ echo $bar_actuel['description']; }?></textarea>
			
			<label for="nom">Nom du gérant</label>
			<input required type="text" id="nom" name="nom" maxlength="45" value="<?php if(isset($_POST['nom'])) {echo $_POST['nom'];}  if(isset($bar_actuel['nom_gerant'])){ echo $bar_actuel['nom_gerant'];}?>" placeholder="Durand" required /><br />
			
			<label for="prenom">Prénom du gérant</label>
			<input required  type="text" id="prenom"  maxlength="30" name="prenom" value="<?php if(isset($_POST['prenom'])) {echo $_POST['prenom'];} if(isset($bar_actuel['prenom_gerant'])){ echo $bar_actuel['prenom_gerant'];}?>" placeholder="Jean"  required /><br />
			
			<h3>Coordonnées de l'établissement</h3>
			<label for="email">Email</label>
			<input required  type="email" id="email" name="email" maxlength="60" value="<?php if(isset($_POST['email'])) {echo $_POST['email'];} elseif(isset($bar_actuel['email'])){ echo $bar_actuel['email']; }?>" placeholder="monmail@mail.com"  required /><br />

			<label for="telephone">Téléphone</label>
			<input required  type="text" id="telephone" name="telephone" maxlength="10" value="<?php if(isset($_POST['telephone'])) {echo $_POST['telephone'];} elseif(isset($bar_actuel['telephone'])){ echo $bar_actuel['telephone']; }?>" placeholder="O111223344" required/><br />


			<label for="ville">Ville</label>
			<input required  type="text" id="ville" name="ville" value="<?php if(isset($_POST['ville'])) {echo $_POST['ville'];} elseif(isset($bar_actuel['ville'])){ echo $bar_actuel['ville']; }?>" placeholder="Maville" required /><br />
		
			<label for="cp">Code Postal</label>
			<input required  type="text" id="cp" name="cp" minlength="5" maxlength="5" value="<?php if(isset($_POST['cp'])) {echo $_POST['cp'];} elseif(isset($bar_actuel['cp'])){ echo $bar_actuel['cp']; }?>" placeholder="99999" required/><br />
			
			<label for="adresse">Adresse</label>
			<textarea required type="text" id="adresse" name="adresse" maxlength="100" placeholder="86 rue de la Ville" required><?php if(isset($_POST['adresse'])) {echo $_POST['adresse'];} elseif(isset($bar_actuel['adresse'])){ echo $bar_actuel['adresse']; }?></textarea><br />
			
			<br />
			<input type="submit" id="enregistrer" name="enregistrer" value="Enregistrer" class="button" /><br />
			<br />
			<a class="button " href="<?php echo RACINE_SITE; ?>profil.php?affichage=affichage">Retour au profil</a><br />
			<br />
		</fieldset>
	</form>			
	<br />	
	<br />
		<?php
		}	
	}
}	
echo '<!-- DERNIERES COMMANDES -->
			 <div class="box_info">
				<h4 class=orange>Vos dernières commandes</h4>';

			
//selection des commandes de l'utilisateur
		$id_utilisateur = $_SESSION['utilisateur']['id_membre'];
		$req = "SELECT * FROM commande WHERE id_membre = '$id_utilisateur' ORDER BY date DESC LIMIT 5";
		$resultat = executeRequete($req);
		echo '<table class="tableau_panier">
				<tr>
					<th>Numero de Suivi</th>
					<th>Date de Commande</th>
					<th>Montant TTC</th>
				</tr>';
		$nb_commandes = $resultat -> num_rows;
		if($nb_commandes < 1)
		{
			echo '<tr>
					<td colspan="4">Vous n\'avez pas encore passé de commande</td>	
				</tr>';
		}
		while($ma_commande = $resultat -> fetch_assoc() )
		{
			echo '<tr>
				<td> '.$ma_commande['id_commande']. ' </td>';
					
					$date_avis = date_create_from_format('Y-m-d H:i:s', $ma_commande['date']);
			echo '<td>'. date_format($date_avis, 'd/m/Y H:i').' </td>
					<td> '.$ma_commande['montant']. ' €</td>
				</tr>';
		}
		echo '</table>
		<br />
		<br />
		<br />

	 </div>';

	require_once("inc/footer.inc.php");
 
  ?>
 
