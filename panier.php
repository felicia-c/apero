<?php
require_once("inc/init.inc.php");

//APERO - Felicia Cuneo - 12/2015
$titre_page = "Panier";


creationDuPanier(); //s'il est deja créé, cette fonction ne l'ecrase pas


if($_POST)
{
	// CODE PROMO
	
	if(isset($_POST['code_promo']) && empty($_SESSION['panier']['promo']['id_promo'])) //si on a rentré un code promo et qu'il n'y a pas déjà un code promo entré (codes promo non cumulables, 1 promo par panier (1 promo par commande), mais le code peut etre appliqué à plusieurs articles) 
	{
		appliquerUnCodePromoAuPanier($_POST['code_promo']);
		unset($_POST['code_promo']);
	}
	elseif(isset($_POST['code_promo']) && !empty($_SESSION['panier']['promo']['id_promo'])) // Si on poste un code promo alors qu'il y en a deja un d'actif
	{
		$msg .= '<div class="msg_erreur" >Un code promo est déjà actif sur ce panier</div>';
	}
	
	// PAYER LE PANIER

	if(isset($_POST['payer']) && $_POST['payer'] == 'Payer')
	{
		//VERIF DES CGV
		if(!isset($_POST['cgv']))  //  en + du required en html
		{
			$msg .= '<div class="msg_erreur" >Vous devez accepter les Conditions Générales de Vente pour valider votre commande</div>';
			$erreur = TRUE;
		}
		else
		{
			for($i=0; $i < count($_SESSION['panier']['id_produit']); $i++) 
			{
				$id_taille_stock= $_SESSION['panier']['taille_stock'][$i];
				
				$resultat_stock = executeRequete("SELECT stock, id_taille FROM taille_stock WHERE id_taille_stock = '$id_taille_stock'"); //on recupere le stock du produit 
				$stock = $resultat_stock ->fetch_assoc();
				
				// PB de STOCK		
				if($stock['stock'] < $_SESSION['panier']['quantite'][$i]) // Si le stock est inférieur à la quantité demandée
				{
					if($stock['stock'] > 0) // il reste du stock mais inferieur a la quantité demandée
					{
						$msg .= '<div class="msg_erreur">La quantité de l\'article n° '. $_SESSION['panier']['id_produit'][$i] .' en taille '. $stock['taille'] .' a été modifiée car notre stock était insuffisant.<br /> Veuillez vérifier votre commande</div>';
						
						$_SESSION['panier']['quantite'][$i] = $stock['stock']; // on change la quantité demandée par le stock restant de la bdd
					}
					else // si le stock est à 0
					{
						$msg .= '<div class="msg_erreur">L\'article n° '. $_SESSION['panier']['id_produit'][$i] .' en taille '. $stock['taille'] .' a été retiré de votre panier car nous sommes en rupture de stock.<br /> Veuillez vérifier votre commande</div>';
				
					retirerUnArticleDuPanier($_SESSION['panier']['taille_stock'][$i]);
					
					$i--; // On décrémente car la fonction retirerUnArticleDuPanier() a réorganisé le tableau array $_SESSION['panier'] au niveau des indices -> pour ne pas rater un article lors controle 
					}
					$erreur = TRUE; // On crée une variable qui nous permet de controler s'il y a au moins une erreur	
				}
			}

			if(!isset($erreur) && ($_SESSION['panier']['id_produit'] !== NULL)) // ON vérifie s'il n'y a pas eu d'erreur lors du controle des disponibilites et si la commande contient bien un article
			{
				if(montantTotal() == 0)
				{
					header("location:panier.php");
					exit;
				}

				executeRequete("INSERT INTO commande(montant, id_membre, date) VALUES ('".montantTotal()."', '".$_SESSION['utilisateur']['id_membre']."', now() )");  //on enregistre la commande dans la table BDD commande
				$id_commande = $mysqli-> insert_id;

				for($i=0; $i < count($_SESSION['panier']['taille_stock']); $i++)
				{
					if($_SESSION['panier']['quantite'][$i] > 0)
					{
						$quantite = $_SESSION['panier']['quantite'][$i];
						$res=executeRequete("SELECT * FROM taille_stock WHERE id_taille_stock = '".$_SESSION['panier']['taille_stock'][$i]."' "); // on recupere la taille
						$taille = $res -> fetch_assoc();
						// pour chaque produit dans le panier -> une ligne dans details_commande + maj stock du produit;

						if($_SESSION['panier']['prix_reduit'][$i] !== NULL)
						{
							$prix = $_SESSION['panier']['prix_reduit'][$i];
						}
						else
						{
							$prix = $_SESSION['panier']['prix'][$i];
						}
						
						executeRequete("INSERT INTO details_commande (id_commande, id_produit, id_taille_produit, quantite, prix) VALUES ('$id_commande', '". $_SESSION['panier']['id_produit'][$i]."', '$taille[id_taille]', '$quantite', '$prix')");
						
						executeRequete("UPDATE taille_stock SET stock= stock-".$_SESSION['panier']['quantite'][$i]." WHERE id_taille_stock='$taille[id_taille_stock]' "); // On modifie le stock dans la BDD produit
					}
				}
				 // Si tout est ok (commande validée, pas d'erreur)on vide le panier
				$msg .= '<div class="msg_success">Merci pour vos achats !<br />Un e-mail de confirmation va vous être envoyé à l\'adresse suivante : <br /><strong>'. $_SESSION['utilisateur']['email'] .'</strong><br /> N° de suivi: '. $id_commande .'</div>';
				
				$mail_vendeur = 'vendeur_apero@yopmail.com';
					
				$message = "<h1>Apéro</h1>
							<p>Merci pour votre commande, votre n° de suivi est le ".$id_commande.". <br />
							<strong>Votre commande sera validée dès réception de votre paiement.</strong><br />
							L'équipe d'Apéro se tient à votre disposition pour répondre à toute question concernant votre commande.</p>";

				mail($_SESSION['utilisateur']['email'], "APERO | Confirmation de votre commande", $message, "From: $mail_vendeur");
				
				unset($_SESSION['panier']); // On vide la session (panier)	
			}
		}
	}
}	

