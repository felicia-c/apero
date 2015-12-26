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

  <body >

    <div class="container">
      <div class="header">
        <header>
  <?php   
          echo '<a href="'.RACINE_SITE.'index.php"><img src="'.RACINE_SITE.'images/apero_logo.png" id="logo_header" alt="apéro" width="150px"/></a>';

         echo '<nav>
            <ul class="nav">
              <li><a href="'.RACINE_SITE.'index.php">accueil</a></li>
              <li><a href="'.RACINE_SITE.'boutique.php">t-shirts</a></li>
              <li><a href="'.RACINE_SITE.'bars_et_promos.php">bars & promos</a></li>
              <li><a href="'.RACINE_SITE.'panier.php">panier</a></li>';

       
          if(utilisateurEstConnecte())
          {
             echo '<li><a href="'.RACINE_SITE.'profil.php">profil</a></li>
                  <li><a href="'.RACINE_SITE.'connexion.php?action=deconnexion">déconnexion</a></li>';
          } 
          else
          {
              echo '<li><a href="'.RACINE_SITE.'inscription.php">inscription</a></li>
                    <li><a href="'.RACINE_SITE.'connexion.php">connexion</a></li>';
          } 

          if(utilisateurEstConnecteEtEstGerant())
          {
           echo '<li><a href="'.RACINE_SITE.'bar/gestion_promo_bar.php">gérer mes promos</li>';
          }
          if(utilisateurEstConnecteEtEstAdmin())
          {
            echo '<li><a href="'.RACINE_SITE.'admin/gestion_produit.php">gestion produits</a></li>
                  <li><a href="'.RACINE_SITE.'admin/gestion_membre.php">gestion membres</a></li>
                  <li><a href="'.RACINE_SITE.'admin/gestion_bar.php">gestion bars</a></li>
                  <li><a href="'.RACINE_SITE.'admin/gestion_promo.php">gestion promos</a></li>
                   <li><a href="'.RACINE_SITE.'admin/gestion_commandes.php">gestion commandes</a></li>';
          }
 
         echo  '</ul>
          </nav>


        </header>
      </div>
      <br />';
  ?>      
 