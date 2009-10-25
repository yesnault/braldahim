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
class Bral_Hotel_Acheter extends Bral_Hotel_Hotel {

	private $materiel = null;

	function getNomInterne() {
		return "box_action";
	}

	public function getTitreAction() {
		return "Hôtel des Ventes - Acheter";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Laban");
		Zend_Loader::loadClass("LabanMinerai");
		Zend_Loader::loadClass("LabanPartieplante");
		Zend_Loader::loadClass("LabanMateriel");
		Zend_Loader::loadClass("CharretteMinerai");
		Zend_Loader::loadClass("CharrettePartieplante");

		$this->idVente = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_1"));

		$poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		$tabDestinationTransfert[] = array("id_destination" => "laban", "texte" => "votre laban", "poids_restant" => $poidsRestant, "possible" => false);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$poidsRestant = $charrette["poids_transportable_charrette"] - $charrette["poids_transporte_charrette"];
			$tabDestinationTransfert[] = array("id_destination" => "charrette", "texte" => "votre charrette", "poids_restant" => $poidsRestant, "possible" => false);
		}
		$this->view->destinationTransfert = $tabDestinationTransfert;

		$this->view->charrette = $charrette;

		$this->prepareVente($this->idVente);
		$this->preparePrix();

		$this->view->estElementsEtal = false;
		$this->view->estElementsEtalAchat = false;
		$this->view->estElementsAchat = true;
	}

	private function prepareVente($idVente) {
		Zend_Loader::loadClass("Vente");
		$venteTable = new Vente();
		$vente = $venteTable->findByIdVente($idVente);

		if ($vente == null || count($vente) != 1) {
			throw new Zend_Exception(get_class($this)."::prepareVente invalide:".$idVente);
		}

		$vente = $vente[0];

		Zend_Loader::loadClass("VentePrixMinerai");
		$venteMineraiTable = new VentePrixMinerai();
		$venteMinerai = $venteMineraiTable->findByIdVente($idVente);

		$minerai = null;
		foreach($this->view->destinationTransfert as $d) {
			$this->prepareVentePrixMinerai($idVente, $d, $venteMinerai, $minerai);
		}

		Zend_Loader::loadClass("VentePrixPartiePlante");
		$ventePartiePlanteTable = new VentePrixPartiePlante();
		$ventePartiePlante = $ventePartiePlanteTable->findByIdVente($idVente);

		$partiesPlantes = null;
		foreach($this->view->destinationTransfert as $d) {
			$this->prepareVentePrixPartiePlante($idVente, $d, $ventePartiePlante, $partiesPlantes);
		}

		$vente = array(
			"id_vente" => $vente["id_vente"],
			"id_fk_hobbit_vente" => $vente["id_fk_hobbit_vente"],
			"prix_1_vente" => $vente["prix_1_vente"],
			"prix_2_vente" => $vente["prix_2_vente"],
			"prix_3_vente" => $vente["prix_3_vente"],
			"unite_1_vente" => $vente["unite_1_vente"],
			"unite_2_vente" => $vente["unite_2_vente"],
			"unite_3_vente" => $vente["unite_3_vente"],
			"commentaire_vente" => $vente["commentaire_vente"],
			"type_vente" => $vente["type_vente"],
			"prix_minerais" => $minerai,
			"prix_parties_plantes" => $partiesPlantes,
		);

		if ($vente["type_vente"] == "materiel") {
			$objet = $this->prepareVenteMateriel($idVente);
		} else if ($vente["type_vente"] == "aliment") {
			$objet = $this->prepareVenteAliment($idVente);
		} else if ($vente["type_vente"] == "element") {
			$objet = $this->prepareVenteElement($idVente);
		} else if ($vente["type_vente"] == "equipement") {
			$objet = $this->prepareVenteEquipement($idVente);
		} else if ($vente["type_vente"] == "minerai") {
			$objet = $this->prepareVenteMinerai($idVente);
		} else if ($vente["type_vente"] == "munition") {
			$objet = $this->prepareVenteMunition($idVente);
		} else if ($vente["type_vente"] == "partieplante") {
			$objet = $this->prepareVentePartieplante($idVente);
		} else if ($vente["type_vente"] == "potion") {
			$objet = $this->prepareVentePotion($idVente);
		} else if ($vente["type_vente"] == "rune") {
			$objet = $this->prepareVenteRune($idVente);
		}

		$tab = array(
			"vente" => $vente,
			"objet" => $objet,
		);

		$this->view->vente = $tab;
	}

	private function prepareVenteMateriel($idVente) {
		Zend_Loader::loadClass("VenteMateriel");
		$venteMaterielTable = new VenteMateriel();

		$materiel = $venteMaterielTable->findByIdVente($idVente);

		if ($materiel == null || count($materiel) != 1) {
			throw new Zend_Exception(get_class($this)."::prepareVenteMateriel invalide:".$idVente);
		}

		$materiel = $materiel[0];

		$estCharrette = false;
		$tabCharrette["possible"] = true;
		$tabCharrette["detail"] = "";

		if (substr($materiel["nom_systeme_type_materiel"], 0, 9) == "charrette") {
			$estCharrette = true;

			Zend_Loader::loadClass("Bral_Util_Metier");
			$tab = Bral_Util_Metier::prepareMetier($this->view->user->id_hobbit, $this->view->user->sexe_hobbit);
			$estMenuisierOuBucheron = false;
			if ($tab["tabMetierCourant"]["nom_systeme"] == "bucheron" || $tab["tabMetierCourant"]["nom_systeme"] == "menuisier") {
				$estMenuisierOuBucheron = true;
			}
			Zend_Loader::loadClass("Bral_Util_Charrette");
			$tab = Bral_Util_Charrette::calculAttraperPossible($materiel, $this->view->user, $estMenuisierOuBucheron);

			$charretteTable = new Charrette();
			$nombre = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);

			if ($nombre > 0) {
				$tabCharrette["possible"] = false;
				$tabCharrette["detail"] = "Vous possédez déjà une charrette";
			}
		}

		$placeDispo = false;
		$i = 0;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= $materiel["poids_type_materiel"] || $estCharrette) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i ++;
		}

		$tabMateriel = array(
			"id_materiel" => $materiel["id_vente_materiel"],
			"nom" => $materiel["nom_type_materiel"],
			"id_type_materiel" => $materiel["id_fk_type_materiel"],
			'nom_systeme_type_materiel' => $materiel["nom_systeme_type_materiel"],
			'capacite' => $materiel["capacite_type_materiel"], 
			'durabilite' => $materiel["durabilite_type_materiel"], 
			'usure' => $materiel["usure_type_materiel"], 
			'poids' => $materiel["poids_type_materiel"], 
			"poids" => $materiel["poids_type_materiel"],
			"place_dispo" => $placeDispo,
			"est_charrette" => $estCharrette,
			"charrette_possible" => $tabCharrette["possible"],
			"charrette_detail" => $tabCharrette["detail"],
		);

		return $tabMateriel;
	}

	private function prepareVenteAliment($idVente) {
		Zend_Loader::loadClass("VenteAliment");
		$venteAlimentTable = new VenteAliment();

		$aliments = $venteAlimentTable->findByIdVente($idVente);

		if ($aliments == null || count($aliments) < 1) {
			throw new Zend_Exception(get_class($this)."::prepareVenteAliment invalide:".$idVente);
		}


		$placeDispo = false;
		$i = 0;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= (count($aliments) * Bral_Util_Poids::POIDS_ALIMENT)) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i ++;
		}

		$nom = "<br>";
		foreach($aliments as $a) {
			$nom .= $a["nom_type_aliment"]." +".$a["bbdf_vente_aliment"]."%<br>";
		}

		$tabAliments = array(
			"aliments" => $aliments,
			"nom" => $nom,
			"place_dispo" => $placeDispo,
			"est_charrette" => false,
			"charrette_possible" => true,
		);

		return $tabAliments;
	}

	private function prepareVenteElement($idVente) {
		Zend_Loader::loadClass("VenteElement");
		$venteElementTable = new VenteElement();

		$element = $venteElementTable->findByIdVente($idVente);

		if ($element == null || count($element) != 1) {
			throw new Zend_Exception(get_class($this)."::prepareVenteElement invalide:".$idVente);
		}

		$element = $element[0];

		$placeDispo = false;
		$i = 0;

		$poidsUnitaire = 10000;
		$nom = $element["quantite_vente_element"]. " ";
		if ($element["type_vente_element"] == "viande_fraiche") {
			$poidsUnitaire = Bral_Util_Poids::POIDS_VIANDE;
			if ($element["quantite_vente_element"] > 1) {
				$nom .= " viandes fraîches";
			} else {
				$nom .= " viande fraîche";
			}
		} else if ($element["type_vente_element"] == "peau") {
			$poidsUnitaire = Bral_Util_Poids::POIDS_PEAU;
			if ($element["quantite_vente_element"] > 1) {
				$nom .= " peaux";
			} else {
				$nom .= " peau";
			}
		} else if ($element["type_vente_element"] == "viande_preparee") {
			$poidsUnitaire = Bral_Util_Poids::POIDS_VIANDE_PREPAREE;
			if ($element["quantite_vente_element"] > 1) {
				$nom .= " viandes préparées";
			} else {
				$nom .= " viande préparée";
			}
		} else if ($element["type_vente_element"] == "cuir") {
			$poidsUnitaire = Bral_Util_Poids::POIDS_CUIR;
			if ($element["quantite_vente_element"] > 1) {
				$nom .= " cuirs";
			} else {
				$nom .= " cuir";
			}
		} else if ($element["type_vente_element"] == "fourrure") {
			$poidsUnitaire = Bral_Util_Poids::POIDS_FOURRURE;
			if ($element["quantite_vente_element"] > 1) {
				$nom .= " fourrures";
			} else {
				$nom .= " fourrure";
			}
		} else if ($element["type_vente_element"] == "planche") {
			$poidsUnitaire = Bral_Util_Poids::POIDS_PLANCHE;
			if ($element["quantite_vente_element"] > 1) {
				$nom .= " planches";
			} else {
				$nom .= " planche";
			}
		} else if ($element["type_vente_element"] == "rondin") {
			$poidsUnitaire = Bral_Util_Poids::POIDS_RONDIN;
			if ($element["quantite_vente_element"] > 1) {
				$nom .= " rondins";
			} else {
				$nom .= " rondin";
			}
		}

		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= ($element["quantite_vente_element"] * $poidsUnitaire)) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i ++;
		}

		$tabElements = array(
			"type_vente_element" => $element["type_vente_element"],
			"quantite_vente_element" => $element["quantite_vente_element"],
			"nom" => $nom,
			"place_dispo" => $placeDispo,
			"est_charrette" => false,
			"charrette_possible" => true,
		);

		return $tabElements;
	}

	private function prepareVenteMunition($idVente) {
		Zend_Loader::loadClass("VenteMunition");
		$venteMunitionTable = new VenteMunition();

		$munition = $venteMunitionTable->findByIdVente($idVente);

		if ($munition == null || count($munition) != 1) {
			throw new Zend_Exception(get_class($this)."::prepareVenteMunition invalide:".$idVente);
		}

		$munition = $munition[0];

		$placeDispo = false;
		$i = 0;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= $munition["quantite_vente_munition"] * Bral_Util_Poids::POIDS_MUNITION) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i ++;
		}

		$nom = $munition["quantite_vente_munition"]. " ";
		if ($munition["quantite_vente_munition"] > 1) {
			$nom .= $munition["nom_pluriel_type_munition"];
		} else {
			$nom .= $munition["nom_type_munition"];
		}

		$tabMunition = array(
			"nom" => $nom,
			"quantite_vente_munition" => $munition["quantite_vente_munition"],
			"id_type_munition" => $munition["id_fk_type_vente_munition"],
			'nom_systeme_type_munition' => $munition["nom_systeme_type_munition"],
			"place_dispo" => $placeDispo,
			"est_charrette" => false,
			"charrette_possible" => true,
		);

		return $tabMunition;
	}

	private function prepareVenteMinerai($idVente) {
		Zend_Loader::loadClass("VenteMinerai");
		$venteMineraiTable = new VenteMinerai();

		$minerai = $venteMineraiTable->findByIdVente($idVente);

		if ($minerai == null || count($minerai) != 1) {
			throw new Zend_Exception(get_class($this)."::prepareVenteMinerai invalide:".$idVente);
		}

		$minerai = $minerai[0];

		$poidsUnitaire = Bral_Util_Poids::POIDS_MINERAI;

		if ($minerai["type_vente_minerai"] == "lingot") {
			$poidsUnitaire = Bral_Util_Poids::POIDS_LINGOT;
		}

		$placeDispo = false;
		$i = 0;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= $minerai["quantite_vente_minerai"] * $poidsUnitaire) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i ++;
		}

		$nom = $minerai["nom_type_minerai"]." : ".$minerai["quantite_vente_minerai"];

		$s = "";
		if ($minerai["quantite_vente_minerai"] > 1) {
			$s = "s";
		}

		if ($minerai["type_vente_minerai"] == "lingot") {
			$nom .= " lingot".$s;
		} else {
			$nom .= " minerai".$s. " brut".$s;
		}

		$tabMinerai = array(
			"nom" => $nom,
			"quantite_vente_minerai" => $minerai["quantite_vente_minerai"],
			"id_type_minerai" => $minerai["id_fk_type_vente_minerai"],
			"type_vente_minerai" => $minerai["type_vente_minerai"],
			"place_dispo" => $placeDispo,
			"est_charrette" => false,
			"charrette_possible" => true,
		);

		return $tabMinerai;
	}

	private function prepareVentePartieplante($idVente) {
		Zend_Loader::loadClass("VentePartieplante");
		$ventePartieplanteTable = new VentePartieplante();

		$partieplante = $ventePartieplanteTable->findByIdVente($idVente);

		if ($partieplante == null || count($partieplante) != 1) {
			throw new Zend_Exception(get_class($this)."::prepareVentePartieplante invalide:".$idVente);
		}

		$partieplante = $partieplante[0];

		if ($partieplante["type_vente_partieplante"] == "preparee") {
			$poidsUnitaire = Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE;
		}

		$placeDispo = false;
		$i = 0;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= $partieplante["quantite_vente_partieplante"] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i ++;
		}

		$nom = $partieplante["quantite_vente_partieplante"]. " ".$partieplante["nom_type_partieplante"];

		$s = "";
		if ($partieplante["quantite_vente_partieplante"] > 1) {
			$s .= "s";
			$nom .= $s;
		}

		if ($partieplante["type_vente_partieplante"] == "preparee") {
			$nom .= " préparée".$s;
		} else {
			$nom .= " brute".$s;
		}

		$nom .= " " .$partieplante["prefix_type_plante"].$partieplante["nom_type_plante"];

		$tabPartieplante = array(
			"nom" => $nom,
			"quantite_vente_partieplante" => $partieplante["quantite_vente_partieplante"],
			"id_type_partieplante" => $partieplante["id_fk_type_vente_partieplante"],
			"id_type_plante" => $partieplante["id_fk_type_plante_vente_partieplante"],
			"type_vente_partieplante" => $partieplante["type_vente_partieplante"],
			"place_dispo" => $placeDispo,
			"est_charrette" => false,
			"charrette_possible" => true,
		);

		return $tabPartieplante;
	}

	private function prepareVenteEquipement($idVente) {
		Zend_Loader::loadClass("VenteEquipement");
		$venteEquipementTable = new VenteEquipement();

		$equipement = $venteEquipementTable->findByIdVente($idVente);

		if ($equipement == null || count($equipement) != 1) {
			throw new Zend_Exception(get_class($this)."::prepareVenteEquipement invalide:".$idVente);
		}

		$equipement = $equipement[0];

		$placeDispo = false;
		$i = 0;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= $equipement["poids_equipement"]) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i ++;
		}

		$runes = $this->prepareEquipementRune($equipement["id_vente_equipement"]);
		$bonus = $this->prepareEquipementBonus($equipement["id_vente_equipement"]);

		Zend_Loader::loadClass("Bral_Util_Equipement");
		$tabEquipement = array(
			"id_equipement" => $equipement["id_vente_equipement"],
			"nom" => Bral_Util_Equipement::getNomByIdRegion($equipement, $equipement["id_fk_region_equipement"]),
			"nom_standard" => $equipement["nom_type_equipement"],
			"qualite" => $equipement["nom_type_qualite"],
			"niveau" => $equipement["niveau_recette_equipement"],
			"id_type_emplacement" => $equipement["id_type_emplacement"],
			"id_type_equipement" => $equipement["id_type_equipement"],
			"emplacement" => $equipement["nom_type_emplacement"],
			"nom_systeme_type_emplacement" => $equipement["nom_systeme_type_emplacement"],
			"nb_runes" => $equipement["nb_runes_equipement"],
			"id_fk_recette_equipement" => $equipement["id_fk_recette_equipement"],
			"armure" => $equipement["armure_recette_equipement"],
			"force" => $equipement["force_recette_equipement"],
			"agilite" => $equipement["agilite_recette_equipement"],
			"vigueur" => $equipement["vigueur_recette_equipement"],
			"sagesse" => $equipement["sagesse_recette_equipement"],
			"vue" => $equipement["vue_recette_equipement"],
			"bm_attaque" => $equipement["bm_attaque_recette_equipement"],
			"bm_degat" => $equipement["bm_degat_recette_equipement"],
			"bm_defense" => $equipement["bm_defense_recette_equipement"],
			"suffixe" => $equipement["suffixe_mot_runique"],
			"id_fk_mot_runique" => $equipement["id_fk_mot_runique_equipement"],
			"id_fk_region" => $equipement["id_fk_region_equipement"],
			"nom_systeme_mot_runique" => $equipement["nom_systeme_mot_runique"],
			"poids" => $equipement["poids_equipement"],
			"place_dispo" => $placeDispo,
			"runes" => $runes,
			"bonus" => $bonus,
			"etat_courant" => $equipement["etat_courant_equipement"],
			"etat_initial" => $equipement["etat_initial_equipement"],
			"ingredient" => $equipement["nom_type_ingredient"],
			"est_charrette" => false,
			"charrette_possible" => true,
		);

		return $tabEquipement;
	}

	private function prepareVentePotion($idVente) {
		Zend_Loader::loadClass("VentePotion");
		$ventePotionTable = new VentePotion();

		$potion = $ventePotionTable->findByIdVente($idVente);

		if ($potion == null || count($potion) != 1) {
			throw new Zend_Exception(get_class($this)."::prepareVentePotion invalide:".$idVente);
		}

		$potion = $potion[0];

		$placeDispo = false;
		$i = 0;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= Bral_Util_Poids::POIDS_POTION) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i ++;
		}
		Zend_Loader::loadClass("Bral_Util_Potion");
		$nom = Bral_Util_Potion::getNomType($potion["type_potion"]);
		$nom .= " ".$potion["nom_type_potion"]. " n°".$potion["id_vente_potion"].", de qualité ".$potion["nom_type_qualite"]." et de niveau ".$potion["niveau_potion"];

		$tabPotion = array(
			"id_potion" => $potion["id_vente_potion"],
			"nom" => $nom,
			"id_fk_type_qualite_potion" => $potion["id_fk_type_qualite_potion"],
			"id_fk_type_potion" => $potion["id_fk_type_potion"],
			"niveau_potion" => $potion["niveau_potion"],
			"place_dispo" => $placeDispo,
			"est_charrette" => false,
			"charrette_possible" => true,
		);

		return $tabPotion;
	}

	private function prepareVenteRune($idVente) {
		Zend_Loader::loadClass("VenteRune");
		$venteRuneTable = new VenteRune();

		$rune = $venteRuneTable->findByIdVente($idVente);

		if ($rune == null || count($rune) != 1) {
			throw new Zend_Exception(get_class($this)."::prepareVenteRune invalide:".$idVente);
		}

		$rune = $rune[0];

		$placeDispo = false;
		$i = 0;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["poids_restant"] >= Bral_Util_Poids::POIDS_RUNE) {
				$placeDispo = true;
				$this->view->destinationTransfert[$i]["possible"] = true;
			}
			$i ++;
		}

		if ($rune["est_identifiee_rune"] == "oui") {
			$nom = "Rune ".$rune["nom_type_rune"]. ", n°".$rune["id_rune_vente_rune"];
		} else {
			$nom = "Rune non identifiée, n°".$rune["id_rune_vente_rune"];
		}

		$tabRune = array(
			"id_rune" => $rune["id_rune_vente_rune"],
			"nom" => $nom,
			"place_dispo" => $placeDispo,
			"est_charrette" => false,
			"charrette_possible" => true,
		);

		return $tabRune;
	}

	private function prepareEquipementRune($idEquipement) {
		Zend_Loader::loadClass("EquipementRune");
		$equipementRuneTable = new EquipementRune();
		$equipementRunes = $equipementRuneTable->findByIdEquipement($idEquipement);

		$runes = null;
		if (count($equipementRunes) > 0) {
			foreach($equipementRunes as $r) {
				if ($r["id_equipement_rune"] == $idEquipement) {
					$runes[] = array(
						"id_rune_equipement_rune" => $r["id_rune_equipement_rune"],
						"id_fk_type_rune" => $r["id_fk_type_rune"],
						"nom_type_rune" => $r["nom_type_rune"],
						"image_type_rune" => $r["image_type_rune"],
						"effet_type_rune" => $r["effet_type_rune"],
					);
				}
			}
		}
		return $runes;
	}

	private function prepareEquipementBonus($idEquipement) {
		Zend_Loader::loadClass("EquipementBonus");
		$equipementBonusTable = new EquipementBonus();
		$equipementBonus = $equipementBonusTable->findByIdEquipement($idEquipement);

		$bonus = null;
		if (count($equipementBonus) > 0) {
			foreach($equipementBonus as $b) {
				if ($b["id_equipement_bonus"] == $idEquipement) {
					$bonus = $b;
					break;
				}
			}
		}
		return $bonus;
	}

	private function prepareVentePrixMinerai($idVente, $destination, $venteMinerai, &$minerai) {

		if ($destination["id_destination"] == "laban") {
			$table = new LabanMinerai();
			$minerais = $table->findByIdHobbit($this->view->user->id_hobbit);
		} else {
			$table = new CharretteMinerai();
			$minerais = $table->findByIdCharrette($this->view->charrette["id_charrette"]);
		}

		if (count($venteMinerai) > 0) {
			foreach($venteMinerai as $r) {
				if ($r["id_fk_vente_prix_minerai"] == $idVente) {
					$possible = false;
					if ($r["prix_vente_prix_minerai"] == 0) {
						$possible = true;
					}
					foreach ($minerais as $m) {
						if ($m["nom_systeme_type_minerai"] == $r["nom_systeme_type_minerai"]
						&& $r["prix_vente_prix_minerai"] <= $m["quantite_brut_".$destination["id_destination"]."_minerai"]) {
							$possible = true;
							break;
						}
					}

					$minerai[] = array(
						"prix_vente_prix_minerai" => $r["prix_vente_prix_minerai"],
						"nom_type_minerai" => $r["nom_type_minerai"],
						"id_fk_type_minerai" => $r["id_fk_type_vente_prix_minerai"],
						"possible" => $possible,
						"id_destination" => $destination["id_destination"],
					);
				}
			}
		}
	}

	private function prepareVentePrixPartiePlante($idVente, $destination, $ventePartiePlante, &$partiesPlantes) {

		if ($destination["id_destination"] == "laban") {
			$labanPartiePlanteTable = new LabanPartieplante();
			$partiePlantes = $labanPartiePlanteTable->findByIdHobbit($this->view->user->id_hobbit);
		} else {
			$table = new CharrettePartieplante();
			$partiePlantes = $table->findByIdCharrette($this->view->charrette["id_charrette"]);
		}

		if (count($ventePartiePlante) > 0) {
			foreach($ventePartiePlante as $a) {
				if ($a["id_fk_vente_prix_partieplante"] == $idVente) {
					$possible = false;
					if ($a["prix_vente_prix_partieplante"] == 0) {
						$possible = true;
					}
					foreach ($partiePlantes as $p) {
						if ($p["nom_systeme_type_partieplante"] == $a["nom_systeme_type_partieplante"]
						&& $p["nom_systeme_type_plante"] == $a["nom_systeme_type_plante"]
						&& $a["prix_vente_prix_partieplante"] <= $p["quantite_".$destination["id_destination"]."_partieplante"] ) {
							$possible = true;
							break;
						}
					}

					$partiesPlantes[] = array(
						"prix_vente_prix_partieplante" => $a["prix_vente_prix_partieplante"],
						"nom_type_plante" => $a["nom_type_plante"],
						"nom_type_partieplante" => $a["nom_type_partieplante"],
						"prefix_type_plante" => $a["prefix_type_plante"],
						"id_fk_type_plante" => $a["id_fk_type_plante_vente_prix_partieplante"],
						"id_fk_type_partieplante" => $a["id_fk_type_vente_prix_partieplante"],
						"possible" => $possible,
						"id_destination" => $destination["id_destination"],
					);
				}
			}
		}
	}

	private function preparePrix() {
		$tabPrix = null;

		$possible = false;
		$acheterOk = false;

		if ($this->view->vente["vente"]["prix_1_vente"] >= 0 && $this->view->vente["vente"]["unite_1_vente"] > 0) {
			$prix = $this->view->vente["vente"]["prix_1_vente"];
			$nom = Bral_Util_Registre::getNomUnite($this->view->vente["vente"]["unite_1_vente"], false, $this->view->vente["vente"]["prix_1_vente"]);
			$type = "element";
			foreach($this->view->destinationTransfert as $d) {
				$possible = $this->calculPrixUnitaire($d, $prix, Bral_Util_Registre::getNomUnite($this->view->vente["vente"]["unite_1_vente"], true));
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $this->view->vente["vente"]["unite_1_vente"], "id_destination" => $d["id_destination"]);
			}
		}
			
		if ($this->view->vente["vente"]["prix_2_vente"] >= 0 && $this->view->vente["vente"]["unite_2_vente"] > 0) {
			$prix = $this->view->vente["vente"]["prix_2_vente"];
			$nom = Bral_Util_Registre::getNomUnite($this->view->vente["vente"]["unite_2_vente"], false, $this->view->vente["vente"]["prix_2_vente"]);
			$type = "element";
			foreach($this->view->destinationTransfert as $d) {
				$possible = $this->calculPrixUnitaire($d, $prix, Bral_Util_Registre::getNomUnite($this->view->vente["vente"]["unite_2_vente"], true));
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $this->view->vente["vente"]["unite_2_vente"], "id_destination" => $d["id_destination"]);
			}
		}
			
		if ($this->view->vente["vente"]["prix_3_vente"] >= 0 && $this->view->vente["vente"]["unite_3_vente"] > 0) {
			$prix = $this->view->vente["vente"]["prix_3_vente"];
			$nom = Bral_Util_Registre::getNomUnite($this->view->vente["vente"]["unite_3_vente"], false, $this->view->vente["vente"]["prix_3_vente"]);
			$type = "element";
			foreach($this->view->destinationTransfert as $d) {
				$possible = $this->calculPrixUnitaire($d, $prix, Bral_Util_Registre::getNomUnite($this->view->vente["vente"]["unite_3_vente"], true));
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "possible" => $possible, "unite" => $this->view->vente["vente"]["unite_3_vente"], "id_destination" => $d["id_destination"]);
			}
		}
			
		if (count($this->view->vente["vente"]["prix_minerais"]) > 0) {
			foreach($this->view->vente["vente"]["prix_minerais"] as $m) {
				if ($m["possible"] === true) {
					$acheterOk = true;
				}
				$prix = $m["prix_vente_prix_minerai"];
				$nom = htmlspecialchars($m["nom_type_minerai"]). " brut";
				if ($prix > 1) {
					$nom .= "s";
				}
				$type = "minerais";
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "minerais" => $m, "possible" => $m["possible"], "id_destination" => $m["id_destination"]);
			}
		}
			
		if (count($this->view->vente["vente"]["prix_parties_plantes"]) > 0) {
			foreach($this->view->vente["vente"]["prix_parties_plantes"] as $p) {
				if ($p["possible"] === true) {
					$acheterOk = true;
				}
				$prix = $p["prix_vente_prix_partieplante"]. " ";
				$s = "";
				if ($p["prix_vente_prix_partieplante"] > 1) {
					$s = "s";
				}
				$nom = htmlspecialchars($p["nom_type_partieplante"]). "$s ";
				$nom .= htmlspecialchars($p["prefix_type_plante"]);
				$nom .= htmlspecialchars($p["nom_type_plante"]);
				$type = "parties_plantes";
				$tabPrix[] = array("prix" => $prix, "nom" => $nom, "type" => $type, "parties_plantes" => $p, "possible" => $p["possible"], "id_destination" => $p["id_destination"]);
			}
		}
			
		$this->view->acheterOk = $acheterOk;
		$this->view->prix = $tabPrix;
	}

	private function calculPrixUnitaire($destination, $prix, $nomSysteme) {
		$retour = false;

		if ($destination["id_destination"] == "laban") {
			$table = new Laban();
			$conteneur = $table->findByIdHobbit($this->view->user->id_hobbit);
			$suffixe = "laban";
			$possedeConteneur = true;
			if ($conteneur == null || count($conteneur) < 1) {
				$conteneur["quantite_peau_laban"] = 0;
				$conteneur["quantite_rondin_laban"] = 0;
			} else {
				$conteneur = $conteneur[0];
			}
		} else {
			$conteneur = $this->view->charrette;
			if ($conteneur == null) {
				$possedeConteneur = false;
			} else {
				$possedeConteneur = true;
			}
			$suffixe = "charrette";
		}

		if (($nomSysteme == "peau" || $nomSysteme == "rondin") && $possedeConteneur == true) {
			if ($conteneur["quantite_".$nomSysteme."_".$suffixe] >= $prix) {
				$retour = true;
			}
		} elseif ($nomSysteme == "castar") {
			if ($destination["id_destination"] == "laban") {
				if ($this->view->user->castars_hobbit >= $prix) {
					$retour = true;
				}
			} else {
				if ($conteneur["quantite_castar_charrette"] >= $prix) {
					$retour = true;
				}
			}
		}
		return $retour;
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this)."::pas assez de PA");
		}

		$idPrix = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_2"));

		// on verifie que idPrix est dans la liste des prix Ok.
		if (!array_key_exists($idPrix, $this->view->prix)) {
			throw new Zend_Exception(get_class($this)."::prix invalide. non connu");
		}

		// on verifie que le hobbit a assez de ressources.
		if ($this->view->prix[$idPrix]["possible"] !== true) {
			throw new Zend_Exception(get_class($this)."::prix invalide");
		}

		if ($this->view->vente["objet"]["place_dispo"] !== true) {
			throw new Zend_Exception(get_class($this)."::place invalide");
		}

		$idDestination = $this->request->get("valeur_3");

		if ($this->view->charrette == null && $this->request->get("valeur_3") == "charrette") {
			throw new Zend_Exception(get_class($this)." destination invalide 2");
		}

		Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));

		if (intval($this->idVente) != intval($this->request->getPost("valeur_1"))) {
			throw new Zend_Exception("Vente invalide : ".$this->idVente. " - ".$this->request->getPost("valeur_1"));
		}

		// on regarde si l'on connait la destination
		$flag = false;
		$destination = null;
		foreach($this->view->destinationTransfert as $d) {
			if ($d["id_destination"] == $idDestination) {
				$destination = $d;
				$flag = true;
				break;
			}
		}

		if ($flag == false) {
			throw new Zend_Exception(get_class($this)." destination inconnue=".$idDestination);
		}

		if ($destination["possible"] == false) {
			throw new Zend_Exception(get_class($this)." destination invalide 3");
		}

		$this->view->detailPrix = "";

		if ($this->view->prix[$idPrix]["type"] == "element") {
			$this->calculAchatElement($this->view->prix[$idPrix]);
		} elseif ($this->view->prix[$idPrix]["type"] == "minerais") {
			$this->calculAchatMinerais($this->view->prix[$idPrix]);
		} elseif ($this->view->prix[$idPrix]["type"] == "parties_plantes") {
			$this->calculAchatPartiesPlantes($this->view->prix[$idPrix]);
		}

		if ($this->view->vente["vente"]["type_vente"] == "materiel") {
			$objet = $this->calculTransfertMateriel($idDestination);
		} else if ($this->view->vente["vente"]["type_vente"] == "aliment") {
			$objet = $this->calculTransfertAliment($idDestination);
		} else if ($this->view->vente["vente"]["type_vente"] == "element") {
			$objet = $this->calculTransfertElement($idDestination);
		} else if ($this->view->vente["vente"]["type_vente"] == "equipement") {
			$objet = $this->calculTransfertEquipement($idDestination);
		} else if ($this->view->vente["vente"]["type_vente"] == "minerai") {
			$objet = $this->calculTransfertMinerai($idDestination);
		} else if ($this->view->vente["vente"]["type_vente"] == "munition") {
			$objet = $this->calculTransfertMunition($idDestination);
		} else if ($this->view->vente["vente"]["type_vente"] == "partieplante") {
			$objet = $this->calculTransfertPartieplante($idDestination);
		} else if ($this->view->vente["vente"]["type_vente"] == "potion") {
			$objet = $this->calculTransfertPotion($idDestination);
		} else if ($this->view->vente["vente"]["type_vente"] == "rune") {
			$objet = $this->calculTransfertRune($idDestination);
		}

		$this->view->destination = $destination;

		if ($this->view->detailPrix != "") {
			$this->view->detailPrix = mb_substr($this->view->detailPrix, 0, -2);
		}

		Zend_Loader::loadClass("Bral_Util_Messagerie");
		$message = "[Hôtel des Ventes]".PHP_EOL.PHP_EOL;
		$message .=  $this->view->user->prenom_hobbit. " ".$this->view->user->nom_hobbit;
		$message .= " (".$this->view->user->id_hobbit.") a achet&eacute; ".PHP_EOL;
		$message .= $this->view->objetAchat. PHP_EOL. "pour ".$this->view->detailPrix." (gain placé dans votre coffre).".PHP_EOL.PHP_EOL;
		$message .= "&Eacute;mile Claclac, gestionnaire de l'Hôtel des ventes.".PHP_EOL;
		$message .= "Inutile de répondre à ce message.";
		Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->hotel->id_hobbit, $this->view->vente["vente"]["id_fk_hobbit_vente"], $message, $this->view);
	}

	private function calculAchatElement($prix) {

		if ($prix["id_destination"] == "charrette") {
			$table = new Charrette();
			$suffixe = "charrette";
		} else {
			$table = new Laban();
			$suffixe = "laban";
		}

		Zend_Loader::loadClass("Coffre");
		$coffreTable = new Coffre();

		$nomSysteme = Bral_Util_Registre::getNomUnite($prix["unite"], true);
		if ($nomSysteme  == "peau" ||$nomSysteme == "rondin") {
			$data = array(
				"id_fk_hobbit_".$suffixe => $this->view->user->id_hobbit,
				"quantite_".$nomSysteme."_".$suffixe => -$prix["prix"],
			);
			$table->insertOrUpdate($data);

			if ($prix["prix"] > 0) {
				$data = array(
					'id_fk_hobbit_coffre' => $this->view->vente["vente"]["id_fk_hobbit_vente"],
					"quantite_".$nomSysteme."_coffre" => $prix["prix"],
				);
				$coffreTable->insertOrUpdate($data);
			}

			$this->view->detailPrix .= $prix["prix"]. " ". Bral_Util_Registre::getNomUnite($prix["unite"], false, $prix["prix"]).", ";

		} elseif ($nomSysteme  == "castar") {
			if ($prix["id_destination"] == "charrette") {
				$data = array(
					"id_fk_hobbit_".$suffixe => $this->view->user->id_hobbit,
					"quantite_".$nomSysteme."_".$suffixe => -$prix["prix"],
				);
				$table->insertOrUpdate($data);
			} else {
				$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $prix["prix"];
			}

			if ($prix["prix"] > 0) {
				$data = array(
					'id_fk_hobbit_coffre' => $this->view->vente["vente"]["id_fk_hobbit_vente"],
					'quantite_castar_coffre' => $prix["prix"],
				);
				$coffreTable->insertOrUpdate($data);
			}

			$this->view->detailPrix .= $prix["prix"]. " ". Bral_Util_Registre::getNomUnite($prix["unite"], false, $prix["prix"]).", ";
		}
	}

	private function calculAchatMinerais($prix) {

		if ($prix["id_destination"] == "charrette") {
			$table = new CharretteMinerai();
			$suffixe = "charrette";
		} else {
			$table = new LabanMinerai();
			$suffixe = "laban";
		}

		$data = array(
			"id_fk_type_".$suffixe."_minerai" => $prix["minerais"]["id_fk_type_minerai"],
			"quantite_brut_".$suffixe."_minerai" => - $prix["prix"],
		);

		if ($prix["id_destination"] == "charrette") {
			$data["id_fk_charrette_minerai"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban_minerai"] = $this->view->user->id_hobbit;
		}

		$table->insertOrUpdate($data);

		Zend_Loader::loadClass("CoffreMinerai");
		$coffreMineraiTable = new CoffreMinerai();
		if ($prix["prix"] > 0) {
			$data = array(
				'id_fk_hobbit_coffre_minerai' => $this->view->vente["vente"]["id_fk_hobbit_vente"],
				'id_fk_type_coffre_minerai' => $prix["minerais"]["id_fk_type_minerai"],
				'quantite_brut_coffre_minerai' => $prix["prix"],
			);
			$coffreMineraiTable->insertOrUpdate($data);
		}

		$this->view->detailPrix .= $prix["prix"]. " ".$prix["nom"].", ";
	}

	private function calculAchatPartiesPlantes($prix) {
		if ($prix["id_destination"] == "charrette") {
			$table = new CharrettePartieplante();
			$suffixe = "charrette";
		} else {
			$table = new LabanPartieplante();
			$suffixe = "laban";
		}

		$data = array(
			"id_fk_type_".$suffixe."_partieplante" => $prix["parties_plantes"]["id_fk_type_partieplante"],
			"id_fk_type_plante_".$suffixe."_partieplante" => $prix["parties_plantes"]["id_fk_type_plante"],
			"quantite_".$suffixe."_partieplante" => - $prix["prix"],
		);

		if ($prix["id_destination"] == "charrette") {
			$data["id_fk_charrette_partieplante"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban_partieplante"] = $this->view->user->id_hobbit;
		}

		$table->insertOrUpdate($data);

		Zend_Loader::loadClass("CoffrePartieplante");
		$coffrePartiePlanteTable = new CoffrePartieplante();

		if ($prix["prix"] > 0) {
			$data = array('quantite_coffre_partieplante' => $prix["prix"],
						  'id_fk_type_coffre_partieplante' => $prix["parties_plantes"]["id_fk_type_partieplante"],
						  'id_fk_type_plante_coffre_partieplante' => $prix["parties_plantes"]["id_fk_type_plante"],
						  'id_fk_hobbit_coffre_partieplante' => $this->view->vente["vente"]["id_fk_hobbit_vente"],
			);
			$coffrePartiePlanteTable->insertOrUpdate($data);
		}

		$this->view->detailPrix .= $prix["prix"]. " ".$prix["nom"].", ";
	}

	private function calculTransfertMateriel($idDestination) {

		if ($this->view->vente["objet"]["est_charrette"] == true) {
			$dataUpdate = array(
				"id_fk_hobbit_charrette" => $this->view->user->id_hobbit,
				"x_charrette" => null,
				"y_charrette" => null,
				"id_charrette" => $this->view->vente["objet"]["id_materiel"],
				"durabilite_max_charrette" => $this->view->vente["objet"]["durabilite"],
				"durabilite_actuelle_charrette" => $this->view->vente["objet"]["durabilite"],
				"poids_transportable_charrette" => $this->view->vente["objet"]["capacite"],
				"poids_transporte_charrette" => 0,
			);
			$where = "id_charrette = ".$this->view->vente["objet"]["id_materiel"];
			$charretteTable = new Charrette();
			$charretteTable->insert($dataUpdate);
		} else {
			if ($idDestination == "charrette") {
				Zend_Loader::loadClass("CharretteMateriel");
				$table = new CharretteMateriel();
				$suffixe = "charrette";
			} else {
				Zend_Loader::loadClass("LabanMateriel");
				$table = new LabanMateriel();
				$suffixe = "laban";
			}

			$data = array(
				"id_".$suffixe."_materiel" => $this->view->vente["objet"]["id_materiel"],
			);

			if ($idDestination == "charrette") {
				$data["id_fk_charrette_materiel"] = $this->view->charrette["id_charrette"];
			} else {
				$data["id_fk_hobbit_laban_materiel"] = $this->view->user->id_hobbit;
			}

			$table->insert($data);
		}

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}

		$this->view->objetAchat = $this->view->vente["objet"]["nom"].", n°".$this->view->vente["objet"]["id_materiel"];

		$venteTable = new Vente();
		$where = "id_vente=".$this->idVente;
		$venteTable->delete($where);
		
		$details = "[h".$this->view->user->id_hobbit."] a acheté le matériel n°".$this->view->vente["objet"]["id_materiel"]. " à l'Hôtel des Ventes";
		Zend_Loader::loadClass("Bral_Util_Materiel");
		Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_ACHETER_ID, $this->view->vente["objet"]["id_materiel"], $details);
	}

	private function calculTransfertEquipement($idDestination) {

		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharretteEquipement");
			$table = new CharretteEquipement();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanEquipement");
			$table = new LabanEquipement();
			$suffixe = "laban";
		}

		$data = array(
				"id_".$suffixe."_equipement" => $this->view->vente["objet"]["id_equipement"],
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_equipement"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban_equipement"] = $this->view->user->id_hobbit;
		}
		$table->insert($data);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}

		$this->view->objetAchat = $this->view->vente["objet"]["nom"].", n°".$this->view->vente["objet"]["id_equipement"];

		$venteTable = new Vente();
		$where = "id_vente=".$this->idVente;
		$venteTable->delete($where);

		$details = "[h".$this->view->user->id_hobbit."] a acheté la pièce d'équipement n°".$this->view->vente["objet"]["id_equipement"]. " à l'Hôtel des Ventes";
		Bral_Util_Equipement::insertHistorique(Bral_Util_Equipement::HISTORIQUE_ACHETER_ID, $this->view->vente["objet"]["id_equipement"], $details);
	}

	private function calculTransfertPotion($idDestination) {

		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharrettePotion");
			$table = new CharrettePotion();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanPotion");
			$table = new LabanPotion();
			$suffixe = "laban";
		}

		$data = array(
				"id_".$suffixe."_potion" => $this->view->vente["objet"]["id_potion"],
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_potion"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban_potion"] = $this->view->user->id_hobbit;
		}
		$table->insert($data);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}

		$this->view->objetAchat = $this->view->vente["objet"]["nom"];

		Zend_Loader::loadClass("Bral_Util_Potion");
		$details = "[h".$this->view->user->id_hobbit."] a acheté ".$this->view->vente["objet"]["nom"]. " à l'Hôtel des Ventes";
		Bral_Util_Potion::insertHistorique(Bral_Util_Potion::HISTORIQUE_ACHETER_ID, $this->view->vente["objet"]["id_potion"], $details);
		
		$venteTable = new Vente();
		$where = "id_vente=".$this->idVente;
		$venteTable->delete($where);
	}

	private function calculTransfertRune($idDestination) {
		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharretteRune");
			$table = new CharretteRune();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanRune");
			$table = new LabanRune();
			$suffixe = "laban";
		}

		$data = array(
				"id_rune_".$suffixe."_rune" => $this->view->vente["objet"]["id_rune"],
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_rune"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban_rune"] = $this->view->user->id_hobbit;
		}
		$table->insert($data);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}

		$this->view->objetAchat = $this->view->vente["objet"]["nom"];

		$venteTable = new Vente();
		$where = "id_vente=".$this->idVente;
		$venteTable->delete($where);
		
		$details = "[h".$this->view->user->id_hobbit."] a acheté la rune n°".$this->view->vente["objet"]["id_rune"]. " à l'Hôtel des Ventes";
		Zend_Loader::loadClass("Bral_Util_Rune");
		Bral_Util_Rune::insertHistorique(Bral_Util_Rune::HISTORIQUE_ACHETER_ID, $this->view->vente["objet"]["id_rune"], $details);
	}

	private function calculTransfertMunition($idDestination) {
		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharretteMunition");
			$table = new CharretteMunition();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanMunition");
			$table = new LabanMunition();
			$suffixe = "laban";
		}

		$data = array(
			"id_fk_type_".$suffixe."_munition" => $this->view->vente["objet"]["id_type_munition"],
			"quantite_".$suffixe."_munition" => $this->view->vente["objet"]["quantite_vente_munition"],
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_munition"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban_munition"] = $this->view->user->id_hobbit;
		}
		$table->insertOrUpdate($data);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}

		$this->view->objetAchat = $this->view->vente["objet"]["nom"];

		$venteTable = new Vente();
		$where = "id_vente=".$this->idVente;
		$venteTable->delete($where);
	}

	private function calculTransfertAliment($idDestination) {
		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharretteAliment");
			$table = new CharretteAliment();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanAliment");
			$table = new LabanAliment();
			$suffixe = "laban";
		}

		foreach($this->view->vente["objet"]["aliments"] as $a) {
			$data = array(
				"id_".$suffixe."_aliment" => $a["id_vente_aliment"],
				"id_fk_type_".$suffixe."_aliment" => $a["id_fk_type_vente_aliment"],
				"id_fk_type_qualite_".$suffixe."_aliment" =>$a["id_fk_type_qualite_vente_aliment"],
				"bbdf_".$suffixe."_aliment" => $a["bbdf_vente_aliment"],
			);

			if ($idDestination == "charrette") {
				$data["id_fk_charrette_aliment"] = $this->view->charrette["id_charrette"];
			} else {
				$data["id_fk_hobbit_laban_aliment"] = $this->view->user->id_hobbit;
			}
			$table->insert($data);
		}

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}

		$this->view->objetAchat = $this->view->vente["objet"]["nom"];

		$venteTable = new Vente();
		$where = "id_vente=".$this->idVente;
		$venteTable->delete($where);
	}

	private function calculTransfertElement($idDestination) {
		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("Charrette");
			$table = new Charrette();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("Laban");
			$table = new Laban();
			$suffixe = "laban";
		}

		$prefix = $this->view->vente["objet"]["type_vente_element"];
		if ($prefix == "viande_fraiche") {
			$prefix = "viande";
		}

		$data = array(
			"quantite_".$prefix."_".$suffixe => $this->view->vente["objet"]["quantite_vente_element"],
		);

		if ($idDestination == "charrette") {
			$data["id_charrette"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban"] = $this->view->user->id_hobbit;
		}
		$table->insertOrUpdate($data);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}

		$this->view->objetAchat = $this->view->vente["objet"]["nom"];

		$venteTable = new Vente();
		$where = "id_vente=".$this->idVente;
		$venteTable->delete($where);
	}

	private function calculTransfertMinerai($idDestination) {
		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharretteMinerai");
			$table = new CharretteMinerai();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanMinerai");
			$table = new LabanMinerai();
			$suffixe = "laban";
		}

		$prefix = "brut";
		if ($this->view->vente["objet"]["type_vente_minerai"] == "lingot") {
			$prefix = "lingots";
		}

		$data = array(
			"id_fk_type_".$suffixe."_minerai" => $this->view->vente["objet"]["id_type_minerai"],
			"quantite_".$prefix."_".$suffixe."_minerai" => $this->view->vente["objet"]["quantite_vente_minerai"],
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_minerai"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban_minerai"] = $this->view->user->id_hobbit;
		}
		$table->insertOrUpdate($data);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}

		$this->view->objetAchat = $this->view->vente["objet"]["nom"];

		$venteTable = new Vente();
		$where = "id_vente=".$this->idVente;
		$venteTable->delete($where);
	}

	private function calculTransfertPartieplante($idDestination) {
		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharrettePartieplante");
			$table = new CharrettePartieplante();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanPartieplante");
			$table = new LabanPartieplante();
			$suffixe = "laban";
		}

		$prefix = "";
		if ($this->view->vente["objet"]["type_vente_partieplante"] == "preparee") {
			$prefix = "_preparee";
		}

		$data = array(
			"id_fk_type_".$suffixe."_partieplante" => $this->view->vente["objet"]["id_type_partieplante"],
			"id_fk_type_plante_".$suffixe."_partieplante" => $this->view->vente["objet"]["id_type_plante"],
			"quantite".$prefix."_".$suffixe."_partieplante" => $this->view->vente["objet"]["quantite_vente_partieplante"],
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_partieplante"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban_partieplante"] = $this->view->user->id_hobbit;
		}
		$table->insertOrUpdate($data);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}

		$this->view->objetAchat = $this->view->vente["objet"]["nom"];

		$venteTable = new Vente();
		$where = "id_vente=".$this->idVente;
		$venteTable->delete($where);
	}

	function getListBoxRefresh() {
		return array("box_profil", "box_hotel", "box_laban", "box_charrette", "box_evenements");
	}
}