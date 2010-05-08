<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
abstract class Bral_Scripts_Conteneur extends Bral_Scripts_Script {

	public function getType() {
		return self::TYPE_STATIQUE;
	}

	public function getEtatService() {
		return self::SERVICE_ACTIVE;
	}

	public function getVersion() {
		return 1;
	}

	public function calculConteneur($type, &$retour, $idCharrette = null) {
		$typem = strtolower($type);
		Zend_Loader::loadClass($type);
		Zend_Loader::loadClass($type."Equipement");
		Zend_Loader::loadClass($type."Graine");
		Zend_Loader::loadClass($type."Ingredient");
		Zend_Loader::loadClass($type."Minerai");
		Zend_Loader::loadClass($type."Munition");
		Zend_Loader::loadClass($type."Partieplante");
		Zend_Loader::loadClass($type."Aliment");
		Zend_Loader::loadClass($type."Potion");
		Zend_Loader::loadClass($type."Materiel");
		Zend_Loader::loadClass($type."Rune");
		Zend_Loader::loadClass($type."Tabac");
		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");
		Zend_Loader::loadClass("Bral_Util_Poids");

		$conteneurMinerai = $type."Minerai";
		$mineraiTable = new $conteneurMinerai();
		if ($idCharrette == null) {
			$minerais = $mineraiTable->findByIdBraldun($this->braldun->id_braldun);
		} else {
			$minerais = $mineraiTable->findByIdCharrette($idCharrette);
		}
		unset($mineraiTable);

		foreach ($minerais as $m) {
			if ($m["quantite_brut_".$typem."_minerai"] > 0) {
				$retour .= 'MINERAI_BRUT;'.$m["quantite_brut_".$typem."_minerai"].';'.$m["nom_type_minerai"].PHP_EOL;
			}
			if ($m["quantite_lingots_".$typem."_minerai"] > 0) {
				$retour .= 'LINGOT;'.$m["quantite_lingots_".$typem."_minerai"].';'.$m["nom_type_minerai"].PHP_EOL;
			}
		}
		unset($minerais);

		$tabLaban = null;
		$conteneur = $type;
		$table = new $conteneur();
		if ($idCharrette == null) {
			$elements = $table->findByIdBraldun($this->braldun->id_braldun);
		} else {
			$elements = $table->findByIdCharrette($idCharrette);
		}
		unset($table);

		foreach ($elements as $e) {
			if ($e["quantite_peau_".$typem] > 0) $retour .= 'ELEMENT;Peau;'.$e["quantite_peau_".$typem].PHP_EOL;
			if ($e["quantite_cuir_".$typem] > 0) $retour .= 'ELEMENT;Cuir;'.$e["quantite_cuir_".$typem].PHP_EOL;
			if ($e["quantite_fourrure_".$typem] > 0) $retour .= 'ELEMENT;Fourrure;'.$e["quantite_fourrure_".$typem].PHP_EOL;
			if ($e["quantite_planche_".$typem] > 0) $retour .= 'ELEMENT;Planche;'.$e["quantite_planche_".$typem].PHP_EOL;
			if ($e["quantite_rondin_".$typem] > 0) $retour .= 'ELEMENT;Rondin;'.$e["quantite_rondin_".$typem].PHP_EOL;
			if ($e["quantite_castar_".$typem] > 0) $retour .= 'ELEMENT;Castar;'.$e["quantite_castar_".$typem].PHP_EOL;
		}
		unset($elements);

		$conteneurRune = $type."Rune";
		$runeTable = new $conteneurRune();
		if ($idCharrette == null) {
			$runes = $runeTable->findByIdBraldun($this->braldun->id_braldun, null, array("niveau_type_rune", "nom_type_rune"));
		} else {
			$runes = $runeTable->findByIdCharrette($this->braldun->id_braldun, null, array("niveau_type_rune", "nom_type_rune"));
		}
		unset($runeTable);

		foreach ($runes as $r) {
			if ($r["est_identifiee_rune"] == "oui") {
				$retour .= "RUNE;".$r["id_rune_".$typem."_rune"].';'.$r["est_identifiee_rune"].';'.$r["nom_type_rune"].PHP_EOL;
			} else {
				$retour .= "RUNE;".$r["id_rune_".$typem."_rune"].';'.$r["est_identifiee_rune"].';'.PHP_EOL;
			}
		}
		unset($runes);

		$this->renderPlante($type, $retour, $idCharrette);
		$this->renderEquipement($type, $retour, $idCharrette);
		$this->renderMateriel($type, $retour, $idCharrette);
		$this->renderMunition($type, $retour, $idCharrette);
		$this->renderPotion($type, $retour, $idCharrette);
		$this->renderAliment($type, $retour, $idCharrette);
		$this->renderGraine($type, $retour, $idCharrette);
		$this->renderIngredient($type, $retour, $idCharrette);
		$this->renderTabac($type, $retour, $idCharrette);
	}

