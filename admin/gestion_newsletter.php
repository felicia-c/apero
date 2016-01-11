<?php
require_once("../inc/init.inc.php");
//APERO - Felicia Cuneo - 12/2015
$titre_page = "Envoi de Newsletter";

if(!utilisateurEstConnecteEtEstAdmin() && !utilisateurEstConnecteEtEstGerantEtAdmin())
{
	header("location:../connexion.php");
	exit();
}
$resultat = executeRequete("SELECT * FROM newsletter");
$mailSent = "";
if(!empty($_POST))
{
	$liste = '';

	// Requete pour récuerer les adresses mail des membres et introduire dans le forumlaire. 
	$result = executeRequete("SELECT email FROM membre,newsletter WHERE newsletter.id_membre=membre.id_membre");
	while ($abonne = $result->fetch_assoc())
	{
	    $liste .= $abonne['email'];
	    $liste .= ','; //On sépare les adresses par une virgule.

	}

	$destinataire = $liste; // mail des membres inscrits a la Newsletter.
    define( 'MAIL_FROM', 'contact_apero@yopmail.com' ); // valeur par défaut  
    define( 'MAIL_OBJECT', '' ); // valeur par défaut  
    define( 'MAIL_MESSAGE', '' ); // valeur par défaut  
	
    $mailSent = false; // drapeau qui aiguille l'affichage du formulaire OU du récapitulatif  
    $msg = ""; // tableau des erreurs de saisie  
      
    if(isset($_POST['envoyer'])) // le formulaire a été soumis avec le bouton Envoyer
    {  
		$from = filter_input( INPUT_POST, 'from', FILTER_VALIDATE_EMAIL );  
        if( $from === NULL ) // || $from === MAIL_FROM  si le courriel fourni est vide OU égale à la valeur par défaut  
        {  
            $msg .= '<div class="msg_erreur"><h4> Vous devez renseigner votre adresse e-mail</h4></div>';  
        }  
        elseif( $from === false ) // si le courriel fourni n'est pas valide  
        {  
            $msg .= '<div class="msg_erreur"><h4>L\'adresse e-mail n\'est pas valide </h4></div>';
            $from = filter_input( INPUT_POST, 'from', FILTER_SANITIZE_EMAIL );  
        }

        $object = filter_input( INPUT_POST, 'object', FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW );  
        if( $object === NULL OR $object === false OR empty( $object ) OR $object === MAIL_OBJECT ) // si l'objet fourni est vide, invalide ou égale à la valeur par défaut  
        {  
            $msg .= '<div class="msg_erreur"><h4>Vous devez renseigner l\'objet. </h4></div>';  
        }  
        $message = filter_input( INPUT_POST, 'message', FILTER_UNSAFE_RAW );  
        if( $message === NULL OR $message === false OR empty( $message ) OR $message === MAIL_MESSAGE ) // si le message fourni est vide ou égale à la valeur par défaut  
        {  
            $msg .= '<div class="msg_erreur"><h4>Vous devez écrire un message. </h4></div>';  
        }  

        if(empty($msg )) // si il n'y a pas d'erreurs  
        {  
            if(mail($destinataire, $object, $message, "From: $from\nReply-to: $from\n" ) ) // tentative d'envoi du message  
            {  
                $mailSent = true;  
            }  
            else // échec de l'envoi  
            {  
                $msg .= '<p class="text_error">Votre message n\'a pas été envoyé. </p>';  
            }  
        }  
    }  
    else // le formulaire est affiché pour la première fois, avec les valeurs par défaut  
    {  
        $from = MAIL_FROM;  
        $object = MAIL_OBJECT;  
        $message = MAIL_MESSAGE;  
    }
}
require_once("../inc/header.inc.php");

?>	
	<div class="box_info">
		
		<h1>Quoi de neuf chez Apéro ?</h1>
	
			<p>Bienvenue sur la page d'envoi des Newsletters. Vous pouvez envoyer une newsletter à tous les abonnés grâce au formulaire ci-dessous.</p>
		
			<br />	
		
	<?php
	if( $mailSent === true ) // si le message a bien été envoyé, on affiche le récapitulatif  
	{  
	?>
			<div class="msg_success">

				<h4>Votre message a bien été envoyé</h4>
			</div>
		</div>
		<br />
		<p>Objet :<br /><?php echo( $object ); ?></p><br />
		<p>Message :<br /><?php echo( nl2br( $message ) ); ?></p>
		<p><a href="envoi_newsletter.php" >Retour Envoi Newsletter</a></p>
<?php
	}  
		else
		{  

			echo $msg;
			/*if(count($msg) > 0 )  
			{  
				echo '<div class="msg_erreur">
						<ul>';  
				foreach( $msg as $error )  
				{  
					echo '<li>'.$error.'</li>';  
				}  
				echo '</ul>
				
					</div>';  
			}  
			elseif(isset($_POST['envoyer']))
			{  
				echo '<div class="msg_erreur"><em>Tous les champs sont obligatoires.</em></div><br />';  
			}*/ 
		}
	?>
			<div>
				<p>Nombre d'abonnés à la Newsletter : <strong><?php  echo ''.$resultat->num_rows.''; ?></strong></p>
			</div>
		</div>
		<div class="box_info">
			<form class="form" method="post" action="<?php echo( $_SERVER['REQUEST_URI'] ); ?>"> 
						
					<label for="from" >Expéditeur</label> 
						<input type="email" name="from" id="from" value="<?php if(!empty($_POST)){echo( $from );} ?>" class="form-control-contact"/><br />
					
					<label for="object">Objet</label>
						<input type="text" name="object" id="object" value="<?php if(!empty($_POST)){echo( $object );} ?>" class="form-control-contact"/><br /> 
						
					<label for="message" >Message</label> 
						<textarea name="message" id="message" style=" height: 150px;"><?php if(!empty($_POST)){ echo( $message ); } ?></textarea><br /><br />
						
					<!--Validation formulaire Contact-->					
					<input class="button" type="submit" name="envoyer" id="envoyer" value="Envoyer la Newsletter" /> 
					
			</form> 
		</div>
	
<?php 		
echo '</div>';	
require_once("../inc/footer.inc.php");
?>