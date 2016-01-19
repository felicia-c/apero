<?php 
$titre_page = "Modifier mon profil";
require_once("inc/init.inc.php");
//APERO- Felicia Cuneo - 12/2015

if(!utilisateurEstConnecte()) //Si l'utilisateur n'est PAS connecté (SECURITE)
{
	header("location:connexion.php");
	exit();
}

//////// SI FORMULAIRE ENVOYE  ///////////////


if($_POST)
{
 // SECURITE 
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['pseudo']); 
	if(!$verif_caractere && !empty($_POST['pseudo']))
	{
		$msg .= '<div class="msg_erreur" ><h4> Caractères acceptés: A à Z et 0 à 9</h4></div>';  
	}
	$verif_caractere = preg_match('#^[a-zA-Z0-9._-]+$#', $_POST['old_mdp']);
	if(!$verif_caractere && !empty($_POST['old_mdp']))
	{
		$msg .= '<div class="msg_erreur" ><h4> 2 Caractères acceptés: -_ A à Z et 0 à 9</h4></div>';  
	}
	$verif_caractere = preg_match('#^[a-zA-Z0-9._-]+$#', $_POST['mdp']);
	if(!$verif_caractere && !empty($_POST['mdp']))
	{
		$msg .= '<div class="msg_erreur" ><h4> 2 Caractères acceptés: -_ A à Z et 0 à 9</h4></div>';  
	}
	
	$verif_caractere = preg_match('#^[a-zA-Z0-9._-]+$#', $_POST['mdp2']);
	if(!$verif_caractere && !empty($_POST['mdp2']))
	{
		$msg .= '<div class="msg_erreur" ><h4> 3 Caractères acceptés: -_ A à Z et 0 à 9</h4></div>';  
	}
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ \'-]+$#', $_POST['nom']); 
	if(!$verif_caractere && !empty($_POST['nom']))
	{
		$msg .= '<div class="msg_erreur" ><h4> Caractères acceptés: - A à Z et 0 à 9</h4></div>';  
	}
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['prenom']); 
	if(!$verif_caractere && !empty($_POST['prenom']))
	{
		$msg .= '<div class="msg_erreur" ><h4> Caractères acceptés: àâäçéèêëï - A à Z et 0 à 9</h4></div>';  
	}

	$email = filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ); 
	if(!$email && !empty($_POST['email']))
	{
		$msg .= '<div class="msg_erreur" >Adresse e-mail invalide !</div>'; 
	} 
		
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['ville']); 
	if(!$verif_caractere && !empty($_POST['ville']))
	{
		$msg .= '<div class="msg_erreur" ><h4> Caractères acceptés: àâäçéèêëï, A à Z -_ 0 à 9 </h4></div>';  
	}
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9.,_ \'-]+$#', $_POST['adresse']); 
	if(!$verif_caractere && !empty($_POST['adresse']))
	{
		$msg .= '<div class="msg_erreur"><h4> Caractères acceptés: àâäçéèêëï A à Z et 0 à 9</h4></div>';  
	}
	
	$verif_caractere = intval($_POST['cp']); 
	if(!$verif_caractere && !empty($_POST['cp']))      
	{
		$msg .= '<div class="msg_erreur" ><h4>Caractères acceptés: 0 à 9</h4></div>';  
	}
		
		

	// VERIF STRLEN
	if(strlen($_POST['pseudo'])< 4 || strlen($_POST['pseudo'])>15) 
	{
		$msg .= '<div class="msg_erreur" ><h4>Le pseudo doit avoir entre 4 et 15 caractères inclus</h4></div>';
	}
	if(!empty($_POST['mdp']) && ((strlen($_POST['mdp'])< 4 || strlen($_POST['mdp'])>15)))
	{
		$msg .= '<div class="msg_erreur" ><h4>Le mot de passe doit avoir entre 4 et 15 caractères inclus</h4></div>';
	}
	if(!empty($_POST['mdp2']) && ((strlen($_POST['mdp2'])< 4 || strlen($_POST['mdp2'])>15)))
	{
		$msg .= '<div class="msg_erreur" ><h4>Le mot de passe doit avoir entre 4 et 15 caractères inclus</h4></div>';
	}
	if(strlen($_POST['nom'])< 2 || strlen($_POST['nom'])>20) 
	{
		$msg .= '<div class="msg_erreur" ><h4>Le nom doit avoir entre 2 et 20 caractères inclus</h4></div>';
	}
	if(strlen($_POST['prenom'])< 2 || strlen($_POST['prenom'])>20) 
	{
		$msg .= '<div class="msg_erreur"><h4>Le prénom doit avoir entre 2 et 20 caractères inclus</h4></div>';
	}
	if(strlen($_POST['email']) < 8)
	{
		$msg .= '<div class="msg_erreur"><h4>L\'email renseigné est trop court !</h4></div>';
	}
	if(strlen($_POST['email']) > 30) 
	{
		$msg .= '<div class="msg_erreur"><h4>L\'email renseigné trop long !</h4></div>';
	}
	if(strlen($_POST['ville']) > 20)
	{
		$msg .= '<div class="msg_erreur"><h4>Le nom de la ville est trop long !</h4></div>';
	}
	if(strlen($_POST['adresse']) > 50)
	{
		$msg .= '<div class="msg_erreur"><h4>Cette adresse est trop longue !</h4></div>';
	}
	if(strlen($_POST['cp']) < 5 || strlen($_POST['cp']) > 5 )
	{
		$msg .= '<div class="msg_erreur"><h4>Le code postal doit contenir 5 caractères</h4></div>';
	}
	// HTMLENTITIES 	
	foreach($_POST AS $indice => $valeur)
	{
		$_POST[$indice] = htmlentities($valeur, ENT_QUOTES);
	} 
	
	// VERIF DISPO PSEUDO
	$new_pseudo = $_POST['pseudo'];
	$id_membre = $_POST['id_membre'];
	$resultat = executeRequete("SELECT pseudo, id_membre AS id, mdp FROM membre WHERE pseudo = '$new_pseudo'");
	$pseudo_dispo = $resultat -> fetch_assoc();
	if($pseudo_dispo !== NULL) 
	{
		//$msg .= '<div class="msg_erreur"><h4>'.debug($pseudo).'</h4></div>';

		if($pseudo_dispo['id'] != $_POST['id_membre'])
		{	
			$msg .= '<div class="msg_erreur"><h4>Ce pseudo est déjà pris !</h4></div>';
		}

	}
