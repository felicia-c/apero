<?php 
require_once("../inc/init.inc.php");
$titre_page = 'Gestion promos bars';

//APERO - Felicia Cuneo - 12/2015

//Redirection si l'utilisateur n'est pas admin
if(!utilisateurEstConnecteEtEstAdmin() && !utilisateurEstConnecteEtEstGerantEtAdmin()){
	header("location:../connexion.php");
}

foreach($_GET AS $indice => $valeur )
{
	$_GET[$indice] = htmlentities($valeur, ENT_QUOTES); 
}
//pagination liens
$page='';
$orderby=''; 
$asc_desc='';

if(isset($_GET['page']))
{
	$page.= '&page='.$_GET['page'];
}
if(isset($_GET['orderby']))
{
	$orderby.= '&orderby='.$_GET['orderby'];
}
if(isset($_GET['asc']))
{
	$asc_desc.= '&asc='.$_GET['asc'];
}
elseif(isset($_GET['desc']))
{
	$asc_desc.='&desc='.$_GET['desc'];
}

if(isset($_POST['ajouter']) && $_POST['ajouter'] == 'Enregistrer')
{
// FORMAT DATES pour entrée en BDD :

	$date1 = $_POST['date_debut'];
	$date2 = $_POST['date_fin'];
	$format_fr = 'd/m/Y';
	$format_bdd = 'Y-m-d';

	$date1Obj = date_create_from_format($format_fr, $date1);
	$date1_bdd = date_format($date1Obj, $format_bdd);

	$date2Obj = date_create_from_format($format_fr, $date2);
	$date2_bdd = date_format($date2Obj, $format_bdd);
	$today = date("Y-m-d H:i:s");  
	if((!$date1_bdd) || (!$date2_bdd))
	{
		$msg .= '<div class="msg_erreur" >Il y a un soucis sur le format des dates <br />Format accepté : JJ/MM/AAAA(ex: 09/01/2015)</div>';
	}
	if($date1_bdd >= $date2_bdd) // si la date d'arrivée est apres la date de depart, ou si elles sont egales
	{
		$msg .='<div class="msg_erreur">La date de début doit précéder la date de fin !</div>';
	}
	if($date1_bdd <= $today)
	{
		$msg .='<div class="msg_erreur">L\'offre ne peut commencer qu\'à partir de demain, pas avant !</div>';
	}	
	if(empty($msg))
	{
		foreach($_POST AS $indice => $valeur )
		{
			$_POST[$indice] = htmlentities($valeur, ENT_QUOTES); 
		}
			extract($_POST);
	// MODIF DU PRODUIT EN BDD
		if(isset($_GET['action']) && $_GET['action'] == 'modifier') 
		{
			executeRequete("UPDATE promo_bar SET id_bar='$id_bar', categorie_produit='$categorie', date_debut='$date1_bdd', date_fin='$date2_bdd', description='$description' WHERE id_promo_bar='$_GET[id_promo_bar]'");
			$msg .='<div class="msg_success"><h4>Produit modifié !</h4></div>';
			header('location:gestion_promos_bar.php?mod=ok&affichage=affichage&id_promo_bar='.$_GET['id_promo_bar'].''.$page.''.$orderby.''.$asc_desc.'');
		} 
	//AJOUT
		else
		{
			executeRequete("INSERT INTO promo_bar (id_bar, categorie_produit, date_debut, date_fin, description) VALUES ( '$id_bar', '$categorie', '$date1_bdd', '$date2_bdd', '$description')"); 
			header('location:gestion_promos_bar.php?add=ok&affichage=affichage&id_promo_bar='.$mysqli->insert_id.''.$page.''.$orderby.''.$asc_desc.'');
		}
	}
}	


// FIN ENREGISTREMENT


//MESSAGE DE VALIDATION 
if(isset($_GET['add']) && $_GET['add'] == 'ok')
{
	$msg .='<div class="msg_success" style="padding: 10px; text-align: center">Nouvel apéro enregistré!</div>';
}
if(isset($_GET['mod']) && $_GET['mod'] == 'ok')
{
	$msg .='<div class="msg_success" style="padding: 10px; text-align: center">Apéro modifié</div>';
}
//SUPPRESSION

 
 if(isset($_GET['action']) && $_GET['action'] == 'suppression')
{
	$resultat = executeRequete("SELECT * FROM promo_bar WHERE id_promo_bar = '$_GET[id_promo_bar]'"); //on recupere les infos dans la table commande
	
	executeRequete("DELETE FROM promo_bar WHERE id_promo_bar='$_GET[id_promo_bar]'");
	$msg .='<div class="msg_success">Apéro N°'. $_GET['id_promo_bar'] .' supprimé !</div>';   //suppression de la commande dans la table + affichage d'un msg de confirmation
}

