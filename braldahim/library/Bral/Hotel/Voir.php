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
class Bral_Hotel_Voir extends Bral_Hotel_Hotel {

	private $arBoutiqueBruts;
	private $arBoutiqueTransformes;

	function getNomInterne() {
		return "box_lieu";
	}

	public function getTitreAction() {
		return null;
	}

	function render() {
		$this->prepareMenu();
		return $this->view->render("hotel/voir.phtml");
	}

	function prepareCommun() {
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
	}

	function getListBoxRefresh() {
	}

	private function prepareMenu() {
		$numeroElement = 0;
		$tabMenuEquipement[] = $this->prepareMenuEquipementEmplacements($numeroElement);
		$tabMenuEquipement[] = $this->prepareMenuEquipementTypes($numeroElement);
		$this->view->menuRechercheEquipement = $tabMenuEquipement;

		$this->prepareMenuMateriels();
		$this->prepareMenusMatieres();
		$this->prepareMenuPotions();
		$this->prepareMenuRunes();
		$this->prepareMenuAliments();
	}

	private function prepareMenuEquipementEmplacements(&$numeroElement) {
		$retour = array("titre" => "Équipements par emplacement");

		Zend_Loader::loadClass("TypeEmplacement");
		$typeEmplacementTable = new TypeEmplacement();
		$typesEmplacements = $typeEmplacementTable->fetchAll(null, "nom_type_emplacement");
		$typesEmplacements = $typesEmplacements->toArray();

		$elements = null;
		foreach($typesEmplacements as $e) {
			$numeroElement++;
			$elements[] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_emplacement"], "table" => "TypeEmplacement", "id_element" => $e["id_type_emplacement"]);
		}

		$retour["elements"] = $elements;

		return $retour;
	}

	private function prepareMenuEquipementTypes(&$numeroElement) {
		$retour = array("titre" => "Équipements par type");

		Zend_Loader::loadClass("TypeEquipement");
		$typeEquipementTable = new TypeEquipement();
		$typesEquipements = $typeEquipementTable->fetchAll(null, "nom_type_equipement");
		$typesEquipements = $typesEquipements->toArray();

		$elements = null;
		foreach($typesEquipements as $e) {
			$numeroElement++;
			$elements[$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_equipement"], "table" => "TypeEquipement", "id_element" => $e["id_type_equipement"]);
		}

		$retour["elements"] = $elements;

		return $retour;
	}

	private function prepareMenuMateriels() {
		Zend_Loader::loadClass("TypeMateriel");
		$typeMaterielTable = new TypeMateriel();
		$typesMateriels = $typeMaterielTable->fetchAll(null, "nom_type_materiel");
		$typesMateriels = $typesMateriels->toArray();

		$elements = null;
		$numeroElement = 0;
		$tabMateriel["elements"] = null;
		foreach($typesMateriels as $e) {
			$numeroElement++;
			$tabMateriel["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_materiel"], "table" => "TypeMateriel", "id_element" => $e["id_type_materiel"]);
		}

		$tab[] = $tabMateriel;
		$this->view->menuRechercheMateriel = $tab;
	}

