<?php

require_once("../inc/init.inc.php");
////////APERO - Felicia Cuneo - 11/2015
$titre_page = "Gestion des promo";


//Redirection si l'utilisateur n'est pas admin
if(!utilisateurEstConnecteEtEstAdmin())
{
	header("location:../connexion.php");
}


if($_POST)
{
	$code_promo = $_POST['code_promo'];
	$reduction = $_POST['reduction'];

	if(strlen($code_promo)< 4 || strlen($code_promo)>6) 
	{
		$msg .= '<div class="msg_erreur" >Le code promo doit avoir  entre 4 et 6 caractères</div>';
	}	
	

	/////MODIF PROMO
	if(empty($msg) && (isset($_GET['action']) && $_GET['action'] == 'modifier'))// Si $msg est vide, on modifie la promo en bdd
	{
			$id_promo_produit = $_GET['id_promo_produit'];
			executeRequete("UPDATE promo_produit SET code_promo = '$code_promo', reduction = '$reduction' WHERE id_promo_produit = '$id_promo_produit'");
				$msg .='<div class="msg_success">Promo modifiée !</div>';
	}
	
	//////AJOUT PROMO
	elseif(empty($msg) && (isset($_GET['action']) && $_GET['action']=='ajout'))
	{		/// VERIF SI LE CODE PROMO EXISTE DEJA
		$promo= executeRequete("SELECT * FROM promo_produit WHERE code_promo ='$code_promo' ");
		if($promo -> num_rows > 0) //si la requete retourne un enregistrement, alors le code promo est deja utilisé, on affiche un message
		{
			$msg .='<div class="msg_erreur">Ce code promo existe déjà</div>';
		}	
		
		if(empty($msg))// Si $msg est vide, alors il n'y a pas d'erreur, nous pouvons lancer l'inscription
		{	
			if(isset($_GET['action']) && $_GET['action'] == 'ajout')
			{
				extract($_POST);
			
				executeRequete("INSERT INTO promo_produit (code_promo, reduction) VALUES ('$code_promo', '$reduction')"); //requete d'inscription 
						
				$msg .='<div class="msg_success">Nouveau code promo enregistré</div>';

			}	
		}

	}
}

// SUPPRESSION DES PROMO
 
 if(isset($_GET['action']) && $_GET['action'] == 'suppression')
{
	$resultat = executeRequete("SELECT * FROM promo_produit WHERE id_promo_produit = '$_GET[id_promo_produit]'");
	
	executeRequete("DELETE FROM promo_produit WHERE id_promo='$_GET[id_promo]'");
	$msg .='<div class="msg_success">Promo N°'. $_GET['id_promo_produit'] .' supprimée</div>';   //suppression de la promo dans la table + affichage d'un msg de confirmation
	
}

$req = "";
// AFFICHAGE

require_once("../inc/header.inc.php");

echo '<div class="box_info">';

