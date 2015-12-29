<?php
/*********************************FONCTIONS DIVERS****************************************/

//-- Execution de requete SQL
function executeRequete($req)
{
	global $mysqli; //permet d'avoir la variable dans l'environnement local de la fonction 
	$resultat = $mysqli->query($req); //on execute la requete recue en argument
	if(!$resultat)	 //equivalent à if($resultat == FALSE) // si c'est le cas alors il y a une errreur de requete
	{
		die ("Erreur sur la requete SQL<br />Message :".$mysqli->error . '<br />Code: '.$req); // S'il y a eu une erreur sur la requete on affiche tout
	}
	return $resultat; //on retourne l'objet issu de la classe mysqli _result qui contient le resultat de la requete
}
//----------------------------------------

//-- Debug : Var_dump ou print_r
function debug($var, $mode = 1)
{
	echo '<div>';
	if($mode === 1) //si $mode vaut 1...
	{
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
	}
	else{
		echo '<pre>';
		print_r($var);
		echo '</pre>';
	}
	echo '</div>';
	return;
}


// PAGINATION + TRI
//-- Pagination et tri des pages gestion (requete simple sur 1 table)
function paginationGestion($items_par_page, $table, $resultat) // attend 2 arguments obligatoires : le nombre de produits par page et la table 
{	
	$nbre_items = executeRequete("SELECT COUNT(*) AS total FROM $table");
	$items_dispo = $nbre_items -> fetch_assoc();

	$total_items = $items_dispo['total'];

	$nbre_pages = ceil($total_items/$items_par_page);

	if(isset($_GET['page']))
	{
		$page_actuelle = intval($_GET['page']);
		
		if($page_actuelle > $nbre_pages)
		{
			$page_actuelle = $nbre_page;
		}
	}
	else
	{
		$page_actuelle = 1;
		
	}
	
	$premiere_entree= abs(($page_actuelle-1)*$items_par_page); // On calcule la première entrée à lire

	if(isset($_GET['orderby']))
	{
		$orderby = $_GET['orderby'];
		$resultat .= " ORDER BY $orderby";
	}
	if(isset($_GET['asc']))
	{
		$resultat .= " ASC";
	}
	if(isset($_GET['desc']))
	{
		$resultat .= " DESC";
	}
	
	$resultat .= " LIMIT $premiere_entree, $items_par_page";
	
	return $resultat;
}



// Affichage de la pagination (avec tri) sur 1 table
function affichagePaginationGestion($items_par_page, $table, $lien) // arguments obligatoires : items par page et table + lien à concatener
{	
	$nbre_items = executeRequete("SELECT COUNT(*) AS total FROM $table");
	$items_dispo = $nbre_items -> fetch_assoc();
	$total_items = $items_dispo['total'];
	$nbre_pages = ceil($total_items/$items_par_page);
	if(isset($_GET['page']))
	{
		$page_actuelle = intval($_GET['page']);
		if($page_actuelle > $nbre_pages)
		{
			$page_actuelle = $nbre_page;	
		}
	}
	else
	{
		$page_actuelle = 1;
	}
	// Pagination affichage
	echo '<p align="center">Page: ';

	for($i = 1; $i <= $nbre_pages; $i ++ ) 
	{
		if($i == $page_actuelle)
		{
			echo ' [ '.$i.' ] ';	
		}
		else //liens page 1, 2, etc
		{
			if(isset($lien))
			{
				echo $lien;
			}
			else
			{
				echo '<a href="?affichage=affichage&';
			}
		
			if(isset($_GET['orderby']))
			{
				$orderby = $_GET['orderby'];
				echo 'orderby='.$orderby;
			}
			if(isset($_GET['asc']))
			{
				echo '&asc=asc&';
			}
			if(isset($_GET['desc']))
			{
				echo '&desc=desc&';
			}

			echo 'page='.$i.'"> '.$i.' </a>';
		}
	}
	echo '</p>';
}


