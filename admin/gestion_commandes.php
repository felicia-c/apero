<?php
require_once("../inc/init.inc.php");
$titre_page = "Gestion des commandes";
//APERO- Felicia Cuneo - 12/2015

//Redirection vers connexion si l'utilisateur n'est pas admin
if(!utilisateurEstConnecteEtEstAdmin() && !utilisateurEstConnecteEtEstGerantEtAdmin()){
	header("location:../connexion.php");
}

 // SUPPRESSION DES COMMANDES
 
 if((isset($_GET['action']) && $_GET['action'] == 'suppression') && (isset($_GET['id_commande'])))
{
	$resultat = executeRequete("SELECT * FROM commande WHERE id_commande = '$_GET[id_commande]'"); //on recupere les infos dans la table commande
	//executeRequete("UPDATE produit SET etat='0' WHERE id_produit IN(SELECT id_produit FROM details_commande WHERE id_commande='$_GET[id_commande]' )");
	executeRequete("DELETE FROM details_commande WHERE id_commande='$_GET[id_commande]'");
	executeRequete("DELETE FROM commande WHERE id_commande='$_GET[id_commande]'");
	   //suppression de la commande dans la table + affichage d'un msg de confirmation
	header('location:gestion_commandes.php?id='.$_GET['id_commande'].'&rem=ok&affichage=affichage&action=commandes');
}

if((isset($_GET['rem']) && $_GET['rem'] == 'ok') && (isset($_GET['id'])))
{
	$msg .='<div class="msg_success"><p>Commande N°'. $_GET['id'] .' supprimée avec succès!</p></div>';
}

$req ="";
require_once("../inc/header.inc.php");

echo '<div class="box_info">
	<p><a href="" onClick="(window.history.back())" title="retour"> < Retour</a></p>';
