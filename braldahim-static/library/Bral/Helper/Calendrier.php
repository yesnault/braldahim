<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_Calendrier {

	public static function getJourSemaine($jour) {

		if ($jour < 0 || $jour > 7) {
			throw new Zend_Exception("getJourSemaine jour invalide:" . $jour);
		}

		$jours = array(
			0 => 'Sunnandaeg',
			1 => 'Monnandaeg',
			2 => 'Tiwesdaeg',
			3 => 'Wodnesdaeg',
			4 => 'Thunresdaeg',
			5 => 'Frigedaeg',
			6 => 'Sæterdaeg',
			7 => 'Sunnandaeg',
		);
		return $jours[$jour];
	}

	public static function affiche($avecSautLigneAnnee = false, $dateAAficher = null) {

		$retour = "";

		if ($dateAAficher == null) {
			$jourSemaine = date('w');
			$numJour = date('z') + 1; // on rajoute 1, la numerotation des jours de l'annee commence à 0
			$annee = date('Y');
		} else {
			$break = explode(" ", $dateAAficher);
			$datebreak = explode("-", $break[0]);
			$time = explode(":", $break[1]);
			$mtime = mktime($time[0], $time[1], $time[2], $datebreak[1], $datebreak[2], $datebreak[0]);
			$jourSemaine = date('w', $mtime);
			$numJour = 1 + date('z', $mtime);
			$annee = date('Y', $mtime);
		}


		// l'an 401 correspond à l'an 2008, soit 1607 années de différence
		$annee = $annee - 1607;
		$anneeTexte = "";

		if ($avecSautLigneAnnee) {
			$anneeTexte .= "<br/>";
		}

		$anneeTexte .= "Année " . $annee . " du Second Âge";

		if ($numJour == 1 || $numJour == 365 || $numJour == 366) {
			$retour .= "Yule";
		} elseif ($numJour == 182 || $numJour == 184) {
			$retour .= "Lithe";
		} elseif ($numJour == 183) {
			$retour .= "Jour du milieu";
		} else {
			$mois = self::getMois($numJour);
			$retour .= self::getJour($numJour, $mois["numero"]) . " " . self::getJourSemaine($jourSemaine) . " " . $mois["texte"];
		}

		return $retour . ", " . $anneeTexte;
	}

	private static function getJour($jour, $numMois) {
		$retour = "";

		if ($jour == 1 || $jour == 182 || $jour == 184 || $jour == 183 || $jour == 365 || $jour == 366) {
			$retour = "";
		} else {
			if ($jour < 182) {
				$retour = $jour - 1 - ($numMois * 30);
			} else {
				$retour = $jour - 4 - ($numMois * 30);
			}
		}

		return $retour;
	}

	private static function getMois($jour) {
		$retour = "";

		if ($jour <= 31) {
			$texte = "Après-Yule";
			$num = 0;
		} else if ($jour <= 61) {
			$texte = "Solmath";
			$num = 1;
		} else if ($jour <= 91) {
			$texte = "Rethe";
			$num = 2;
		} else if ($jour <= 121) {
			$texte = "Astron";
			$num = 3;
		} else if ($jour <= 151) {
			$texte = "Thrimidge";
			$num = 4;
		} else if ($jour <= 184) {
			$texte = "Avant-Lithe";
			$num = 5;
		} else if ($jour <= 214) {
			$texte = "Après-Lithe";
			$num = 6;
		} else if ($jour <= 244) {
			$texte = "Wedmath";
			$num = 7;
		} else if ($jour <= 274) {
			$texte = "Halimath";
			$num = 8;
		} else if ($jour <= 304) {
			$texte = "Winterfilth";
			$num = 9;
		} else if ($jour <= 334) {
			$texte = "Blotmath";
			$num = 10;
		} else if ($jour <= 366) {
			$texte = "Avant-Yule";
			$num = 11;
		}

		$retour["numero"] = $num;
		$retour["texte"] = $texte;

		return $retour;
	}

}
