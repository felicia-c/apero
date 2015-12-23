<?php
$titre_page = "Profil";
require_once("inc/init.inc.php");
//APERO - Felicia Cuneo - 12/2015//

if(!utilisateurEstConnecte()) //Si l'utilisateur n'est PAS connecté (SECURITE)
{
	header("location:connexion.php");
	exit();
}

require_once("inc/header.inc.php");
//echo debug($_SESSION);
$membre_actuel = $_SESSION['utilisateur'];


echo '<div class="box_info" >';
			echo $msg;
			//echo debug($_SESSION);
				if(isset($_SESSION['utilisateur']))
				{
					echo '<h2>Bonjour <strong>'. ucfirst($membre_actuel['prenom']).' !</strong></h2>';
				}
				else{
					header("location:".RACINE_SITE."connexion.php");
				}
				

// INFOS UTILISATEUR

			if(utilisateurEstConnecteEtEstAdmin())
				{
					echo '<h3>Compte administrateur</h3>';
				}
				else
				{
					echo '<h3>Bienvenue sur votre profil</h3>' ;
				} 
				echo '<div class="float photo_profil">
						<img src="images/userpic_default.png" class="thumbnail float" alt="photo par défaut" >
					</div>
				<div class="infos_profil">';
				if (isset($membre_actuel['sexe']) && $membre_actuel['sexe'] == 'f')
				{
					echo '<p>Mme ';
				}
				else
				{
					echo '<p>M. ';
				}
				echo ucfirst($membre_actuel['prenom']) .' '. ucfirst($membre_actuel['nom']) .'</p>';
// adresse de livraison
			echo '<strong>'. ucfirst($membre_actuel['pseudo']) .'</strong></p>
			
				<p><strong>'. $membre_actuel['email'] .	'</strong></p><br />
				</div>
				<hr />
				
				<h4>Votre adresse de livraison</h4>
			
				<p><strong>'. ucfirst($membre_actuel['prenom']) .' '. ucfirst($membre_actuel['nom']) .'</strong><br />'.$membre_actuel['adresse'] .'<br />'.$membre_actuel['cp'] .' '. ucfirst($membre_actuel['ville']) .'</p>';
	//LIEN MODIFIER		
		
		
		if(isset($membre_actuel['id_membre']))
		{
			$id_membre_actuel = $_SESSION['utilisateur']['id_membre'];
			echo '<hr />
			<br />
			<a href="'.RACINE_SITE.'modif_profil.php?id_membre='.$id_membre_actuel.'&action=Modifier" class="button" >Modifier</a>';		
		}

		echo '</div>
			<br />
<!-- DERNIERES COMMANDES -->
		 <div class="box_info">
			<h4>Vos dernières commandes</h4>';
			
//selection des commandes de l'utilisateur
		$id_utilisateur = $_SESSION['utilisateur']['id_membre'];
		$req = "SELECT * FROM commande WHERE id_membre = '$id_utilisateur' ORDER BY date DESC LIMIT 5";
		$resultat = executeRequete($req);
		echo '<table class="tableau_panier">
				<tr>
					<th>Numero de Suivi</th>
					<th>Date de Commande</th>
					<th>Montant TTC</th>
				</tr>';
		$nb_commandes = $resultat -> num_rows;
		if($nb_commandes < 1)
		{
			echo '<tr>
					<td colspan="4">Vous n\'avez pas encore passé de commande</td>	
				</tr>';
		}
		while($ma_commande = $resultat -> fetch_assoc() )
		{
			echo '<tr>
				<td> '.$ma_commande['id_commande']. ' </td>';
					
					$date_avis = date_create_from_format('Y-m-d H:i:s', $ma_commande['date']);
			echo '<td>'. date_format($date_avis, 'd/m/Y H:i').' </td>
					<td> '.$ma_commande['montant']. ' €</td>
				</tr>';
		}
		echo '</table>
		<br />
		<br />
		<br />

	 </div>';
	require_once("inc/footer.inc.php");
 
  ?>
 
