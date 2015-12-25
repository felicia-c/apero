<?php
$titre_page = "Newsletter";
 require_once("inc/init.inc.php");
 
//APERO - Felicia Cuneo - 12/2015
if(utilisateurEstConnecte())
{
	if(isset($_POST['newsletter']) && ($_POST['newsletter']=='ok'))
	{
		$id_membre = $_SESSION['utilisateur']['id_membre'];
		$inscrit = executeRequete("SELECT COUNT(*) FROM newsletter WHERE id_membre = '$id_membre'");;
		if($inscrit)
		{
			$msg .='<div class="msg_erreur"><h4>Vous êtes déjà inscrit à notre newsletter</h4></div>';
		}
		else
		{
			executeRequete("INSERT INTO newsletter VALUES ('', '$id_membre')");
			$msg .='<div class="msg_success"><h4>Inscription réussie !</h4></div>';	
		}
	}
}
require_once("inc/header.inc.php");

echo '<div class="box_info">
		<h1>Quoi de neuf chez apéro ?</h1>';
 echo $msg; 
			
				
if(!utilisateurEstConnecte()) //Si l'utilisateur n'est PAS connecté (SECURITE)
{
	echo '<p>Connectez-vous pour vous inscrire à la newsletter d\' Apéro</p><br />
		<p><a href="connexion.php" class="button btn-resadetails" title="Se connecter">Connectez-vous</a></p><br />
		<p>Pas encore de compte ? Créez-en un en 2 minutes !</p><br />
		<p><a href="inscription.php" class="button btn-resadetails" title="Créer un compte">Créer un compte</a></p>';
}
elseif(!$_POST)
{
?>
	<form class="form" method="post" action="">

		<label for="newsletter">Recevez des informations sur <span class="orange">nos dernières offres, les apéros du moment et tous nos bon plans </span>: </label><br />
		<br />
		<fieldset>
		<input type="checkbox" value="ok" id="newsletter" class="float" style="margin: 0;" name="newsletter" value="ok" <?php if(isset($_POST['newsletter']) && $_POST['newsletter'] == "ok") { echo 'checked';} elseif(!isset($_POST['newsletter'])){echo 'checked';} ?> required / ><label ><i>Oui, je souhaite recevoir des promotions et informations de la part d'Apéro</i></label>
		<br /><br />

		<input type="submit" class="button" id="inscription" name="inscription" value="inscription" />
		</fieldset>
	</form>

<?php
}	
?>
	<br /><br />
	</div>

<?php
require_once("inc/footer.inc.php");
?>