// VERIF CONCORDANCE mdp
	if(!empty($_POST['mdp']) && !empty($_POST['old_mdp']))
	{
		$old_mdp = sha1($_POST['old_mdp']); 
		if($old_mdp != $pseudo_dispo['mdp'] )
		{
			$msg .= '<div class="msg_erreur">Veuillez entrer votre mot de passe actuel</div>';
		}
		if($_POST['mdp2'] != $_POST['mdp'] ){
			
			$msg .= '<div class="msg_erreur">Veuillez confirmer votre mot de passe</div>';
		}
	}


		//FIN SECURITE
	// MODIFICATION DU PROFIL	
	if(empty($msg))
	{	
		if(isset($_POST['modifier']) && ($_POST['modifier'] == 'Modifier'))
		{
			
			if(utilisateurEstConnecte())
			{
				extract($_POST); 
				//requete de modif
				if(!empty($_POST['mdp']) && !empty($_POST['old_mdp']))
				{
					$mdp= sha1($_POST['mdp']);
					$modif=executeRequete("UPDATE membre  SET  pseudo='$pseudo', mdp='$mdp', nom='$nom', prenom='$prenom', email='$email', sexe='$sexe', ville='$ville', cp='$cp', adresse='$adresse' WHERE id_membre='$_POST[id_membre]' ");
				}
				else
				{
					$modif=executeRequete("UPDATE membre  SET  pseudo='$pseudo', nom='$nom', prenom='$prenom', email='$email', sexe='$sexe', ville='$ville', cp='$cp', adresse='$adresse' WHERE id_membre='$_POST[id_membre]' ");
				}
				
				$msg .='<div class="msg_success">Modification réussie </div>';
				
				// on redefini la session avec les nouvelles infos
				 $_SESSION['utilisateur'] = $_POST;
				 $id_membre = $_SESSION['utilisateur']['id_membre'];
				 $statut = executeRequete("SELECT statut FROM membre WHERE id_membre = '$id_membre'");
				$statut_membre = $statut -> fetch_assoc();
				$_SESSION['utilisateur']['statut'] =  $statut_membre['statut'];

				header("location:profil.php?modif=ok"); //redirige sur la page profil.
				exit(); // SECURITE qui permet d'arreter l'execution du code de cette page apres la redirection
				
			}		
		}
	}	
}
require_once("inc/header.inc.php");


