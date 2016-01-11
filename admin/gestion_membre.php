<?php
require_once("../inc/init.inc.php");
//APERO - Felicia Cuneo - 12/2015
$titre_page = "Gestion des membres";

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
	if(isset($_GET['action']) && $_GET['action'] == 'ajout')
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
			
			
		// HTMLENTITIES 	
			
		foreach($_POST AS $indice => $valeur)
		{
			$_POST[$indice] = htmlentities($valeur, ENT_QUOTES);
		} 
		
		// VERIF STRLEN
		
		if(strlen($_POST['nom'])< 2 || strlen($_POST['nom'])>20)
		{
			$msg .= '<div class="msg_erreur" >Le nom doit avoir entre 2 et 20 caractères inclus</div>';
		}
		if(strlen($_POST['prenom'])< 2 || strlen($_POST['prenom'])>20) 
		{
			$msg .= '<div class="msg_erreur">Le prénom doit avoir entre 2 et 20 caractères inclus</div>';
		}
		if(strlen($_POST['email'])<8 ) 
		{
			$msg .= '<div class="msg_erreur">E-mail trop court !</div>';
		}
		if(strlen($_POST['email'])>30) 
		{
			$msg .= '<div class="msg_erreur">E-mail trop long !</div>';
		}
		if(strlen($_POST['ville'])>20) 
		{
			$msg .= '<div class="msg_erreur">Le nom de la ville est trop long !</div>';
		}
		if(strlen($_POST['adresse'])>30) 
		{
			$msg .= '<div class="msg_erreur">Adresse trop longue !</div>';
		}
		if(strlen($_POST['cp']) != 5) 
		{
			$msg .= '<div class="msg_erreur">Le code postal doit contenir 5 caractères</div>';
		}
		if(strlen($_POST['statut']) != 1) 
		{
			$msg .= '<div class="msg_erreur">Le statut est trop long !</div>';
		}
		//FIN SECURITE
		if($_POST['mdp2'] != $_POST['mdp'] )
		{
			$msg .= '<div class="msg_erreur">Veuillez confirmer votre mot de passe</div>';
		}
	
	}
	
	if(empty($msg)) //si pas d'erreur
	{
		extract($_POST);
		if(isset($_GET['action']) && $_GET['action'] == 'ajout')
		{
			$membre= executeRequete("SELECT * FROM membre WHERE pseudo='$_POST[pseudo]' AND id_membre != '$_POST[id_membre]' ");
			if($membre -> num_rows > 0) //si le pseudo est deja utilisé
			{
				$msg .='<div class="msg_erreur">Ce pseudo est déjà utilisé !</div>';
			}
			else
			{
				executeRequete("INSERT INTO membre (pseudo, mdp, nom, prenom, email, sexe, ville, cp, adresse, statut) VALUES ('$pseudo', '$mdp', '$nom', '$prenom', '$email', '$sexe', '$ville', '$cp', '$adresse', '$statut')"); //requete d'inscription 
				header('location:gestion_membre.php?add=ok&affichage=affichage&id_membre='.$mysqli->insert_id.''.$page.''.$orderby.''.$asc_desc.'');
				exit;
			}
		}
		else
		{
			executeRequete("UPDATE membre SET statut = '$statut' WHERE id_membre = '$id_membre'");
			header('location:gestion_membre.php?mod=ok&affichage=affichage&id_membre='.$id_membre.$page.''.$orderby.''.$asc_desc.'');
			exit;
		}
	}
}

//FIN ENREGISTREMENT

//MESSAGE DE VALIDATION AJOUT
if(isset($_GET['add']) && $_GET['add'] == 'ok')
{
	$msg .='<div class="msg_success">Inscription réussie ! <a href="'.RACINE_SITE.'admin/gestion_bar.php?action=ajout&id_membre='.$mysqli->insert_id.'" >Associer un bar à ce nouveau membre</a><br /></div>';
}
//MESSAGE DE VALIDATION MODIF
if(isset($_GET['mod']) && $_GET['mod'] == 'ok')
{
	$msg .='<div class="msg_success">Statut modifié !</div>';
}


// SUPPRESSION