	private function renderTabac($type, &$retour, $idCharrette = null) {
		$typem = strtolower($type);
		$conteneurTabac = $type."Tabac";
		$tabacTable = new $conteneurTabac();
		if ($idCharrette == null) {
			$tabacs = $tabacTable->findByIdBraldun($this->braldun->id_braldun);
		} else {
			$tabacs = $tabacTable->findByIdCharrette($idCharrette);
		}
		unset($tabacTable);

		foreach ($tabacs as $m) {
			if ($m["quantite_feuille_".$typem."_tabac"] > 0) {
				$retour .= 'TABAC;'.$m["quantite_feuille_".$typem."_tabac"].';'.$m["nom_court_type_tabac"].PHP_EOL;
			}
		}
		unset($tabacs);
	}

	private function renderPlante($type, &$retour, $idCharrette = null) {
		$typem = strtolower($type);
		$conteneurPlante = $type."Partieplante";
		$partiePlanteTable = new $conteneurPlante();
		if ($idCharrette == null) {
			$partiePlantes = $partiePlanteTable->findByIdBraldun($this->braldun->id_braldun);
		} else {
			$partiePlantes = $partiePlanteTable->findByIdCharrette($idCharrette);
		}
		unset($partiePlanteTable);

		foreach ($partiePlantes as $m) {
			if ($m["quantite_".$typem."_partieplante"] > 0) {
				$retour .= 'PLANTE_BRUTE;'.$m["quantite_".$typem."_partieplante"].';'.$m["nom_type_partieplante"].';'.$m["nom_type_plante"].PHP_EOL;
			}

			if ($m["quantite_preparee_".$typem."_partieplante"] > 0) {
				$retour .= 'PLANTE_PREPAREE;'.$m["quantite_preparee_".$typem."_partieplante"].';'.$m["nom_type_partieplante"].';'.$m["nom_type_plante"].PHP_EOL;
			}
		}
		unset($partiePlantes);
	}

	private function renderEquipement($type, &$retour, $idCharrette = null) {
		$typem = strtolower($type);
		$conteneurEquipement = $type."Equipement";
		$equipementTable = new $conteneurEquipement();
		if ($idCharrette == null) {
			$equipements = $equipementTable->findByIdBraldun($this->braldun->id_braldun);
		} else {
			$equipements = $equipementTable->findByIdCharrette($idCharrette);
		}
		unset($equipementTable);

		Zend_Loader::loadClass("Bral_Util_Equipement");
		$tabEquipements = Bral_Util_Equipement::prepareTabEquipements($equipements);

		$tabRetour = null;
		if ($tabEquipements != null) {
			foreach($tabEquipements as $e) {
				$retour .= 'EQUIPEMENT;'.$e["id_equipement"].';'.$e["nom"].';'.$e["qualite"].';'.$e["niveau"].';'.$e["suffixe"].PHP_EOL;
			}
		}
	}