//VIDER LE PANIER
if(isset($_GET['action']) && $_GET['action'] == 'vider')
{
	unset($_SESSION['panier']); // On vide la session (panier)
	//header("location:panier.php");
}

// AJOUT D'ARTICLE
if(isset($_POST['ajout_panier']) && (isset($_POST['quantite']) && isset($_POST['taille_stock'])))
{	
	$resultat= executeRequete("SELECT prix, produit.id_produit FROM produit INNER JOIN taille_stock ON produit.id_produit = taille_stock.id_produit WHERE id_taille_stock = '$_POST[taille_stock]'");
	
	$mon_produit = $resultat -> fetch_assoc(); 
	$prix = $mon_produit['prix'] * 1.2;
	ajouterArticleDansPanier($mon_produit['id_produit'], $_POST['quantite'], $prix, $_POST['taille_stock']); //on rajoute le produit dans le panier
	//unset($_POST);
	header("location:panier.php"); // pour éviter de rajouter plusieur fois l'article quand on rafraichit la page
	//exit;
}

//MODIFIER LA QUANTITE
if(isset($_GET['id']))
{
	//$id_produit = $_GET['id'];
	$taille_stock = $_GET['id'];
	if(isset($_GET['add']) && ($_GET['add'] === '1'))
	{
		$position_produit = array_search($taille_stock, $_SESSION['panier']['taille_stock']); 
		if($position_produit !== FALSE) // = si l'article a été trouvé 
		{
			$resultat = executeRequete("SELECT stock FROM taille_stock WHERE id_taille_stock = '$taille_stock'");
			$stock_produit = $resultat -> fetch_assoc();
			if($_SESSION['panier']['quantite'][$position_produit] < $stock_produit['stock'])
			{
				$_SESSION['panier']['quantite'][$position_produit]++;  //on augmente la quantité	
			}
			else
			{
				$_SESSION['panier']['quantite'][$position_produit] = $stock_produit['stock'];
			}
		}
	}
	if(isset($_GET['rem']) && ($_GET['rem'] === '1'))
	{
		$position_produit = array_search($taille_stock, $_SESSION['panier']['taille_stock']); 
		if($position_produit !== FALSE) // = si l'article a été trouvé 
		{
			if($_SESSION['panier']['quantite'][$position_produit] > 0)
			//if($_SESSION['panier']['quantite'][$position_produit] != 0)
			{
				$_SESSION['panier']['quantite'][$position_produit]--;  //on diminue la quantité	
			}
			else
			{
				$_SESSION['panier']['quantite'][$position_produit]  = 1;	
			}
			
		}	
	}
	header("location:panier.php");
	exit;
}



