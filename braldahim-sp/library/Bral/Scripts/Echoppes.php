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
		return 1;
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
				$retour .= "ELEMENT;CAISSE;Rondin;". $e["quantite_rondin_caisse_echoppe"].PHP_EOL;
				$retour .= "ELEMENT;CAISSE;Peau;".$e["quantite_peau_caisse_echoppe"].PHP_EOL;
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

		if (count($idAliments) > 0) {
			$echoppeAlimentMineraiTable = new EchoppeAlimentMinerai();
			$echoppeAlimentMinerai = $echoppeAlimentMineraiTable->findByIdsAliment($idAliments);

			$echoppeAlimentPartiePlanteTable = new EchoppeAlimentPartiePlante();
			$echoppeAlimentPartiePlante = $echoppeAlimentPartiePlanteTable->findByIdsAliment($idAliments);
		}

		if (count($aliments) > 0) {
			foreach($aliments as $e) {
				$retour .= "ALIMENT;";
				$retour .= $e["id_echoppe_aliment"].';';
				$retour .= $e["id_type_aliment"].';';
				$retour .= $e["nom_type_aliment"].';';
				$retour .= $e["nom_aliment_type_qualite"].';';
				$retour .= $e["bbdf_aliment"].';';
				$retour .= Bral_Util_Aliment::getNomType($e["type_bbdf_type_aliment"]).';'; //recette
				$retour .= $e["type_vente_echoppe_aliment"].';';
				$retour .= $e["prix_1_vente_echoppe_aliment"].';';
				$retour .= $e["prix_2_vente_echoppe_aliment"].';';
				$retour .= $e["prix_3_vente_echoppe_aliment"].';';
				$retour .= Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_aliment"]).';';
				$retour .= Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_aliment"]).';';
				$retour .= Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_aliment"]).';';
				$retour .= str_replace(PHP_EOL, ", ", $e["commentaire_vente_echoppe_aliment"]).PHP_EOL;
			}
			if (count($echoppeAlimentMinerai) > 0) {
				foreach($echoppeAlimentMinerai as $r) {
					$retour .= "ALIMENT_PRIX_MINERAI;";
					$retour .= $r["id_echoppe_aliment"].';';
					$retour .= $r["prix_echoppe_aliment_minerai"].';';
					$retour .= $r["nom_type_minerai"].PHP_EOL;
				}
			}

			if (count($echoppeAlimentPartiePlante) > 0) {
				foreach($echoppeAlimentPartiePlante as $p) {
					$retour .= "ALIMENT_PRIX_PLANTE;";
					$retour .= $p["id_echoppe_aliment"].';';
					$retour .= $p["prix_echoppe_aliment_partieplante"].';';
					$retour .= $p["nom_type_plante"].';';
					$retour .= $p["nom_type_partieplante"].';';
					$retour .= $p["prefix_type_plante"].PHP_EOL;
				}
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

		if (count($idEquipements) > 0) {
			$echoppeEquipementMineraiTable = new EchoppeEquipementMinerai();
			$echoppeEquipementMinerai = $echoppeEquipementMineraiTable->findByIdsEquipement($idEquipements);

			$echoppeEquipementPartiePlanteTable = new EchoppeEquipementPartiePlante();
			$echoppeEquipementPartiePlante = $echoppeEquipementPartiePlanteTable->findByIdsEquipement($idEquipements);
		}

		if (count($equipements) > 0) {
			foreach($equipements as $e) {
				$retour .= "EQUIPEMENT;";
				$retour .= $e["id_echoppe_equipement"].';';
				$retour .= $e["type_vente_echoppe_equipement"].';';
				$retour .= $e["prix_1_vente_echoppe_equipement"].';';
				$retour .= $e["prix_2_vente_echoppe_equipement"].';';
				$retour .= $e["prix_3_vente_echoppe_equipement"].';';
				$retour .= Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_equipement"]).';';
				$retour .= Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_equipement"]).';';
				$retour .= Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_equipement"]).';';
				$retour .= str_replace(PHP_EOL, ", ", $e["commentaire_vente_echoppe_equipement"]).PHP_EOL;
			}

			if (count($echoppeEquipementMinerai) > 0) {
				foreach($echoppeEquipementMinerai as $r) {
					$retour .= "EQUIPEMENT_PRIX_MINERAI;";
					$retour .= $r["id_echoppe_equipement"].';';
					$retour .= $r["prix_echoppe_equipement_minerai"].';';
					$retour .= $r["nom_type_minerai"].PHP_EOL;
				}
			}

			if (count($echoppeEquipementPartiePlante) > 0) {
				foreach($echoppeEquipementPartiePlante as $p) {
					$retour .= "EQUIPEMENT_PRIX_PLANTE;";
					$retour .= $p["id_echoppe_equipement"].';';
					$retour .= $p["prix_echoppe_equipement_partieplante"].';';
					$retour .= $p["nom_type_plante"].';';
					$retour .= $p["nom_type_partieplante"].';';
					$retour .= $p["prefix_type_plante"].PHP_EOL;
				}
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

		if (count($idMateriels) > 0) {
			$echoppeMaterielMineraiTable = new EchoppeMaterielMinerai();
			$echoppeMaterielMinerai = $echoppeMaterielMineraiTable->findByIdsMateriel($idMateriels);

			$echoppeMaterielPartiePlanteTable = new EchoppeMaterielPartiePlante();
			$echoppeMaterielPartiePlante = $echoppeMaterielPartiePlanteTable->findByIdsMateriel($idMateriels);
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
				$retour .= $e["poids_type_materiel"].';';
				$retour .= $e["type_vente_echoppe_materiel"].';';
				$retour .= $e["prix_1_vente_echoppe_materiel"].';';
				$retour .= $e["prix_2_vente_echoppe_materiel"].';';
				$retour .= $e["prix_3_vente_echoppe_materiel"].';';
				$retour .= Bral_Util_Registre::getNomUnite($e["unite_1_vente_echoppe_materiel"]).';';
				$retour .= Bral_Util_Registre::getNomUnite($e["unite_2_vente_echoppe_materiel"]).';';
				$retour .= Bral_Util_Registre::getNomUnite($e["unite_3_vente_echoppe_materiel"]).';';
				$retour .= str_replace(PHP_EOL, ", ", $e["commentaire_vente_echoppe_materiel"]).PHP_EOL;
			}

			if (count($echoppeMaterielMinerai) > 0) {
				foreach($echoppeMaterielMinerai as $r) {
					$retour .= "MATERIEL_PRIX_MINERAI;";
					$retour .= $r["id_echoppe_materiel"].';';
					$retour .= $r["prix_echoppe_materiel_minerai"].';';
					$retour .= $r["nom_type_minerai"].PHP_EOL;
				}
			}

			if (count($echoppeMaterielPartiePlante) > 0) {
				foreach($echoppeMaterielPartiePlante as $p) {
					$retour .= "MATERIEL_PRIX_PLANTE;";
					$retour .= $p["id_echoppe_materiel"].';';
					$retour .= $p["prix_echoppe_materiel_partieplante"].';';
					$retour .= $p["nom_type_plante"].';';
					$retour .= $p["nom_type_partieplante"].';';
					$retour .= $p["prefix_type_plante"].PHP_EOL;
				}
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

		if (count($idPotions) > 0) {
			$echoppPotionMineraiTable = new EchoppePotionMinerai();
			$echoppePotionMinerai = $echoppPotionMineraiTable->findByIdsPotion($idPotions);

			$echoppePotionPartiePlanteTable = new EchoppePotionPartiePlante();
			$echoppePotionPartiePlante = $echoppePotionPartiePlanteTable->findByIdsPotion($idPotions);
		}

		if (count($potions) > 0) {
			foreach($potions as $p) {
				$retour .= "POTION;";
				$retour .= $p["id_echoppe_potion"].';';
				$retour .= $p["bm_type_potion"].';';
				$retour .= $p["nom_type_potion"].';';
				$retour .= $p["nom_type_qualite"].';';
				$retour .= $p["niveau_potion"].';';
				$retour .= $p["type_vente_echoppe_potion"].';';
				$retour .= $p["prix_1_vente_echoppe_potion"].';';
				$retour .= $p["prix_2_vente_echoppe_potion"].';';
				$retour .= $p["prix_3_vente_echoppe_potion"].';';
				$retour .= Bral_Util_Registre::getNomUnite($p["unite_1_vente_echoppe_potion"]).';';
				$retour .= Bral_Util_Registre::getNomUnite($p["unite_2_vente_echoppe_potion"]).';';
				$retour .= Bral_Util_Registre::getNomUnite($p["unite_3_vente_echoppe_potion"]).';';
				$retour .= str_replace(PHP_EOL, ", ", $e["commentaire_vente_echoppe_potion"]).PHP_EOL;
			}

			if (count($echoppePotionMinerai) > 0) {
				foreach($echoppePotionMinerai as $r) {
					$retour .= "POTION_PRIX_MINERAI;";
					$retour .= $r["id_echoppe_potion"].';';
					$retour .= $r["prix_echoppe_potion_minerai"].';';
					$retour .= $r["nom_type_minerai"].PHP_EOL;
				}
			}

			if (count($echoppePotionPartiePlante) > 0) {
				foreach($echoppePotionPartiePlante as $a) {
					$retour .= "POTION_PRIX_PLANTE;";
					$retour .= $a["id_echoppe_potion"].';';
					$retour .= $a["prix_echoppe_potion_partieplante"].';';
					$retour .= $a["nom_type_plante"].';';
					$retour .= $a["nom_type_partieplante"].';';
					$retour .= $a["prefix_type_plante"].PHP_EOL;
				}
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
				$retour .= $p["quantite_caisse_echoppe_partieplante"].';';
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
				$retour .= $m["quantite_brut_caisse_echoppe_minerai"];
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
				$retour .= $m["quantite_caisse_echoppe_ingredient"];
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