if(isset($_GET['action']) && $_GET['action'] == 'suppression')
{
	$resultat = executeRequete("SELECT * FROM membre WHERE id_membre = '$_GET[id_membre]'"); //on recupere les infos dans la table membre
	//executeRequete("DELETE FROM avis WHERE id_membre = '$_GET[id_membre]'"); // on supprime les avis du membre
	executeRequete("UPDATE commande SET id_membre = NULL WHERE id_membre = '$_GET[id_membre]' ");
	executeRequete("DELETE FROM newsletter WHERE id_membre = '$_GET[id_membre]'");
	executeRequete("DELETE FROM membre WHERE id_membre='$_GET[id_membre]'");
	$msg .='<div class="msg_success">Membre N°'. $_GET['id_membre'] .' supprimé avec succès!</div>';   //suppression de la commande dans la table + affichage d'un msg de confirmation

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

$resultat_membres = executeRequete("SELECT COUNT(id_membre) AS nbre_membres FROM membre");
$membres =$resultat_membres -> fetch_assoc();	

echo '<h3>CA Total : '. $commandes['total'] .'€  |  Nombre de commandes: '. $commandes['nbre_commandes'].' | Commande moyenne : '.$commandes['panier_moyen'].'€</h3><br />';

// FIN STATS

// LIENS
$resultat = executeRequete("SELECT COUNT(id_membre) AS nbre_membre FROM membre");
$donnees =$resultat -> fetch_assoc();	

if(isset($_GET['affichage']) && $_GET['affichage'] == 'affichage')
{	
	
	echo '<h2><a href="?affichage=affichage" class="button active" >Tous les membres ('. $donnees['nbre_membre'].')</a></h2>
	<a href="?action=ajout" class="button">Ajouter un membre / admin</a><br />';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'ajout')
{
	echo '<h2><a href="?action=ajout" class="button active">Ajouter un membre /admin</a></h2>
	<a href="?affichage=affichage" class="button" >Tous les membres</a>';
}
else
{
	echo '<h2><a href="?affichage=affichage" class="button" >Tous les membres</a></h2>
		<h2><a href="?action=ajout" class="button">Ajouter un membre / admin</a></h2><br />
		<a href="'.RACINE_SITE.'admin/gestion_newsletter.php">Newsletter</a>';
}

// FIN LIENS
echo $msg;

echo '<br /><br />
	<div id="box_info">';
	
/////////AFFICHAGE DE TOUS LES MEMBRES///////////
if(isset($_GET['affichage']) && $_GET['affichage'] == 'affichage')
{
	$resultat_membres = executeRequete("SELECT COUNT(id_membre) AS nbre_membres FROM membre");
	$membres =$resultat_membres -> fetch_assoc();	
	
	echo '<table class="large_table" id="details">';

	$req .= "SELECT * FROM membre"; 
	$req = paginationGestion(5, 'membre', $req);
	$resultat = executeRequete($req);

	$dont_link = null; // entete du tablau sans order by
	$dont_show = 'photo'; // colonne non affichée
	enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
	
	while ($ligne = $resultat->fetch_assoc()) 
	{
		echo '<tr '; 
		if(isset($_GET['id_membre']) && ($_GET['id_membre'] == $ligne['id_membre']))
		{
			echo ' class="tr_active" ';
		}
		echo '>';
		foreach($ligne AS $indice => $valeur)
		{
			if ($indice == 'adresse')
			{
				echo '<td colspan ="2">'. $valeur.'</td>';
			}

			//FORM MODIF STATUT
			elseif ($indice == 'statut')
			{
				echo '<td>
				<form method="post" action="" >			
					<input type="hidden" name="id_membre" value="'.$ligne['id_membre'].'" />
					<select required id="statut" name="statut">
						<option value="0"';
					if(isset($ligne['statut'])&& $ligne['statut'] == '0')
					{ 
						echo 'selected';
					} 
					echo '>Membre</option>
						<option  value="1"';
					if(isset($ligne['statut'])&& $ligne['statut'] == '1')
					{
						echo 'selected';
					}
					echo'>Administrateur</option>
						<option  value="2"';
					if(isset($ligne['statut'])&& $ligne['statut'] == '2')
					{
						echo 'selected';
					}
					echo'>Admin et barman</option>
						<option  value="3"';
					if(isset($ligne['statut'])&& $ligne['statut'] == '3')
					{
						echo 'selected';
					}
					echo'>Barman</option>
					</select>
					<input type="submit" name="ok" value="ok" onClick="return(confirm(\'Voulez-vous vraiment modifier le statut du membre n°'.$ligne['id_membre'] .' ? \'));" />
				</form>
			</td>';
			}
			elseif(($indice != 'mdp') && ($indice != 'photo'))
			{
			echo '<td ><a class="lien_tr" href="?affichage=affichage&action=detail&id_membre='.$ligne['id_membre'].''.$page.''.$orderby.''.$asc_desc.'#details" >'.ucfirst($valeur).'</a></td>';	
			}
		}
		echo '<td>
				<a class="btn_delete" href="?affichage=affichage&action=suppression&id_membre='.$ligne['id_membre'].''.$page.''.$orderby.''.$asc_desc.'" onClick="return(confirm(\'Voulez-vous vraiment supprimer le membre n°'.$ligne['id_membre'] .' ? (il sera désinscrit de la newsletter)\'));"> X </a>
			</td>
		</tr> ';
	}	
	echo '</table>
		<br />';
	affichagePaginationGestion(5, 'membre', '');
	echo '<br />';
}

// COMMANDES MEMBRE
if(isset($_GET['action']) && $_GET['action'] == 'detail') 
{
	if(isset($_GET['id_membre']))
	{
		echo '<div class="box_info">
			<h3 id="details_membre">Commandes du membre n°'.$_GET['id_membre'].'</h3>
			<table>';
	
		$resultat = executeRequete("SELECT * FROM commande WHERE id_membre = '".$_GET['id_membre']."'");

		$dont_link = 'nono'; // entete du tablau sans order by
		$dont_show = ''; // colonne non affichée
		enteteTableau($resultat, $dont_show, $dont_link); //entete tableau

		while ($ligne = $resultat->fetch_assoc()) 
		{
			echo '<tr '; 
			if(isset($_GET['id_commande']) &&  $_GET['id_commande'] == $ligne['id_commande'])
			{
				echo ' class="tr_active" ';
			}
			echo '>';

			foreach($ligne AS $indice => $valeur)
			{
				
				if($indice == 'id_commande')//Lien au niveau de l'id pour afficher les details de la commande
				{
					echo '<td><a href="?affichage=affichage&action=detail&id_membre='.$ligne['id_membre'].'&id_commande='.$ligne['id_commande'].''.$page.''.$orderby.''.$asc_desc.'#details_membre">'.$valeur.'</a></td>'; 
				}
				elseif($indice == 'date') // affichage du timestamp de la commande en format fr
				{
					echo '<td>';
						$date = date_create_from_format('Y-m-d H:i:s', $valeur);
					echo date_format($date, 'd/m/Y H:i') . '</td>';
				}
				elseif($indice == 'montant')
				{		
					echo  '<td>'.$valeur. ' € </td>';
				}
				else
				{
					echo '<td >'.$valeur.'</td>';
				}
			}
			echo '<td>
			<a class="btn_delete" href="?affichage=affichage&action=detail&action=suppression&id_membre='.$_GET['id_membre'].'&id_commande='.$ligne['id_commande'].''.$page.''.$orderby.''.$asc_desc.'" onClick="return(confirm(\'En êtes-vous certain ?\'));"> X </a>
				</td>
			</tr>';
		}						
		echo '</table>
		<br />
		</div>';
	}
//DETAILS COMMANDE
	if(isset($_GET['id_commande']))
	{
		echo '<div class="box_info">
		<table>';
		$resultat = executeRequete("SELECT * FROM details_commande WHERE id_commande = '".$_GET['id_commande']."'");
		$dont_link = 'nono'; // entete du tablau sans order by
		$dont_show = ''; // colonne non affichée
		enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
		while ($ligne = $resultat->fetch_assoc()) 
		{
			echo '<tr '; 
			if(isset($_GET['id_details_commande']) && ($_GET['id_details_commande'] == $ligne['id_details_commande']))
			{
				echo ' class="tr_active" ';
			}
			echo '>';
			
			foreach($ligne AS $indice => $valeur) 
			{	
				if($indice == 'id_produit')
				{
					echo '<td><a href="?affichage=affichage&action=detail&id_produit='.$ligne['id_produit'].'&id_membre='.$_GET['id_membre'].'&id_commande='.$_GET['id_commande'].'&id_details_commande='.$ligne['id_details_commande'].''.$page.''.$orderby.''.$asc_desc.'#details_membre">'.$valeur.'</a></td>'; 
				}
				elseif($indice == 'prix') // affichage du timestamp de la commande en format fr
				{		
					echo  '<td>'.$valeur. ' € </td>';
				}
				else
				{
					echo '<td >'.$valeur.'</td>';
				}
			}
			echo '<td></td>';
			echo '</tr>';
		}					
		echo '</table></div><br />';
	}
// DETAILS PRODUITS	
	if(isset($_GET['id_produit']))
	{
		echo '<div class="box_info">
		<table  class="large_table">';
		$resultat = executeRequete("SELECT * FROM produit WHERE id_produit = '".$_GET['id_produit']."'");

		$dont_link = 'nono'; // entete du tablau sans order by
		$dont_show = ''; // colonne non affichée
		enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
		while ($ligne = $resultat->fetch_assoc()) 
		{
			echo '<tr>';
			
			foreach($ligne AS $indice => $valeur) 
			{	
				if($indice == 'description')
				{
					echo '<td>'. substr($valeur, 0, 20).'...</td>';
				}
				elseif($indice == 'photo')
				{
					echo '<td ><img src="'.$valeur.'" alt="'.$ligne['titre'].'" title="'.$ligne['titre'].'" class="thumbnail_tableau" width="80px" /></td>';
				}
				else
				{
					echo '<td >'.$valeur.'</td>';
				}
			}
			echo '<td></td></tr>';
		}					
		echo '</table><br />';
	}
}

// FORM AJOUT
if(isset($_GET['action']) && $_GET['action']=='ajout') 
{
	?>
		
	<form class="form" method="post" action="" enctype="multipart/form-data"> <!--enctype pour ajout eventuel d'un champs photo -->
		<fieldset>
			<legend><h3>Nouveau membre / administrateur</h3></legend>
		 
			<input type="hidden" name="id_membre" id="id_membre" value="<?php if(isset($membre_actuel['id_membre'])){ echo $membre_actuel['id_membre']; }?>" /><!-- On met un input caché pour pouvoir identifier le membre lors de la modification (REPLACE se base sur l'id uniquement(PRIMARY KEY)) /!\SECURITE : On est ici dans un back-office, on peut donc se permettre une certaine confiance en l'utilisateur, mais les champs cachés ne sont pas sécurisés pour l'acces public il faut faire des controles securités sur les url -->
			<label for="pseudo">Pseudo</label>
			<input required type="text" id="pseudo" name="pseudo"  maxlength="14" value="<?php if(isset($membre_actuel['pseudo'])) {echo $membre_actuel['pseudo'];}?>" placeholder="JohnDoe" /><br />
			
			<label for="mdp">Mot de passe</label>
			<input required type="password" id="mdp" name="mdp"  maxlength="14" value="<?php if(isset($membre_actuel['mdp'])) {echo $membre_actuel['mdp'];}?>"  placeholder="Password"/><br /> 
			
				
			<label for="mdp2">Confirmer le mot de passe</label>
			<input required type="password" id="mdp2" name="mdp2"  maxlength="14" /><br /> 
		</fieldset>
		<fieldset>
			<label for="statut">Statut </label>
			<select required id="statut" name="statut">
				<option value="0"<?php if(isset($_POST['statut']) && $_POST['statut'] == "0"){ echo 'selected';} ?> >Membre</option>
				<option  value="1"<?php if(isset($_POST['statut']) && $_POST['statut'] == "1"){ echo 'selected';} ?> >Administrateur (accès au BO)</option>
				<option value="2"<?php if(isset($_POST['statut']) && $_POST['statut'] == "2"){ echo 'selected';} ?> >Barman & Admin (accès au BO)</option>
				<option  value="3"<?php if(isset($_POST['statut']) && $_POST['statut'] == "3"){ echo 'selected';} ?> >Barman</option>
			</select><br />	
				
			<label for="nom">Nom</label>
			<input required type="text" id="nom" name="nom" value="<?php if(isset($_POST['nom'])) {echo $_POST['nom'];}?>" placeholder="Durand" required /><br />
			
			<label for="prenom">Prénom</label>
			<input required  type="text" id="prenom" name="prenom" value="<?php if(isset($_POST['prenom'])) {echo $_POST['prenom'];}?>" placeholder="Jean"  required /><br />
			
			<label for="email">Email</label>
			<input required  type="text" id="email" name="email" value="<?php if(isset($_POST['email'])) {echo $_POST['email'];}?>" placeholder="monmail@mail.com"  required /><br />
			
			<label for="sexe">Sexe </label><br /> <!--On met un cas par défaut + une valeur checkée si le formulaire a dejà été rempli-->
			<select required  id="sexe" name="sexe" required >
				<option value="m" <?php if(isset($_POST['sexe']) && $_POST['sexe'] == "m") { echo 'selected';} ?>> Homme</option>
				<option value="f" <?php if(isset($_POST['sexe']) && $_POST['sexe'] == "f") { echo 'selected';} ?> >Femme</option></select><br /><br />
			</select>
		</fieldset>

		<fieldset>
			<legend><h3>Adresse de Livraison</h3></legend>
			
			<label for="ville">Ville</label>
			<input required  type="text" id="ville" name="ville" value="<?php if(isset($_POST['ville'])) {echo $_POST['ville'];}?>" placeholder="Maville" required /><br />
		
			<label for="cp">Code Postal</label>
			<input required  type="text" id="cp" name="cp" value="<?php if(isset($_POST['cp'])) {echo $_POST['cp'];}?>" placeholder="99999" required/><br />
			
			<label for="adresse">Adresse</label>
			<textarea required type="text" id="adresse" name="adresse" placeholder="86 rue de la Ville" required><?php if(isset($_POST['adresse'])) {echo $_POST['adresse'];}?></textarea><br />
			
			<br />
			<input type="submit" id="ajouter" name="ajouter" value="Ajouter" class="button" /><br />
		
			<a class="button " href="<?php echo RACINE_SITE; ?>admin/gestion_membre.php?affichage=affichage">Retour aux membres</a><br />
			<br />
			<a href="'.RACINE_SITE.'admin/gestion_bar.php?action=ajout" >Ajouter un bar</a><br /><br />
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