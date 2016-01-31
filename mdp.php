<?php
require_once('inc/init.inc.php');
$titre_page = 'Mot de passe perdu';
//APERO - Felicia Cuneo 01/2015
if($_POST)
{
//Contrôle de l'e-mail.
	if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
	{ 
	//SECURITE
		foreach($_POST AS $indice => $valeur)
		{
			$_POST[$indice] = htmlentities($valeur, ENT_QUOTES);
		} 
	
		$email = executeRequete("SELECT * FROM membre WHERE email='$_POST[email]'");
		// si la requête retourne un enregistrement, alors l'email existe donc on envoi un nouveau MDP.
		if($email->num_rows > 0 ){
			// E-mail avec un nouveau mdp généré:
			$pass = chaine_aleatoire(8); // Récuperation d'un mdp aléatoire
			$to      = $_POST['email']; // Récuperation de l'adresse mail 
			$from    = 'From: Apéro'; // Expéditeur (Nous)
			$object  = "Votre demande de nouveau mot de passe sur Apéro"; // Sujet de Message
			$message = utf8_decode("<p font-family='verdana' >Bonjour,\n Votre mot de passe vient d'être réinitialisé.\n Voici votre nouveau mot de passe :<strong> $pass </strong>\n Pour plus de sécurité il vous est conseillé de le changer dans votre profil une fois connecté.\n Cordialement, \n L'équipe Apéro.</p>");
			
			if( filter_has_var( INPUT_POST, 'envoyer' ))
			{
			// le formulaire a été soumis avec le bouton [Envoyer]  
				 if( mail($to, $object, $message, $from )){
				// Envoi d'un message pour valider l'e-mail et redirection sur la connexion
					$msg .='<div class="msg_success"><h4>votre demande a bien été prise en compte, vous recevrez votre nouveau mot-de-passe dans quelques minutes <i>Redirection dans <span id="compteur">5</span> secondes.</i></h4></div>';
				// Enregistrement de MDP en BDD.
					$hashPass= sha1($pass);
					$changeMdp = executeRequete("UPDATE membre SET mdp='$hashPass' WHERE email='$_POST[email]'");
				}
				else
				{ 
				// Echec de l'envoi du mail. 
					$msg .='<div class="msg-erreur"><h4>Oups ! Une erreur est survenue, votre message n\'a pas été envoyé. Veuillez réessayer</h4></div>';
				} 	
			}		
		}
		else
		{
			// Adresse e-mail inconnue on bloque.
			$msg .='<div class="msg-erreur"><h4>Cette adresse e-mail est inconnue, veuillez vérifier votre adresse e-mail</h4></div>';
		}
	}
	else
	{
		// Adresse e-mail avec un format non valide on bloque.
		$msg .='<div class="bg-danger"><h4>Format non Valide.</h4></div>';
	}
}
require_once("inc/header.inc.php");
?>
<!-- JAVASCRIPT : Compteur 5s/ -->
	<script src="<?php echo RACINE_SITE; ?>js/mdp.js"></script>
	<!-- Formulaire MDP --> 

		<div class="box_info">
			<div id="texte_ml">
				<h1>Connexion</h1>
				<p><a href="" onClick="(window.history.back())" title="page précédente"> < Retour</a></p>
				<br />
				<?php	echo $msg;  ?>
				<br />
				<div class="box_info">
					<h2>Mot de passe perdu</h2>					
					<!-- <div id="boite_connexion" > -->
						
						<p>Pour recevoir <strong>votre nouveau mot de passe</strong>, veuillez renseigner votre adresse e-mail :</p>
						<form method="post" action="#" class="form">
							<label class="label" for="email">E-mail :</label>
							<input type="email" id="email" name="email" value="<?php if (isset($_POST['email'])){ echo $_POST['email'];}?>" placeholder="mail@votre-mail.fr" />
							<br />	<br />
							<input class="button" type="submit" id="envoyer" name="envoyer" value="Envoyer" />
						</form>
					<!-- </div> -->
				</div>
			</div>
		</div>
<?php
require_once("inc/footer.inc.php");
?>