// RETIRER UN ARTICLE
 if(isset($_GET['action']) && isset($_GET['taille_stock']) && $_GET['action'] == 'retirer')
{
	retirerUnArticleDuPanier($_GET['taille_stock']);
}


//RETIRER UNE PROMO
if(isset($_GET['action']) && $_GET['action'] == 'supprimer_promo')
{
	unset($_SESSION['panier']['promo']['id_promo'][0]); // On vide la session (id promo)
	unset($_SESSION['panier']['promo']['code_promo'][0]); // On vide la session ( code promo)
	
	$nb_prix = count($_SESSION['panier']['prix_reduit']);
	for($i = 0; $i < $nb_prix; $i++)
	{	
		if($_SESSION['panier']['prix_reduit'][$i] !== NULL)
		{
			//unset($_SESSION['panier']['prix_reduit'][$i]);
			$_SESSION['panier']['prix_reduit'][$i] = NULL; // On vide les prix_reduits de la session
		}
	}
	echo '<meta http-equiv="refresh" content="2;URL=panier.php">';
}	

require_once("inc/header.inc.php");	

//debug($_SESSION);
//debug($_POST);

echo '<div class="box_info">
		<h1>Panier</h1>';
echo $msg;
//echo debug($_SESSION['panier']);
//debug($_SESSION['panier']['promo']);
				//echo debug($_SESSION);
