<?php

require_once("inc/init.inc.php");
//APERO- Felicia Cuneo - 12/2015
$titre_page = "Contact";
$destinataire = 'apero_contact@yopmail.com'; // mail des membres inscrits a la Newsletter.
    define( 'MAIL_FROM', '' ); // valeur par défaut  
    define( 'MAIL_OBJECT', '' ); // valeur par défaut  
    define( 'MAIL_MESSAGE', '' ); // valeur par défaut  
		
	$object = MAIL_OBJECT;  
    $message = MAIL_MESSAGE;  

    $mailSent = false; // affichage du formulaire OU du récapitulatif  
    $msg = ""; // tableau des erreurs de saisie  
      
    if(isset($_POST['envoyer'])) // le formulaire a été soumis avec le bouton Envoyer
    {   
    	$message = filter_input( INPUT_POST, 'message', FILTER_UNSAFE_RAW ); 
		$object = filter_input( INPUT_POST, 'object', FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_LOW );  
		if(utilisateurEstConnecte())// Si l'utilisateur est connecté, il n'a pas a rentrer son mail, on le récupère en session 
  		{  
      		$from = $_SESSION['utilisateur']['email'];    
    	}
    	else
    	{
    		$from = filter_input( INPUT_POST, 'from', FILTER_VALIDATE_EMAIL );  
        	
    	}
    	if( $from === NULL ) // || $from === MAIL_FROM  si le courriel fourni est vide OU égale à la valeur par défaut  
   		{  
        	$msg .= '<div class="msg_erreur"><h4>Vous devez renseigner votre adresse e-mail</h4></div>';  
   	 	}  
  		elseif( $from === false ) // si le courriel fourni n'est pas valide  
    	{  
        	$msg .= '<div class="msg_erreur"><h4>L\'adresse e-mail n\'est pas valide </h4></div>';
        	//$from = filter_input( INPUT_POST, 'from', FILTER_SANITIZE_EMAIL );  
    	}	

       
        if( $object === NULL OR $object === false OR empty( $object ) OR $object === MAIL_OBJECT ) // si l'objet fourni est vide, invalide ou égale à la valeur par défaut  
        {  
            $msg .= '<div class="msg_erreur"><h4>Vous devez renseigner l\'objet. </h4></div>';  
        }  
         
        if( $message === NULL OR $message === false OR empty( $message ) OR $message === MAIL_MESSAGE ) // si le message fourni est vide ou égale à la valeur par défaut  
        {  
            $msg .= '<div class="msg_erreur"><h4>Vous devez écrire un message. </h4></div>';  
        }  

        if(empty($msg )) // si il n'y a pas d'erreurs  
        {  
            if( mail($destinataire, $object, $message, "From: $from\nReply-to: $from\n" ) ) // tentative d'envoi du message  
            {  
                $mailSent = true;  
                $msg .= '<div class="msg_success"><h4>Votre message a bien été envoyé :</h4><br /> <p>adresse de retour : $from <br /> Message envoyé : $message </p></div>';
            }  
            else // échec de l'envoi  
            {  
                $msg .= '<div class="msg_erreur"><h4>Votre message n\'a pas été envoyé. </h4></div>';  
            }  
        }  
    } 
require_once("inc/header.inc.php");

?>
	<div class="box_info">
			
		<h1>Nous contacter</h1>
		
		<p>Pour un renseignement, pour refaire le monde ou juste pour nous inviter à boire l'apéro, vous pouvez nous contater en remplissant le formulaire ci-dessous.</p>
		<p> Apéro est également joignable par mail à l'adresse suivante: contact@tshirt-apero.com</p><br /><br />
	
	<?php echo $msg; ?>
			<div class= "form" >
				<form method="post" action="">
					<?php  
				if(!utilisateurEstConnecte())
				{
					?>
				<!-- bloc ID-->			
					<label>Civilité</label>
					<select name="civilite">
						<option value="monsieur">Monsieur</option>
						<option value="madame">Madame</option>
						<option value="mademoiselle">Mademoiselle</option>
					</select>
					
					<label>Nom*</label>
					<input type="text"  name="nom" id="nom" required/>
				
					<label>Prénom*</label>
					<input type="text"  name="prenom" id="prenom" required />
					
					<label>Société</label>
					<input type="text"  name="societe" id="societe" />
					
					<label for="from">Email*</label>
					<input type="email" id="from" required/>		
		<?php  
				}
			
		?>
					<label>Objet de votre message*</label> 
					<input type="text" id="object" name="object" required/>
					
					<br />
					<label>Message*</label>
					<textarea name="message" id="message" style="height: 200px;" required></textarea><br />
				
					<input type="submit" class="button" value="Envoyer" id="envoyer"/>
				<br />
				<p class="asterisque">*Champs obligatoire</p>
				
			</form>
		</div>
    </div>

	<br /><br />

<?php
require_once("inc/footer.inc.php");
  
?>