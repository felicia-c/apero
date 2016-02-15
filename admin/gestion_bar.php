<?php 
require_once("../inc/init.inc.php");
//APERO - Felicia Cuneo - 12/2015
$titre_page = "Gestion des bars";

//Redirection si l'utilisateur n'est pas admin
if(!utilisateurEstConnecteEtEstAdmin() && !utilisateurEstConnecteEtEstGerantEtAdmin())
{
	header("location:../connexion.php");
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

if(!empty($_POST))
{
 // SECURITE 
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9.\'_ -]+$#', $_POST['nom']); 
	if(!$verif_caractere && !empty($_POST['nom']))
	{
		$msg .= '<div class="msg_erreur" >Nom - Caractères acceptés: _ -\' àâäçéèêëï A à Z et 0 à 9</div>';  
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
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ ,\'-]+$#', $_POST['adresse']); 
	if(!$verif_caractere && !empty($_POST['adresse']))
	{
		$msg .= '<div class="msg_erreur">Adresse - Caractères acceptés: , \' _ - àâäçéèêëïa A à Z et 0 à 9</div>';  
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
				// $msg .= '<div class="bg-success"><h4>OK !</h4></div>';
				$nom_photo = $_POST['nom_bar'] . '_' . $_FILES['photo']['name']; //afin que chaque nom de photo soit unique
				
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
			foreach($_POST AS $indice => $valeur )
			{
				$_POST[$indice] = htmlentities($valeur, ENT_QUOTES); 
			}
			extract($_POST);
			$resultat = executeRequete("SELECT statut FROM membre WHERE id_membre = '$id_membre' ");
			$statut = $resultat -> fetch_assoc();
			if(($statut['statut'] != 3) && $statut['statut'] != 2)
			{
				if($statut['statut'] == 1)
				{
					executeRequete("UPDATE membre SET statut = 2 WHERE id_membre='$_POST[id_membre]'");
				}
				else
				{
					executeRequete("UPDATE membre SET statut = 3 WHERE id_membre='$_POST[id_membre]'");
				}
			}
			if($_GET['action'] == 'modification')
			{
				executeRequete("UPDATE bar SET id_membre = '$id_membre', siret ='$siret', nom_bar = '$nom_bar', photo = '$photo_bdd', description= '$description', nom_gerant = '$nom', prenom_gerant = '$prenom', ville = '$ville', cp = '$cp', adresse = '$adresse', telephone= '$telephone', email = '$email' WHERE id_bar = '$_GET[id_bar]'");
				header('location:gestion_bar.php?mod=ok&affichage=affichage&id_bar='.$_GET['id_bar'].$page.''.$orderby.''.$asc_desc.'');
			}
			else
			{
				executeRequete("INSERT INTO bar (id_membre, siret, nom_bar, photo, description, nom_gerant, prenom_gerant, ville, cp, adresse, telephone, email) VALUES ( '$id_membre', '$siret', '$nom_bar', '$photo_bdd', '$description', '$nom', '$prenom', '$ville', '$cp', '$adresse', '$telephone', '$email')"); //requete d'inscription (pour la PHOTO on utilise le chemin src que l'on a enregistré ds $photo_bdd)
				header('location:gestion_bar.php?add=ok&affichage=affichage&id_bar='.$mysqli->insert_id.''.$page.''.$orderby.''.$asc_desc.'');
			}
		}	
	}
}
// FIN ENREGISTREMENT

if(isset($_GET['modif']) && $_GET['modif'] == 'desactiver')
	{
		executeRequete("UPDATE bar SET statut='0' WHERE id_bar='$_GET[id_bar]'");
		$msg .='<div class="msg_success"><h4>Compte bar N°'. $_GET['id_bar'] .' désactivé!</h4></div>';
	}

	//VALIDER UNE COMMANDE ( = PAYEE, prête à envoyer)
	 if(isset($_GET['modif']) && $_GET['modif'] == 'activer')
	{
		executeRequete("UPDATE bar SET statut='1' WHERE id_bar='$_GET[id_bar]'");
		$msg .='<div class="msg_success"><h4>Compte bar N°'. $_GET['id_bar'] .' activé!</h4></div>';
	}

