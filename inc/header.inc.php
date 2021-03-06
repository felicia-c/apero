<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="Apéro | Le T-shirt qui paye l'apéro">
    <meta name="author" content="Felicia Cuneo">
    <link rel="icon" href="<?php echo RACINE_SITE; ?>images/favicon.ico">

    <title>Apéro ?</title>

    <link href="<?php echo RACINE_SITE; ?>css/custom_style.css" rel="stylesheet">
    <link href="<?php echo RACINE_SITE; ?>js/jquery-1.11.3.min.js" rel="javascript">
    

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

  </head>

  <body>

    <div class="container">
    <div class="bg">
      <div class="header">
        <header>
  <?php  


          echo '<div class="bloc_logo"><a href="'.RACINE_SITE.'index.php"><img src="'.RACINE_SITE.'images/apero_logo.png" id="logo_header" alt="apéro" width="150px"/></a>';
          if(utilisateurEstConnecteEtEstAdmin()|| utilisateurEstConnecteEtEstGerantEtAdmin())
          {
             echo '<img src="'.RACINE_SITE.'images/logo_admin.png" alt="apéro" class="logo_admin" width="100px"/>';
          }
         
         echo '</div>
         <nav>
              <ul class="nav">
                <li><a '; if ($titre_page == 'Accueil') {echo 'class="active"';} echo' href="'.RACINE_SITE.'index.php">accueil</a></li>
                <li><a '; if ($titre_page == 'T-shirts Apéro') {echo 'class="active"';} echo' href="'.RACINE_SITE.'boutique.php">t-shirts</a></li>
                <li><a '; if ($titre_page == 'Bars Apéro') {echo ' class="active"';} echo' href="'.RACINE_SITE.'bars_et_promos.php">bars</a></li>
                <li><a '; if ($titre_page == 'Panier') {echo 'class="active"';} echo' href="'.RACINE_SITE.'panier.php">panier</a></li>';
        if(utilisateurEstConnecte())
        {
          echo '<li><a '; if ($titre_page == 'Profil') {echo 'class="active"';} echo' href="'.RACINE_SITE.'profil.php">profil</a></li>';
        }
       /* if(utilisateurEstConnecteEtEstGerant() || utilisateurEstConnecteEtEstGerantEtAdmin())
        {
          echo '<li><a'; if ($titre_page == 'Mes apéros') {echo ' class="active"';} echo' href="'.RACINE_SITE.'mes_promos.php">mes apéros</a></li>';
        }
*/
        if(utilisateurEstConnecte())
        {
          echo '<li><a href="'.RACINE_SITE.'connexion.php?action=deconnexion">déconnexion</a></li>';
        } 
        else
        {
          echo '<li><a '; if ($titre_page == 'Inscription') {echo 'class="active"';} echo' href="'.RACINE_SITE.'inscription.php">inscription</a></li>
                <li><a '; if ($titre_page == 'Connexion') {echo 'class="active"';} echo' href="'.RACINE_SITE.'connexion.php">connexion</a></li>';
        } 
 
        echo '</ul>';
        
        if(utilisateurEstConnecteEtEstAdmin() || utilisateurEstConnecteEtEstGerantEtAdmin())
        {
            echo'<ul>
                <li><a class="admin no_border '; if ($titre_page == 'Gestion des commandes') {echo 'active';} echo'" href="'.RACINE_SITE.'admin/gestion_commandes.php?orderby=id_commande&affichage=affichage&action=commandes&desc=desc">commandes</a></li>
                <li><a class="admin no_border '; if ($titre_page == 'Gestion des produits') {echo 'active';} echo'" href="'.RACINE_SITE.'admin/gestion_produit.php?orderby=id_produit&affichage=affichage&desc=desc">produits</a></li>
                <li><a class="admin no_border '; if ($titre_page == 'Gestion des membres') {echo 'active';} echo'" href="'.RACINE_SITE.'admin/gestion_membre.php?orderby=id_membre&affichage=affichage&desc=desc">membres</a></li>
                <li><a class="admin no_border '; if ($titre_page == 'Gestion des apéros') {echo 'active';} echo'" href="'.RACINE_SITE.'admin/gestion_promos_bar.php?orderby=id_promo_bar&affichage=affichage&desc=desc">apéros</a></li>
                <li><a class="admin no_border '; if ($titre_page == 'Gestion des promos') {echo 'active';} echo'" href="'.RACINE_SITE.'admin/gestion_promo.php?orderby=id_promo_produit&affichage=affichage&desc=desc">codes promos</a></li>
                <li><a class="admin no_border '; if ($titre_page == 'Gestion des avis') {echo 'active';} echo'" href="'.RACINE_SITE.'admin/gestion_avis.php?orderby=date&affichage=affichage&desc=desc">avis</a></li>
                <li><a class="admin no_border '; if ($titre_page == 'Envoi de Newsletter') {echo 'active';} echo'" href="'.RACINE_SITE.'admin/gestion_newsletter.php">envoi newsletter</a></li>
            </ul>';
        }
        echo '</nav>';
        echo '</header>
        </div>

        <br />
        <div class="section">';
        if(isset($titre_page) && ($titre_page == 'Accueil'))
        {
          echo '<a ';if ($titre_page == "C'est quoi ?") {echo 'class="active"';} echo' href="'.RACINE_SITE.'apero.php">c\'est quoi ?</a>';

        }

       echo '<div class="msg_apero accroche">Le T-shirt qui te paye l\'apéro </div>';
       
        if(isset($titre_page) && $titre_page !== 'Accueil')
        {
          echo '<h1>'.$titre_page.'</h1>';
        }
        
  ?>  
