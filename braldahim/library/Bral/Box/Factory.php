<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Factory {

	public static function getBox($nom, $request, $view, $interne) {
		switch($nom) {
			case "box_banque" :
				return self::getBanque($request, $view, $interne);
				break;
			case "box_blabla" :
				return self::getBlabla($request, $view, $interne);
				break;
			case "box_bpeaux" :
				return self::getBpeaux($request, $view, $interne);
				break;
			case "box_carnet" :
				return self::getCarnet($request, $view, $interne);
				break;
			case "box_cockpit" :
				return self::getCockpit($request, $view, $interne);
				break;
			case "box_charrette" :
				return self::getCharrette($request, $view, $interne);
				break;
			case "box_coffre" :
				return self::getCoffre($request, $view, $interne);
				break;
			case "box_champ" :
				return self::getChamp($request, $view, $interne);
				break;
			case "box_champs" :
				return self::getChamps($request, $view, $interne);
				break;
			case "box_communaute" :
				return self::getCommunaute($request, $view, $interne);
				break;
			case "box_communaute_batiments" :
				return self::getCommunauteBatiments($request, $view, $interne);
				break;
			case "box_communaute_coffre" :
				return self::getCommunauteCoffre($request, $view, $interne);
				break;
			case "box_communaute_evenements" :
				return self::getCommunauteEvenements($request, $view, $interne);
				break;
			case "box_communaute_gestion" :
				return self::getCommunauteGestion($request, $view, $interne);
				break;
			case "box_communaute_membres" :
				return self::getCommunauteMembres($request, $view, $interne);
				break;
			case "box_competences" :
				return self::getCompetences($request, $view, $interne);
				break;
			case "box_contrats" :
				return self::getContrats($request, $view, $interne);
				break;
			case "box_echoppe" :
				return self::getEchoppe($request, $view, $interne);
				break;
			case "box_filatures" :
				return self::getFilatures($request, $view, $interne);
				break;
			case "box_echoppes" :
				return self::getEchoppes($request, $view, $interne);
				break;
			case "box_effets" :
				return self::getEffets($request, $view, $interne);
				break;
			case "box_equipement" :
				return self::getEquipement($request, $view, $interne);
				break;
			case "box_evenements" :
				return self::getEvenements($request, $view, $interne);
				break;
			case "box_famille" :
				return self::getFamille($request, $view, $interne);
				break;
			case "box_hotel" :
				return self::getHotel($request, $view, $interne);
				break;
			case "box_quetes" :
				return self::getQuetes($request, $view, $interne);
				break;
			case "box_interface" :
				return self::getInterface($request, $view, $interne);
				break;
			case "box_laban" :
				return self::getLaban($request, $view, $interne);
				break;
			case "box_lieu" :
				return self::getLieu($request, $view, $interne);
				break;
			case "box_personnage" :
				return self::getPersonnage($request, $view, $interne);
				break;
			case "box_profil" :
				return self::getProfil($request, $view, $interne);
				break;
			case "box_messagerie" :
				return self::getMessagerie($request, $view, $interne);
				break;
			case "box_metier" :
				return self::getMetier($request, $view, $interne);
				break;
			case "box_btabac" :
				return self::getBtabac($request, $view, $interne);
				break;
			case "box_titres" :
				return self::getTitres($request, $view, $interne);
				break;
			case "box_soule" :
				return self::getSoule($request, $view, $interne);
				break;
			case "box_vue" :
				return self::getVue($request, $view, $interne);
				break;
			case "box_vuedetails" :
				return self::getVuedetails($request, $view, $interne);
				break;
			default :
				throw new Zend_Exception("getBox::nom invalide :".$nom);
		}
	}

	static function getBanque($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Banque");
		return new Bral_Box_Banque($request, $view, $interne);
	}

	static function getCoffre($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Banque");
		Zend_Loader::loadClass("Bral_Box_Coffre");
		return new Bral_Box_Coffre($request, $view, $interne);
	}

	static function getChamp($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Champ");
		return new Bral_Box_Champ($request, $view, $interne);
	}

	static function getChamps($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Champs");
		return new Bral_Box_Champs($request, $view, $interne);
	}

	static function getBtabac($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Boutique");
		Zend_Loader::loadClass("Bral_Box_Btabac");
		return new Bral_Box_Btabac($request, $view, $interne);
	}

	static function getCommunaute($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Communaute");
		return new Bral_Box_Communaute($request, $view, $interne);
	}

	static function getCommunauteBatiments($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Communaute_Batiments");
		return new Bral_Box_Communaute_Batiments($request, $view, $interne);
	}

	static function getCommunauteCoffre($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Communaute_Coffre");
		return new Bral_Box_Communaute_Coffre($request, $view, $interne);
	}

	static function getCommunauteEvenements($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Communaute_Evenements");
		return new Bral_Box_Communaute_Evenements($request, $view, $interne);
	}

	static function getCommunauteGestion($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Communaute_Gestion");
		return new Bral_Box_Communaute_Gestion($request, $view, $interne);
	}

	static function getCommunauteMembres($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Communaute_Membres");
		return new Bral_Box_Communaute_Membres($request, $view, $interne);
	}

	static function getCompetences($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Competences");
		return new Bral_Box_Competences($request, $view, $interne);
	}

	public static function getEchoppe($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Echoppe");
		return new Bral_Box_Echoppe($request, $view, $interne);
	}

	public static function getInterface($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Interface");
		return new Bral_Box_Interface($request, $view, $interne);
	}

	public static function getEchoppes($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Echoppes");
		return new Bral_Box_Echoppes($request, $view, $interne);
	}

	static function getCarnet($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Carnet");
		return new Bral_Box_Carnet($request, $view, $interne);
	}
	
	static function getCockpit($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Cockpit");
		return new Bral_Box_Cockpit($request, $view, $interne);
	}

	public static function getBlabla($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Blabla");
		return new Bral_Box_Blabla($request, $view, $interne);
	}

	public static function getContrats($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Contrats_Contrats");
		Zend_Loader::loadClass("Bral_Contrats_Liste");
		return new Bral_Contrats_Liste("liste", $request, $view, "ask");
	}

	public static function getFilatures($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Filatures_Filatures");
		Zend_Loader::loadClass("Bral_Filatures_Liste");
		return new Bral_Filatures_Liste("liste", $request, $view, "ask");
	}

	public static function getEffets($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Effets");
		return new Bral_Box_Effets($request, $view, $interne);
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

	public static function getHotel($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Hotel");
		return new Bral_Box_Hotel($request, $view, $interne);
	}

	public static function getCharrette($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Charrette");
		return new Bral_Box_Charrette($request, $view, $interne);
	}

	static function getQuetes($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Quetes");
		return new Bral_Box_Quetes($request, $view, $interne);
	}

	public static function getLieu($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Lieu");
		return new Bral_Box_Lieu($request, $view, $interne);
	}

	public static function getPersonnage($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Personnage");
		return new Bral_Box_Personnage($request, $view, $interne);
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

	public static function getSoule($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Soule");
		return new Bral_Box_Soule($request, $view, $interne);
	}

	public static function getTitres($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Titres");
		return new Bral_Box_Titres($request, $view, $interne);
	}

	public static function getVue($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Vue");
		return new Bral_Box_Vue($request, $view, $interne);
	}

	public static function getVuedetails($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Vuedetails");
		return new Bral_Box_Vuedetails($request, $view, $interne);
	}

	public static function getTour($request, $view, $interne) {
		Zend_Loader::loadClass("Bral_Box_Tour");
		return new Bral_Box_Tour($request, $view, $interne);
	}
}