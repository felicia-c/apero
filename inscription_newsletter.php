<?php
 require_once("inc/init.inc.php");
 $titre_page = "Newsletter";

//APERO - Felicia Cuneo - 12/2015




require_once("inc/header.inc.php");

echo '<div class="box_info">
		<h3 class="orange">Quoi de neuf chez apéro ?</h3>';
 
formulaireNewsletter();

inscriptionNewsletter($msg);

?>
	<br />
	<br />
	</div>

<?php
require_once("inc/footer.inc.php");
?>