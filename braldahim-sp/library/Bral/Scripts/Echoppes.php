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
class Bral_Scripts_Echoppes extends Bral_Scripts_Script {

	public function getType() {
		return self::TYPE_STATIQUE;
	}

	public function getEtatService() {
		return self::SERVICE_ACTIVE;
	}

	public function getVersion() {
		return 2;
	}

	public function calculScriptImpl() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Echoppes - calculScriptImpl - enter -");

		$retour = null;
		$retour .= $this->calculEchoppes();

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Echoppes - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculEchoppes() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Echoppes - calculEchoppes - enter -");
		$retour = "";
		$this->calculEchoppesBraldun($retour);
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Echoppes - calculEchoppes - exit -");
		return $retour;
	}

	private function calculEchoppesBraldun(&$retour) {
		Zend_Loader::loadClass("Echoppe");
		Zend_Loader::loadClass("Bral_Util_Registre");
		
		$echoppesTable = new Echoppe();
		$echoppesRowset = $echoppesTable->findByIdBraldun($this->braldun->id_braldun);

		if ($echoppesRowset != null) {
			foreach($echoppesRowset as $e) {
				$retour .= "ECHOPPE;".$e["id_echoppe"].';'.$e["x_echoppe"].';'.$e["y_echoppe"].';'.$e["z_echoppe"].';'.$e["id_metier"].';'.$e["id_region"].PHP_EOL;

				$this->renderAliments($retour, $e["id_echoppe"]);
				$this->renderEquipements($retour, $e["id_echoppe"]);
				$this->renderMateriels($retour, $e["id_echoppe"]);
				$this->renderPotions($retour, $e["id_echoppe"]);
				$this->renderRessources($retour, $e["id_echoppe"]);

				$retour .= "ELEMENT;CAISSE;Castar;".$e["quantite_castar_caisse_echoppe"].PHP_EOL;
				$retour .= "ELEMENT;ARRIERE;Rondin;".$e["quantite_rondin_arriere_echoppe"].PHP_EOL;
				$retour .= "ELEMENT;ARRIERE;Peau;".$e["quantite_peau_arriere_echoppe"].PHP_EOL;
				$retour .= "ELEMENT;ARRIERE;Cuir;".$e["quantite_cuir_arriere_echoppe"].PHP_EOL;
				$retour .= "ELEMENT;ARRIERE;Fourrure;".$e["quantite_fourrure_arriere_echoppe"].PHP_EOL;
				$retour .= "ELEMENT;ARRIERE;Planche;".$e["quantite_planche_arriere_echoppe"].PHP_EOL;
			}
		} else {
			$retour .= "AUCUNE_ECHOPPE";
		}

	}

	private function renderAliments(&$retour, $idEchoppe) {
		Zend_Loader::loadClass("EchoppeAliment");
		Zend_Loader::loadClass("EchoppeAlimentMinerai");
		Zend_Loader::loadClass("EchoppeAlimentPartiePlante");
		Zend_Loader::loadClass("Bral_Util_Aliment");

		$tabAlimentsArriereBoutique = null;
		$tabAlimentsEtal = null;
		$echoppeAlimentTable = new EchoppeAliment();
		$aliments = $echoppeAlimentTable->findByIdEchoppe($idEchoppe);
		$idAliments = null;

		foreach ($aliments as $e) {
			$idAliments[] = $e["id_echoppe_aliment"];
		}

		if (count($aliments) > 0) {
			foreach($aliments as $e) {
				$retour .= "ALIMENT;";
				$retour .= $e["id_echoppe_aliment"].';';
				$retour .= $e["id_type_aliment"].';';
				$retour .= $e["nom_type_aliment"].';';
				$retour .= $e["nom_aliment_type_qualite"].';';
				$retour .= $e["bbdf_aliment"].';';
				$retour .= Bral_Util_Aliment::getNomType($e["type_bbdf_type_aliment"]).PHP_EOL;
			}
		}
	}


	private function renderEquipements(&$retour, $idEchoppe) {
		Zend_Loader::loadClass("Bral_Util_Equipement");
		Zend_Loader::loadClass("EchoppeEquipement");
		Zend_Loader::loadClass("EchoppeEquipementMinerai");
		Zend_Loader::loadClass("EchoppeEquipementPartiePlante");
		Zend_Loader::loadClass("EquipementRune");
		Zend_Loader::loadClass("EquipementBonus");

		$tabEquipementsArriereBoutique = null;
		$tabEquipementsEtal = null;
		$echoppeEquipementTable = new EchoppeEquipement();
		$equipements = $echoppeEquipementTable->findByIdEchoppe($idEchoppe);
		$idEquipements = null;

		foreach ($equipements as $e) {
			$idEquipements[] = $e["id_echoppe_equipement"];
		}

		if (count($equipements) > 0) {
			foreach($equipements as $e) {
				$retour .= "EQUIPEMENT;";
				$retour .= $e["id_echoppe_equipement"].PHP_EOL;
			}
		}

	}


	private function renderMateriels(&$retour, $idEchoppe) {
		Zend_Loader::loadClass("EchoppeMateriel");
		Zend_Loader::loadClass("EchoppeMaterielMinerai");
		Zend_Loader::loadClass("EchoppeMaterielPartiePlante");

		$tabMaterielsArriereBoutique = null;
		$tabMaterielsEtal = null;
		$echoppeMaterielTable = new EchoppeMateriel();
		$materiels = $echoppeMaterielTable->findByIdEchoppe($idEchoppe);
		$idMateriels = null;

		foreach ($materiels as $e) {
			$idMateriels[] = $e["id_echoppe_materiel"];
		}

		if (count($materiels) > 0) {
			foreach($materiels as $e) {
				$retour .= "MATERIEL;";
				$retour .= $e["id_echoppe_materiel"].';';
				$retour .= $e["id_type_materiel"].';';
				$retour .= $e["nom_systeme_type_materiel"].';';
				$retour .= $e["nom_type_materiel"].';';
				$retour .= $e["capacite_type_materiel"].';';
				$retour .= $e["durabilite_type_materiel"].';';
				$retour .= $e["usure_type_materiel"].';';
				$retour .= $e["poids_type_materiel"].PHP_EOL;
			}
		}

	}

	private function renderPotions(&$retour, $idEchoppe) {
		Zend_Loader::loadClass("EchoppePotion");
		Zend_Loader::loadClass("EchoppePotionMinerai");
		Zend_Loader::loadClass("EchoppePotionPartiePlante");
		Zend_Loader::loadClass("Bral_Util_Potion");

		$tabPotionsArriereBoutique = null;
		$tabPotionsEtal = null;
		$echoppePotionTable = new EchoppePotion();
		$potions = $echoppePotionTable->findByIdEchoppe($idEchoppe);

		$idPotions = null;

		foreach ($potions as $p) {
			$idPotions[] = $p["id_echoppe_potion"];
		}

		if (count($potions) > 0) {
			foreach($potions as $p) {
				$retour .= "POTION;";
				$retour .= $p["id_echoppe_potion"].';';
				$retour .= $p["bm_type_potion"].';';
				$retour .= $p["nom_type_potion"].';';
				$retour .= $p["nom_type_qualite"].';';
				$retour .= $p["niveau_potion"].PHP_EOL;
			}
		}
	}

	private function renderRessources(&$retour, $idEchoppe) {
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass("EchoppeIngredient");
		Zend_Loader::loadClass("EchoppePotion");
		Zend_Loader::loadClass("Bral_Util_Potion");

		$echoppePartiePlanteTable = new EchoppePartieplante();
		$partiePlantes = $echoppePartiePlanteTable->findByIdEchoppe($idEchoppe);

		if ($partiePlantes != null) {
			foreach ($partiePlantes as $p) {
				$retour .= "PLANTE";
				$retour .= $p["id_type_partieplante"].';';
				$retour .= $p["id_fk_type_plante_echoppe_partieplante"].';';
				$retour .= $p["quantite_arriere_echoppe_partieplante"].';';
				$retour .= $p["quantite_preparee_echoppe_partieplante"];
				$retour .= PHP_EOL;
			}
		}

		$echoppeMineraiTable = new EchoppeMinerai();
		$minerais = $echoppeMineraiTable->findByIdEchoppe($idEchoppe);

		if ($minerais != null) {
			foreach ($minerais as $m) {
				$retour .= "MINERAI;";
				$retour .= $m["nom_type_minerai"].';';
				$retour .= $m["id_type_minerai"].';';
				$retour .= $m["quantite_brut_arriere_echoppe_minerai"].';';
				$retour .= $m["quantite_lingots_echoppe_minerai"].';';
				$retour .= PHP_EOL;
			}
		}

		$echoppeIngredientTable = new EchoppeIngredient();
		$ingredients = $echoppeIngredientTable->findByIdEchoppe($idEchoppe);

		if ($ingredients != null) {
			foreach ($ingredients as $m) {
				$retour .= "INGREDIENT;";
				$retour .= $m["nom_type_ingredient"].';';
				$retour .= $m["id_type_ingredient"].';';
				$retour .= $m["quantite_arriere_echoppe_ingredient"].';';
				$retour .= PHP_EOL;
			}
		}

		Zend_Loader::loadClass("Bral_Util_Potion");
		$echoppePotionTable = new EchoppePotion();
		$potions = $echoppePotionTable->findByIdEchoppe($idEchoppe);

		if ($potions != null) {
			foreach ($potions as $p) {
				$retour .= "POTION_ARRIERE;";
				$retour .= $p["id_echoppe_potion"].';';
				$retour .= $p["bm_type_potion"].';';
				$retour .= $p["nom_type_potion"].';';
				$retour .= $p["nom_type_qualite"].';';
				$retour .= $p["niveau_potion"];
				$retour .= PHP_EOL;
			}
		}

	}

}