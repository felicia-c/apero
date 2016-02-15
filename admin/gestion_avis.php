<?php
require_once("../inc/init.inc.php");
$titre_page = "Gestion des avis";

////////APERO - Felicia Cuneo - 01/2016


//Redirection si l'utilisateur n'est pas admin
if(!utilisateurEstConnecteEtEstAdmin()&& !utilisateurEstConnecteEtEstGerantEtAdmin()){
	header("location:../connexion.php");
}	


	// SUPPRESSION DES AVIS
 
 if(isset($_GET['action']) && $_GET['action'] == 'suppression')
{
	$id = filter_input( INPUT_GET, 'id_avis', FILTER_SANITIZE_NUMBER_INT );
	$resultat = executeRequete("SELECT * FROM avis WHERE id_avis = '$id'"); //on recupere les infos dans la table avis

	executeRequete("DELETE FROM avis WHERE id_avis='$id'");
	$msg .='<div class="msg_success"><h4>Avis N°'. $id .' supprimé!</h4></div>';   //suppression de l'avis dans la table + affichage d'un msg de confirmation
}

$req = "";	
require_once("../inc/header.inc.php");

?>

<?php 
$resultat = executeRequete("SELECT SUM(montant) AS total,
										COUNT(id_commande) AS nbre_commandes,
										ROUND(AVG(montant),2) AS panier_moyen,
										MAX(date) AS der_commande 
									FROM commande");
$commandes = $resultat -> fetch_assoc();
//<div class="box_info">
echo '<h3>CA Total : '. $commandes['total'] .'€  |  Nombre de commandes: '. $commandes['nbre_commandes'].' | Commande moyenne : '.$commandes['panier_moyen'].'€</h3><br />';
echo $msg;

echo '<br />';

echo '<h2 class="orange">Tous les avis</h2>

	<table id="details">'; 
$req .= "SELECT * FROM avis"; 
$req = paginationGestion(10, 'avis', $req);
$resultat = executeRequete($req);
$dont_show= null;
$dont_link = '';
enteteTableau($resultat, $dont_show, $dont_link);


while ($ligne = $resultat->fetch_assoc()) 
{
	echo '<tr>';
		foreach($ligne AS $indice => $valeur)
		// foreach = pour chaque element du tableau

		if($indice == 'date')
		{
			$date1 = date_create_from_format('Y-m-d H:i:s', $ligne['date']);
			echo '<td>'.date_format($date1, 'd/m/Y H:i').' </td>';
		}
		elseif($indice == 'id_bar')
		{
			echo '<td><a href="'.RACINE_SITE.'fiche_bar.php?id_bar='.$ligne['id_bar'].'">'.$ligne['id_bar'].'</a></td>';
		}
		else
		{
			echo '<td >'.ucfirst($valeur).'</td>';
		}
	

	echo '<td><a href="?affichage=affichage&action=suppression&id_avis='.$ligne['id_avis'] .'" class="btn_delete" onClick="return(confirm(\'En êtes-vous certain ?\'));"> X </a>
		</td>
	</tr>';	
	
}			
echo '</table>
	<br />';
	affichagePaginationGestion(10, 'avis', '');
	
echo '<br /><br />';

require_once("../inc/footer.inc.php");	
?>					
		