// Fonction pagination pour la page recherche
function paginationRecherche($items_par_page, $resultat)
{
	$items = executeRequete($resultat);
	$nbre_items = $items -> num_rows;

	$total_items = $nbre_items;

	$nbre_pages = ceil($total_items/$items_par_page);
	if(isset($_GET['page']))
	{
		$page_actuelle = intval($_GET['page']);
		if($page_actuelle > $nbre_pages)
		{
			$page_actuelle = $nbre_page;	
		}
	}
	else
	{
		$page_actuelle = 1;
	}
	$premiere_entree= abs(($page_actuelle-1)*$items_par_page); // On calcul la première entrée à lire

	if(isset($_GET['orderby']))
	{
		$orderby = $_GET['orderby'];
		$resultat .= " ORDER BY $orderby";
	}
	if(isset($_GET['asc']))
	{
		$resultat .= " ASC";
	}
	if(isset($_GET['desc']))
	{
		$resultat .= " DESC";
	}
	
	$resultat .= " LIMIT $premiere_entree, $items_par_page";
	
	return $resultat;
}



// Pagination recherche (avec num_rows sur la requete concaténée de recherche)
function affichagePaginationRecherche($items_par_page, $req) // arguments obligatoires : items par page et requete
{	
	$items = executeRequete($req);
	$nbre_items = $items -> num_rows;

	$total_items = $nbre_items;

	$nbre_pages = ceil($total_items/$items_par_page);
	if(isset($_GET['page']))
	{
		$page_actuelle = intval($_GET['page']);
		if($page_actuelle > $nbre_pages)
		{
			$page_actuelle = $nbre_page;	
		}
	}
	else
	{
		$page_actuelle = 1;
	}
			// Pagination affichage
	echo '<p align="center">Page: '; 

	for($i = 1; $i <= $nbre_pages; $i ++ ) 
	{
		if($i == $page_actuelle)
		{
			echo ' [ '.$i.' ] ';	
		}
		else   //liens page 1, 2, etc
		{
			echo '<a href="?';
				if(isset($_GET['orderby']))
				{
					$orderby = $_GET['orderby'];
					echo 'orderby='.$orderby;
				}
				elseif(isset($_GET['asc']))
				{
					echo '&asc=asc&';
				}
				elseif(isset($_GET['desc']))
				{
					echo '&desc=desc&';
				}
			echo '&page='.$i.'"> '.$i.' </a>';
		}
	}
	echo '</p>';
}

/*********************************FIN FONCTIONS DIVERS*****************************/


/********************************FONCTIONS USER***********************************/

function utilisateurEstConnecte()
{
	//cette fonction vérifie si l'utilisateur est connecté à une session
	if(!isset($_SESSION['utilisateur']))
	{

		return FALSE;
	}
	else
	{
		return TRUE;
	}
}

//---------------

function utilisateurEstConnecteEtEstAdmin()
{
	if(utilisateurEstConnecte() && $_SESSION['utilisateur']['statut'] == 1) //on controle s'il est bien connecte et en plus si son statut est celui de l'admin (ici statut à 1 = admin)
	{
		return TRUE; 
	}
	else{
		return FALSE;
	}
}

// -----------------

function utilisateurEstConnecteEtEstGerant()
{
	if(utilisateurEstConnecte() && $_SESSION['utilisateur']['statut'] == 3)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}

}

/******************************FIN FONCTIONS USER**********************************/

/******************************FONCTIONS ADMIN*******************************/

function verificationExtensionPhoto()
{
	$extension = strrchr($_FILES['photo']['name'], '.'); //permet de retourner le texte contenu apres le . (fourni en 2e argument) en partant de la fin. Si le nom du fichier est pantalon.jpg => on récupere .jpg
	$extension = strtolower(substr($extension, 1)); //nous coupons le point avec substr et strtolower transforme d'eventuelles majuscules en minuscules
	$tab_extension_valide = array("gif", "jpg", "jpeg", "png"); //on declare un tableau array contenant les extension que nous autorisons
	$verif_extension = in_array($extension, $tab_extension_valide); //in_array vérifie si la valeur du premier argument correspond à une des valeurs du tableau array. si c'est le cas $verif_extension contiendra TRUE ou FALSE
	return $verif_extension; //on retourne le resultat qui sera soit TRUE soit FALSE
}



/******************************FIN FONCTIONS ADMIN*******************************/

/*******************************FONCTIONS PANIER *********************************/

//fonction qui crée le panier 
//nous créons dans la session un tableau array panier qui contiendra 4 tableaux array (prix, id, quantite, titre) = chaque info est représentée dans un tableau array, chaque produit ajouté au panier rajoutera une ligne dans chacun des tableaux array

