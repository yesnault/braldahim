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

	public function calculConteneur($type, &$retour) {

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
		$minerais = $mineraiTable->findByIdHobbit($this->hobbit->id_hobbit);
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
		$elements = $table->findByIdHobbit($this->hobbit->id_hobbit);
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
		$runes = $runeTable->findByIdHobbit($this->hobbit->id_hobbit, null, array("niveau_type_rune", "nom_type_rune"));
		unset($runeTable);

		foreach ($runes as $r) {
			if ($r["est_identifiee_rune"] == "oui") {
				$retour .= "RUNE;".$r["id_rune_".$typem."_rune"].';'.$r["est_identifiee_rune"].';'.$r["nom_type_rune"].PHP_EOL;
			} else {
				$retour .= "RUNE;".$r["id_rune_".$typem."_rune"].';'.$r["est_identifiee_rune"].';'.PHP_EOL;
			}
		}
		unset($runes);

		$this->renderPlante($type, $retour);
		$this->renderEquipement($type, $retour);
		/*
		 $this->renderMateriel($type, $retour);
		 $this->renderMunition($type, $retour);
		 $this->renderPotion($type, $retour);
		 $this->renderAliment($type, $retour);
		 $this->renderGraine($type, $retour);
		 $this->renderIngredient($type, $retour);
		 */
		$this->renderTabac($type, $retour);

	}

	private function renderTabac($type, &$retour) {
		$typem = strtolower($type);
		$conteneurTabac = $type."Tabac";
		$tabacTable = new $conteneurTabac();
		$tabacs = $tabacTable->findByIdHobbit($this->hobbit->id_hobbit);
		unset($tabacTable);

		foreach ($tabacs as $m) {
			if ($m["quantite_feuille_".$typem."_tabac"] > 0) {
				$retour .= 'TABAC;'.$m["quantite_feuille_".$typem."_tabac"].';'.$m["nom_court_type_tabac"].PHP_EOL;
			}
		}
		unset($tabacs);
	}

	private function renderPlante($type, &$retour) {
		$typem = strtolower($type);

		$tabTypePlantes = null;
		$conteneurPlante = $type."Partieplante";
		$partiePlanteTable = new $conteneurPlante();
		$partiePlantes = $partiePlanteTable->findByIdHobbit($this->hobbit->id_hobbit);
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

	private function renderEquipement($type, &$retour) {

		$typem = strtolower($type);

		$tabEquipements = null;
		$conteneurEquipement = $type."Equipement";
		$equipementTable = new $conteneurEquipement();
		$equipements = $equipementTable->findByIdHobbit($this->hobbit->id_hobbit);
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

	private function renderMateriel() {
		$tabMateriels = null;
		$labanMaterielTable = new LabanMateriel();
		$materiels = $labanMaterielTable->findByIdHobbit($this->hobbit->id_hobbit);
		unset($labanMaterielTable);

		$tabWhere = null;
		foreach ($materiels as $e) {
			$tabMateriels[$e["id_".$typem."_materiel"]] = array(
					"id_materiel" => $e["id_".$typem."_materiel"],
					'id_type_materiel' => $e["id_type_materiel"],
					'nom_systeme_type_materiel' => $e["nom_systeme_type_materiel"],
					'nom' =>$e["nom_type_materiel"],
					'capacite' => $e["capacite_type_materiel"], 
					'durabilite' => $e["durabilite_type_materiel"], 
					'usure' => $e["usure_type_materiel"], 
					'poids' => $e["poids_type_materiel"], 
			);
			$tabWhere[] = $e["id_".$typem."_materiel"];
		}
		unset($materiels);
	}

	private function renderMunition() {
		$tabMunitions = null;
		$labanMunitionTable = new LabanMunition();
		$munitions = $labanMunitionTable->findByIdHobbit($this->hobbit->id_hobbit);
		unset($labanMunitionTable);

		foreach ($munitions as $m) {
			$tabMunitions[] = array(
				"type" => $m["nom_type_munition"],
				"quantite" => $m["quantite_".$typem."_munition"],
				"poids" =>  $m["quantite_".$typem."_munition"] * Bral_Util_Poids::POIDS_MUNITION,
			);
		}
		unset($munitions);
	}

	private function renderPotion() {
		Zend_Loader::loadClass("Bral_Util_Potion");
		$tabPotions = null;
		$labanPotionTable = new LabanPotion();
		$potions = $labanPotionTable->findByIdHobbit($this->hobbit->id_hobbit);
		unset($labanPotionTable);

		foreach ($potions as $p) {
			$tabPotions[$p["id_".$typem."_potion"]] = array(
					"id_potion" => $p["id_".$typem."_potion"],
					"id_type_potion" => $p["id_type_potion"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"caracteristique2" => $p["caract2_type_potion"],
					"bm2_type" => $p["bm2_type_potion"],
					"nom_type" => Bral_Util_Potion::getNomType($p["type_potion"]),
			);
		}
		unset($potions);
	}

	private function renderAliment() {
		$tabAliments = null;
		$labanAlimentTable = new LabanAliment();
		$aliments = $labanAlimentTable->findByIdHobbit($this->hobbit->id_hobbit);
		unset($labanAlimentTable);

		Zend_Loader::loadClass("Bral_Util_Aliment");
		foreach ($aliments as $p) {
			$tabAliments[$p["id_".$typem."_aliment"]] = array(
					"id_aliment" => $p["id_".$typem."_aliment"],
					"id_type_aliment" => $p["id_type_aliment"],
					"nom" => $p["nom_type_aliment"],
					"qualite" => $p["nom_aliment_type_qualite"],
					"bbdf" => $p["bbdf_aliment"],
					"recette" => Bral_Util_Aliment::getNomType($p["type_bbdf_type_aliment"]),
			);
		}
		unset($aliments);
	}

	private function renderGraine() {
		$tabGraines = null;
		$labanGraineTable = new LabanGraine();
		$graines = $labanGraineTable->findByIdHobbit($this->hobbit->id_hobbit);
		unset($labanGraineTable);

		foreach ($graines as $g) {
			if ($g["quantite_".$typem."_graine"] > 0) {
				$tabGraines[] = array(
					"type" => $g["nom_type_graine"],
					"id_type_graine" => $g["id_type_graine"],
					"quantite" => $g["quantite_".$typem."_graine"],
					"poids" => $g["quantite_".$typem."_graine"] * Bral_Util_Poids::POIDS_POIGNEE_GRAINES,
				);
			}
		}
		unset($graines);
	}

	private function renderIngredient(&$tabMetiers, &$tabLaban) {
		$tabIngredients = null;
		$labanIngredientTable = new LabanIngredient();
		$ingredients = $labanIngredientTable->findByIdHobbit($this->hobbit->id_hobbit);
		unset($labanIngredientTable);

		Zend_Loader::loadClass("TypeIngredient");

		foreach ($ingredients as $g) {
			if ($g["quantite_".$typem."_ingredient"] > 0) {

				if ($g["id_type_ingredient"] ==  TypeIngredient::ID_TYPE_VIANDE_FRAICHE) {
					if (isset($tabMetiers["chasseur"])) {
						$tabMetiers["chasseur"]["a_afficher"] = true;
					}
					$tabLaban["nb_viande"] = $g["quantite_".$typem."_ingredient"];
					$tabLaban["nb_viande_poids_unitaire"] = $g["poids_unitaire_type_ingredient"];
				} else {
					$tabIngredients[] = array(
						"type" => $g["nom_type_ingredient"],
						"id_type_ingredient" => $g["id_type_ingredient"],
						"quantite" => $g["quantite_".$typem."_ingredient"],
						"poids" => $g["quantite_".$typem."_ingredient"] * $g["poids_unitaire_type_ingredient"],
					);
					if (isset($tabMetiers["cuisinier"])) {
						$tabMetiers["cuisinier"]["a_afficher"] = true;
					}
				}
			}
		}
		unset($ingredients);
	}
}