	private function prepareMenusMatieres() {

		Zend_Loader::loadClass("TypeMinerai");
		$typeMineraiTable = new TypeMinerai();
		$typesMinerais = $typeMineraiTable->fetchAll(null, "nom_type_minerai");
		$typesMinerais = $typesMinerais->toArray();

		$numeroElement = 0;

		$tabMenuMatieresPremieres = null;
		$tabMenuMatieresTransformees = null;

		$tabMineraisBruts = array("titre" => "Minerais bruts");
		$tabLingots = array("titre" => "Lingots");

		foreach($typesMinerais as $e) {
			$numeroElement++;
			$tabMineraisBruts["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Minerai Brut : ".$e["nom_type_minerai"], "table" => "TypeMinerai", "id_element" => $e["id_type_minerai"], "type_forme" => "brut");
			$tabLingots["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => "Lingot : ".$e["nom_type_minerai"], "table" => "TypeMinerai", "id_element" => $e["id_type_minerai"], "type_forme" => "lingot");
		}

		$tabMenuMatieresPremieres[] = $tabMineraisBruts;
		$tabMenuMatieresTransformees[] = $tabLingots;

		$tabPlantesBrutes = array("titre" => "Parties de plantes brutes");
		$tabPlantesPreparees = array("titre" => "Parties de plantes préparées");

		Zend_Loader::loadClass("TypePartieplante");
		$typePartiePlanteTable = new TypePartieplante();
		$typePartiePlanteRowset = $typePartiePlanteTable->fetchall(null, "nom_type_partieplante");
		$typePartiePlanteRowset = $typePartiePlanteRowset->toArray();
		foreach($typePartiePlanteRowset as $t) {
			$partiePlante[$t["id_type_partieplante"]] = array("nom_partieplante" => $t["nom_type_partieplante"], "nom_systeme_partieplante" => $t["nom_systeme_type_partieplante"]);
		}

		Zend_Loader::loadClass("TypePlante");
		$typePlanteTable = new TypePlante();
		$typePlanteRowset = $typePlanteTable->fetchall(null, "nom_type_plante");
		$typePlanteRowset = $typePlanteRowset->toArray();

		foreach($typePlanteRowset as $t) {
			$numeroElement++;
			$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "brute");
			$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "preparee");

			if ($t["id_fk_partieplante2_type_plante"] != "") {
				$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "brute");
				$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "preparee");
			}

			if ($t["id_fk_partieplante3_type_plante"] != "") {
				$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "brute");
				$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "preparee");
			}

			if ($t["id_fk_partieplante4_type_plante"] != "") {
				$tabPlantesBrutes["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "brute");
				$tabPlantesPreparees["elements"][$numeroElement] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "preparee");
			}
		}

		$tabMenuMatieresPremieres[] = $tabPlantesBrutes;
		$tabMenuMatieresTransformees[] = $tabPlantesPreparees;
		
		$tabAutresPremieres = array("titre" => "Autres éléments");
		$tabAutresTransformees = array("titre" => "Autres éléments");
		
		$numeroElement++;
		$tabAutresPremieres["elements"][] = array('numero_element' => $numeroElement, 'nom' => "Peau", "type" => "peau");
		$tabAutresTransformees["elements"] = array('numero_element' => $numeroElement, 'nom' => "Cuir", "type" => "cuir");
		
		$numeroElement++;
		$tabAutresPremieres["elements"][] = array('numero_element' => $numeroElement, 'nom' => "Viande fraîche", "type" => "viande_fraiche");
		$tabAutresTransformees["elements"] = array('numero_element' => $numeroElement, 'nom' => "Fourrure", "type" => "fourrure");
		
		$numeroElement++;
		$tabAutresPremieres["elements"][] = array('numero_element' => $numeroElement, 'nom' => "Rondin", "type" => "rondin");
		$tabAutresTransformees["elements"] = array('numero_element' => $numeroElement, 'nom' => "Planche", "type" => "planche");
		
		$numeroElement++;
		$tabAutresTransformees["elements"][] = array('numero_element' => $numeroElement, 'nom' => "Viande préparée", "type" => "viande_preparee");
		
		$tabMenuMatieresPremieres[] = $tabAutresPremieres;

		$this->view->menuRechercheMatieresPremieres = $tabMenuMatieresPremieres;
		$this->view->menuRechercheMatieresTransformees = $tabMenuMatieresTransformees;
	}

	private function prepareUnitesRowPlante($type, $partiePlante, $num, $forme) {
		if ($forme == "brute") {
			$nomForme = "Brute";
		} else {
			$nomForme = "Préparée";
		}
		return array("id_type_plante" =>  $type["id_type_plante"],
					  "id_type_partieplante" => $type["id_fk_partieplante".$num."_type_plante"],
					  "nom_systeme_type_unite" => "plantebrute:".$type["nom_systeme_type_plante"] ,
					  "nom" => "Plante ".$nomForme.": ".$type["nom_type_plante"]. ' '.$partiePlante[$type["id_fk_partieplante".$num."_type_plante"]]["nom_partieplante"],
					  "table" => "TypePlante",
					  "type_forme" => $forme);
	}

	private function prepareMenuPotions() {
		Zend_Loader::loadClass("TypePotion");
		$typePotionTable = new TypePotion();
		$typesPotions = $typePotionTable->fetchAll(null, "nom_type_potion");
		$typesPotions = $typesPotions->toArray();

		$elements = null;
		$numeroElement = 0;
		$tabPotion["elements"] = null;
		foreach($typesPotions as $e) {
			$numeroElement++;
			$tabPotion["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_potion"], "table" => "TypePotion", "id_element" => $e["id_type_potion"]);
		}

		$tab[] = $tabPotion;
		$this->view->menuRecherchePotion = $tab;
	}

	private function prepareMenuRunes() {
		Zend_Loader::loadClass("TypeRune");
		$typeRuneTable = new TypeRune();
		$typesRunes = $typeRuneTable->fetchAll(null, "nom_type_rune");
		$typesRunes = $typesRunes->toArray();

		$elements = null;
		$numeroElement = 0;
		$tabRune["elements"] = null;
		foreach($typesRunes as $e) {
			$numeroElement++;
			$tabRune["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_rune"], "table" => "TypeRune", "id_element" => $e["id_type_rune"]);
		}

		$tab[] = $tabRune;
		$this->view->menuRechercheRune = $tab;
	}
	
	private function prepareMenuAliments() {
		Zend_Loader::loadClass("TypeAliment");
		$typeAlimentTable = new TypeAliment();
		$typesAliments = $typeAlimentTable->fetchAll(null, "nom_type_aliment");
		$typesAliments = $typesAliments->toArray();

		$elements = null;
		$numeroElement = 0;
		$tabAliment["elements"] = null;
		foreach($typesAliments as $e) {
			$numeroElement++;
			$tabAliment["elements"][$numeroElement] = array('numero_element' => $numeroElement, 'nom' => $e["nom_type_aliment"], "table" => "TypeAliment", "id_element" => $e["id_type_aliment"]);
		}

		$tab[] = $tabAliment;
		$this->view->menuRechercheAliment = $tab;
	}

}