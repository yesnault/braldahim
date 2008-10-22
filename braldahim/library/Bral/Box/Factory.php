<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Box_Factory {

	public static function getBox($nom, $request, $view, $interne) {
		switch($nom) {
			case "box_profil" :
				return self::getProfil($request, $view, $interne);
				break;
			case "box_echoppe" :
				return self::getEchoppe($request, $view, $interne);
				break;
			case "box_echoppes" :
				return self::getEchoppes($request, $view, $interne);
				break;
			case "box_equipement" :
				return self::getEquipement($request, $view, $interne);
				break;
			case "box_evenements" :
				return self::getEvenements($request, $view, $interne);
				break;
			case "box_communaute" :
				return self::getCommunaute($request, $view, $interne);
				break;
			case "box_famille" :
				return self::getFamille($request, $view, $interne);
				break;
			case "box_vue" :
				return self::getVue($request, $view, $interne);
				break;
			case "box_laban" :
				return self::getLaban($request, $view, $interne);
				break;
			case "box_charrette" :
				return self::getCharrette($request, $view, $interne);
				break;
			case "box_lieu" :
				return self::getLieu($request, $view, $interne);
				break;
			case "box_competences_basiques" :
				return self::getCompetencesBasic($request, $view, $interne);
				break;
			case "box_competences_communes" :
				return self::getCompetencesCommun($request, $view, $interne);
				break;
			case "box_competences_metiers":
				return self::getCompetencesMetier($request, $view, $interne);
				break;
			case "box_metier" :
				return self::getMetier($request, $view, $interne);
				break;
			case "box_messagerie" :
				return self::getMessagerie($request, $view, $interne);
				break;
			default :
				throw new Zend_Exception("getBox::nom invalide :".$nom);
		}
	}

	static function getCommunaute($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Communaute");
		return new Bral_Box_Communaute($request, $view, $interne);
	}

	static function getCompetencesBasic($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Competences");
		return self::getCompetences($request, $view, $interne, "basic");
	}

	static function getCompetencesCommun($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Competences");
		return self::getCompetences($request, $view, $interne, "commun");
	}

	static function getCompetencesMetier($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Competences");
		return self::getCompetences($request, $view, $interne, "metier");
	}

	private static function getCompetences($request, $view, $interne, $type) {
		Zend_Loader::loadClass("Bral_Box_Competences");
		return new Bral_Box_Competences($request, $view, $interne, $type);
	}

	public static function getEchoppe($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Echoppe");
		return new Bral_Box_Echoppe($request, $view, $interne);
	}
	
	public static function getEchoppes($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Echoppes");
		return new Bral_Box_Echoppes($request, $view, $interne);
	}
	
	public static function getEquipement($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Equipement");
		return new Bral_Box_Equipement($request, $view, $interne);
	}

	public static function getEvenements($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Evenements");
		return new Bral_Box_Evenements($request, $view, $interne);
	}

	public static function getErreur($request, $view, $interne, $message) {
		Zend_Loader::loadClass("Bral_Box_Erreur");
		$box = new Bral_Box_Erreur($request, $view, $interne);
		$box->setMessage($message);
		return $box;
	}

	public static function getLaban($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Laban");
		return new Bral_Box_Laban($request, $view, $interne);
	}
	
	public static function getFamille($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Famille");
		return new Bral_Box_Famille($request, $view, $interne);
	}
	
	public static function getCharrette($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Charrette");
		return new Bral_Box_Charrette($request, $view, $interne);
	}

	public static function getLieu($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Lieu");
		return new Bral_Box_Lieu($request, $view, $interne);
	}

	public static function getProfil($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Profil");
		return new Bral_Box_Profil($request, $view, $interne);
	}

	public static function getMetier($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Metier");
		return new Bral_Box_Metier($request, $view, $interne);
	}

	public static function getMessagerie($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Messagerie");
		return new Bral_Box_Messagerie($request, $view, $interne);
	}

	public static function getVue($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Vue");
		return new Bral_Box_Vue($request, $view, $interne);
	}

	public static function getTour($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Tour");
		return new Bral_Box_Tour($request, $view, $interne);
	}
}