function creationDuPanier()
{
		if(!isset($_SESSION['panier'])) //si le panier n'existe pas
		{
			$_SESSION['panier'] = array();
			$_SESSION['panier']['id_produit']= array();
			$_SESSION['panier']['quantite']= array();
			$_SESSION['panier']['prix']= array();

			$_SESSION['panier']['prix_reduit']= array();
			
			// PROMO
			$_SESSION['panier']['promo']= array();
			$_SESSION['panier']['promo']['code_promo']= array();
			$_SESSION['panier']['promo']['id_promo']= array();
		}
	return TRUE;
}


//appliquer une promo
/*
function appliquerUnePromoAuProduit($id_promo, $id_produit, $prix_reduit)
{
	$msg ="";
	$prix_reduit = NULL;
	$resultat = executeRequete("SELECT produit.id_produit,
											produit.prix,
											produit.id_promo_produit AS id_promo_produit,
											produit.prix AS prix, 
											pp.id_promo_produit AS id_promo, 
											pp.reduction AS reduction, 
											pp.code_promo AS code_promo
									FROM produit 
									INNER JOIN promo_produit pp ON produit.id_promo_produit = pp.id_promo_produit 
									WHERE produit.id_produit = '$id_produit' AND produit.id_promo_produit='$id_promo'");
	if($resultat) //si la promo session correspond a la promo du produit
	{
		$infos = $resultat->fetch_assoc(); 

		$prix_ttc = $infos['prix'] * 1.2;
		
		$prix_reduit = ($prix_ttc -= ($prix_ttc * ($infos['reduction'] / 100)));  // calcule du pourcentage de réduction	
		$prix_reduit = round($prix_reduit, 2);

		//$prix_reduit -= $prix_ttc * ($infos['reduction'] / 100);  // calcule du pourcentage de réduction
			return $prix_reduit;
			return TRUE; 
	}
	else
	{
		return FALSE;
	}
}

*/

// fonction permettant d'appliquer un code promo aux produits du panier correspondants
 
 function appliquerUnCodePromoAuPanier($code_promo)
 {
 	$msg ="";
	$verif_caractere = preg_match('#^[a-zA-Z0-9._-]+$#', $code_promo); //retourne FALSE si mauvais caracteres dans $_POST['pseudo'], sinon TRUE
	if(!$verif_caractere && !empty($_POST['code_promo']))
	{
		$msg .= '<div class="msg_erreur" >Caractères acceptés: A à Z et 0 à 9</div>';  
	}
	else
	{
		if(empty($msg))
		{
			//par securité on s'assure que le code promo existe bien dans la table promotion
			$resultat = executeRequete("SELECT * FROM promo_produit WHERE code_promo = '$code_promo' ");

			if($resultat) //si le code promo existe dans la table promotion, on vérifie qu'il est bien associé au produit (dans la table produit) 
			{	
				$infos_promo = $resultat->fetch_assoc(); // on récupere les infos 
				
				$_SESSION['panier']['promo']['code_promo'][] = $infos_promo['code_promo'];
				$id_promo = $_SESSION['panier']['promo']['id_promo'][] = $infos_promo['id_promo_produit']; // ENREGISTREMENT EN SESSION OK
				
				$nb_produit = count($_SESSION['panier']['id_produit']); 
				
				for($i = 0; $i < $nb_produit; $i++)
				{	
					$id_mon_produit = $_SESSION['panier']['id_produit'][$i];
					//recherche des id_promo et prix dans la table produit pour chaque produit du panier 
					
				/*	if(appliquerUnePromoAuProduit($id_promo, $id_mon_produit, $prix_reduit))
					{
						$_SESSION['panier']['prix_reduit'][] = $prix_reduit; //On stocke le prix reduit dans la session
						$msg .= '<div class="msg_success" >La promotion a été appliquée</div>';
					}
					else
					{
						$_SESSION['panier']['prix_reduit'][$position_produit] = NULL;
					}*/
					$resultat = executeRequete("SELECT id_produit, id_promo_produit, prix FROM produit WHERE id_produit = '$id_mon_produit' "); 
					$infos_mon_produit = $resultat->fetch_assoc();
					
					if($infos_mon_produit['id_promo_produit'] == $infos_promo['id_promo_produit']) // si les id_promo session et bdd sont =
					{
						$prix_ttc = $infos_mon_produit['prix'] * 1.2;
						$prix_reduit = ($prix_ttc-= $prix_ttc * ($infos_promo['reduction'] / 100));  // calcule du pourcentage de réduction
						
						$prix_reduit = round($prix_reduit, 2);
						
						//$position_produit = array_search($infos_mon_produit['id_produit'], $_SESSION['panier']['id_produit']);
						//unset($_SESSION['panier']['prix_reduit'][$i]);
						$_SESSION['panier']['prix_reduit'][$i] = $prix_reduit; //On stocke le prix reduit dans la session
						$msg .= '<div class="msg_success" >La promotion a été appliquée</div>';
					}	
					
				}

			}
			else
			{
				//$_SESSION['panier']['prix_reduit'][$position_produit] = NULL;
				$msg .= '<div class="msg_erreur" >Le code promo saisit n\'est pas valide !</div>';
			}
		}
	}
	
 }

 
 
 
