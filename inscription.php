<?php
$titre_page = "Inscription";
require_once("inc/init.inc.php");
//APERO- Felicia Cuneo - 12/2015
if(utilisateurEstConnecte())
{
	header("location:profil.php");
	exit;
}
if($_POST){
	
//SECURITE -- VERIFICATION DES CARACTERES 	

	$verif_caractere = preg_match('#^[a-zA-Z0-9._-]+$#', $_POST['pseudo']); //retourne FALSE si mauvais caracteres dans $_POST['pseudo'], sinon TRUE
	if(!$verif_caractere && !empty($_POST['pseudo']))
	{
		$msg .= '<div class="msg_erreur" ><h4>Pseudo invalide.<br /> Caractères acceptés: _- A à Z et 0 à 9</h4></div>';  
	}
	
	$verif_caractere = preg_match('#^[a-zA-Z0-9._-]+$#', $_POST['mdp']);
	if(!$verif_caractere && !empty($_POST['mdp']))
	{
		$msg .= '<div class="msg_erreur" ><h4>Mot de passe invalide.<br /> Caractères acceptés: _- A-Z et 0-9</h4></div>';  
	}
	
	$verif_caractere = preg_match('#^[a-zA-Z0-9._-]+$#', $_POST['mdp2']);
	if(!$verif_caractere && !empty($_POST['mdp2']))
	{
		$msg .= '<div class="msg_erreur" ><h4>Mot de passe invalide.<br /> Caractères acceptés: _- A-Z et 0-9</h4></div>';  
	}
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['nom']); // RAJOUTER LES ACCENTS et espaces!!
	if(!$verif_caractere && !empty($_POST['nom']))
	{
		$msg .= '<div class="msg_erreur" ><h4>Erreur sur le nom.<br />Caractères acceptés: - àâäçéèêëï A-Z et 0-9</h4></div>';  
	}
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['prenom']); // RAJOUTER LES ACCENTS et espaces!!
	if(!$verif_caractere && !empty($_POST['prenom']))
	{
		$msg .= '<div class="msg_erreur" ><h4>Erreur sur le prénom.<br /> Caractères acceptés: - àâäçéèêëï A-Z et 0-9</h4></div>';  
	}
	
	$email = filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ); 
	if(!$email && !empty($_POST['email']))
	{
		$msg .= '<div class="msg_erreur" >Adresse e-mail invalide !</div>'; 
	} 

	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9._ -]+$#', $_POST['ville']); // RAJOUTER LES ACCENTS et espaces!!
	if(!$verif_caractere && !empty($_POST['ville']))
	{
		$msg .= '<div class="msg_erreur" ><h4>Erreur sur la ville.<br /> Caractères acceptés: àâäçéèêëï a-z A-Z 0 à 9 -_</h4></div>';  
	}
	
	$verif_caractere = preg_match('#^[àâäçéèêëïa-zA-Z0-9.,_ \'-]+$#', $_POST['adresse']); 
	if(!$verif_caractere && !empty($_POST['adresse']))
	{
		$msg .= '<div class="msg_erreur"><h4>Erreur sur l\'adresse.<br /> Caractères acceptés: .,_ \'- àâäçéèêëï a-z A-Z et 0-9</h4></div>';  
	}
	
	$verif_caractere = preg_match('#^[0-9]+$#', $_POST['cp']); 
	if(!$verif_caractere && !empty($_POST['cp']))      
	{
		$msg .= '<div class="msg_erreur" ><h4>Le code postal doit comporter des chiffres de 0 à 9</h4></div>';  
	}
	
	
	foreach($_POST AS $indice => $valeur)
	{
		$_POST[$indice] = htmlentities($valeur, ENT_QUOTES);
	} // SECURITE on retravaille les valeurs contenues dans $_POST avec htmlentities afin d'éviter les injection de code via le formulaire. 
	
	
	         // VERIF STRLEN
	
	if(strlen($_POST['pseudo'])< 4 || strlen($_POST['pseudo'])>14) 
	{
		$msg .= '<div class="msg_erreur" ><h4>Le pseudo doit avoir entre 4 et 14 caractères inclus</h4></div>';
	}
	if(strlen($_POST['mdp'])< 4 || strlen($_POST['mdp'])>14) 
	{
		$msg .= '<div class="msg_erreur"><h4>Le mot de passe doit avoir entre 4 et 14 caractères inclus</h4></div>';
	}

	if(strlen($_POST['cp']) != 5) //pb de taille  ?
	{
		$msg .= '<div class="msg_erreur"><h4>Le code postal doit contenir 5 caractères</h4></div>';
	}
	
	// VERIF CONCORDANCE mdp
	
	if($_POST['mdp2'] != $_POST['mdp'] ){
		
		$msg .= '<div class="msg_erreur"><h4>Veuillez confirmer votre mot-de-passe</h4></div>';

	}
	
	
	// INSCRIPTION
	if(empty($msg))// Si $msg est vide, alors il n'y a pas d'erreur, nous pouvons lancer l'inscription !
	{
		$membre= executeRequete("SELECT * FROM membre WHERE pseudo='$_POST[pseudo]'");
		if($membre -> num_rows > 0)  //verif dispo du pseudo
		{
			$msg .='<div class="msg_erreur"><h4>Ce pseudo est déjà utilisé !</h4></div>';
		}
		else
		{

			extract($_POST); // EXTRACT marche sur un tableau array (si indices non-numerique)
			executeRequete("INSERT INTO membre (pseudo, mdp, nom, prenom, email, sexe, ville, cp, adresse) VALUES ('$pseudo', '$mdp', '$nom', '$prenom', '$email', '$sexe', '$ville', '$cp', '$adresse')"); //requete d'inscription 
			$msg .='<div class="msg_success"><h4>Inscription réussie !</h4></div>';
			$id_membre = $mysqli-> insert_id; 
			if($_POST['bar'])
			{
				executeRequete("UPDATE membre SET statut=3 WHERE id_membre='$id_membre'");
			}
			if(isset($_POST['newsletter']) && ($_POST['newsletter']=='ok'))
			{
				executeRequete("INSERT INTO newsletter VALUES ('', '$id_membre')");
			}			
			
			header("location:connexion.php?msg=inscription");
			exit();
		}
	}
}


