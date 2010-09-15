<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Helper_Lune {

	const PHASE_NOUVELLE_LUNE = 1;
	const PHASE_ASCENDENTE = 2;
	const PHASE_PLEINE_LUNE = 3;
	const PHASE_DESCENDENTE = 4;
	

	/*
	 *
	 Zend_Loader::loadClass("Bral_Util_Lune");
	 list($MoonPhase, $MoonAge, $MoonDist, $MoonAng, $SunDist, $SunAng, $mpfrac) = Bral_Util_Lune::calculPhase(2009, 04, 17, 13, 38, 01);
	 echo "La Lune est éclairée $MoonPhase à ".number_format($MoonPhase*100, 2, ',', '')."%"."<br>";
	 echo "Son age est de ".number_format($MoonAge, 0, ',', '')." jours"."<br>";
	 echo "Et elle se situe à une distance de ".number_format($MoonDist, 0, ',', '')." km par rapport à la Terre."."<br>";


	 list($MoonPhase, $MoonAge, $MoonDist, $MoonAng, $SunDist, $SunAng, $mpfrac) = Bral_Util_Lune::calculPhase(2009, 04, 2, 14, 33, 01);
	 echo "La Lune est éclairée $mpfrac à ".number_format($MoonPhase*100, 2, ',', '')."%"." => premier<br>";

	 list($MoonPhase, $MoonAge, $MoonDist, $MoonAng, $SunDist, $SunAng, $mpfrac) = Bral_Util_Lune::calculPhase(2009, 04, 9, 14, 55, 01);
	 echo "La Lune est éclairée $mpfrac à ".number_format($MoonPhase*100, 2, ',', '')."%"." => pleine <br>";

	 list($MoonPhase, $MoonAge, $MoonDist, $MoonAng, $SunDist, $SunAng, $mpfrac) = Bral_Util_Lune::calculPhase(2009, 04, 17, 13, 38, 01);
	 echo "La Lune est éclairée $mpfrac à ".number_format($MoonPhase*100, 2, ',', '')."%"." => dernier <br>";

	 list($MoonPhase, $MoonAge, $MoonDist, $MoonAng, $SunDist, $SunAng, $mpfrac) = Bral_Util_Lune::calculPhase(2009, 04, 25, 13, 23, 01);
	 echo "La Lune est éclairée $mpfrac à ".number_format($MoonPhase*100, 2, ',', '')."%"." nouvelle <br>";

	 */

	public static function afficheSuivantDate($annee, $mois, $jour, $heure, $mine, $seconde) {
		return  self::afficheImg($annee, $mois, $jour, $heure, $mine, $seconde);
	}

	public static function affiche() {
		$annee = date('Y');
		$mois = date('m');
		$jour = date('d');
		$heure = date('H');
		$mine = date('i');
		$seconde = date('s');

		return  self::afficheImg($annee, $mois, $jour, $heure, $mine, $seconde);
	}

	private static function afficheImg($annee, $mois, $jour, $heure, $mine, $seconde) {
		Zend_Loader :: loadClass("Bral_Util_Lune");
		list($moonPhase, $moonAge, $moonDist, $moonAng, $sunDist, $sunAng, $mpfrac) = Bral_Util_Lune::calculPhase($annee, $mois, $jour, $heure, $mine, $seconde);

		$titre = "La Lune au ";
		$titre .= Bral_Helper_Calendrier::affiche(true, $annee."-".$mois."-".$jour." ".$heure.":".$mine.":".$seconde);

		$age = floor($moonAge);
		$s = '';
		if ($age > 1) {
			$s = 's';
		}

		$libelle = '';
		if ($mpfrac >= 0.99 || $mpfrac <= 0.1) {
			$libelle = 'une Nouvelle Lune';
		} elseif ($mpfrac > 0.1 && $mpfrac < 0.24) { // premier quartier
			$libelle = 'une Lune croissante';
		} elseif ($mpfrac >= 0.24 && $mpfrac <= 0.26) { // premier quartier
			$libelle = 'le Premier quartier';
		} elseif ($mpfrac > 0.26 && $mpfrac < 0.49) {
			$libelle = 'une Lune gibbeuse croissante';
		} elseif ($mpfrac >= 0.49 && $mpfrac <= 0.51) { // pleine lune
			$libelle = 'une Pleine Lune';
		} elseif ($mpfrac > 0.51 && $mpfrac < 0.74) {
			$libelle = 'une Lune gibbeuse décroissante';
		} elseif ($mpfrac >= 0.74 && $mpfrac <= 0.76) { // Dernier quartier
			$libelle = 'le Dernier quartier';
		} elseif ($mpfrac > 0.76 && $mpfrac < 0.99) { //
			$libelle = 'une Lune décroissante';
		}

		$texte = "<br>Son âge est de ".floor($moonAge)." jour".$s."<br>";
		$texte .= "C'est ".$libelle." (indice : ".floor($mpfrac * 100)." %)<br>";

		$retour = "<span class='lune lune".floor($moonAge)."'>".Bral_Helper_Tooltip::render($texte, $titre)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
		return $retour;
	}

	public static function calculPhase() {
		$annee = date('Y');
		$mois = date('m');
		$jour = date('d');
		$heure = date('H');
		$mine = date('i');
		$seconde = date('s');

		Zend_Loader :: loadClass("Bral_Util_Lune");
		list($moonPhase, $moonAge, $moonDist, $moonAng, $sunDist, $sunAng, $mpfrac) = Bral_Util_Lune::calculPhase($annee, $mois, $jour, $heure, $mine, $seconde);

		if ($mpfrac >= 0.99 || $mpfrac <= 0.1) {
			$retour = self::PHASE_NOUVELLE_LUNE;
		} elseif ($mpfrac > 0.1 && $mpfrac < 0.49) {
			$retour = self::PHASE_ASCENDENTE;
		} elseif ($mpfrac >= 0.49 && $mpfrac <= 0.51) { // pleine lune
			$retour = self::PHASE_PLEINE_LUNE;
		} elseif ($mpfrac > 0.51 && $mpfrac < 0.99) { //
			$retour = self::PHASE_DESCENDENTE;
		}

		return $retour;
	}

}
