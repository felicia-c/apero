<?php
require_once("../inc/init.inc.php");
$titre_page = "Gestion des commandes";
//APERO- Felicia Cuneo - 12/2015

//Redirection vers connexion si l'utilisateur n'est pas admin
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

//
if(isset($_GET['id_commande']))
{
	if(isset($_GET['modif']) && $_GET['modif'] == 'expedier')
	{
		executeRequete("UPDATE commande SET etat='expediee' WHERE id_commande='$_GET[id_commande]'");
		$msg .='<div class="msg_success"><h4>Commande N°'. $_GET['id_commande'] .' exédiée!</h4></div>';
	}

	//VALIDER UNE COMMANDE ( = PAYEE, prête à envoyer)
	 if(isset($_GET['modif']) && $_GET['modif'] == 'valider')
	{
		executeRequete("UPDATE commande SET etat='validee' WHERE id_commande='$_GET[id_commande]'");
		$msg .='<div class="msg_success"><h4>Commande N°'. $_GET['id_commande'] .' validée!</h4></div>';
	}
	// SUPPRESSION DES COMMANDES
	 
	 if(isset($_GET['modif']) && $_GET['modif'] == 'suppression')
	{
		$resultat = executeRequete("SELECT * FROM commande WHERE id_commande = '$_GET[id_commande]'"); //on recupere les infos dans la table commande
		//executeRequete("UPDATE produit SET etat='0' WHERE id_produit IN(SELECT id_produit FROM details_commande WHERE id_commande='$_GET[id_commande]' )");
		executeRequete("DELETE FROM details_commande WHERE id_commande='$_GET[id_commande]'");
		executeRequete("DELETE FROM commande WHERE id_commande='$_GET[id_commande]'");
		   //suppression de la commande dans la table + affichage d'un msg de confirmation
		header('location:gestion_commandes.php?id='.$_GET['id_commande'].'&rem=ok&affichage=affichage&action=commandes'.$page.$orderby.$asc_desc.'');
	}
}


if((isset($_GET['rem']) && $_GET['rem'] == 'ok') && (isset($_GET['id'])))
{
	$msg .='<div class="msg_success"><p>Commande N°'. $_GET['id'] .' supprimée avec succès!</p></div>';
}
 

$req ="";

//AFFICHAGE
require_once("../inc/header.inc.php");


