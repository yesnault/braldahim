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
class Bral_Util_Poids {

	const POIDS_CASTARS = 0.001;

	// la peau et la viande ont le meme poids. Si cela change, çà impactera depiauter, methode preCalculPoids()
	const POIDS_PEAU = 0.4;
	const POIDS_VIANDE = 0.4;

	const POIDS_RATION = 0.4;
	const POIDS_ALIMENT = 0.4;
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
	public static function calculPoidsCharretteTransportable($idHobbit, $niveauVigueur) {
		Zend_Loader::loadClass('Charrette');

		$tabCharrette = null;
		$charretteTable = new Charrette();
		$charrette = $charretteTable->findByIdHobbit($idHobbit);
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

	public static function calculPoidsCharrette($idHobbit, $updateBase = false) {
		$tab["transporte"] = 0;
		$tab["transportable"] = 0;

		$charretteTable = new Charrette();
		$charretteRowset = $charretteTable->findByIdHobbit($idHobbit);

		if ($charretteRowset == null || count($charretteRowset) == 0) {
			return $retour;
		} else if (count($charretteRowset) > 1) {
			throw new Zend_Exception("calculPoidsCharretteTransporte Nb Charrette invalide idh:".$idHobbit. " n:".count($charretteRowset));
		} else {
			$charrette = $charretteRowset[0];
		}

		if ($updateBase == true) {
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElement($idHobbit, $charrette);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementMinerais($idHobbit, $charrette);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementPartiesPlantes($idHobbit, $charrette);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementEquipement($idHobbit, $charrette);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementPotion($idHobbit, $charrette);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementAliment($idHobbit, $charrette);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementGraine($idHobbit, $charrette);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementIngredient($idHobbit, $charrette);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementRune($idHobbit, $charrette);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementMunitions($idHobbit, $charrette);
			$tab["transporte"] = $tab["transporte"] + self::calculPoidsTransporteElementMateriel($idHobbit, $charrette);

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

	// $idHobbit => -1 pour un nouvel hobbit
	public static function calculPoidsTransporte($idHobbit, $castars) {
		$retour = 0;
		$retour = self::ajoute($retour, $castars, self::POIDS_CASTARS);

		if ($idHobbit > 0) {
			$retour = $retour + self::calculPoidsTransporteElement($idHobbit);
			$retour = $retour + self::calculPoidsTransporteElementMinerais($idHobbit);
			$retour = $retour + self::calculPoidsTransporteElementPartiesPlantes($idHobbit);
			$retour = $retour + self::calculPoidsTransporteElementEquipement($idHobbit);
			$retour = $retour + self::calculPoidsTransporteElementPotion($idHobbit);
			$retour = $retour + self::calculPoidsTransporteElementAliment($idHobbit);
			$retour = $retour + self::calculPoidsTransporteElementRune($idHobbit);
			$retour = $retour + self::calculPoidsTransporteEquipement($idHobbit);
			$retour = $retour + self::calculPoidsTransporteElementMunitions($idHobbit);
			$retour = $retour + self::calculPoidsTransporteElementMateriel($idHobbit);
			$retour = $retour + self::calculPoidsTransporteElementGraine($idHobbit);
			$retour = $retour + self::calculPoidsTransporteElementIngredient($idHobbit);
		}
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
			$ajout = $n * $poidsUnitaire;
		}
		return $poids + $ajout;
	}

	private static function calculPoidsTransporteElement($idHobbit, $charrette = null) {
		$poids = 0;

		if ($charrette != null) {
			$elements[] = $charrette;
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("Laban");
			$table = new Laban();
			$elements = $table->findByIdHobbit($idHobbit);
			$suffixe = "laban";
		}

		foreach ($elements as $p) {
			if ($charrette != null) {
				$poids = self::ajoute($poids, $p["quantite_castar_charrette"], self::POIDS_CASTARS);
			}
			$poids = self::ajoute($poids, $p["quantite_peau_".$suffixe], self::POIDS_PEAU);
			$poids = self::ajoute($poids, $p["quantite_cuir_".$suffixe], self::POIDS_CUIR);
			$poids = self::ajoute($poids, $p["quantite_fourrure_".$suffixe], self::POIDS_FOURRURE);
			$poids = self::ajoute($poids, $p["quantite_planche_".$suffixe], self::POIDS_PLANCHE);
			$poids = self::ajoute($poids, $p["quantite_rondin_".$suffixe], self::POIDS_RONDIN);
		}
		unset($table);
		unset($table);

		return $poids;
	}

	private static function calculPoidsTransporteElementMinerais($idHobbit, $charrette = null) {
		$poids = 0;

		if ($charrette != null) {
			$suffixe = "charrette";
			Zend_Loader::loadClass("CharretteMinerai");
			$table = new CharretteMinerai();
			$minerais = $table->findByIdCharrette($charrette["id_charrette"]);
		} else {
			$suffixe = "laban";
			Zend_Loader::loadClass("LabanMinerai");
			$table = new LabanMinerai();
			$minerais = $table->findByIdHobbit($idHobbit);
		}

		foreach ($minerais as $m) {
			$poids = self::ajoute($poids, $m["quantite_brut_".$suffixe."_minerai"], self::POIDS_MINERAI);
			$poids = self::ajoute($poids, $m["quantite_lingots_".$suffixe."_minerai"], self::POIDS_LINGOT);
		}

		unset($table);
		unset($minerais);

		return $poids;
	}

	private static function calculPoidsTransporteElementMunitions($idHobbit, $charrette = null) {
		$poids = 0;

		if ($charrette != null) {
			$suffixe = "charrette";
			Zend_Loader::loadClass("CharretteMunition");
			$table = new CharretteMunition();
			$munitions = $table->findByIdCharrette($charrette["id_charrette"]);
		} else {
			$suffixe = "laban";
			Zend_Loader::loadClass("LabanMunition");
			$table = new LabanMunition();
			$munitions = $table->findByIdHobbit($idHobbit);
		}

		foreach ($munitions as $m) {
			$poids = self::ajoute($poids, $m["quantite_".$suffixe."_munition"], self::POIDS_MUNITION);
		}

		unset($table);
		unset($munitions);

		return $poids;
	}

	private static function calculPoidsTransporteElementMateriel($idHobbit, $charrette = null) {
		$poids = 0;

		if ($charrette != null) {
			$suffixe = "charrette";
			Zend_Loader::loadClass("CharretteMateriel");
			$table = new CharretteMateriel();
			$materiels = $table->findByIdCharrette($charrette["id_charrette"]);
		} else {
			$suffixe = "laban";
			Zend_Loader::loadClass("LabanMateriel");
			$table = new LabanMateriel();
			$materiels = $table->findByIdHobbit($idHobbit);
		}

		foreach ($materiels as $m) {
			$poids = self::ajoute($poids, 1, $m["poids_type_materiel"]);
		}

		unset($table);
		unset($materiels);

		return $poids;
	}

	private static function calculPoidsTransporteElementIngredient($idHobbit, $charrette = null) {
		$poids = 0;

		if ($charrette != null) {
			$suffixe = "charrette";
			Zend_Loader::loadClass("CharretteIngredient");
			$table = new CharretteIngredient();
			$ingredients = $table->findByIdCharrette($charrette["id_charrette"]);
		} else {
			$suffixe = "laban";
			Zend_Loader::loadClass("LabanIngredient");
			$table = new LabanIngredient();
			$ingredients = $table->findByIdHobbit($idHobbit);
		}

		foreach ($ingredients as $m) {
			$poids = self::ajoute($poids, $m["quantite_".$suffixe."_ingredient"], $m["poids_unitaire_type_ingredient"]);
		}

		unset($table);
		unset($ingredients);

		return $poids;
	}

	private static function calculPoidsTransporteElementPartiesPlantes($idHobbit, $charrette = null) {
		$poids = 0;

		if ($charrette != null) {
			$suffixe = "charrette";
			Zend_Loader::loadClass("CharrettePartieplante");
			$table = new CharrettePartieplante();
			$partiePlantes = $table->findByIdCharrette($charrette["id_charrette"]);
		} else {
			$suffixe = "laban";
			Zend_Loader::loadClass("LabanPartieplante");
			$table = new LabanPartieplante();
			$partiePlantes = $table->findByIdHobbit($idHobbit);
		}

		foreach ($partiePlantes as $p) {
			$poids = self::ajoute($poids, $p["quantite_".$suffixe."_partieplante"], self::POIDS_PARTIE_PLANTE_BRUTE);
			$poids = self::ajoute($poids, $p["quantite_preparee_".$suffixe."_partieplante"], self::POIDS_PARTIE_PLANTE_PREPAREE);
		}

		unset($table);
		unset($partiePlantes);

		return $poids;
	}

	private static function calculPoidsTransporteElementEquipement($idHobbit, $charrette = null) {
		$poids = 0;

		if ($charrette != null) {
			$suffixe = "charrette";
			Zend_Loader::loadClass("CharretteEquipement");
			$table = new CharretteEquipement();
			$equipements = $table->findByIdCharrette($charrette["id_charrette"]);
		} else {
			$suffixe = "laban";
			Zend_Loader::loadClass("LabanEquipement");
			$table = new LabanEquipement();
			$equipements = $table->findByIdHobbit($idHobbit);
		}

		$tabWhere = null;
		foreach ($equipements as $e) {
			$poids = self::ajoute($poids, 1, $e["poids_equipement"]);
			$tabWhere[] = $e["id_".$suffixe."_equipement"];
		}

		unset($table);
		unset($equipements);
		unset($tabWhere);

		return $poids;
	}

	private static function calculPoidsTransporteElementPotion($idHobbit, $charrette = null) {
		if ($charrette != null) {
			Zend_Loader::loadClass("CharrettePotion");
			$table = new CharrettePotion();
			$nbPotions = $table->countByIdCharrette($charrette["id_charrette"]);
		} else {
			Zend_Loader::loadClass("LabanPotion");
			$table = new LabanPotion();
			$nbPotions = $table->countByIdHobbit($idHobbit);
		}

		unset($table);
		return self::ajoute(0, $nbPotions, self::POIDS_POTION);
	}

	private static function calculPoidsTransporteElementAliment($idHobbit, $charrette = null) {
		if ($charrette != null) {
			Zend_Loader::loadClass("CharretteAliment");
			$table = new CharretteAliment();
			$nbAliments = $table->countByIdCharrette($charrette["id_charrette"]);
		} else {
			Zend_Loader::loadClass("LabanAliment");
			$table = new LabanAliment();
			$nbAliments = $table->countByIdHobbit($idHobbit);
		}

		unset($table);
		return self::ajoute(0, $nbAliments, self::POIDS_ALIMENT);
	}

	private static function calculPoidsTransporteElementGraine($idHobbit, $charrette = null) {
		if ($charrette != null) {
			Zend_Loader::loadClass("CharretteGraine");
			$table = new CharretteGraine();
			$nbPoigneesGraines = $table->countByIdCharrette($charrette["id_charrette"]);
		} else {
			Zend_Loader::loadClass("LabanGraine");
			$table = new LabanGraine();
			$nbPoigneesGraines = $table->countByIdHobbit($idHobbit);
		}

		unset($table);
		return self::ajoute(0, $nbPoigneesGraines, self::POIDS_POIGNEE_GRAINES);
	}



	private static function calculPoidsTransporteElementRune($idHobbit, $charrette = null) {
		if ($charrette != null) {
			Zend_Loader::loadClass("CharretteRune");
			$table = new CharretteRune();
			$nbRunes = $table->countByIdCharrette($charrette["id_charrette"]);
		} else {
			Zend_Loader::loadClass("LabanRune");
			$table = new LabanRune();
			$nbRunes = $table->countByIdHobbit($idHobbit);
		}

		unset($table);
		return self::ajoute(0, $nbRunes, self::POIDS_RUNE);
	}

	private static function calculPoidsTransporteEquipement($idHobbit) {
		$poids = 0;
		Zend_Loader::loadClass("HobbitEquipement");
		$hobbitEquipementTable = new HobbitEquipement();
		$equipements = $hobbitEquipementTable->findByIdHobbit($idHobbit);

		$tabWhere = null;
		foreach ($equipements as $e) {
			$poids = self::ajoute($poids, 1, $e["poids_equipement"]);
			$tabWhere[] = $e["id_equipement_hequipement"];
		}

		unset($hobbitEquipementTable);
		unset($equipements);
		unset($tabWhere);

		return $poids;
	}

	public static function getPoidsUnite($nomSystemeUnite) {
		$retour = null;
		switch($nomSystemeUnite) {
			case "rondin":
				$retour = self::POIDS_RONDIN;
				break;
			case "peau":
				$retour = self::POIDS_PEAU;
				break;
			case "castar":
				$retour = self::POIDS_CASTARS;
				break;
			default:
				throw new Zend_Exception("Unite invalide:".$nomSystemeUnite);
				break;
		}
		return $retour;
	}
}