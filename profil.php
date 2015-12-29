<?php
require_once("inc/init.inc.php");
$titre_page = "Profil";
//APERO - Felicia Cuneo - 12/2015//

if(!utilisateurEstConnecte()) //Si l'utilisateur n'est PAS connecté (SECURITE)
{
	header("location:connexion.php");
	exit();
}
if(isset($_GET['modif'])&& $_GET['modif'] == 'ok')
{
	$msg .= '<div class="msg_success"><p>Votre profil a été modifié</p></div>';
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
else
{
	header("location:".RACINE_SITE."connexion.php");
}
	

// INFOS UTILISATEUR

if(utilisateurEstConnecteEtEstAdmin())
{
	echo '<h3>Compte administrateur</h3>';
}
elseif(utilisateurEstConnecteEtEstGerant())
{
	echo '<h3>Compte barman</h3>';
}
else
{
	echo '<h3>Bienvenue sur votre profil</h3>' ;
} 
echo '<div class="float photo_profil">
		<img src="images/userpic_default.png" class="thumbnail float" alt="photo par défaut" >
	</div>
	<div class="infos_profil inline-block">';
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
echo '<p><strong>'. ucfirst($membre_actuel['pseudo']) .'</strong></p>

	<p><strong>'. $membre_actuel['email'] .	'</strong></p>';

//LIEN MODIFIER		
if(isset($membre_actuel['id_membre']))
{
	$id_membre_actuel = $_SESSION['utilisateur']['id_membre'];
	echo '<a class="teal" href="'.RACINE_SITE.'modif_profil.php?id_membre='.$id_membre_actuel.'&action=Modifier" class="button" >Modifier</a>';		
}

echo '</div>
<div class="infos_profil inline-block">
	<h4 class="orange">Votre adresse de livraison</h4>
<p><strong>'. ucfirst($membre_actuel['prenom']) .' '. ucfirst($membre_actuel['nom']) .'</strong><br />'.$membre_actuel['adresse'] .'<br />'.$membre_actuel['cp'] .' '. ucfirst($membre_actuel['ville']) .'</p>';

echo '</div>
	</div>';

if(utilisateurEstConnecteEtEstGerant())
{
	echo '<div class="box_info">
			<h4 class=orange>Vos bars</h4>';
	$id_utilisateur = $_SESSION['utilisateur']['id_membre'];
	$req = "SELECT * FROM bar WHERE id_membre = '$id_utilisateur' ORDER BY id_membre DESC";

	$resultat = executeRequete($req);
	$nbcol = $resultat->field_count; 
	echo '<table><tr>';

	$nb_bars = $resultat -> num_rows;

	if($nb_bars < 1)
	{
		echo '
				<td colspan="6">Vous n\'avez actuellement aucun compte Bar activé</td>	
			</tr>';
	}
	else
	{
		for($i= 0; $i < $nbcol; $i++) 
		{
			$colonne= $resultat->fetch_field(); 
			if($colonne->name == 'photo')
			{
					echo '<th class="text-center" width="150">'. ucfirst($colonne->name).'</th>'; 
			}
			elseif($colonne->name == 'email')
			{
				echo '<th class="text-center" colspan="3">E-mail</a></th>'; 
			}
			//elseif($colonne->name == 'description')
		//	{
			//	echo '<th colspan="3" class="text-center">'. ucfirst($colonne->name).'</th>'; 
		//	}
			elseif((($colonne->name != 'description') && ($colonne->name != 'siret')) && $colonne->name != 'prenom_gerant')
			{

				if($colonne->name == 'nom_gerant')
				{
					echo '<th class="text-center" colspan="2"><a href="?affichage=affichage&orderby='. $colonne->name ; 
				}	

				echo '<th class="text-center"><a href="?affichage=affichage&orderby='. $colonne->name ; 
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
					echo ' class="active" ';
				}
				elseif($colonne->name == 'id_bar') 
				{
					echo '>Id</a></th>'; 
				}
				elseif($colonne->name == 'id_membre')
				{
					echo '>Membre</a></th>';
				}
				elseif($colonne->name == 'nom_gerant')
				{
					echo '>Gérant</th>'; 
				}
				else
				{
					echo '>'. ucfirst($colonne->name).'</a></th>'; 		
				}
			}		
		}
		echo'<th></th><th></th></tr>';

		while ($ligne = $resultat->fetch_assoc()) // = tant qu'il y a une ligne de resultat, on en fait un tableau 
		{
			echo '<tr>';
			foreach($ligne AS $indice => $valeur) // foreach = pour chaque element du tableau
			{

				if($indice == 'photo')
				{
					echo '<td ><img src="'.$valeur.'" alt="'.$ligne['nom_bar'].'" title="'.$ligne['nom_bar'].'" class="thumbnail_tableau" width="80px" /></td>';
				}
				//elseif($indice == 'description')
				//{
			//		echo '<td colspan="3">' . substr($valeur, 0, 70) . '...</td>'; //Pour couper la description (affiche une description de 70 caracteres maximum)
			//	}
				elseif($indice == 'email')
				{
					echo '<td colspan="3">' . ucfirst($valeur).'</td>';	
				}
				elseif($indice == 'nom_gerant')
				{
					echo '<td colspan="2">' . ucfirst($valeur).' ';	
				}
				elseif($indice == 'prenom_gerant')
				{
					echo ucfirst($valeur) .'</td>';
				}
				elseif($indice == 'prenom_gerant')
				{
					echo ucfirst($valeur) .'</td>';
				}
				elseif(($indice != 'description') && $indice != 'siret')
				{
					echo '<td >'.$valeur.'</td>';
				}	
			}
			echo '<td><a href="?action=suppression&id_bar='.$ligne['id_bar'] .'" class="btn_delete" onClick="return(confirm(\'En êtes-vous certain ?\'));">X</a></td>';
			echo '<td><a href="?action=modification&id_produit='.$ligne['id_bar'] .'" class="btn_edit">éditer</a></td>';
			echo '</tr>';		
		}						
			
	}
	echo '</table><br />';	
}	
	
echo '<!-- DERNIERES COMMANDES -->
			 <div class="box_info">
				<h4 class=orange>Vos dernières commandes</h4>';

			
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
 