// SUPPRESSION
 
 if(isset($_GET['action']) && $_GET['action'] == 'suppression')
{
	$resultat = executeRequete("SELECT * FROM bar WHERE id_bar= '$_GET[id_bar]'"); //on recupere les infos afin de connaitre son image  pour pouvoir la supprimer
	$produit_a_supprimer = $resultat->fetch_assoc();
	//Spécial pour la suppression de fichiers (et pas de données):
	$chemin_produit = RACINE_SERVER . $produit_a_supprimer['photo'] ; // Nous avons besoin du chemin du produit depuis la racine serveur pour supprimer la photo du serveur
	if(!empty($produit_a_supprimer['photo']) && file_exists($chemin_produit)) // FILE_EXISTS verifie si l'élément existe
	{
		unlink($chemin_produit); //UNLINK() va  SUPPRIMER le fichier du serveur
	}
	
	executeRequete("DELETE FROM bar WHERE id_bar='$_GET[id_bar]'");
	$msg .='<div class="msg_success" style="padding: 10px; text-align: center">Bar N°'. $_GET['id_bar'] .' supprimé avec succès!</div>';
	$_GET['affichage'] = 'affichage';
	$page;
	$orderby;
	$asc_desc; 	
}
//FIN SUPPRESSION

//MESSAGE DE VALIDATION AJOUT
if(isset($_GET['add']) && $_GET['add'] == 'ok')
{
	$msg .='<div class="msg_success">Bar enregistré ! Il sera validé après vérification</div>';
}
//MESSAGE DE VALIDATION MODIF
if(isset($_GET['mod']) && $_GET['mod'] == 'ok')
{
	$msg .='<div class="msg_success">Bar modifié !</div>';
}


$req = "";
require_once("../inc/header.inc.php");

echo '<div="box_info">';
		