$resultat = executeRequete("SELECT SUM(montant) AS total,
										COUNT(id_commande) AS nbre_commandes,
										ROUND(AVG(montant),2) AS panier_moyen,
										MAX(date) AS der_commande 
								FROM commande");
$commandes = $resultat -> fetch_assoc();
echo '<h3>CA Total : '. $commandes['total'] .'€  |  Nombre de commandes: '. $commandes['nbre_commandes'].' | Commande moyenne : '.$commandes['panier_moyen'].'€</h3><br />';
$resultat = executeRequete("SELECT COUNT(id_commande) AS nbre_commandes FROM commande");
$donnees =$resultat -> fetch_assoc();	

if(isset($_GET['affichage']) && $_GET['affichage'] == 'affichage')
{	
	
	echo '<h2><a href="?affichage=affichage&action=commandes" class="button active" >Toutes les commandes ('. $donnees['nbre_commandes'].')</a></h2>
	<a href="?affichage=affichage&action=detail" class="button">Détails des commandes</a><br />';
}
elseif(isset($_GET['action']) && $_GET['action'] == 'detail')
{
	$resultat_details = executeRequete("SELECT COUNT(id_details_commande) AS nbre_details FROM  details_commande");
	$details =$resultat_details -> fetch_assoc();	
	echo '<h2><a href="?affichage=affichage&action=detail" class="button active">Détails des commandes ('. $details['nbre_details'].')</a></h2>
	<a href="?affichage=affichage&action=commandes" class="button" >Toutes les commandes</a>';
}
else
{
	echo '<h2><a href="?affichage=affichage&action=commandes" class="button" >Toutes les commandes</a></h2>
		<h2><a href="?affichage=affichage&action=detail" class="button">Détails des commandes</a></h2><br />';
}

echo $msg; 
echo '<br /><br />';
/////AFFiCHAGE DETAILS COMMANDE

if(isset($_GET['action']) && $_GET['action'] == 'detail')
{		
	// DETAILs DES PRODUITS
	if((isset($_GET['info']) && $_GET['info'] == 'detailproduit') && (isset($_GET['action'])&& $_GET['action'] == 'detail'))
	{
		echo '
			<h4>Détail du produit N° '.$_GET['id_produit'].'</h4>';
		$resultat = executeRequete("SELECT * FROM produit WHERE id_produit = '$_GET[id_produit]'");
		// $produit = $resultat -> fetch_assoc();
		echo'<table>';
		
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
		echo '</tr></table><br />';
		
	}	
// DETAILs DES MEMBRES
	if(isset($_GET['id_membre']))
	{
		if((isset($_GET['action']) && $_GET['action'] == 'detail') && (isset($_GET['info']) == 'membre'))
		{
			//On affiche les infos du membre associé à la commande :
			
			$resultat = executeRequete("SELECT * FROM membre WHERE id_membre = '$_GET[id_membre]'");
			echo'<table>';
			
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
						if($valeur == '1')
						{
							echo '<td>Admin</td>';
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
			echo '<h3>Toutes les commandes de ce membre ('. $membre_commandes['nbre_commandes'].')</h3>
			<!-- LES COMMANDES / MEMBRE -->';				
			// On affiche toutes les commandes associées à ce membre :
			
			$resultat = executeRequete("SELECT * FROM commande WHERE id_membre = '$_GET[id_membre]'");

			echo'<table>';
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
						echo '<td><a href="?action=detail&info=membre&id_membre='.$ligne['id_membre'].'&id_commande='.$ligne['id_commande'].'">'.$valeur.'</a></td>'; //Lien au niveau de l'id pour afficher les details de la commande
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

		echo'<table>';
		
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
					echo '<td><a href="?action=detail&info=membre&id_membre='.$ligne['id_membre'].'&id_commande='.$ligne['id_commande'].'">'.$valeur.'</a></td>'; //Lien au niveau de l'id pour afficher les details du membre
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
	if(isset($_GET['action']) && ($_GET['action'] == 'detail'))
	{
		echo '<br />';

		if(isset($_GET['id_commande']) && ($_GET['action'] != 'suppression'))
		{
			echo '<h3 id="detail_commande">Détail de la commande N°'.$_GET['id_commande'].' </h3>';
			$resultat = executeRequete("SELECT * FROM details_commande WHERE id_commande= '$_GET[id_commande]'");
			echo'<table id="details">';
			$dont_link = 'nono'; // entete du tablau sans order by
			$dont_show = ''; // colonne non affichée
			enteteTableau($resultat, $dont_show, $dont_link); //entete tableau
		}
		else
		{
			//$resultat = executeRequete("SELECT * FROM details_commande");
			$req .= "SELECT * FROM details_commande";
			$req = paginationGestion(5,'details_commande',$req);  // PAGINATION + TRI
			$resultat = executeRequete($req);
			$nbcol = $resultat->field_count; 
			echo'<table>
				<tr>';
			for($i= 0; $i < $nbcol; $i++) 
			{
				$colonne= $resultat->fetch_field(); 	
				echo '<th style="text-align: center;"><a href="?affichage=affichage&action=detail&orderby='. $colonne->name ; 
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
				echo '>'. ucfirst($colonne->name).'</a></th>';  	
			}
			echo '<td></td></tr>';
		}

		
		while ($ligne = $resultat->fetch_assoc()) // = tant qu'il y a une ligne de resultat, on en fait un tableau 
		{
			echo '<tr '; 
			if(isset($_GET['id_produit']) && ($_GET['id_produit'] == $ligne['id_produit']))
			{
				echo ' class="tr_active" ';
			}
			echo '>';
			
			foreach($ligne AS $indice => $valeur) // foreach = pour chaque element du tableau
			{
					
				if($indice == 'id_produit')
				{
					echo '<td><a href="?action=detail&info=detailproduit&id_produit='.$ligne['id_produit'].'&id_commande='.$ligne['id_commande'].'">'.$valeur.'</a></td>'; //Lien au niveau de l'id pour afficher les details de la commande
				}
				elseif($indice == 'id_commande')
				{
					echo '<td><a href="?action=detail&info=infocommande&id_commande='.$ligne['id_commande'].'&id_produit='.$ligne['id_produit'].'">'.$valeur.'</a></td>'; //Lien au niveau de l'id pour afficher les infos de la commande
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
			affichagePaginationGestion(5, 'details_commande', $lien);
		}
		echo '</div>
		</div>';
		
	}		

}

//AFFICHAGE DE TOUTES LES COMMANDES
	if((isset($_GET['affichage']) && $_GET['affichage'] == 'affichage') && (isset($_GET['action']) && $_GET['action'] == 'commandes'))
	{
		echo '<table>
				<tr>';
	// PAGINATION + TRI
			$req .= "SELECT * FROM commande";
			$req = paginationGestion(10, 'commande', $req);  
			$resultat = executeRequete($req);
			$nbcol = $resultat->field_count; 
			for($i= 0; $i < $nbcol; $i++) 
			{
					$colonne= $resultat->fetch_field(); 	
					echo '<th style="text-align: center;"><a href="?affichage=affichage&action=commandes&orderby='. $colonne->name ; 
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
				echo '>'. ucfirst($colonne->name).'</a></th>';  
					
			}
			echo '<th></th>
				</tr>';
			
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
						echo '<td><a href="?action=detail&id_commande='.$ligne['id_commande'].'">'.$valeur.'</a></td>'; 
					}
					elseif($indice == 'id_membre')//Lien au niveau de l'id pour afficher les details du membre
					{
						echo '<td><a href="?action=detail&info=membre&id_membre='.$ligne['id_membre'].'&id_commande='.$ligne['id_commande'].'">'.$valeur.'</a></td>'; 
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
				echo '<td>
				<a class="btn_delete" href="?affichage=affichage&action=commandes&action=suppression&id_commande='.$ligne['id_commande'] .'" onClick="return(confirm(\'En êtes-vous certain ?\'));"> X </a>
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
		
	
	</div>
	
	 
	 <br />
	 <br />
		</div>
<?php
	
require_once("../inc/footer.inc.php");	
	
?>