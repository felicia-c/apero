<?php
require_once('inc/init.inc.php');
$titre_page = "Bars Apéro";
//APERO - Felicia Cuneo - 12/2015
if(isset($_POST['evaluer']))
{
	if(isset($_GET['id_bar']))
	{
		foreach($_POST AS $indice => $valeur)
		{
			$_POST[$indice] = htmlentities($valeur, ENT_QUOTES);
		} // SECURITE
		
		$verif_caractere = preg_match('#^[0-9]+$#', $_POST['note']); 
		if(!$verif_caractere && !empty($_POST['note']))      
		{
			$msg .= '<div class="msg_erreur" ><h4>Caractères acceptés: 0 à 9</h4></div>';  
		}
		
		if($_POST['note'] > 10)
		{
			$msg .= '<div class="msg_erreur"><h4> La note attribuée ne peut pas dépasser 10 ! </h4></div>';
		}
		if(empty($msg))
		{
			$membre_actuel = $_SESSION['utilisateur'];
			$avis_membre = executeRequete("SELECT COUNT(*) as total FROM avis WHERE id_membre = '$membre_actuel[id_membre]' && id_bar= '$_GET[id_bar]' ");
			$avis_membre = $avis_membre -> fetch_assoc();
			if($avis_membre['total'] > 0)
			{
				$msg .= '<div class="msg_erreur" ><h4>Vous avez déjà donné votre avis sur ce bar</h4></div>';  
			}
			else
			{
				executeRequete("INSERT INTO avis  (id_membre, id_bar, commentaire, note, date) VALUES ('$membre_actuel[id_membre]', '$_POST[bar]', '$_POST[commentaire]', '$_POST[note]', NOW()) ");
				$msg .= '<div class="msg_success" ><h4>Merci pour votre évaluation!</h4></div>';  
				unset($_POST);
				echo '<meta http-equiv="refresh" content="2;URL='.RACINE_SITE.'fiche_bar.php?id_bar='.$_GET['id_bar'].'">';	
			}
		}
	}
	elseif(!isset($_GET['id_bar']))
	{
		$msg .= '<div class="msg_erreur" ><h4>Une erreur est survenue</h4></div>';  
	}
}
require_once('inc/header.inc.php');

echo '<div class="box_info">';

echo $msg;
if(isset($_GET['id_bar']))
{

	$id_bar = filter_input( INPUT_GET, 'id_bar', FILTER_SANITIZE_NUMBER_INT ); 
	$req="SELECT * FROM bar WHERE id_bar = '$id_bar'";

	//afficheBar($req);
	
	if(afficheBar($req))
	{
		echo '<hr />
			<h2 class="tomato msg_apero">Apéros proposés par ce bar</h2>';

		$req_promo= "SELECT * FROM promo_bar WHERE id_bar='$id_bar' AND date_fin > NOW() ORDER BY date_debut";
		affichePromoBar($req_promo);
	}
}

echo '<br /><br />';
echo '<hr /><div id="boxnewsletter">';
	formulaireNewsletter();
	inscriptionNewsletter($msg);
echo '</div>
<br /><br />';

echo '</div>';
require_once('inc/footer.inc.php');