//STATS
$resultat = executeRequete("SELECT SUM(montant) AS total,
								COUNT(id_commande) AS nbre_commandes,
								ROUND(AVG(montant),2) AS panier_moyen,
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
	
	echo '<h2 class="orange">Tous les bars ('. $donnees['nbre_bar'].')</h2>
	<a href="?action=ajout" class="button"> > Ajouter un bar</a><br />
	<a href="'.RACINE_SITE.'admin/gestion_promos_bar.php"> > Offres Apéro</a>';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'ajout')
{
	echo '<h2 class="orange">Ajouter un bar</h2>
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
		<table class="large_table" id="details">';
	$req = "SELECT * FROM bar";

	$req = paginationGestion(7, 'bar', $req);
	$resultat = executeRequete($req); 
	
	$dont_link = null; // entete du tablau sans order by
	$dont_show = ''; // colonne non affichée
	enteteTableau($resultat, $dont_show, $dont_link); //entete tableau

	while ($ligne = $resultat->fetch_assoc())
	{
		echo '<tr '; 
		if((isset($_GET['id_bar']) && ($_GET['id_bar'] == $ligne['id_bar'])) || (isset($_GET['id_membre']) && ($_GET['id_membre'] == $ligne['id_membre'])))
		{
			echo ' class="tr_active" ';
		}
		echo '>';
		foreach($ligne AS $indice => $valeur)
		{
			if($indice == 'photo')
			{
				echo '<td ><img src="'.RACINE_SITE.$valeur.'" alt="'.$ligne['nom_bar'].'" title="'.$ligne['nom_bar'].'" class="thumbnail_tableau" width="80px" /></td>';
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
			elseif($indice == 'adresse')
			{
				echo '<td colspan="2">' . ucfirst($valeur).'</td>';	
			}
			elseif($indice == 'id_membre')
			{
				echo '<td><a href="?affichage=affichage&action=detail&info=membre&id_membre='.$ligne['id_membre'].'&id_bar='.$ligne['id_bar'].$page.$orderby.$asc_desc.'#details">'.$valeur.'</a></td>'; //Lien au niveau de l'id pour afficher les details de la commande
			}
			elseif($indice == 'email')
			{
				echo '<td colspan="1">' . ucfirst($valeur).'</td>';	
			}
			elseif($indice == 'description')
			{
				echo '<td>'.substr($valeur, 0, 40).'...</td>';
			}
			elseif($indice == 'statut')
			{
				if($valeur === '1')
				{
					echo '<td class="teal">actif</td>';
					echo '<td>
						<a class="btn_delete" href="?affichage=affichage&modif=desactiver&id_bar='.$ligne['id_bar'] .$page.''.$orderby.''.$asc_desc.'#details" class="btn" onClick="return(confirm(\'En êtes-vous certain ?\'));">désactiver</a>
					</td>';
				}
				else
				{
					echo '<td class="tomato">en attente de validation</td>';
					echo '<td>
						<a class="btn_edit" href="?affichage=affichage&modif=activer&id_bar='.$ligne['id_bar'] . $page.''.$orderby.''.$asc_desc.'#details" class="btn" onClick="return(confirm(\'En êtes-vous certain ?\'));">activer</a>
					</td>';
				}
				
			}
			else
			{
				echo '<td >'.ucfirst($valeur).'</td>';
			}
		}

		
		echo '<td><a href="?affichage=affichage&action=suppression&id_bar='.$ligne['id_bar'].''.$page.''.$orderby.''.$asc_desc.'#details" class="btn_delete" onClick="return(confirm(\'En êtes-vous certain ?\'));">X</a></td>';
		
		echo '<td><a href="?affichage=affichage&action=modification&id_bar='.$ligne['id_bar'].''.$page.''.$orderby.''.$asc_desc.'#details" class="btn_edit">éditer</a></td>';
		echo '</tr>';
	}						
	echo '</table><br />';

	affichagePaginationGestion(7, 'bar', '');
	
}

//DETAILS MEMBRE

if(isset($_GET['id_membre']))
	{
		if((isset($_GET['action']) && $_GET['action'] == 'detail') && (isset($_GET['info']) == 'membre'))
		{
			//On affiche les infos du membre associé au bar :
			
			echo '<h4 id="details">Membre N° '.$_GET['id_membre'].'</h4>';

			$resultat = executeRequete("SELECT * FROM membre WHERE id_membre = '$_GET[id_membre]'");
			echo'<table >';
			
			$dont_link = 'nono'; // entete du tablau sans order by
			$dont_show = 'photo'; // colonne non affichée
			enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
			while ($ligne = $resultat->fetch_assoc())  
			{
				echo '<tr>';
				foreach($ligne AS $indice => $valeur)
				{
					if ($indice == 'adresse')
					{
						echo '<td colspan ="">'. $valeur.'</td>';
					}
						elseif ($indice == 'statut')
					{
						if(($valeur == '1') || $valeur == '2')
						{
							echo '<td>Admin</td>';
						}
						elseif($valeur == '3')
						{
							echo '<td>Barman</td>';
						}
						else
						{
							echo '<td>Membre</td>';
						}
					}
					elseif(($indice != 'mdp') && ($indice != 'photo'))
					{
						echo '<td >'.ucfirst($valeur).'</td>';	
					}
				}
			}
			echo '</tr>
			</table>
			<br />';	
		}
	}	

//FORM AJOUT / MODIF
	
if(isset($_GET['action']) &&  (($_GET['action']=='modification') || ($_GET['action']) == 'ajout'))
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
		
			<label for="id_membre">Compte membre lié au bar</label>
			<select required id="id_membre" name="id_membre">
			<?php
				$req = "SELECT id_membre, pseudo, nom, prenom, statut FROM membre ORDER BY nom";
				$resultat = executeRequete($req);
				//$nb_ligne = count($resultat)
				while($ligne = $resultat -> fetch_assoc())
				{
					echo '<option value="'.$ligne['id_membre'].'"';
					if((isset($_GET['id_membre']) && $_GET['id_membre'] == $ligne['id_membre']) || (isset($bar_actuel['id_membre']) && $bar_actuel['id_membre'] == $ligne['id_membre']))
					{
						echo 'selected';
					}
					if(isset($_POST['id_membre']) && isset($_POST['id_membre']) == $ligne['id_membre'])
					{
						echo 'selected';
					}
					echo ' >'.$ligne['id_membre'].' - '.$ligne['prenom'].' '.$ligne['nom'].' | '.$ligne['pseudo'].' | '.$ligne['statut'].'</option>';
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
		/*	if(isset($_POST['photo'])) // on affiche la photo actuelle par defaut
			{
				echo '<label>Photo actuelle</label><br />';
				echo '<img src="'. $_POST['photo'].'" width="140"/><br />';
				echo '<input type="hidden" name="photo_actuelle" value="'. $_POST['photo'].'" /><br />';
			} */
			if(isset($bar_actuel['photo'])) // on affiche la photo actuelle par defaut
			{
				echo '<label>Photo actuelle</label><br />';
				echo '<img src="'.RACINE_SITE. $bar_actuel['photo'].'" width="140"/><br />';
				echo '<input type="hidden" name="photo_actuelle" id="photo_actuelle" value="'. $bar_actuel['photo'].'" /><br />';
			}
			?>	
			<label for="description">Description </label><br />
			<textarea id="description" name="description" maxlength="200" class="description_form" ><?php if(isset($_POST['description'])) {echo $_POST['description'];} elseif(isset($bar_actuel['description'])){ echo $bar_actuel['description']; }?> </textarea>
			
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
			<a class="button " href="<?php echo RACINE_SITE; ?>admin/gestion_bar.php?affichage=affichage<?php echo $page.''.$orderby.''.$asc_desc ?>">Retour aux bars</a><br />
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