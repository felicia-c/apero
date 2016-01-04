<?php
require_once("../inc/init.inc.php");
//APERO - Felicia Cuneo - 12/2015
$titre_page = "Gestion des membres";

//Redirection si l'utilisateur n'est pas admin
if(!utilisateurEstConnecteEtEstAdmin() && !utilisateurEstConnecteEtEstGerantEtAdmin()){
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
		
		//FIN SECURITE
		if($_POST['mdp2'] != $_POST['mdp'] )
		{
			$msg .= '<div class="msg_erreur">Veuillez confirmer votre mot de passe</div>';
		}
	
	
	
	if(empty($msg)) //si pas d'erreur
	{
		$membre= executeRequete("SELECT * FROM membre WHERE pseudo='$_POST[pseudo]'");
		if($membre -> num_rows > 0) //si le pseudo est deja utilisé
		{
			$msg .='<div class="msg_erreur">Ce pseudo est déjà utilisé !</div>';
		}
		else{
		
			extract($_POST);
			$ajout = executeRequete("INSERT INTO membre (pseudo, mdp, nom, prenom, email, sexe, ville, cp, adresse, statut) VALUES ('$pseudo', '$mdp', '$nom', '$prenom', '$email', '$sexe', '$ville', '$cp', '$adresse', '$statut')"); //requete d'inscription 
			$msg .='<div class="msg_success">Inscription réussie ! 
						<a href="'.RACINE_SITE.'admin/gestion_bar.php?action=ajout&id_membre='.$mysqli->insert_id.'" >Associer un bar à ce nouveau membre</a><br />
					</div>';
		}
	}
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
								ROUND(AVG(montant),0) AS panier_moyen,
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

echo '<br /><br /><div id="large_table">';
	
/////////AFFICHAGE DE TOUS LES MEMBRES///////////
if(isset($_GET['affichage']) && $_GET['affichage'] == 'affichage')
{
	$resultat_membres = executeRequete("SELECT COUNT(id_membre) AS nbre_membres FROM membre");
	$membres =$resultat_membres -> fetch_assoc();	
	
	echo '<table>
		<tr>';

	$req .= "SELECT * FROM membre"; 
	$req = paginationGestion(10, 'membre', $req);
	$resultat = executeRequete($req);

	$nbcol = $resultat->field_count; 

	for($i= 0; $i < $nbcol; $i++) 
	{
		 $colonne= $resultat->fetch_field(); 
		
		if ($colonne->name == 'adresse')
		{
			echo '<th colspan ="2">'. $colonne->name.'</th>';
		}
		elseif(($colonne->name != 'mdp') && ($colonne->name != 'photo'))
		{
			echo '<th style="text-align: center;"><a href="?affichage=affichage&orderby='. $colonne->name;
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
			if($colonne->name == 'id_membre')
			{
				echo '> Id </a></th>';
			}
			else
			{
				echo '>'. ucfirst($colonne->name).'</a></th>'; 
			}
		}
	}
	
	echo '<th></th>

		</tr>';

	while ($ligne = $resultat->fetch_assoc()) // = tant qu'il y a une ligne de resultat, on en fait un tableau 
	{
		echo '<tr '; 
		if(isset($_GET['id_membre']) && ($_GET['id_membre'] == $ligne['id_membre']))
		{
			echo ' class="tr_active" ';
		}
		echo '>';
		foreach($ligne AS $indice => $valeur) // foreach = pour chaque element du tableau
		{
			if ($indice == 'adresse')
			{
				echo '<td colspan ="2">'. $valeur.'</td>';
			}
			elseif ($indice == 'statut')
			{
				
				if($valeur == '0')
				{
					echo '<td>Membre</td>';
				}
				if($valeur == '1')
				{
					echo '<td>Admin</td>';
				}
				if($valeur == '2')
				{
					echo '<td>Barman-Admin</td>';
				}
				if($valeur == '3')
				{
					echo '<td>Barman</td>';
				}
			}
			elseif(($indice != 'mdp') && ($indice != 'photo'))
			{
			echo '<td >'.ucfirst($valeur).'</td>';	
			}
		}
		echo '<td>
		<a class="btn_delete" href="?affichage=affichage&action=suppression&id_membre='.$ligne['id_membre'] .'" onClick="return(confirm(\'En êtes-vous certain ?\'));">
		X</a>
			</td></tr>';
	/*		<td>
		<a href="?action=modifier&id_membre='.$ligne['id_membre'] .'" >
		<img src="'.RACINE_SITE.'image/modif-icon.png" width="30px" alt="Modifier" title="Modifier" ></a>
			</td>
		; */
	}	
	
	echo '</table>
		<br />';
	affichagePaginationGestion(10, 'membre', '');
}

if(isset($_GET['action']) &&  $_GET['action']=='ajout') 
{

	?>
		
	<form class="form" method="post" action="" enctype="multipart/form-data"> <!--enctype pour ajout eventuel d'un champs photo -->
		<fieldset>
			<legend>Nouveau membre administrateur</legend>
		 
			<input type="hidden" name="id_membre" id="id_membre" value="<?php if(isset($membre_actuel['id_membre'])){ echo $membre_actuel['id_membre']; }?>" /><!-- On met un input caché pour pouvoir identifier le membre lors de la modification (REPLACE se base sur l'id uniquement(PRIMARY KEY)) /!\SECURITE : On est ici dans un back-office, on peut donc se permettre une certaine confiance en l'utilisateur, mais les champs cachés ne sont pas sécurisés pour l'acces public il faut faire des controles securités sur les url -->
			<label for="pseudo">Pseudo</label>
			<input required type="text" id="pseudo" name="pseudo"  maxlength="14" value="<?php if(isset($membre_actuel['pseudo'])) {echo $membre_actuel['pseudo'];}?>" placeholder="JohnDoe" /><br />
			
			<label for="mdp">Mot de passe</label>
			<input required type="password" id="mdp" name="mdp"  maxlength="14" value="<?php if(isset($membre_actuel['mdp'])) {echo $membre_actuel['mdp'];}?>"  placeholder="Password"/><br /> 
			
				
			<label for="mdp2">Confirmer le mot de passe</label>
			<input required type="password" id="mdp2" name="mdp2"  maxlength="14" /><br /> 
				
			<label for="statut">Statut </label>
			<select required id="statut" name="statut">
				<option value="0"<?php if((isset($_POST['statut']) && $_POST['statut'] == "0") ||(isset($article_actuel['statut'])&& $article_actuel['statut'] == "0")) { echo 'selected';} ?> >Membre</option>
				<option  value="1"<?php if((isset($_POST['statut']) && $_POST['statut'] == "1") ||(isset($article_actuel['statut'])&& $article_actuel['statut'] == "1")) { echo 'selected';} ?> >Administrateur</option>
			</select><br>	
				
				
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
			</select><br />
			
			<h3>Adresse de Livraison</h3>
			
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
echo '</div><br /><br /><br />';


	
require_once("../inc/footer.inc.php");	
	
?>