$membre_actuel = $_SESSION['utilisateur'];
$mdp_membre = executeRequete("SELECT mdp FROM membre WHERE id_membre = '$membre_actuel[id_membre]' ");
$mdp_membre = $mdp_membre -> fetch_assoc();
$mdp = sha1($mdp_membre['mdp']);
?>


		
			<div class="box_info">
			<h3>Modifier votre profil</h3>
				<?php echo $msg; ?>
			
			<form class="form" method="post" action="" enctype="multipart/form-data"> <!--enctype pour ajout eventuel d'un champs photo -->
			
				<a class="button" href="profil.php">Retour au profil</a><br />
				<fieldset>
					<legend> Identifiants </legend>
					<input type="hidden" name="id_membre" id="id_membre" value="<?php if(isset($membre_actuel['id_membre'])){ echo $membre_actuel['id_membre']; }?>" /><!-- On met un input caché pour pouvoir identifier le membre lors de la modification (REPLACE se base sur l'id uniquement(PRIMARY KEY)) /!\SECURITE : On est ici dans un back-office, on peut donc se permettre une certaine confiance en l'utilisateur, mais les champs cachés ne sont pas sécurisés pour l'acces public il faut faire des controles securités sur les url -->
					
					<label for="pseudo">Pseudo</label>
					<input type="text" id="pseudo" name="pseudo"  maxlength="15" value="<?php if(isset($membre_actuel['pseudo'])) {echo $membre_actuel['pseudo'];} elseif(isset($_POST['pseudo'])){ echo $_POST['pseudo'];}?>" placeholder="JohnDoe"  required/><br /><br />
				
					<label for="old_mdp">Mot de passe actuel</label><br />
					<input type="password" id="old_mdp" name="old_mdp"  maxlength="15" value="<?php if(isset($_POST['mdp'])) {echo $_POST['mdp'];}?>"  placeholder="Password" /><br /><br />
					
					<label for="mdp">Nouveau mot de passe</label><br />
					<input type="password" id="mdp" name="mdp"  maxlength="15" value="<?php if(isset($_POST['mdp'])) {echo $_POST['mdp'];}?>"  placeholder="Password"  /><br /><br />				
						
					<label for="mdp2">Confirmer le mot de passe</label><br />
					<input type="password" id="mdp2" maxlength="15" name="mdp2" value=""  /><br /><br />	
					</fieldset>
					<fieldset>
					<legend> Informations </legend>
					<label for="nom">Nom</label>
					<input type="text" id="nom" name="nom" maxlength="15" value="<?php if(isset($membre_actuel['nom'])) {echo $membre_actuel['nom'];} elseif(isset($_POST['nom'])){ echo $_POST['nom'];}?>" placeholder="Durand" required /><br />
					
					<label for="prenom">Prénom</label>
					<input type="text" id="prenom" name="prenom" maxlength="15" value="<?php if(isset($membre_actuel['prenom'])) {echo $membre_actuel['prenom'];} elseif(isset($_POST['prenom'])){ echo $_POST['prenom'];}?>" placeholder="Jean"  required /><br />
					
					
					<label for="email">Email</label>
					<input type="text" id="email" name="email" maxlength="30" value="<?php if(isset($membre_actuel['email'])) {echo $membre_actuel['email'];} elseif(isset($_POST['email'])){ echo $_POST['email'];}?>" placeholder="monmail@mail.com"  required /><br />
					
					<label for="sexe">Sexe </label><br /> <!--On met un cas par défaut + une valeur checkée si le formulaire a dejà été rempli-->
					<select id="sexe" name="sexe" required >
					<option value="m" <?php if(isset($membre_actuel['sexe']) && $membre_actuel['sexe'] == "m") { echo 'selected';} ?>> Homme</option>
					 <option value="f" <?php if(isset($membre_actuel['sexe']) && $membre_actuel['sexe'] == "f") { echo 'selected';} ?> >Femme</option></select><br /><br />
					</select><br />
					</fieldset><br />
					<fieldset>
					<legend> Adresse </legend>
					
					<label for="ville">Ville</label>
					<input type="text" id="ville" name="ville" maxlength="20" value="<?php if(isset($membre_actuel['ville'])) {echo $membre_actuel['ville'];} elseif(isset($_POST['ville'])){ echo $_POST['ville'];}?>" placeholder="Maville" required /><br />
				
					<label for="cp">Code Postal</label>
					<input type="text" id="cp" name="cp" maxlength="15" value="<?php if(isset($membre_actuel['cp'])) {echo $membre_actuel['cp'];} elseif(isset($_POST['cp'])){ echo $_POST['cp'];}?>" placeholder="99999" required/><br />
					
					<label for="adresse">Adresse</label>
					<textarea type="text" id="adresse" name="adresse" maxlength="50" placeholder="86 rue de la Ville" required><?php if(isset($membre_actuel['adresse'])) {echo $membre_actuel['adresse'];} elseif(isset($_POST['adresse'])){ echo $_POST['adresse'];}?></textarea><br /><br />
					
					<input type="submit" id="modifier" name="modifier" value="Modifier" class="button" /><br />
					<a class="button text-center" href="<?php RACINE_SITE ?>profil.php">Retour au profil</a><br />
				</fieldset>

			</form>
		
			<br />
			
		</div>
		<br /><br />

 <?php

//debug($_POST);
//debug($_SESSION);
	require_once("inc/footer.inc.php");
  
  ?>