$resultat = executeRequete("SELECT SUM(montant) AS total,
									COUNT(id_commande) AS nbre_commandes,
									ROUND(AVG(montant),0) AS panier_moyen,
									MAX(date) AS der_commande 
							FROM commande");
$commandes = $resultat -> fetch_assoc();
echo '<p>CA Total : '. $commandes['total'] .'€  |  Nombre de commandes: '. $commandes['nbre_commandes'].' | Commande moyenne : '.$commandes['panier_moyen'].'€</p><br />';

$resultat = executeRequete("SELECT COUNT(id_promo_produit) AS nbre_promos FROM promo_produit");
$donnees =$resultat -> fetch_assoc();	

if(isset($_GET['affichage']) && $_GET['affichage'] == 'affichage')
{	
	
	echo '<h2><a href="?affichage=affichage" class="button active" >Toutes les promos ('. $donnees['nbre_promos'].')</a></h2>
	<a href="?action=ajout" class="button">Ajouter des promos</a><br />';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'ajout')
{
	echo '<h2><a href="?action=ajout" class="button active">Ajouter des promos</a></h2>
	<a href="?affichage=affichage" class="button" >Toutes les promos</a>';
}
else
{
	echo '<h2><a href="?affichage=affichage" class="button" >Toutes les promos</a></h2>
		<h2><a href="?action=ajout" class="button">Ajouter des promos</a></h2><br />';
}
echo $msg;
		/////////AFFICHAGE DE TOUTES LES PROMO///////////
if(isset($_GET['affichage']) && $_GET['affichage'] == 'affichage')
{
	echo '
		<br />
		<br />
		<br />
		<table>
			<tr>'; 
	
	$req .= "SELECT * FROM promo_produit"; 
	
	$req = paginationGestion(10, 'promo_produit', $req);
	$resultat = executeRequete($req);
	$nbcol = $resultat->field_count; 
	
	for($i= 0; $i < $nbcol; $i++) 
	{
			$colonne= $resultat->fetch_field(); 	
			echo '<th style="text-align: center;"><a href="?affichage=affichage&orderby='. $colonne->name ; 
			if(isset($_GET['asc']))
			{
				echo '&desc=desc';
			}
			else
			{
				echo '&asc=asc';
			}
			
			echo '"'; 
		
		if(isset($_GET['orderby']) && ($_GET['orderby'] == $colonne->name))
		{
			echo ' class="actif" ';
		}
		if($colonne->name == 'id_promo_produit')
		{
			echo '> Id </a></th>';
		}
		elseif($colonne->name == 'code_promo')
		{
			echo '> Code promo </a></th>'; 
		}
		else
		{
			echo '> '. ucfirst($colonne->name).' </a></th>';
		}
	}
	echo '</tr>';
	while ($ligne = $resultat->fetch_assoc()) 
	{
		echo '<tr>';
			foreach($ligne AS $indice => $valeur)
			{	
				if($indice == 'reduction')
				{
					echo '<td> - '.$valeur.' %</td>'; 
				}
				else
				{
					echo '<td >'.$valeur.'</td>';
				}
				
			} 

		echo '<td><a href="?affichage=affichage&action=suppression&id_promo_produit='.$ligne['id_promo_produit'] .'" onClick="return(confirm(\'En êtes-vous certain ?\'));">
		<img src="'.RACINE_SITE.'images/delete-icon.png" width="30px" alt="Supprimer" title="Supprimer" ></a>
			</td>
				<td>
		<a href="?action=modifier&id_promo_produit='.$ligne['id_promo_produit'] .'" >éditer</a>
			</td>
		</tr>';	
		
	}				
	echo '</table>
		<br />';
	affichagePaginationGestion(10, 'promo_produit', '');
}
	
if(isset($_GET['action']) && ($_GET['action']=='ajout' || $_GET['action'] == 'modifier') )
	{	
		echo'<form method="post" action="" id="form_inscription" class="form" >
				<fieldset>
				<legend>';
			if($_GET['action'] == 'modifier')
			{
				$resultat = executeRequete("SELECT * FROM promo_produit WHERE id_promo_produit = '$_GET[id_promo_produit]' "); // Recup les infos sur la promo à modifier
				$promo_actuelle = $resultat -> fetch_assoc(); 
				echo 'Modification de la promo N° '. $promo_actuelle['id_promo_produit'] ;
			}
			elseif($_GET['action'] == 'ajout')
			{
				echo 'Nouvelle Promotion';
			}

				echo '</legend>
					<label for="code_promo">Code Promo</label><br />
					<input type="text" id="code_promo" name="code_promo" required value="'; if(isset($_POST['code_promo'])) {echo $_POST['code_promo'];} elseif(isset($promo_actuelle)){ echo $promo_actuelle['code_promo']; } echo '" /><br /><br />
					
					<label for="reduction">Reduction (en %)</label><br />
					<input type="text" id="reduction" name="reduction" required value="'; if(isset($_POST['reduction'])) {echo $_POST['reduction'];} elseif(isset($promo_actuelle)){ echo $promo_actuelle['reduction'];} echo '" /><br /><br />
						
					<input type="submit" id="enregistrer" class="button" name="enregistrer" value="Enregistrer" />
				</fieldset>	
			</form>
			<br />';
?>
		</div>
		<br />
		<br />
					
<?php
	}	
require_once("../inc/footer.inc.php");	
?>					
					