require_once("inc/header.inc.php");

?>

				

<br />

<br />
<div class="box_info" >
	<?php  echo $msg; ?>
	<h2>Créez votre compte</h2>
	
	<form method="post" action="" id="form_inscription" class="form" enctype="multipart/form-data">
	<fieldset>
		<label for="pseudo">Pseudo *</label><br />
		<input type="text" id="pseudo" name="pseudo" minlength="4" maxlength="14" value="<?php if(isset($_POST['pseudo'])) {echo $_POST['pseudo'];}?>" placeholder="JohnDoe"  required /><br />
		
		<label for="mdp">Mot de passe *</label><br />
		<input type="password" id="mdp" name="mdp" minlength="4" maxlength="14" value="<?php if(isset($_POST['mdp'])) {echo $_POST['mdp'];}?>"  placeholder="Password"  required /><br /><br />	<!------- Modifier Type = password ------>				
		
		<label for="mdp2">Confirmer le mot de passe *</label><br />
		<input type="password" id="mdp2" name="mdp2" required /><br />	<br />	
	</fieldset>
	<fieldset  class="block_inline" >	
		<label for="nom">Nom *</label><br />
		<input type="text" id="nom" name="nom" value="<?php if(isset($_POST['nom'])) {echo $_POST['nom'];}?>" placeholder="Durand"  required/><br /><br />
		
		<label for="prenom">Prénom *</label><br />
		<input type="text" id="prenom" name="prenom" value="<?php if(isset($_POST['prenom'])) {echo $_POST['prenom'];}?>" placeholder="Jean"  required/><br /><br />
		
		<label for="email">E-mail *</label><br />
		<input type="text" id="email" name="email" value="<?php if(isset($_POST['email'])) {echo $_POST['email'];}?>" placeholder="monmail@mail.com"  required /><br /><br /><br />
	
		<label for="sexe">Sexe *</label><br />
		<select id="sexe" name="sexe" required >
			<option value="m"<?php if(isset($_POST['sexe']) && $_POST['sexe'] == "m") { echo 'checked';} elseif(!isset($_POST['sexe'])){echo 'checked';} ?> >Homme</option>
			<option value="f" <?php if(isset($_POST['sexe']) && $_POST['sexe'] == "f") { echo 'checked';} ?>>Femme</option>
		</select><br /><br />
	</fieldset>
	<fieldset  class="block_inline" >
		<label for="ville">Ville *</label><br />
		<input type="text" id="ville" name="ville" required value="<?php if(isset($_POST['ville'])) {echo $_POST['ville'];}?>" /><br /><br />
		
		<label for="cp">Code Postal *</label><br />
		<input type="text" id="cp" name="cp" required value="<?php if(isset($_POST['cp'])) {echo $_POST['cp'];}?>" placeholder="99999"/><br /><br />
		
		<label for="adresse">Adresse *</label><br />
		<textarea  id="adresse" rows="8" name="adresse" required><?php if(isset($_POST['adresse'])) {echo $_POST['adresse'];}?></textarea><br /><br />
	</fieldset>	<br /><br />		
	<div class="box_info">
		<label for="newsletter" ><strong>S'inscrire à notre newsletter</strong></label><br />
		<input type="radio" value="ok" id="newsletter" class="float" name="newsletter" value="ok" <?php if(isset($_POST['newsletter']) && $_POST['newsletter'] == "ok") { echo 'checked';} elseif(!isset($_POST['newsletter'])){echo 'checked';} ?> required / >
		<label class="label_nl"><strong>OUI</strong>, je souhaite être tenu au courant des offres d'Apéro</label><br />
		<input type="radio" value="" id="newsletter" name="newsletter" class="float" />
		<label class="label_nl"><strong>NON</strong>, je ne souhaite pas être informé(e)</label>
	</div>
	<br /><br />
	<div class="box_info">
		<p><strong>Vous avez un bar?</strong> Vous souhaitez le faire connaître et fidéliser votre clientèle ? <br />Inscrivez votre bar !<br /> <a href="<?php echo RACINE_SITE; ?>">En savoir plus</a></p><br/>
		<input type="checkbox" name="bar" id="bar" class="float"/>
		<label class="label_nl" for="bar">Je certifie que je suis gérant d'un bar<br />(nous procèderons aux vérifications nécessaires avant de valider l'inscription d'un bar)</label>
	</div>
		<br /><br />

		<p>* champs sont obligatoires</p>
	
		<input type="submit" id="inscription" class="button" name="inscription" value="inscription" />
		</fieldset>						
	</form>
	<br />
	
</div>
<br />
<br />

<?php
	
require_once("inc/footer.inc.php");	
	
?>