$req = "";
require_once("../inc/header.inc.php");

//echo '<div="box_info">';
		
//STATS
$resultat = executeRequete("SELECT SUM(montant) AS total,
								COUNT(id_commande) AS nbre_commandes,
								ROUND(AVG(montant),2) AS panier_moyen,
								MAX(date) AS der_commande 
							FROM commande");
$commandes = $resultat -> fetch_assoc();
echo '<h3>CA Total : '. $commandes['total'] .'€  |  Nombre de commandes: '. $commandes['nbre_commandes'].' | Commande moyenne : '.$commandes['panier_moyen'].'€</h3><br />';
// FIN STATS

// LIENS

$resultat = executeRequete("SELECT COUNT(id_promo_bar) AS nbre_promo FROM promo_bar");
$donnees =$resultat -> fetch_assoc();	

if(isset($_GET['affichage']) && $_GET['affichage'] == 'affichage')
{	
	
	echo '<h2 class="orange">Tous les apéros ('. $donnees['nbre_promo'].')</h2>
	<a href="?action=ajout" class="button"> > Ajouter un apéro</a><br />
	<a href="'.RACINE_SITE.'admin/gestion_bar.php"> >Gestion Bars</a><br />';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'ajout')
{
	echo '<h2 class="orange">Ajouter un apéro</h2>
	<a href="?orderby=id_promo_bar&affichage=affichage&desc=desc" class="button" > > Tous les apéro</a><br />
	<a href="'.RACINE_SITE.'admin/gestion_bar.php"> >Gestion Bars</a><br />';
}
else
{
	echo '<h2><a href="?orderby=id_promo_bar&affichage=affichage&desc=desc" class="button" >Tous les apéros</a></h2>
		<h2><a href="?action=ajout" class="button">Ajouter un apéro</a></h2>
		<h2><a href="'.RACINE_SITE.'admin/gestion_bar.php"> >Gestion Bars</a></h2>';
}
echo $msg;
echo '<br />';
//AFFICHAGE

if(isset($_GET['affichage']) &&  $_GET['affichage']=='affichage')
{

	echo '<table id="details">'; 
	$table= 'promo_bar';
	$req .= "SELECT * FROM $table";
	$req = paginationGestion(10, $table, $req);
	$resultat = executeRequete($req);

	$dont_link = null; // entete du tablau sans order by
	$dont_show = ''; // colonne non affichée
	enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
			
	while ($ligne = $resultat->fetch_assoc())
	{
		echo '<tr '; 
		if((isset($_GET['id_promo_bar']) && ($_GET['id_promo_bar'] == $ligne['id_promo_bar'])) || (isset($_GET['id_bar']) && ($_GET['id_bar'] == $ligne['id_bar'])))
		{
			echo ' class="tr_active" ';
		}
		echo '>';
		foreach($ligne AS $indice => $valeur)
		{
			if($indice == 'id_bar')
			{
				echo '<td><a href="?affichage=affichage&action=detail&id_bar='.$ligne['id_bar'].$page.''.$orderby.''.$asc_desc.'#details">'.$valeur.'</a></td>';
			}
			
			elseif($indice == 'id_promo')
			{
				echo '<td><a href="?affichage=affichage&action=detail&categorie='.$ligne['categorie'].$page.''.$orderby.''.$asc_desc.'#details">'.$valeur.'</a></td>';
			}	
			elseif (($indice == 'date_debut') || ($indice == 'date_fin'))
			{
				echo '<td>';
					$date = date_create_from_format('Y-m-d', $valeur);
				echo date_format($date, 'd/m/Y') . '</td>';
			}					
			else
			{
				echo '<td >'.$valeur.'</td>';
			}
		}
		echo '<td>
				<a class="btn_delete" href="?affichage=affichage&action=suppression&id_promo_bar='.$ligne['id_promo_bar'] .$page.''.$orderby.''.$asc_desc.'" onClick="return(confirm(\'En êtes-vous certain ?\'));"> X </a>
			</td>
			<td>
				<a class="btn_edit" href="?action=modifier&id_promo_bar='.$ligne['id_promo_bar'] .'" >éditer</a>
			</td>
		</tr>';
	}						
	echo '</table>
		<br />';
		
	affichagePaginationGestion(10, $table, '');
	echo '</div><br /><br />';
}


// DETAILs DES BARS
if(isset($_GET['id_bar']))
{
	if((isset($_GET['action']) && $_GET['action'] == 'detail'))
	{
		echo '<div class="box_info no_border"><table class="large_table">';
		//On affiche les infos du bar associé a l'apero:
		$resultat = executeRequete("SELECT * FROM bar WHERE id_bar = '$_GET[id_bar]'");
		$nbcol = $resultat->field_count;
		
		$dont_link = 'nono'; // entete du tablau sans order by
		$dont_show = 'description'; // colonne non affichée
		enteteTableau($resultat, $dont_show, $dont_link); //entete tableau

		while ($ligne = $resultat->fetch_assoc())
		{
			echo '<tr ';
			if(isset($_GET['id_bar']) && ($_GET['id_bar'] == $ligne['id_bar']))
			{
				echo ' class="tr_active" ';
			}
			echo '>';
			foreach($ligne AS $indice => $valeur)
			{
				if($indice == 'photo')
				{
					echo '<td ><img src="'.RACINE_SITE.$valeur.'" alt="'.$ligne['nom_bar'].'" title="'.$ligne['nom_bar'].'" class="thumbnail_tableau" width="100px" /></td>';
				}
				//elseif($indice == 'description')
				//{
			//		echo '<td colspan="3">' . substr($valeur, 0, 70) . '...</td>'; //Pour couper la description (affiche une description de 70 caracteres maximum)
			//	}
				elseif($indice == 'nom_gerant') 
				{
					echo '<td colspan="2">' . ucfirst($valeur).' ';	
				}
				elseif($indice == 'prenom_gerant')
				{
					echo ucfirst($valeur) .'</td>';
				}
				elseif($indice == 'adresse')
				{
					echo '<td colspan="">' . ucfirst($valeur).'</td>';	
				}
				elseif($indice == 'statut')
				{
					if($valeur === '1')
					{
						echo '<td >actif</td>';
					}
					else
					{
						echo '<td >en attente de validation</td>';
					}
				}
				elseif($indice != 'description')
				{
					echo '<td >'.$valeur.'</td>';
				}
			}
			echo '<td><a href="?action=suppression&id_bar='.$ligne['id_bar'] .$page.''.$orderby.''.$asc_desc.'#details" class="btn_delete" onClick="return(confirm(\'En êtes-vous certain ?\'));">X</a></td>';
			
			//echo '<td><a href="?action=modification&id_produit='.$ligne['id_bar'] .'" class="btn_edit">éditer</a></td>';
			echo '</tr>';	
		}
		echo '</table><br />';
	}

//DETAIL PROMO / BAR	
	echo '<br />';
	$resultat_bar_promo = executeRequete("SELECT COUNT(id_promo_bar) AS nbre_promo FROM promo_bar WHERE id_bar = '$_GET[id_bar]'");
	$bar_promo = $resultat_bar_promo -> fetch_assoc();
	echo '<h3>Tous les apéros proposés par ce bar ('. $bar_promo['nbre_promo'].')</h3>';

	$req = "SELECT * FROM promo_bar WHERE id_bar = '$_GET[id_bar]'";
	$resultat = executeRequete($req); 
	
	echo '<table>';
	$dont_link = 'nono'; // entete du tablau sans order by
	$dont_show = ''; // colonne non affichée
	enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
			
	while ($ligne = $resultat->fetch_assoc())
	{
		echo '<tr>';
			foreach($ligne AS $indice => $valeur) 
			{	
				//elseif($indice == 'categorie_produit')
				//{
				//	echo '<td><a href="?affichage=affichage&action=detail&id_bar='.$ligne['id_bar'].'&id_promo_bar='.$ligne['id_promo_bar'].'&categorie='.$ligne['categorie_produit'].'">'.$valeur.'</a></td>'; //Lien au niveau de l'id pour afficher les details du membre
				//}	
				if (($indice == 'date_debut') || ($indice == 'date_fin'))
				{
					echo '<td>';
						$date = date_create_from_format('Y-m-d', $valeur);
					echo date_format($date, 'd/m/Y') . '</td>';
				}				
				else
				{
					echo '<td >'.$valeur.'</td>';
				}
			}
		echo '<td>
				<a class="btn_delete" href="?affichage=affichage&action=suppression&id_promo_bar='.$ligne['id_promo_bar'] .$page.''.$orderby.''.$asc_desc.'#details" onClick="return(confirm(\'En êtes-vous certain ?\'));"> X </a>
			</td>
			<td>
				<a class="btn_edit" href="?action=modifier&id_promo_bar='.$ligne['id_promo_bar'] .'" >éditer</a>
			</td>
		</tr>';
	}						
	echo '</table>
		</div><br /><br />';
}

//FORM AJOUT / MODIF
if(isset($_GET['action']) && (($_GET['action']=='ajout') || ($_GET['action'] == 'modifier')))
{


?>		
	<div class="box_info">
		<form class="form" method="post" action="" enctype="multipart/form-data"> <!--enctype pour ajout eventuel d'un champs photo -->
			<fieldset>
	<?php
	
		if(isset($_GET['id_promo_bar']) && (isset($_GET['action']) && ($_GET['action']=='modifier')))
		{
			$resultat = executeREquete("SELECT * FROM promo_bar WHERE id_promo_bar ='$_GET[id_promo_bar]'") ; // on recupere les infos de l'article à partir de l'id_article récupéré dans l'URL pour les afficher ds le formulaire
			$promo_actuelle = $resultat ->fetch_assoc();

			echo '<legend>Modifier l\' apéro n°';				
			if(isset($promo_actuelle['id_promo_bar'])) // n° de la promo ds le titre
			{ 
				echo $promo_actuelle['id_promo_bar'];
			} 
			elseif(isset($_POST['id_promo_bar']))
			{ 
				echo $_POST['id_promo_bar'];
			}
			echo '</legend>';
		}
		else
		{
			echo '<legend>Ajouter un apéro</legend>';	
		}
		?> 
		<input type="hidden" name="id_promo_bar" id="id_promo_bar" value="<?php if(isset($promo_actuelle['id_promo_bar'])){ echo $promo_actuelle['id_promo_bar']; }?>" />
			<label for="id_bar">Nom de l'établissement</label>
			<select required id="id_bar" name="id_bar">
			<?php
				$req = "SELECT id_bar, nom_bar, cp FROM bar ORDER BY nom_bar";
				$resultat = executeRequete($req);
				//$nb_ligne = count($resultat)
				while($ligne = $resultat -> fetch_assoc())
				{
					echo '<option value="'.$ligne['id_bar'].'"';
					if(isset($_GET['id_bar']) && $_GET['id_bar'] == $ligne['id_bar'])
					{
						echo 'selected';
					}
					elseif(isset($_POST['id_bar']) && $_POST['id_bar'] == $ligne['id_bar'])
					{
						echo 'selected';
					}
					elseif(isset($promo_actuelle) && $promo_actuelle['id_bar'] == $ligne['id_bar'])
					{
						echo 'selected';
					}
					echo ' >'.$ligne['id_bar'].' - '.$ligne['cp'].' - '.$ligne['nom_bar'].'</option>';
				}
	
				echo '</select>
					<label for="categorie">Categorie de produits</label>
					<select required id="categorie" name="categorie">';	
			
				$req = "SELECT DISTINCT categorie FROM produit ORDER BY categorie";
				$resultat = executeRequete($req);
				//$nb_ligne = count($resultat)
				while($ligne = $resultat -> fetch_assoc())
				{
					foreach($ligne AS $indice => $valeur) 
					{	
						echo '<option ' ;
						if(isset($_GET['categorie']) && $_GET['categorie'] == $ligne['categorie'])
						{
							echo ' selected ';
						}
						elseif(isset($_POST['categorie']) && $_POST['categorie'] == $ligne['categorie'])
						{
							echo ' selected ';
						}
						elseif(isset($promo_actuelle['categorie_produit']) && $promo_actuelle['categorie_produit'] == $ligne['categorie'])
						{
							echo ' selected ';
						}
						echo ' >'.$ligne['categorie'].'</option>';
						
					}
				}
			
				echo '</select>';	
	?>	
				<label for="date_debut">Date de début (JJ/MM/AAAA)</label>
				<input required type="text" id="date_debut" name="date_debut" maxlength="10" value="<?php
					if(isset($_POST['date_debut']))
					{ 
						$date1 = $_POST['date_debut'];
						echo $date1;
					}  
					elseif(isset($promo_actuelle))
					{ 
						$date1 = date_create_from_format('Y-m-d', $promo_actuelle['date_debut']) ;
						echo date_format($date1, 'd/m/Y') ;
					} ?>" /><br /> 
					
				<label for="date_fin">Date de fin (JJ/MM/AAAA)</label>
				<input required type="text" id="date_fin" name="date_fin" maxlength="10" value="<?php
				if(isset($_POST['date_fin']))
					{ 
						$date1 = $_POST['date_fin'];
						echo $date1;
					}  
					elseif(isset($promo_actuelle))
					{ 
						$date1 = date_create_from_format('Y-m-d', $promo_actuelle['date_fin']) ;
						echo date_format($date1, 'd/m/Y') ;
					} ?>" /><br /> 
					
				
				<label for="description">Description </label><br />
				<textarea id="description" name="description" maxlength="200" class="description_form" ><?php if(isset($_POST['description'])) {echo $_POST['description'];}  elseif(isset($promo_actuelle)){ echo $promo_actuelle['description'];} ?></textarea>
				
			
				
				<br />
				<input type="submit" id="ajouter" name="ajouter" value="Enregistrer" class="button" /><br />
				<br />
				<a class="button " href="?affichage=affichage">Retour aux apéros</a><br />
				<br />
				</fieldset>
			</form>	

<?php 
}
echo '</div>
<br />
<br /> ';
require_once('../inc/footer.inc.php');