$resultat = executeRequete("SELECT SUM(montant) AS total,
										COUNT(id_commande) AS nbre_commandes,
										ROUND(AVG(montant),2) AS panier_moyen,
										MAX(date) AS der_commande 
								FROM commande");
$commandes = $resultat -> fetch_assoc();
echo '<h3>CA Total : '. $commandes['total'] .'€  |  Nombre de commandes: '. $commandes['nbre_commandes'].' | Commande moyenne : '.$commandes['panier_moyen'].'€</h3><br />';
$resultat = executeRequete("SELECT COUNT(id_commande) AS nbre_commandes FROM commande");
$donnees =$resultat -> fetch_assoc();	

if((isset($_GET['affichage']) && $_GET['affichage'] == 'affichage') && (isset($_GET['action']) && $_GET['action'] == 'commandes'))
{	
	echo '<h2><a href="?affichage=affichage&action=commandes" class="button active" >Toutes les commandes ('. $donnees['nbre_commandes'].')</a></h2>
	<a href="?affichage=all_details&action=detail" class="button">Détails des commandes</a><br />
	<p><a href="" onClick="(window.history.back())" title="retour"> < Retour</a></p>';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'detail')
{
	$resultat_details = executeRequete("SELECT COUNT(id_details_commande) AS nbre_details FROM  details_commande");
	$details =$resultat_details -> fetch_assoc();	
	echo '<h2><a href="?affichage=all_details&action=detail" class="button active">Détails des commandes ('. $details['nbre_details'].')</a></h2>
	<a href="?affichage=affichage&action=commandes" class="button" >Toutes les commandes</a>
	<p><a href="" onClick="(window.history.back())" title="retour"> < Retour</a></p>';
}
else
{
	echo '<h2><a href="?affichage=affichage&action=commandes" class="button" >Toutes les commandes</a></h2>
		<h2><a href="?affichage=all_details&action=detail" class="button">Détails des commandes</a></h2><br />';
}

echo $msg; 
echo '<br /><br />';
/////AFFiCHAGE DETAILS COMMANDE

if(isset($_GET['action']) && $_GET['action'] == 'detail')
{		
	
	// DETAILs DES MEMBRES
	if(isset($_GET['id_membre']))
	{
		if((isset($_GET['action']) && $_GET['action'] == 'detail') && (isset($_GET['info']) == 'membre'))
		{
			//On affiche les infos du membre associé à la commande :
			
			echo '<h4 id="details">Membre N° '.$_GET['id_membre'].'</h4>';

			$resultat = executeRequete("SELECT * FROM membre WHERE id_membre = '$_GET[id_membre]'");
			echo'<table >';
			
			$dont_link = 'nono'; // entete du tablau sans order by
			$dont_show = 'photo'; // colonne non affichée
			enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
			while ($ligne = $resultat->fetch_assoc())  
			{
				echo '<tr>';
				foreach($ligne AS $indice => $valeur)
				{
					if ($indice == 'adresse')
					{
						echo '<td colspan ="">'. $valeur.'</td>';
					}
						elseif ($indice == 'statut')
					{
						if(($valeur == '1') || $valeur == '2')
						{
							echo '<td>Admin</td>';
						}
						elseif($valeur == '3')
						{
							echo '<td>Barman</td>';
						}
						else
						{
							echo '<td>Membre</td>';
						}
					}
					elseif(($indice != 'mdp') && ($indice != 'photo'))
					{
						echo '<td >'.ucfirst($valeur).'</td>';	
					}
				}
			}
			echo '</tr>
			</table>
			<br />';

			$resultat_membre_commande = executeRequete("SELECT COUNT(id_commande) AS nbre_commandes FROM commande WHERE id_membre = '$_GET[id_membre]'");
			$membre_commandes = $resultat_membre_commande -> fetch_assoc();
		//COMMANDES DU MEMBRE	
			echo '<h3>Toutes les commandes de ce membre ('. $membre_commandes['nbre_commandes'].')</h3>
			<!-- LES COMMANDES / MEMBRE -->';				
			// On affiche toutes les commandes associées à ce membre :
			
			$resultat = executeRequete("SELECT * FROM commande WHERE id_membre = '$_GET[id_membre]'");

			echo'<table id="details">';
			$dont_link = 'nono'; // entete du tablau sans order by
			$dont_show = ''; // colonne non affichée
			enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
			while ($ligne = $resultat->fetch_assoc()) 
			{
				echo '<tr '; 
				if(isset($_GET['id_commande']) && ($_GET['id_commande'] == $ligne['id_commande']))
				{
					echo ' class="tr_active" ';
				}
				echo '>';
				foreach($ligne AS $indice => $valeur)
				{
					if($indice == 'id_commande')
					{
						echo '<td><a href="?affichage=affichage&action=detail&info=membre&id_membre='.$ligne['id_membre'].'&id_commande='.$ligne['id_commande'].$page.$orderby.$asc_desc.'#details">'.$valeur.'</a></td>'; //Lien au niveau de l'id pour afficher les details de la commande
					}
					elseif($indice == 'date')
					{
						$date= date_create_from_format('Y-m-d H:i:s', $ligne['date']);
						echo '<td>'.date_format($date, 'd/m/Y H:i').'</td>';
					}
					else
					{
						echo '<td>'.$valeur.'</td>';
					}
				}
				echo '<td></td>
			</tr>';
			}
			echo '</table><br />';	
		}
	}	
		
	// AFFICHAGE INFOS COMMANDE			
	if((isset($_GET['info']) && $_GET['info'] == 'infocommande') && (isset($_GET['action'])&& $_GET['action'] == 'detail'))
	{
		echo '<h4>Infos de la commande N° '.$_GET['id_commande'].'</h4>';
		$resultat = executeRequete("SELECT * FROM commande WHERE id_commande = '$_GET[id_commande]'");

		echo'<table id="details">';
		
		$dont_link = 'nono'; // entete du tablau sans order by
		$dont_show = 'description'; // colonne non affichée
		enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
		while ($ligne = $resultat->fetch_assoc()) 
		{
			echo '<tr>';
			foreach($ligne AS $indice => $valeur)
			{
				if($indice == 'id_membre')
				{
					echo '<td><a href="?affichage=affichage&action=detail&info=membre&id_membre='.$ligne['id_membre'].'&id_commande='.$ligne['id_commande'].$page.$orderby.$asc_desc.'#details">'.$valeur.'</a></td>'; //Lien au niveau de l'id pour afficher les details du membre
				}
				elseif($indice == 'date')
				{
					$date= date_create_from_format('Y-m-d H:i:s', $ligne['date']);
		
					echo '<td>'.date_format($date, 'd/m/Y H:i').'</td>';
				}
				else
				{
					echo '<td >'.$valeur.'</td>';
				}
			}
		}
		echo '<td></td></tr>
		</table>
		<br />';
	}
	// AFFICHAGE DETAILS COMMANDE(s)
	
	if(isset($_GET['id_commande']))
	{
		echo '<h3 id="detail_commande">Détail de la commande N°'.$_GET['id_commande'].' </h3>';
		$resultat = executeRequete("SELECT * FROM details_commande WHERE id_commande= '$_GET[id_commande]'");
		echo'<table id="details">';
		$dont_link = 'nono'; // entete du tablau sans order by
		$dont_show = ''; // colonne non affichée
	}
	else
	{
		//$resultat = executeRequete("SELECT * FROM details_commande");
		$req .= "SELECT * FROM details_commande";
		$req = paginationGestion(10,'details_commande',$req);  // PAGINATION + TRI
		$resultat = executeRequete($req);
		//$nbcol = $resultat->field_count; 
		$dont_link = null; // entete du tablau sans order by
		$dont_show = ''; // colonne non affichée
		echo'<table id="details">';
	}	
	enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
	
	while ($ligne = $resultat->fetch_assoc())
	{
		echo '<tr '; 
		if(isset($_GET['id_produit']) && ($_GET['id_produit'] == $ligne['id_produit']))
		{
			echo ' class="tr_active" ';
		}
		if(isset($_GET['id_commande']) && ($_GET['id_commande'] == $ligne['id_commande']) && !isset($_GET['id_produit']))
		{
			echo ' class="tr_active" ';
		}
		echo '>';
	
		foreach($ligne AS $indice => $valeur)
		{	
			if($indice == 'id_produit')
			{
				echo '<td><a href="?affichage=affichage&action=detail&info=detailproduit&id_produit='.$ligne['id_produit'].'&id_commande='.$ligne['id_commande'].$page.''.$orderby.''.$asc_desc.'#details">'.$valeur.'</a></td>'; //Lien au niveau de l'id pour afficher les details de la commande
			}
			elseif($indice == 'id_commande')
			{
				echo '<td><a href="?affichage=affichage&action=detail&info=infocommande&id_commande='.$ligne['id_commande'].$page.''.$orderby.''.$asc_desc.'#details">'.$valeur.'</a></td>'; //Lien au niveau de l'id pour afficher les infos de la commande
			}
			elseif($indice == 'id_taille_produit')
			{
				$res = executeRequete("SELECT * FROM taille WHERE id_taille='$valeur'");
				$taille = $res -> fetch_assoc();
				echo '<td >'.$taille['taille'].'</td>';
			}	
			else
			{
				echo '<td >'.$valeur.'</td>';
			}
		}
		echo '<td></td></tr>';
	}					
	echo '</table><br />';
	if(isset($_GET['action']) && ($_GET['action'] == 'detail') && !isset($_GET['id_commande']))
	{
		$lien = '<a href="?affichage=affichage&action=detail&';
		affichagePaginationGestion(10, 'details_commande', $lien);
	}
	//echo '</div>';		
	

	// DETAILs DES PRODUITS
	if((isset($_GET['info']) && $_GET['info'] == 'detailproduit'))
	{
		echo '
			<h4>Détail du produit N° '.$_GET['id_produit'].'</h4>';
		$resultat = executeRequete("SELECT * FROM produit WHERE id_produit = '$_GET[id_produit]'");
		// $produit = $resultat -> fetch_assoc();
		echo'<table id="details">';
		
		$dont_link = 'nono'; // entete du tablau sans order by
		$dont_show = 'description'; // colonne non affichée
		enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
		
		while ($ligne = $resultat->fetch_assoc())
		{
			echo '<tr>';
			foreach($ligne AS $indice => $valeur)
			{
				if($indice == 'photo')
				{
					echo '<td ><img src="'.$valeur.'" alt="'.$ligne['titre'].'" title="'.$ligne['titre'].'" class="thumbnail_tableau" width="80px" /></td>';
				}
				elseif($indice == 'id_promo_produit')
				{
					if(!empty($valeur))
					{
						$resultat_promo = executeRequete("SELECT * FROM promo_produit WHERE id_promo_produit = '$ligne[id_promo_produit]'");
						$promo = $resultat_promo -> fetch_assoc();
						echo '<td >'.$promo['code_promo'].' ('.$promo['id_promo_produit'].') <br /> -'. $promo['reduction'].'%</td>';
					}
					else
					{
						echo '<td >PAS DE PROMO</td>';
					}	
				}
				elseif($indice == 'stock')
				{
					echo '<td > x '.$valeur.'</td>';
				}
				elseif($indice == 'prix')
				{
					echo '<td >'.$valeur.'€</td>';
				}
				elseif($indice != 'description')
				{
					echo '<td >'.$valeur.'</td>';
				}
			}
		}
		echo '<td></td>
		</tr></table><br />';	
	}	
}
//AFFICHAGE DE TOUTES LES COMMANDES
if((isset($_GET['affichage']) && $_GET['affichage'] == 'affichage') && (isset($_GET['action']) && $_GET['action'] == 'commandes'))
{
// PAGINATION + TRI
	$req .= "SELECT * FROM commande";
	$req = paginationGestion(10, 'commande', $req);  
	$resultat = executeRequete($req);

	$dont_link = null; // entete du tablau sans order by
	$dont_show = ''; // colonne non affichée
	echo'<table id="details">';
		
	enteteTableau($resultat, $dont_show, $dont_link);

	//$nbcol = $resultat->field_count; 

	while ($ligne = $resultat->fetch_assoc()) 
	{
		echo '<tr '; 
		if(isset($_GET['id_commande']) && ($_GET['id_commande'] == $ligne['id_commande']))
		{
			echo ' class="tr_active" ';
		}
		echo '>';
		foreach($ligne AS $indice => $valeur)
		{
			
			if($indice == 'id_commande')//Lien au niveau de l'id pour afficher les details de la commande
			{
				echo '<td><a href="?affichage=affichage&action=detail&id_commande='.$ligne['id_commande'].$page.''.$orderby.''.$asc_desc.'#details">'.$valeur.'</a></td>'; 
			}
			elseif($indice == 'id_membre')//Lien au niveau de l'id pour afficher les details du membre
			{
				echo '<td><a href="?action=detail&info=membre&id_membre='.$ligne['id_membre'].'&id_commande='.$ligne['id_commande'].$page.''.$orderby.''.$asc_desc.'#details">'.$valeur.'</a></td>'; 
			}
			elseif($indice == 'date') // affichage du timestamp de la commande en format fr
			{
				echo '<td>';
					$date = date_create_from_format('Y-m-d H:i:s', $valeur);
				echo date_format($date, 'd/m/Y H:i') . '</td>';
			}
			elseif($indice == 'montant') // affichage du timestamp de la commande en format fr
			{		
				echo  '<td>'.$valeur. ' € </td>';
			}
			else
			{
				echo '<td >'.$valeur.'</td>';
			}
		}
		
			
		echo '<td>';
		if($ligne['etat'] == 'en cours de traitement')
		{
			echo'<a class="btn_edit" href="?affichage=affichage&action=commandes&modif=valider&id_commande='.$ligne['id_commande'] . $page.''.$orderby.''.$asc_desc.'" class="btn" onClick="return(confirm(\'En êtes-vous certain ?\'));">valider</a>';
		}
		elseif($ligne['etat'] == 'validee')
		{
			echo '<a class="btn_edit" href="?affichage=affichage&action=commandes&modif=expedier&id_commande='.$ligne['id_commande'] .$page.''.$orderby.''.$asc_desc.'" class="btn" onClick="return(confirm(\'En êtes-vous certain ?\'));">expédier</a>';
		}
		else
		{
			echo '<p>Commande traitée</p>';
		}
		
		echo '</td>
		<td>
			<a class="btn_delete" href="?affichage=affichage&action=commandes&modif=suppression&id_commande='.$ligne['id_commande'] .$page.''.$orderby.''.$asc_desc.'" onClick="return(confirm(\'Voulez-vous vraiment supprimer la commande n° '.$ligne['id_commande'] .' ?\'));"> X </a>
		</td>
		</tr>';
	}						
	echo '</table>
		<br />';
	if((isset($_GET['affichage']) && $_GET['affichage'] == 'affichage') && (isset($_GET['action']) && $_GET['action'] == 'commandes'))
	{
		$lien = '<a href="?affichage=affichage&action=commandes&';
		affichagePaginationGestion(10, 'commande', $lien);
	}

	echo '</div>
	</div>';
}
	?>
		
	
	
	
	 
	 <br />
	 <br />
<?php
	
require_once("../inc/footer.inc.php");	
	
?>