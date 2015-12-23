<?php
require_once("inc/init.inc.php");
$titre_page = "Se connecter";

//APERO - Felicia Cuneo - 12/2015

if(isset($_GET['action']) && $_GET['action'] == 'deconnexion') 
{
	session_destroy();
	// OU unset($_SESSION['utilisateur']);
}
if(utilisateurEstConnecte())
{
	header("location:profil.php");
}
if(isset($_GET['msg']) && $_GET['msg'] == 'inscription')
{
	$msg .='<div class="msg_success"><h4>Inscription réussie ! Vous pouvez dès à présent accèder à votre compte en vous connectant avec vos identifiants</h4></div>';
}
if(!empty($_POST)) 
{
	 if(!isset($_POST['pseudo']) || !isset($_POST['mdp']))
	 {
		 $msg .= '<div class="msg_erreur"><h4>Veuillez entrer vos identifiants </h4></div>';
	 }
	 else
	 {
		$pseudo = htmlentities($_POST['pseudo'], ENT_QUOTES);
		$mdp = htmlentities($_POST['mdp'], ENT_QUOTES);
		
		
		$resultat = executeRequete("SELECT * FROM membre WHERE pseudo='$pseudo' AND mdp='$mdp'"); 

		$selection_membre = $resultat;
		if($selection_membre -> num_rows == 0) //si la requete ne retourne pas d'enregistrement, alors le compte n'existe pas, on affiche un message
		{
			 $msg .='<div class="msg_erreur"><h4>Pseudo ou mot de passe incorrect !</h4></div>';
		}
		
		else
		{

			$membre = $selection_membre->fetch_assoc(); 
			
			foreach($membre AS $indice => $valeur)
			{
				if($indice != 'mdp')
				{
					$_SESSION['utilisateur'][$indice] = $valeur; 
				}
				else{
					
					$_SESSION['utilisateur']['mdp'] = md5($_POST['mdp']);
				}
			}
			if(isset($_POST['remember']) == 'remember')
			{
				$pseudo_membre = $_SESSION['utilisateur']['pseudo'];
				setCookie('pseudo', $pseudo_membre, time() + 3600 * 24 * 360, null, null, false, true); //false = on met true si on a une connexion https, true = cookie non editable en JS	
			}

			header("location:profil.php"); 
			exit(); 
		}
	}	
}

require_once("inc/header.inc.php");


?>	
	<div class="box_info">
	<?php	echo $msg;  ?>
		<form method="post" action=""  class="form" >
			<fieldset>
				<legend><h3> Connectez-vous </h3></legend>
				<br />
				<label for="pseudo">Pseudo</label>
				<input type="text" id="pseudo" name="pseudo" value="<?php if(isset($_COOKIE['pseudo'])){ echo $_COOKIE['pseudo']; } ?>" /><br />
				<label for="mdp">Mot de passe</label>
				<input type="password" id="mdp" name="mdp"  /><br >	
				<label id="label_remember" for="remember">Se souvenir de moi</label>
				<input type="checkbox" name="remember" id="remember"/><br /><br />
				<input type="submit" class="button" id="connexion" name="connexion" value="connexion" /><br />
				<p><a class="text-center" href="<?php echo RACINE_SITE;?>mdp_perdu.php" title="Générer un nouveau mot de passe">Mot de passe perdu ?</a><br />
				<a class="text-center" href="<?php echo RACINE_SITE; ?>inscription.php" title="Inscription">Créer un compte</a></p>		
			</fieldset>		
					
		</form>									
		<br />
			
		
	</div>
<br />
<br />
						
			
 <?php
 