echo '<!-- TITRES TABLEAU PANIER  -->
		<div id="tableau_panier style="width: 100%;"">
			<table>
				<tr>
					<th>Ref.</th> 
					<th>Photo</th>
					<th>Titre</th> 
					<th>Taille</th>
					<th>Couleur</th>
					<th>Quantité</th>
					<th>Unité</th>
					<th>Total</th>
					<th></th>
				</tr>';
			//	if(!utilisateurEstConnecte())  // liens creation compte / connexion
			//	{
			/*		echo '<tr>
							<td colspan="4"><p>Vous devez être connecté pour créer un panier <br /><br /> <a href="'.RACINE_SITE.'connexion.php" class="button">Connexion</a></p></td>
							<td></td>
							<td colspan="5"><p>Pas encore de compte ? <br /> Créez-en un en 2 minutes<br /><br /> <a href="'.RACINE_SITE.'inscription.php" class="button">Créer un compte</a></p></td>
						</tr>'; */
			//	}
				//else
				//{
					
				
	if(empty($_SESSION['panier']['id_produit'])) // s'il n'y a pas de produit dans le panier : mesg panier vide + liens recherche et profil
	{
		echo '<tr>
			<td colspan="11" ><h2>Votre panier est vide !</h2> </td>
		</tr>
		<tr>

			<td colspan="3"><a class=" produit button" href="boutique.php" >Poursuivre mes achats</a></td>
			<td colspan="6"><a class="noborder" href="profil.php"> >Voir mon profil</a></td>
		</tr>';
	}
	else
	{
		for($i = 0; $i < count($_SESSION['panier']['id_produit']); $i++) 
		{
			$infos_produit = executeRequete("SELECT * FROM produit INNER JOIN taille_stock ON produit.id_produit = taille_stock.id_produit WHERE id_taille_stock = '".$_SESSION['panier']['taille_stock'][$i]."'");

			$produit = $infos_produit -> fetch_assoc();
			$infos_taille = executeRequete("SELECT * FROM taille WHERE id_taille = '$produit[id_taille]'");
			$taille = $infos_taille -> fetch_assoc();
			echo '<tr>
					<td>'. $produit['reference'].'</td>
					<td><a href="'.RACINE_SITE.'fiche_produit.php?id_produit='.$produit['id_produit'].'" title="Détails" ><img src="'.$produit['photo'].'" alt="'.$produit['titre'].'" title="'.$produit['titre'].'" class="thumbnail_tableau" width="80px" /></a></td>
					<td>'. $produit['titre'].'</td>
					<td>'. $taille['taille'].'</td>
					<td>'. $produit['couleur'].'</td>';
		
	// QUANTITE
			echo '<td>'.$_SESSION['panier']['quantite'][$i];  
			echo '<br />';
			if($_SESSION['panier']['quantite'][$i] < $produit['stock'])
			{
				echo '<a class="teal" href="?add=1&id='.$_SESSION['panier']['taille_stock'][$i].'" >+1</a>';
			}
			else
			{
				echo '+1';
			}
			if($_SESSION['panier']['quantite'][$i] >= 1)
			{
				echo '/ <a class="orange" href="?rem=1&id='.$_SESSION['panier']['taille_stock'][$i].'" >-1</a>';
			}
			else
			{
				echo '/ -1';
			}
			
			echo '</td>';
	
	//Affichage PRIX et TVA	
		
		//PRIX PRODUIT UNITE		
			echo '<td >'. $_SESSION['panier']['prix'][$i] .' €</td>
				<td>';
			
			$prix_ttc_total = $_SESSION['panier']['prix'][$i] * $_SESSION['panier']['quantite'][$i];		
			
		//PRIX PRODUIT REDUIT
			if($_SESSION['panier']['prix_reduit'][$i] !== NULL)// SI CODE PROMO validé sur ce produit
			{		
				$prix_reduit_total = $_SESSION['panier']['prix_reduit'][$i] * $_SESSION['panier']['quantite'][$i];
				
				echo '<strong>'.round($prix_reduit_total, 2). '</strong>€'; 
				
				//Promo- economie réalisée
				$economie = $prix_ttc_total - $prix_reduit_total;
				echo '<br />Vous économisez: -'. round($economie, 2) .'€';
			}
			else
			{	
		// OU PRIX PRODUIT NON REDUIT		
				echo round($prix_ttc_total, 2) .' €';	
			}
	
			echo ' </td>';
	//LIEN SUPPRIMER
			$id_article = $_SESSION['panier']['taille_stock'][$i];
			echo '<td><a class="btn_delete" href="?action=retirer&taille_stock='. $id_article.'"  onClick="return(confirm(\'Voulez-vous vraiment retirer cet article de votre panier ? \'));" title="Supprimer" > X </a></td>';
		
			echo '</tr>';
		}
	
	//TOTAL et CODE PROMO

		echo '<tr>
			<th colspan="6">Total HT</th>
			<td colspan="2">'. totalHt() .' €</td>
			<td></td>
		</tr>
		<tr>
			<th colspan="6">Total T.V.A</th>
			<td colspan="2">'. totalTva() .' €</td>
			<td></td>
		</tr>
		<tr>
			<th colspan="6">Total TTC (hors promotions)</th>
			<td colspan="2"><strong>' . totalTtc() .' € </strong></td>
			<td></td>
		</tr>
		<tr>
			<th colspan="6">Votre code promo</th>
			<td colspan="2">';
	//code promo
		if(!empty($_SESSION['panier']['promo']['id_promo']))
		{ 
			for($i = 0; $i < count($_SESSION['panier']['promo']['id_promo']); $i++) 
			{

				$code_promo=$_SESSION['panier']['promo']['code_promo'][$i];
				$promo = executeRequete("SELECT code_promo, reduction FROM promo_produit WHERE code_promo='$code_promo'");
				while($reduction = $promo -> fetch_assoc())
				{
					echo '<p>'.$reduction['code_promo']. ' -'. $reduction['reduction'] .'% <a class="btn_delete" href="?action=supprimer_promo" title="Supprimer"  onClick="return(confirm(\'Voulez-vous vraiment retirer ce code promo de votre panier ? \'));"> X</a></p>';
				}
			}
		}
		else
		{
			echo '<form method="post" action="" class="">
					<input type="text" name="code_promo" id="code_promo" value="';
			if(isset($_POST['code_promo']))
			{ 
				echo $_POST['code_promo']; 
			} 
			echo '"/>
					<input type="submit" class="button" id="recalculer" value="Recalculer le total" />
				</form>';
		}
	//MONTANT TOTAL
		echo '</td>
			<td></td>
			</tr>

		<tr>
			<th colspan="6">Montant total avec promo</th>
			<td colspan="2"><strong>' . montantTotal() .' € </strong></td>
			<td></td>
		</tr>';
		
	}	
	if(!empty($_SESSION['panier']['id_produit']))
	{
		if(utilisateurEstConnecte()) //par securite
		{	
//CGV	
			echo '<tr>
				
				<td colspan="3" ><a href="'.RACINE_SITE.'boutique.php" class="button produit">Poursuivre mes achats</a></td>

				<td colspan="6">
					<form method="post" action="" class="">
					<input required type="checkbox" name="cgv" value="cgv"  style="margin: 0; display: inline-block;" id="cgv"  /> 
					<label for="cgv" style="text-align: left; display: inline-block; width: 80%;"> J\'ai lu et j\'accepte les <a class="cgv noborder" href="'.RACINE_SITE.'cgv.php" >Conditions Générales de Vente</a></label>
					
					<input type="submit" name="payer" class="button" value="Payer" id="payer" /></td>
			</tr>
		
			<tr>
				<td></td>
				<td colspan="3"><a class="btn_delete" href="panier.php?action=vider">Vider le panier</a></td>
			</tr>';
		}
		else
		{
			echo '<tr>
				<td colspan="1"><a href="'.RACINE_SITE.'boutique.php" class="button btn_resa">Poursuivre mes achats</a></td>
				<td colspan="1">Pour valider vos achats veuillez vous <a href="'.RACINE_SITE.'connexion.php">Connecter</a> Pas encore de compte ? <a href="'.RACINE_SITE.'inscription.php">inscrivez-vous</a></td>
			</tr>';
		}	
	}	
