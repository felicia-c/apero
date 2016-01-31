// Compteur 5 secondes page mdp.php
window.onload = Init;
var waitTime = 5; // Temps d'attente en seconde
var url = 'connexion.php';     // Lien de destination
var x;
	function Init() {
	window.document.getElementById('compteur').innerHTML = waitTime;
	x = window.setInterval('Decompte()', 1000);
	}
		function Decompte() {
			((waitTime > 0)) ? (window.document.getElementById('compteur').innerHTML = --waitTime) : (window.clearInterval(x));
			if (waitTime == 0) {
			window.location = url;
			}
		}
		