// AJOUTER UN ARTICLE AU PANIER

function ajouterArticleDansPanier($id_produit, $quantite, $prix)
{
	$msg="";
	//on vérifie que le produit n'est pas déjà présent dans le panier en cours (si déjà present -> on affiche un message d'erreur)
	$position_produit = array_search($id_produit, $_SESSION['panier']['id_produit']); //la fonction ARRAY_SEARCH() nous donne l'index où se trouve le produit que l'on cherche. array_search a besoin de 2 arguments (quoi, où)
	
	 //var_dump($position_produit);
	
	if($position_produit !==FALSE) // = si l'article a été trouvé 
	{
		$_SESSION['panier']['quantite'][$position_produit] += $quantite;  //on augmente la quantité	
	}
	else //sinon c'est donc un nouvel ajout
	{	
		$_SESSION['panier']['id_produit'][] = $id_produit;
		$_SESSION['panier']['quantite'][]= $quantite;
		$_SESSION['panier']['prix'][] = round($prix, 2);
		//$_SESSION['panier']['prix_reduit'][] = NULL;
		
		if(isset($_SESSION['panier']['promo']['id_promo'][0]))//si un id promo existe dans la session
		{	
			$id_promo = $_SESSION['panier']['promo']['id_promo'][0];
			$resultat = executeRequete("SELECT produit.id_produit,
											produit.prix,
											produit.id_promo_produit AS id_promo_produit,
											produit.prix AS prix, 
											pp.id_promo_produit AS id_promo, 
											pp.reduction AS reduction, 
											pp.code_promo AS code_promo
									FROM  promo_produit pp 
									INNER JOIN produit ON pp.id_promo_produit  = produit.id_promo_produit 
									WHERE produit.id_produit = '$id_produit' AND produit.id_promo_produit='$id_promo'");
			$infos = $resultat->fetch_assoc(); 
			if($infos) //si la promo session correspond a la promo du produit
			{
				//$prix_ttc = $infos['prix'] * 1.2;
				$prix_reduit = ($prix -= ($prix * ($infos['reduction'] / 100)));  // calcule du pourcentage de réduction	
				$prix_reduit = round($prix_reduit, 2);

				//$prix_reduit -= $prix_ttc * ($infos['reduction'] / 100);  // calcule du pourcentage de réduction
				$_SESSION['panier']['prix_reduit'][] = $prix_reduit; //On stocke le prix reduit dans la session
			}
			else // si la promo ne correspond pas
			{
				$_SESSION['panier']['prix_reduit'][] = NULL;
			}
		}
		else //si il n'y a pas de promo dans le panier
		{
			$_SESSION['panier']['prix_reduit'][] = NULL;
		}
	}
}




// fonction permettant d'obtenir le montant total HT (hors promo)

function totalHt()
{
	$total = 0; // On prepare la variable afin de ne pas avoir d'erreur undefined lors de l'ajout des valeurs dans cette variable
	$nb_de_produits = count($_SESSION['panier']['id_produit']);
	
	
	for($i = 0; $i < $nb_de_produits; $i++)
	{	
		$id_produit = $_SESSION['panier']['id_produit'][$i];
		
		$resultat_prix = executeRequete("SELECT prix FROM produit WHERE id_produit = '$id_produit'");
		$prix_produit = $resultat_prix-> fetch_assoc();
		$prix_produit = $prix_produit['prix'] * $_SESSION['panier']['quantite'][$i];

		$total += $prix_produit;
	}	
	return round($total, 2); //prix total du panier arrondi à 2 chiffres apres la virgule
} 




// fonction permettant d'obtenir le montant total avec promo des produits 

function montantTotal()
{
	$total = 0;
	$nb_de_produits = count($_SESSION['panier']['id_produit']);
		for($i=0; $i < $nb_de_produits; $i++)
		{
			if(isset($_SESSION['panier']['prix_reduit'][$i]))
			{
				$total_produit = $_SESSION['panier']['prix_reduit'][$i] * $_SESSION['panier']['quantite'][$i];
			}
			else
			{
				$total_produit = $_SESSION['panier']['prix'][$i] * $_SESSION['panier']['quantite'][$i];
			}
			$total += $total_produit; //on multiplie la quantité par le prix de chaque produit			
		}
		return round($total, 2); //prix total du panier arrondi à 2 chiffres apres la virgule
} 

// fonction permettant d'obtenir le total de la TVA du panier (hors promo)
function totalTva()
{
	$total_tva = totalHt() * 0.2;
	return  round($total_tva, 2);
}

//  fonction permettant d'obtenir le total ttc du panier (hors promo)
function totalTtc()
{
	$total_ttc = totalHt() + totalTva(); 
	return round($total_ttc, 2);
}


// fonction permettant de retirer 1 produit du panier

function retirerUnArticleDuPanier($produit_a_supprimer)
{
	$position_produit = array_search($produit_a_supprimer, $_SESSION['panier']['id_produit']); // retourne un chiffre correspondant à l'indice du tableau array ou se trouve cette valeur(1er argument fourni), sinon renvoi FALSE
	if($position_produit !== FALSE) // SI le produit est present dans le panier
	{
		array_splice($_SESSION['panier']['id_produit'], $position_produit, 1);
		array_splice($_SESSION['panier']['quantite'], $position_produit, 1);
		array_splice($_SESSION['panier']['prix'], $position_produit, 1);
		//if(isset($_SESSION['panier']['prix_reduit'][$position_produit]))     //SI Promo appliquée au produit, on retire aussi le prix réduit
		//{
			array_splice($_SESSION['panier']['prix_reduit'], $position_produit, 1);
		//}

		
		// ARRAY_SPLICE (à ne pas confondre avec array_slice) permet de retirer un element du tableau array et de réordoner les indices du tableau afin de ne pas avoir de trou dans le tableau, et ne pas faire d'incohérences dans le reste de nos affichages/traitements
	}
}

/////// GENERER UN NOUVEAU MDP //////


// Génération d'une chaine aléatoire
function chaine_aleatoire($nb_car, $chaine = 'azertyuiopqsdfghjklmwxcvbn123456789AZERTYUIOPQSDFGHJKLMWXCVBN')
{
	$nb_lettres = strlen($chaine) - 1;
	$generation = '';
	for($i=0; $i < $nb_car; $i++)
	{
		$pos = mt_rand(0, $nb_lettres);
		$car = $chaine[$pos];
		$generation .= $car;
	}
	return $generation;
}


	
/////////DATES //////

///Fonction affichant la date et l'heure en format fr sans les secondes, avec une chaine de caractere pouvant correspondre à ' au '  ou  ' - '
// Cette fonction attend 3 parametres : 2 dates au format datetime (BDD) et 1 chaine de caracteres

function AfficheDateFr($date1, $date2, $stringAuChoix)
{
	$date = date_create_from_format('Y-m-d H:i:s', $date1);
	echo date_format($date, 'd/m/Y H:i'). $stringAuChoix; 
					
	$date = date_create_from_format('Y-m-d H:i:s', $date2);
	echo date_format($date, 'd/m/Y H:i'); 
}


//fonction permettant de verifier si la date d'arrivée entrée et bien antérieure à la date de départ (page gestion produits / ajout /modif)				
function coherenceDates($date1, $date2)
{
	//$date1 = new DateTime($date1);
	//$date2 = new DateTime($date2);
	
	
	// $date1 = date_create_from_format('d/M/Y H:i:s', $date1_str);
	// $date2 = date_create_from_format('d/M/Y H:i:s', $date2_str);
	
	$date1 = strtotime($date1);
	$date2 = strtotime($date2);
	$date1 = time($date1);
	$date2 = time($date2);

	if($date1 < $date2)
	{
		return TRUE;
	}
}

//// VIEW ////
function afficheProduits($req)
{
	$resultat = executeRequete($req);
	while($mon_produit = $resultat -> fetch_assoc())
	{
		echo '<div class ="produit">
				<img src="'. $mon_produit['photo'].'" style=" width: 200px; max-width: 100%;" />
				<h3>'. $mon_produit['titre'] .'</h3>
				<p>'. $mon_produit['prix'] * 1.2.' €</p>
				<a href="fiche_produit.php?id_produit='. $mon_produit['id_produit'].'" class="btn ">Voir la fiche</a>
			</div>';
	}
}