//	}	
	echo '</table>
		</div>
	<!-- fin box-info-->
	<br /><br />';

	echo '<hr /> <p>Tous nos prix sont calculés à partir d\'un taux de TVA à 20%</p>
		<p class="">Vos achats seront expédiés dès réception de votre règlement par chèque (à l\'ordre de Tous Supports) à l\'adresse suivante :</p>
		<p class="">Tous Supports- 3, rue des Montiboeufs 75020 PARIS, France.</p>';		
//}	
if(utilisateurEstConnecte())  // par securité
{
// INFOS UTILISATEUR
	$membre_actuel = $_SESSION['utilisateur'];
	
	echo '<div class="float">
			<h4>Vos informations</h4>
			<p><strong>'. ucfirst($membre_actuel['pseudo']) .'</strong></p>
			<p><strong>'. ucfirst($membre_actuel['email']) .'</strong></p>
		</div>
	
		<div class="float">
			<h4>Votre adresse de facturation</h4>
			
			<article>
				<adress><strong>'. ucfirst($membre_actuel['prenom']) .' '. ucfirst($membre_actuel['nom']) .'<br />'.$membre_actuel['adresse'] .'<br />'.$membre_actuel['cp'] .' '. ucfirst($membre_actuel['ville']) .'</strong>
				</adress>
			</article>
		</div>
	<br />
	<br />
	<br />';
}
echo '</div>
<br />
<br />';		
require_once("inc/footer.inc.php");	
	
?>