	private function renderMateriel($type, &$retour, $idCharrette = null) {
		$typem = strtolower($type);
		$conteneurMateriel = $type."Materiel";
		$materielTable = new $conteneurMateriel();
		if ($idCharrette == null) {
			$materiels = $materielTable->findByIdBraldun($this->braldun->id_braldun);
		} else {
			$materiels = $materielTable->findByIdCharrette($idCharrette);
		}
		foreach ($materiels as $e) {
			$retour .= 'MATERIEL;'.$e["id_".$typem."_materiel"].';'.$e["nom_type_materiel"].PHP_EOL;
		}
	}

	private function renderMunition($type, &$retour, $idCharrette = null) {
		$typem = strtolower($type);
		$conteneurMunition = $type."Munition";
		$munitionTable = new $conteneurMunition();
		if ($idCharrette == null) {
			$munitions = $munitionTable->findByIdBraldun($this->braldun->id_braldun);
		} else {
			$munitions = $munitionTable->findByIdCharrette($idCharrette);
		}
		foreach ($munitions as $m) {
			$retour .= 'MUNITION;'.$m["nom_type_munition"].';'.$m["nom_pluriel_type_munition"].';'.$m["quantite_".$typem."_munition"].PHP_EOL;
		}
	}

	private function renderPotion($type, &$retour, $idCharrette = null) {
		$typem = strtolower($type);
		Zend_Loader::loadClass("Bral_Util_Potion");
		$conteneurPotion = $type."Potion";
		$potionTable = new $conteneurPotion();
		if ($idCharrette == null) {
			$potions = $potionTable->findByIdBraldun($this->braldun->id_braldun);
		} else {
			$potions = $potionTable->findByIdCharrette($idCharrette);
		}
		foreach ($potions as $p) {
			$retour .= 'POTION;'.$p["id_".$typem."_potion"].';'.Bral_Util_Potion::getNomType($p["type_potion"]).';'.$p["nom_type_potion"].';'.$p["nom_type_qualite"].';'.$p["niveau_potion"].PHP_EOL;
		}
	}

	private function renderAliment($type, &$retour, $idCharrette = null) {
		$typem = strtolower($type);
		$conteneurAliment = $type."Aliment";
		$alimentTable = new $conteneurAliment();
		if ($idCharrette == null) {
			$aliments = $alimentTable->findByIdBraldun($this->braldun->id_braldun);
		} else {
			$aliments = $alimentTable->findByIdCharrette($idCharrette);
		}
		foreach ($aliments as $p) {
			$retour .= 'ALIMENT;'.$p["id_".$typem."_aliment"].';'.$p["nom_type_aliment"].';'.$p["nom_type_qualite"].PHP_EOL;
		}
	}

	private function renderGraine($type, &$retour, $idCharrette = null) {
		$typem = strtolower($type);
		$conteneurGraine = $type."Graine";
		$graineTable = new $conteneurGraine();
		if ($idCharrette == null) {
			$graines = $graineTable->findByIdBraldun($this->braldun->id_braldun);
		} else {
			$graines = $graineTable->findByIdCharrette($idCharrette);
		}
		foreach ($graines as $g) {
			if ($g["quantite_".$typem."_graine"] > 0) {
				$retour .= 'GRAINE;'.$g["quantite_".$typem."_graine"].';'.$g["nom_type_graine"].PHP_EOL;
			}
		}
	}

	private function renderIngredient($type, &$retour, $idCharrette = null) {
		$typem = strtolower($type);
		$conteneurIngredient = $type."Ingredient";
		$ingredientTable = new $conteneurIngredient();
		if ($idCharrette == null) {
			$ingredients = $ingredientTable->findByIdBraldun($this->braldun->id_braldun);
		} else {
			$ingredients = $ingredientTable->findByIdCharrette($idCharrette);
		}
		foreach ($ingredients as $p) {
			if ($p["quantite_".$typem."_ingredient"] > 0) {
				$retour .= 'INGREDIENT;'.$p["quantite_".$typem."_ingredient"].';'.$p["nom_type_ingredient"].PHP_EOL;
			}
		}
	}
}