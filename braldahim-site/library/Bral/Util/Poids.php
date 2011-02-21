<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Poids {

	const POIDS_CASTARS = 0.001;

	// la peau et la viande ont le meme poids. Si cela change, çà impactera depiauter, methode preCalculPoids()
	const POIDS_PEAU = 0.3;
	const POIDS_VIANDE = 0.3;

	const POIDS_RATION = 0.25;
	const POIDS_CUIR = 0.4;
	const POIDS_FOURRURE = 0.4;
	const POIDS_PLANCHE = 2;
	const POIDS_RONDIN = 3;
	const POIDS_RUNE = 0.05;
	const POIDS_POTION = 0.3;
	const POIDS_MINERAI = 0.6;
	const POIDS_POIGNEE_GRAINES = 0.01;
	const POIDS_LINGOT = 1;
	const POIDS_PARTIE_PLANTE_BRUTE = 0.002;
	const POIDS_PARTIE_PLANTE_PREPAREE = 0.003;
	const POIDS_MUNITION = 0.04;
	const POIDS_TABAC = 0;
	const POIDS_BIERE = 0.3;

	function __construct() {
	}

	/**
	 * Retourne un tableau avec les cles suivantes:
	 * nb_rondins_presents
	 * nb_rondins_transportables
	 */
	public static function calculPoidsCharretteTransportable($idBraldun, $niveauVigueur) {
		Zend_Loader::loadClass('Charrette');

		$tabCharrette = null;
		$charretteTable = new Charrette();
		$charrette = $charretteTable->findByIdBraldun($idBraldun);
		$tabRondins = null;

		foreach ($charrette as $c) {
			$nbRondinsTransportables = (floor($niveauVigueur / 10) * 20) + 20;
			if ($nbRondinsTransportables < 20) {
				$nbRondinsTransportables = 20;
			}
			$tabRondins = array(
				"nb_rondins_presents" => $c["quantite_rondin_charrette"],
				"nb_rondins_transportables" => $nbRondinsTransportables,
			);
			break;
		}
		return $tabRondins;
	}

	public static function calculPoidsTransportable($niveauForce) {
		return (2 * $niveauForce) + 3;
	}

	public static function calculPoidsCharrette($idBraldun, $updateBase = false) {
		$tab["transporte"] = 0;
		$tab["transportable"] = 0;

		Zend_Loader::loadClass("Charrette");
		$charretteTable = new Charrette();
		$charretteRowset = $charretteTable->findByIdBraldun($idBraldun);

		if ($charretteRowset == null || count($charretteRowset) == 0) {
			return null;
		} else if (count($charretteRowset) > 1) {
			throw new Zend_Exception("calculPoidsCharretteTransporte Nb Charrette invalide idh:".$idBraldun. " n:".count($charretteRowset));
		} else {
			$charrette = $charretteRowset[0];
		}

		$suffixe = "charrette";
		$nomTable = Bral_Util_String::firstToUpper($suffixe);

		if ($updateBase == true) {
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElement($charrette, $nomTable, $suffixe);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementMinerais($charrette["id_charrette"], $nomTable, $suffixe);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementPartiesPlantes($charrette["id_charrette"], $nomTable, $suffixe);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementEquipement($charrette["id_charrette"], $nomTable, $suffixe);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementPotion($charrette["id_charrette"], $nomTable, $suffixe);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementAliment($charrette["id_charrette"], $nomTable, $suffixe);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementGraine($charrette["id_charrette"], $nomTable, $suffixe);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementIngredient($charrette["id_charrette"], $nomTable, $suffixe);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementRune($charrette["id_charrette"], $nomTable, $suffixe);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementMunitions($charrette["id_charrette"], $nomTable, $suffixe);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementMateriel($charrette["id_charrette"], $nomTable, $suffixe);

			$tab["transportable"] = $charrette["poids_transportable_charrette"];

			$data = array(
				"poids_transporte_charrette" => $tab["transporte"],
			);
			$where = "id_charrette = ".$charrette["id_charrette"];
			$charretteTable->update($data, $where);
		} else {
			$tab["transporte"] = $charrette["poids_transporte_charrette"];
			$tab["transportable"] = $charrette["poids_transportable_charrette"];
		}

		$tab["place_restante"] = $tab["transportable"] - $tab["transporte"];
		if ($tab["place_restante"] < 0) {
			$tab["place_restante"] = 0;
		}

		return $tab;
	}

	// $idBraldun => -1 pour un nouvel braldun
	public static function calculPoidsTransporte($idBraldun, $castars) {
		$retour = 0;
		$retour = self::ajoute($retour, $castars, self::POIDS_CASTARS);

		$suffixe = "laban";
		$nomTable = Bral_Util_String::firstToUpper($suffixe);

		if ($idBraldun > 0) {
			Zend_Loader::loadClass("Laban");
			$labanTable = new Laban();
			$laban = $labanTable->findByIdBraldun($idBraldun);
			if (count($laban) != 1) {
				return $retour; // pour les PNJ
			}
			$laban = $laban[0];
				
			$retour = $retour + self::calculPoidsTransporteElement($laban, $nomTable, $suffixe);
			$retour = $retour + self::calculPoidsTransporteElementMinerais($idBraldun, $nomTable, $suffixe);
			$retour = $retour + self::calculPoidsTransporteElementPartiesPlantes($idBraldun, $nomTable, $suffixe);
			$retour = $retour + self::calculPoidsTransporteElementEquipement($idBraldun, $nomTable, $suffixe);
			$retour = $retour + self::calculPoidsTransporteElementPotion($idBraldun, $nomTable, $suffixe);
			$retour = $retour + self::calculPoidsTransporteElementAliment($idBraldun, $nomTable, $suffixe);
			$retour = $retour + self::calculPoidsTransporteElementRune($idBraldun, $nomTable, $suffixe);
			$retour = $retour + self::calculPoidsTransporteElementMunitions($idBraldun, $nomTable, $suffixe);
			$retour = $retour + self::calculPoidsTransporteElementMateriel($idBraldun, $nomTable, $suffixe);
			$retour = $retour + self::calculPoidsTransporteElementGraine($idBraldun, $nomTable, $suffixe);
			$retour = $retour + self::calculPoidsTransporteElementIngredient($idBraldun, $nomTable, $suffixe);
		}
		return $retour;
	}

	public static function calculPoidsLot($idLot) {
		$retour = 0;

		$suffixe = "lot";
		$nomTable = Bral_Util_String::firstToUpper($suffixe);

		$retour = $retour + self::calculPoidsTransporteElement(null, $nomTable, $suffixe);
		$retour = $retour + self::calculPoidsTransporteElementMinerais($idLot, $nomTable, $suffixe);
		$retour = $retour + self::calculPoidsTransporteElementPartiesPlantes($idLot, $nomTable, $suffixe);
		$retour = $retour + self::calculPoidsTransporteElementEquipement($idLot, $nomTable, $suffixe);
		$retour = $retour + self::calculPoidsTransporteElementPotion($idLot, $nomTable, $suffixe);
		$retour = $retour + self::calculPoidsTransporteElementAliment($idLot, $nomTable, $suffixe);
		$retour = $retour + self::calculPoidsTransporteElementRune($idLot, $nomTable, $suffixe);
		$retour = $retour + self::calculPoidsTransporteElementMunitions($idLot, $nomTable, $suffixe);
		$retour = $retour + self::calculPoidsTransporteElementMateriel($idLot, $nomTable, $suffixe);
		$retour = $retour + self::calculPoidsTransporteElementGraine($idLot, $nomTable, $suffixe);
		$retour = $retour + self::calculPoidsTransporteElementIngredient($idLot, $nomTable, $suffixe);
		return $retour;
	}

	/**
	 * Ajoute au poids n elements de poids poidsUnitaire
	 * @param unknown_type $poids poids initial
	 * @param unknown_type $n nombre d'élément
	 * @param unknown_type $poidsUnitaire poids
	 * @return unknown_type le poids
	 */
	public static function ajoute($poids, $n, $poidsUnitaire) {
		$ajout = 0;
		if ($n > 0) {
			$ajout = intval($n) *  floatval($poidsUnitaire);
		}
		return floatval($poids + $ajout);
	}

	private static function calculPoidsTransporteElement($conteneur, $nomTable, $suffixe) {
		$poids = 0;

		if ($suffixe != "laban") {
			$poids = self::ajoute($poids, $conteneur["quantite_castar_".$suffixe], self::POIDS_CASTARS);
		}
		$poids = self::ajoute($poids, $conteneur["quantite_peau_".$suffixe], self::POIDS_PEAU);
		$poids = self::ajoute($poids, $conteneur["quantite_cuir_".$suffixe], self::POIDS_CUIR);
		$poids = self::ajoute($poids, $conteneur["quantite_fourrure_".$suffixe], self::POIDS_FOURRURE);
		$poids = self::ajoute($poids, $conteneur["quantite_planche_".$suffixe], self::POIDS_PLANCHE);
		$poids = self::ajoute($poids, $conteneur["quantite_rondin_".$suffixe], self::POIDS_RONDIN);

		return $poids;
	}

	private static function calculPoidsTransporteElementMinerais($idConteneur, $nomTable, $suffixe) {
		$poids = 0;

		$nomTable = $nomTable."Minerai";
		Zend_Loader::loadClass($nomTable);
		$table = new $nomTable();
		$minerais = $table->findByIdConteneur($idConteneur);
			
		foreach ($minerais as $m) {
			$poids = self::ajoute($poids, $m["quantite_brut_".$suffixe."_minerai"], self::POIDS_MINERAI);
			$poids = self::ajoute($poids, $m["quantite_lingots_".$suffixe."_minerai"], self::POIDS_LINGOT);
		}
		return $poids;
	}

	private static function calculPoidsTransporteElementMunitions($idConteneur, $nomTable, $suffixe) {
		$poids = 0;

		$nomTable = $nomTable."Munition";
		Zend_Loader::loadClass($nomTable);
		$table = new $nomTable();
		$munitions = $table->findByIdConteneur($idConteneur);

		foreach ($munitions as $m) {
			$poids = self::ajoute($poids, $m["quantite_".$suffixe."_munition"], self::POIDS_MUNITION);
		}
		return $poids;
	}

	private static function calculPoidsTransporteElementMateriel($idConteneur, $nomTable, $suffixe) {
		$poids = 0;

		$nomTable = $nomTable."Materiel";
		Zend_Loader::loadClass($nomTable);
		$table = new $nomTable();
		$materiels = $table->findByIdConteneur($idConteneur);

		foreach ($materiels as $m) {
			$poids = self::ajoute($poids, 1, $m["poids_type_materiel"]);
		}
		return $poids;
	}

	private static function calculPoidsTransporteElementIngredient($idConteneur, $nomTable, $suffixe) {
		$poids = 0;

		$nomTable = $nomTable."Ingredient";
		Zend_Loader::loadClass($nomTable);
		$table = new $nomTable();
		$ingredients = $table->findByIdConteneur($idConteneur);

		foreach ($ingredients as $m) {
			$poids = self::ajoute($poids, $m["quantite_".$suffixe."_ingredient"], $m["poids_unitaire_type_ingredient"]);
		}
		return $poids;
	}

	private static function calculPoidsTransporteElementPartiesPlantes($idConteneur, $nomTable, $suffixe) {
		$poids = 0;

		$nomTable = $nomTable."Partieplante";
		Zend_Loader::loadClass($nomTable);
		$table = new $nomTable();
		$partiePlantes = $table->findByIdConteneur($idConteneur);

		foreach ($partiePlantes as $p) {
			$poids = self::ajoute($poids, $p["quantite_".$suffixe."_partieplante"], self::POIDS_PARTIE_PLANTE_BRUTE);
			$poids = self::ajoute($poids, $p["quantite_preparee_".$suffixe."_partieplante"], self::POIDS_PARTIE_PLANTE_PREPAREE);
		}

		return $poids;
	}

	private static function calculPoidsTransporteElementEquipement($idConteneur, $nomTable, $suffixe) {
		$poids = 0;

		$nomTable = $nomTable."Equipement";
		Zend_Loader::loadClass($nomTable);
		$table = new $nomTable();
		$equipements = $table->findByIdConteneur($idConteneur);

		foreach ($equipements as $e) {
			$poids = self::ajoute($poids, 1, $e["poids_equipement"]);
		}
		return $poids;
	}

	private static function calculPoidsTransporteElementPotion($idConteneur, $nomTable, $suffixe) {
		$nomTable = $nomTable."Potion";
		Zend_Loader::loadClass($nomTable);
		$table = new $nomTable();
		$nbPotions = $table->countByIdConteneur($idConteneur);
		return self::ajoute(0, $nbPotions, self::POIDS_POTION);
	}

	private static function calculPoidsTransporteElementAliment($idConteneur, $nomTable, $suffixe) {
		$poids = 0;
		$nomTable = $nomTable."Aliment";
		Zend_Loader::loadClass($nomTable);
		$table = new $nomTable();
		$aliments = $table->findByIdConteneur($idConteneur);

		foreach ($aliments as $a) {
			$poids = self::ajoute($poids, 1, $e["poids_unitaire_type_aliment"]);
		}
		return $poids;
	}

	private static function calculPoidsTransporteElementGraine($idConteneur, $nomTable, $suffixe) {
		$nomTable = $nomTable."Graine";
		Zend_Loader::loadClass($nomTable);
		$table = new $nomTable();
		$nbPoigneesGraines = $table->countByIdConteneur($idConteneur);
		return self::ajoute(0, $nbPoigneesGraines, self::POIDS_POIGNEE_GRAINES);
	}

	private static function calculPoidsTransporteElementRune($idConteneur, $nomTable, $suffixe) {
		$nomTable = $nomTable."Rune";
		Zend_Loader::loadClass($nomTable);
		$table = new $nomTable();
		$nbRunes = $table->countByIdConteneur($idConteneur);
		return self::ajoute(0, $nbRunes, self::POIDS_RUNE);
	}

	private static function calculPoidsTransporteEquipement($idBraldun) {
		$poids = 0;
		Zend_Loader::loadClass("BraldunEquipement");
		$braldunEquipementTable = new BraldunEquipement();
		$equipements = $braldunEquipementTable->findByIdBraldun($idBraldun);

		foreach ($equipements as $e) {
			$poids = self::ajoute($poids, 1, $e["poids_equipement"]);
		}

		unset($braldunEquipementTable);
		unset($equipements);

		return $poids;
	}
}