<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Hotel extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - calculBatchImpl - enter -");
		Zend_Loader::loadClass('Vente');

		$venteTable = new Vente();
		$dateFin = date("Y-m-d H:i:s");
		$where = "date_fin_vente <= '".$dateFin."'";
		$ventes = $venteTable->fetchAll($where);

		if ($ventes != null && count($ventes) > 0) {
			foreach($ventes as $vente) {
				$this->transfertVersCoffre($vente);
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - calculBatchImpl - exit -");
		return " date:".$dateFin;
	}

	private function transfertVersCoffre($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffre - enter -");

		if ($vente["type_vente"] == "aliment") {
			$retour = $this->transfertVersCoffreAliment($vente);
		} else if ($vente["type_vente"] == "element") {
			$retour = $this->transfertVersCoffreElement($vente);
		} else if ($vente["type_vente"] == "equipement") {
			$retour = $this->transfertVersCoffreEquipement($vente);
		} else if ($vente["type_vente"] == "materiel") {
			$retour = $this->transfertVersCoffreMateriel($vente);
		} else if ($vente["type_vente"] == "minerai") {
			$retour = $this->transfertVersCoffreMinerai($vente);
		} else if ($vente["type_vente"] == "munition") {
			$retour = $this->transfertVersCoffreMunition($vente);
		} else if ($vente["type_vente"] == "partieplante") {
			$retour = $this->transfertVersCoffrePartieplante($vente);
		} else if ($vente["type_vente"] == "potion") {
			$retour = $this->transfertVersCoffrePotion($vente);
		} else if ($vente["type_vente"] == "rune") {
			$retour = $this->transfertVersCoffreRune($vente);
		} else if ($vente["type_vente"] == "ingredient") {
			$retour = $this->transfertVersCoffreIngredient($vente);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffre - Message -".$retour);

		//TODO Message vers Braldun

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffre - exit -");
	}

	private function transfertVersCoffreAliment($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreAliment - enter -");

		Zend_Loader::loadClass("VenteAliment");
		$venteAlimentTable = new VenteAliment();
		$aliments = $venteAlimentTable->findByIdVente($vente["id_vente"]);

		$retour = "";

		if ($aliments != null && count($aliments) > 0) {
			Zend_Loader::loadClass("CoffreAliment");
			$coffreAlimentTable = new CoffreAliment();
			foreach($aliments as $a) {
				$data = array(
					"id_coffre_aliment" => $a["id_vente_aliment"],
					"id_fk_braldun_coffre_aliment" => $vente["id_fk_braldun_vente"],
					"id_fk_type_qualite_coffre_aliment" => $a["id_fk_type_qualite_vente_aliment"],
					"bbdf_coffre_aliment" => $a["bbdf_vente_aliment"],
				);

				$coffreAlimentTable->insert($data);

				$retour .= $a["nom_type_aliment"]." n°".$a["id_vente_aliment"] ." +".$a["bbdf_vente_aliment"]."%, ";
			}
				
			$this->deleteVente($vente);
		}

		if ($retour != "") {
			$retour = substr($retour, 0, strlen($retour) - 2);
		}


		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreAliment - exit -");
		return $retour;
	}


	private function transfertVersCoffreIngredient($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreIngredient - enter -");

		Zend_Loader::loadClass("VenteIngredient");
		$venteIngredientTable = new VenteIngredient();
		$ingredients = $venteIngredientTable->findByIdVente($vente["id_vente"]);

		$retour = "";

		if ($ingredients != null && count($ingredients) > 0) {
			Zend_Loader::loadClass("CoffreIngredient");
			$coffreIngredientTable = new CoffreIngredient();
			foreach($ingredients as $a) {
				$data = array(
					"id_fk_type_coffre_ingredient" => $a["id_fk_type_vente_ingredient"],
					"id_fk_braldun_coffre_ingredient" => $vente["id_fk_braldun_vente"],
					"quantite_coffre_ingredient" => $a["quantite_vente_ingredient"],
				);

				$coffreIngredientTable->insert($data);

				$retour .= $a["nom_type_ingredient"]." n°".$a["id_vente_ingredient"]. ' quantité:'.$a["quantite_vente_ingredient"];
			}
				
			$this->deleteVente($vente);
		}

		if ($retour != "") {
			$retour = substr($retour, 0, strlen($retour) - 2);
		}


		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreIngredient - exit -");
		return $retour;
	}

	private function transfertVersCoffreElement($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreElement - enter -");
		$retour = "";

		Zend_Loader::loadClass("VenteElement");
		$venteElementTable = new VenteElement();
		$elements = $venteElementTable->findByIdVente($vente["id_vente"]);

		if ($elements != null && count($elements) == 1) {
			$element = $elements[0];

			Zend_Loader::loadClass("Coffre");
			$coffreTable = new Coffre();

			$prefix = "_".$element["type_vente_element"];

			$data = array(
				"id_fk_braldun_coffre" => $vente["id_fk_braldun_vente"],
				"quantite".$prefix."_coffre" => $element["quantite_vente_element"],
			);
			$coffreTable->insertOrUpdate($data);
				
			$this->deleteVente($vente);

			$nom = $element["quantite_vente_element"];
			if ($element["type_vente_element"] == "peau") {
				if ($element["quantite_vente_element"] > 1) {
					$nom .= " peaux";
				} else {
					$nom .= " peau";
				}
			} else if ($element["type_vente_element"] == "cuir") {
				if ($element["quantite_vente_element"] > 1) {
					$nom .= " cuirs";
				} else {
					$nom .= " cuir";
				}
			} else if ($element["type_vente_element"] == "fourrure") {
				if ($element["quantite_vente_element"] > 1) {
					$nom .= " fourrures";
				} else {
					$nom .= " fourrure";
				}
			} else if ($element["type_vente_element"] == "planche") {
				if ($element["quantite_vente_element"] > 1) {
					$nom .= " planches";
				} else {
					$nom .= " planche";
				}
			} else if ($element["type_vente_element"] == "rondin") {
				if ($element["quantite_vente_element"] > 1) {
					$nom .= " rondins";
				} else {
					$nom .= " rondin";
				}
			}
			$retour = $nom;
		} else {
			throw new Zend_Exception("Bral_Batchs_Hotel transfertVersCoffreElement vente invalide:".$vente["id_vente"]);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreElement - exit -");
		return $retour;
	}

	private function transfertVersCoffreEquipement($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreEquipement - enter -");
		$retour = "";

		Zend_Loader::loadClass("VenteEquipement");
		$venteEquipementTable = new VenteEquipement();
		$equipement = $venteEquipementTable->findByIdVente($vente["id_vente"]);

		if ($equipement != null && count($equipement) == 1) {
			$equipement = $equipement[0];

			Zend_Loader::loadClass("CoffreEquipement");
			$coffreEquipementTable = new CoffreEquipement();

			$data = array(
				"id_coffre_equipement" => $equipement["id_vente_equipement"],
				"id_fk_braldun_coffre_equipement" => $vente["id_fk_braldun_vente"],
			);
			$coffreEquipementTable->insert($data);
				
			$this->deleteVente($vente);

			Zend_Loader::loadClass("Bral_Util_Equipement");
			$retour = Bral_Util_Equipement::getNomByIdRegion($equipement, $equipement["id_fk_region_equipement"]). " de qualité ";
			$retour .= $equipement["nom_type_qualite"]. " et de niveau ".$equipement["niveau_recette_equipement"];
		} else {
			throw new Zend_Exception("Bral_Batchs_Hotel transfertVersCoffreEquipement vente invalide:".$vente["id_vente"]);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreEquipement - exit -");
		return $retour;
	}

	private function transfertVersCoffreMateriel($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreMateriel - enter -");
		$retour = "";

		Zend_Loader::loadClass("VenteMateriel");
		$venteMaterielTable = new VenteMateriel();
		$materiel = $venteMaterielTable->findByIdVente($vente["id_vente"]);

		if ($materiel != null && count($materiel) == 1) {
			$materiel = $materiel[0];

			Zend_Loader::loadClass("CoffreMateriel");
			$coffreMaterielTable = new CoffreMateriel();

			$data = array(
				"id_coffre_materiel" => $materiel["id_vente_materiel"],
				"id_fk_braldun_coffre_materiel" => $vente["id_fk_braldun_vente"],
			);
			$coffreMaterielTable->insert($data);
				
			$this->deleteVente($vente);

			$retour = $materiel["nom_type_materiel"]. " n°".$materiel["id_vente_materiel"];
		} else {
			throw new Zend_Exception("Bral_Batchs_Hotel transfertVersCoffreMateriel vente invalide:".$vente["id_vente"]);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreMateriel - exit -");
		return $retour;
	}

	private function transfertVersCoffreMunition($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreMunition - enter -");
		$retour = "";

		Zend_Loader::loadClass("VenteMunition");
		$venteMunitionTable = new VenteMunition();
		$munition = $venteMunitionTable->findByIdVente($vente["id_vente"]);

		if ($munition != null && count($munition) == 1) {
			$munition = $munition[0];

			Zend_Loader::loadClass("CoffreMunition");
			$coffreMunitionTable = new CoffreMunition();

			$data = array(
				"id_fk_type_coffre_munition" => $munition["id_fk_type_vente_munition"],
				"id_fk_braldun_coffre_munition" => $vente["id_fk_braldun_vente"],
				"quantite_coffre_munition" => $munition["quantite_vente_munition"],
			);
			$coffreMunitionTable->insertOrUpdate($data);
				
			$this->deleteVente($vente);

			$retour = $munition["quantite_vente_munition"];
			if ($munition["quantite_vente_munition"] > 1) {
				$retour .=  " ".$munition["nom_pluriel_type_munition"];
			} else {
				$retour .=  " ".$munition["nom_type_munition"];
			}
		} else {
			throw new Zend_Exception("Bral_Batchs_Hotel transfertVersCoffreMunition vente invalide:".$vente["id_vente"]);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreMunition - exit -");
		return $retour;
	}


	private function transfertVersCoffreMinerai($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreMinerai - enter -");
		$retour = "";

		Zend_Loader::loadClass("VenteMinerai");
		$venteMineraiTable = new VenteMinerai();
		$minerai = $venteMineraiTable->findByIdVente($vente["id_vente"]);

		if ($minerai != null && count($minerai) == 1) {
			$minerai = $minerai[0];

			Zend_Loader::loadClass("CoffreMinerai");
			$coffreMineraiTable = new CoffreMinerai();

			$prefix = "brut";

			if ($minerai["type_vente_minerai"] == "lingot") {
				$prefix = "lingots";
			}

			$data = array(
				"id_fk_type_coffre_minerai" => $minerai["id_fk_type_vente_minerai"],
				"id_fk_braldun_coffre_minerai" => $vente["id_fk_braldun_vente"],
				"quantite_".$prefix."_coffre_minerai" => $minerai["quantite_vente_minerai"],
			);
			$coffreMineraiTable->insertOrUpdate($data);
				
			$this->deleteVente($vente);

			$retour = $minerai["nom_type_minerai"]. " : ".$minerai["quantite_vente_minerai"];

			$s = "";
			if ($minerai["quantite_vente_minerai"] > 1) {
				$s = "s";
			}

			if ($minerai["type_vente_minerai"] == "lingot") {
				$retour .= " lingot".$s;
			} else {
				$retour .= " minerai".$s. " brut".$s;
			}
		} else {
			throw new Zend_Exception("Bral_Batchs_Hotel transfertVersCoffreMinerai vente invalide:".$vente["id_vente"]);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreMinerai - exit -");
		return $retour;
	}

	private function transfertVersCoffrePartieplante($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffrePartieplante - enter -");
		$retour = "";

		Zend_Loader::loadClass("VentePartieplante");
		$ventePartieplanteTable = new VentePartieplante();
		$partieplante = $ventePartieplanteTable->findByIdVente($vente["id_vente"]);

		if ($partieplante != null && count($partieplante) == 1) {
			$partieplante = $partieplante[0];

			Zend_Loader::loadClass("CoffrePartieplante");
			$coffrePartieplanteTable = new CoffrePartieplante();

			$prefix = "";

			if ($partieplante["type_vente_partieplante"] == "preparee") {
				$prefix = "_preparee";
			}

			$data = array(
				"id_fk_type_coffre_partieplante" => $partieplante["id_fk_type_vente_partieplante"],
				"id_fk_type_plante_coffre_partieplante" => $partieplante["id_fk_type_plante_vente_partieplante"],
				"id_fk_braldun_coffre_partieplante" => $vente["id_fk_braldun_vente"],
				"quantite".$prefix."_coffre_partieplante" => $partieplante["quantite_vente_partieplante"],
			);
			$coffrePartieplanteTable->insertOrUpdate($data);
				
			$this->deleteVente($vente);

			$retour = $partieplante["quantite_vente_partieplante"];

			$s = "";
			if ($partieplante["quantite_vente_partieplante"] > 1) {
				$s = "s";
			}
			$retour = $partieplante["quantite_vente_partieplante"]. " ".$partieplante["nom_type_partieplante"].$s. " ";
			$retour .= $partieplante["prefix_type_plante"].$partieplante["nom_type_plante"];
				
		} else {
			throw new Zend_Exception("Bral_Batchs_Hotel transfertVersCoffrePartieplante vente invalide:".$vente["id_vente"]);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffrePartieplante - exit -");
		return $retour;
	}

	private function transfertVersCoffrePotion($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffrePotion - enter -");
		$retour = "";

		Zend_Loader::loadClass("VentePotion");
		$ventePotionTable = new VentePotion();
		$potion = $ventePotionTable->findByIdVente($vente["id_vente"]);

		if ($potion != null && count($potion) == 1) {
			$potion = $potion[0];

			Zend_Loader::loadClass("CoffrePotion");
			$coffrePotionTable = new CoffrePotion();

			$data = array(
				"id_coffre_potion" => $potion["id_vente_potion"],
				"id_fk_braldun_coffre_potion" => $vente["id_fk_braldun_vente"],
			);
			$coffrePotionTable->insert($data);
				
			$this->deleteVente($vente);

			$retour = "la potion n°".$potion["id_vente_potion"]." ".$potion["nom_type_potion"]. " de qualité ".$potion["nom_type_qualite"]." et de niveau ".$potion["niveau_potion"];
		} else {
			throw new Zend_Exception("Bral_Batchs_Hotel transfertVersCoffrePotion vente invalide:".$vente["id_vente"]);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffrePotion - exit -");
		return $retour;
	}

	private function transfertVersCoffreRune($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreRune - enter -");
		$retour = "";

		Zend_Loader::loadClass("VenteRune");
		$venteRuneTable = new VenteRune();
		$rune = $venteRuneTable->findByIdVente($vente["id_vente"]);

		if ($rune != null && count($rune) == 1) {
			$rune = $rune[0];

			Zend_Loader::loadClass("CoffreRune");
			$coffreRuneTable = new CoffreRune();

			$data = array(
				"id_rune_coffre_rune" => $rune["id_rune_vente_rune"],
				"id_fk_braldun_coffre_rune" => $vente["id_fk_braldun_vente"],
			);
			$coffreRuneTable->insert($data);
				
			$this->deleteVente($vente);

			if ($rune["est_identifiee_rune"] == "oui") {
				$retour = "Rune ".$rune["nom_type_rune"]. " n°".$rune["id_rune_vente_rune"];
			} else {
				$retour = "Rune non identifiée n°".$rune["id_vente_rune"];
			}
		} else {
			throw new Zend_Exception("Bral_Batchs_Hotel transfertVersCoffreRune vente invalide:".$vente["id_vente"]);
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - transfertVersCoffreRune - exit -");
		return $retour;
	}

	private function deleteVente($vente) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - deleteVente - enter - idVente:".$vente["id_vente"]);
		$venteTable = new Vente();
		$where = "id_vente = ".$vente["id_vente"];
		$venteTable->delete($where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hotel - deleteVente - exit -");
	}

}