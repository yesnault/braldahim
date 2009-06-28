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
class Bral_Hotel_Vendre extends Bral_Hotel_Hotel {

	function getNomInterne() {
		return "box_action";
	}

	public function getTitreAction() {
		return "Hôtel des Ventes - Mise en vente";
	}

	function prepareCommun() {
		$this->view->assezDeCastars = false;
		if ($this->view->user->castars_hobbit >= 1) {
			$this->view->assezDeCastars = true;
		}

		$this->prepareDepart();
	}

	function prepareFormulaire() {
		$this->boxHotelToRefresh = false;
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		if ($this->view->assezDeCastars == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de castars : ".$this->view->user->castars_hobbit);
		}

		if ($this->view->vendreOk == false) {
			throw new Zend_Exception(get_class($this)." Deposer interdit ");
		}

		$valeur_4 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));
		if ($valeur_4 <= 0) {
			throw new Zend_Exception(get_class($this)." Valeur 4 invalide : ".$valeur_4);
		}

		$this->boxHotelToRefresh = true;
		$this->calculVendre($this->view->typeDepart[$this->view->idTypeCourantDepart]);

		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - 1;
	}

	private function prepareDepart() {
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass('Laban');

		if ($this->request->get("valeur_1") != "" && $this->request->get("valeur_1") != -1) {
			$idTypeCourantDepart = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_1"));
			if ($idTypeCourantDepart < 1 && $idTypeCourantDepart > count($tabEndroit)) {
				throw new Zend_Exception("Bral_Competences_Transbahuter Valeur invalide : id_type_courant_depart=".$idTypeCourantDepart);
			}
		} else {
			$idTypeCourantDepart = -1;
		}

		$selected = "";
		if ($idTypeCourantDepart == 1) {
			$selected = "selected";
		}
		$tabEndroit[1] = array("id_type_depart" => 1, "nom_systeme" => "Laban", "nom_type_depart" => "Votre laban", "selected" => $selected, "suffixe" => "laban", "box" => "box_laban");

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$selected = "";
			if ($idTypeCourantDepart == 2) {
				$selected = "selected";
			}
			$tabEndroit[2] = array("id_type_depart" => 2, "nom_systeme" => "Charrette", "nom_type_depart" => "Votre charrette", "selected" => $selected, "suffixe" => "charrette", "box" => "box_charrette", "id_charrette" => $charrette["id_charrette"]);
		}

		$choixDepartDansListe = false;
		foreach($tabEndroit as $t) {
			if ($t["id_type_depart"] == $idTypeCourantDepart) {
				$choixDepartDansListe = true;
				break;
			}
		}
		$this->view->typeDepart = $tabEndroit;

		if ($choixDepartDansListe === true) {
			$this->view->vendreOk = false;
			$this->prepareType($tabEndroit[$idTypeCourantDepart]);
			$choixDepart = true;
		} else {
			$choixDepart = false;
		}

		$this->view->idTypeCourantDepart = $idTypeCourantDepart;
		$this->view->choixDepart = $choixDepart;
	}

	private function prepareType($endroit) {
		if ($this->request->get("valeur_2") != "") {
			$idTypeCourant = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
			if ($idTypeCourant < 1 && $idTypeCourant > 8) {
				throw new Zend_Exception("Bral_Hotel_Vendre Type invalide : idTypeCourant=".$idTypeCourant);
			}
		} else {
			$idTypeCourant = -1;
		}

		$typesElements[1] = array("id_type_element" => 1, "selected" => $idTypeCourant, "nom_systeme" => "aliments", "nom_element" => "Aliments");
		$typesElements[2] = array("id_type_element" => 2, "selected" => $idTypeCourant, "nom_systeme" => "equipements", "nom_element" => "Equipements");
		$typesElements[3] = array("id_type_element" => 3, "selected" => $idTypeCourant, "nom_systeme" => "materiels", "nom_element" => "Matériels");
		$typesElements[4] = array("id_type_element" => 4, "selected" => $idTypeCourant, "nom_systeme" => "munitions", "nom_element" => "Munitions");
		$typesElements[5] = array("id_type_element" => 5, "selected" => $idTypeCourant, "nom_systeme" => "minerais", "nom_element" => "Minerais");
		$typesElements[6] = array("id_type_element" => 6, "selected" => $idTypeCourant, "nom_systeme" => "partiesplantes", "nom_element" => "Parties de Plantes");
		$typesElements[7] = array("id_type_element" => 7, "selected" => $idTypeCourant, "nom_systeme" => "potions", "nom_element" => "Potions");
		$typesElements[8] = array("id_type_element" => 8, "selected" => $idTypeCourant, "nom_systeme" => "runes", "nom_element" => "Runes");
		$typesElements[9] = array("id_type_element" => 9, "selected" => $idTypeCourant, "nom_systeme" => "autres", "nom_element" => "Autres Elements");

		$this->view->typeElements = $typesElements;
		$this->view->typeCourant = null;

		if ($idTypeCourant != -1) {
			$this->view->typeCourant = $typesElements[$idTypeCourant]["nom_systeme"];
			$this->prepareDonnees($endroit);
			$this->prepareUnites();
		}
	}

	private function prepareDonnees($endroit) {
		switch($this->view->typeCourant) {
			case "equipements" :
				$this->prepareTypeEquipements($endroit);
				break;
			case "munitions" :
				$this->prepareTypeMunitions($endroit);
				break;
			case "runes" :
				$this->prepareTypeRunes($endroit);
				break;
			case "potions" :
				$this->prepareTypePotions($endroit);
				break;
			case "aliments" :
				$this->prepareTypeAliments($endroit);
				break;
			case "materiels" :
				$this->prepareTypeMateriels($endroit);
				break;
			case "minerais" :
				$this->prepareTypeMinerais($endroit);
				break;
			case "partiesplantes" :
				$this->prepareTypePartiesPlantes($endroit);
				break;
			case "autres" :
				$this->prepareTypeAutres($endroit);
				break;
			default :
				throw new Zend_Exception("Bral_Competences_Deposer prepareType invalide : type=".$this->view->typeCourant);
		}
	}

	private function calculVendre($endroit) {
		$this->listBoxRefresh = $this->constructListBoxRefresh(array("box_laban", "box_hotel"));

		$tabPrix = $this->verificationPrix();
		switch($this->view->typeCourant) {
			case "equipements" :
				$this->deposeTypeEquipements($endroit);
				break;
			case "munitions" :
				$this->deposeTypeMunitions($endroit);
				break;
			case "runes" :
				$this->deposeTypeRunes($endroit);
				break;
			case "potions" :
				$this->deposeTypePotions($endroit);
				break;
			case "aliments" :
				$this->deposeTypeAliments($endroit);
				break;
			case "materiels" :
				$this->deposeTypeMateriels($endroit);
				break;
			case "minerais" :
				$this->deposeTypeMinerais($endroit);
				break;
			case "partiesplantes" :
				$this->deposeTypePartiesPlantes($endroit);
				break;
			case "autres" :
				$this->deposeTypeAutres($endroit);
				break;
			default :
				throw new Zend_Exception("Bral_Competences_Deposer calculVendre invalide : type=".$this->view->typeCourant);
		}
	}

	private function prepareUnites() {
		Zend_Loader::loadClass("TypeUnite");
		$typeUniteTable = new TypeUnite();
		$typeUniteRowset = $typeUniteTable->fetchall(null, "nom_type_unite");
		$typeUniteRowset = $typeUniteRowset->toArray();

		foreach($typeUniteRowset as $t) {
			$unites[$t["nom_systeme_type_unite"]] = array(
							"id_type_unite" => $t["id_type_unite"] ,
							"nom_type_unite" => $t["nom_type_unite"],
							"nom_pluriel_type_unite" => $t["nom_pluriel_type_unite"],
			);
		}

		Zend_Loader::loadClass("TypeMinerai");
		$typeMineraiTable = new TypeMinerai();
		$typeMineraiRowset = $typeMineraiTable->fetchall(null, "nom_type_minerai");
		$typeMineraiRowset = $typeMineraiRowset->toArray();

		foreach($typeMineraiRowset as $t) {
			$unites["mineraibrut:".$t["id_type_minerai"]] = array("id_type_minerai" => $t["id_type_minerai"], "nom_type_unite" => "Minerai Brut: ".$t["nom_type_minerai"], "type_forme" => "brut", "texte_forme_singulier" => "Minerai Brut", "texte_forme_pluriel" => "Minerais Bruts");
			//	$unites["minerailingot:".$t["id_type_minerai"]] = array("id_type_minerai" => $t["id_type_minerai"], "nom_type_unite" => "Lingot: ".$t["nom_type_minerai"], "type_forme" => "lingot", "texte_forme_singulier" => "Lingot", "texte_forme_pluriel" => "Lingots");
		}

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
			$unites["plantebrute:".$t["id_type_plante"]."|".$t["id_fk_partieplante1_type_plante"]] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "brute");
			//	$unites["plantepreparee:".$t["id_type_plante"]."|".$t["id_fk_partieplante1_type_plante"]] = $this->prepareUnitesRowPlante($t, $partiePlante, 1, "preparee");

			if ($t["id_fk_partieplante2_type_plante"] != "") {
				$unites["plantebrute:".$t["id_type_plante"]."|".$t["id_fk_partieplante2_type_plante"]] = $this->prepareUnitesRowPlante($t, $partiePlante, 2, "brute");
				//		$unites["plantepreparee:".$t["id_type_plante"]."|".$t["id_fk_partieplante2_type_plante"]] = $this->prepareUnitesRowPlante($t, $partiePlante, 2, "preparee");
			}

			if ($t["id_fk_partieplante3_type_plante"] != "") {
				$unites["plantebrute:".$t["id_type_plante"]."|".$t["id_fk_partieplante3_type_plante"]] = $this->prepareUnitesRowPlante($t, $partiePlante, 3, "brute");
				//		$unites["plantepreparee:".$t["id_type_plante"]."|".$t["id_fk_partieplante3_type_plante"]] = $this->prepareUnitesRowPlante($t, $partiePlante, 3, "preparee");
			}

			if ($t["id_fk_partieplante4_type_plante"] != "") {
				$unites["plantebrute:".$t["id_type_plante"]."|".$t["id_fk_partieplante4_type_plante"]] = $this->prepareUnitesRowPlante($t, $partiePlante, 4, "brute");
				//		$unites["plantepreparee:".$t["id_type_plante"]."|".$t["id_fk_partieplante4_type_plante"]] = $this->prepareUnitesRowPlante($t, $partiePlante, 4, "preparee");
			}
		}

		$this->view->unites = $unites;
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
					  "nom_type_unite" => "Plante ".$nomForme.": ".$type["nom_type_plante"]. ' '.$partiePlante[$type["id_fk_partieplante".$num."_type_plante"]]["nom_partieplante"],
					  "type_forme" => $forme);
	}

	private function prepareTypeAutres($endroit) {
		$tabAutres = null;
		$table = new $endroit["nom_systeme"]();
		$autresRowset = $table->findByIdHobbit($this->view->user->id_hobbit);
		unset($table);

		if (count($autresRowset) == 1) {
			foreach ($autresRowset as $p) {
				if ($p["quantite_peau_".$endroit["suffixe"]] > 0) $tabAutres[1] = array("type_element" => "peau", "nom" => "Peau", "nom_pluriel" => "Peaux", "nom_systeme" => "quantite_peau" , "nb" => $p["quantite_peau_".$endroit["suffixe"]]);
				if ($p["quantite_viande_".$endroit["suffixe"]] > 0) $tabAutres[2] = array("type_element" => "viande_fraiche", "nom" => "Viande", "nom_pluriel" => "Viandes", "nom_systeme" => "quantite_viande" , "nb" => $p["quantite_viande_".$endroit["suffixe"]]);
				if ($p["quantite_viande_preparee_".$endroit["suffixe"]] > 0) $tabAutres[3] = array("type_element" => "viande_preparee", "nom" => "Viande pr&eacute;par&eacute;e", "nom_pluriel" => "Viandes pr&eacute;par&eacute;es", "nom_systeme" => "quantite_viande_preparee" , "nb" => $p["quantite_viande_preparee_".$endroit["suffixe"]]);
				if ($p["quantite_cuir_".$endroit["suffixe"]] > 0) $tabAutres[4] = array("type_element" => "cuir", "nom" => "Cuir", "nom_pluriel" => "Cuirs", "nom_systeme" => "quantite_cuir" , "nb" => $p["quantite_cuir_".$endroit["suffixe"]]);
				if ($p["quantite_fourrure_".$endroit["suffixe"]] > 0) $tabAutres[5] = array("type_element" => "fourrure", "nom" => "Fourrure", "nom_pluriel" => "Fourrures", "nom_systeme" => "quantite_fourrure" , "nb" => $p["quantite_fourrure_".$endroit["suffixe"]]);
				if ($p["quantite_planche_".$endroit["suffixe"]] > 0) $tabAutres[6] = array("type_element" => "planche", "nom" => "Planche", "nom_pluriel" => "Planches", "nom_systeme" => "quantite_planche" , "nb" => $p["quantite_planche_".$endroit["suffixe"]]);
				if ($p["quantite_rondin_".$endroit["suffixe"]] > 0) $tabAutres[7] = array("type_element" => "rondin", "nom" => "Rondin", "nom_pluriel" => "Rondins", "nom_systeme" => "quantite_rondin" , "nb" => $p["quantite_rondin_".$endroit["suffixe"]]);

				if (count($tabAutres) > 0) {
					$this->view->vendreOk = true;
				}
			}
		} else {
			$this->view->vendreOk = false;
		}
		$this->view->autres = $tabAutres;
	}

	private function deposeTypeAutres($endroit) {
		$idAutre = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
		$nb = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));

		if (!array_key_exists($idAutre, $this->view->autres)) {
			throw new Zend_Exception(get_class($this)." ID Autres invalide : ".$idAutre);
		}

		$autre = $this->view->autres[$idAutre];

		if ($nb > $autre["nb"]) {
			$nb = $autre["nb"];
		}

		if ($nb < 0) {
			throw new Zend_Exception(get_class($this)." Quantite invalide : ".$nb);
		}

		$idVente = $this->initVente("element");

		$table = new $endroit["nom_systeme"]();
		$data = array(
		$autre["nom_systeme"]."_".$endroit["suffixe"]  => -$nb,
			"id_fk_hobbit_".$endroit["suffixe"] => $this->view->user->id_hobbit,
		);
		$table->insertOrUpdate($data);

		Zend_Loader::loadClass("VenteElement");
		$venteTable = new VenteElement();
		$data = array(
			"quantite_vente_element" => $nb,
			"type_vente_element" => $autre["type_element"],
			"id_fk_vente_element" => $idVente,
		);
		$venteTable->insert($data);

		$keyTexte = "nom";
		if ($nb > 1) {
			$keyTexte = "nom_pluriel";
		}
		$this->view->objetVente = $nb. " ".$autre[$keyTexte];
	}

	private function prepareTypeEquipements($endroit) {
		$tabEquipements = null;
		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanEquipement");
			$table = new LabanEquipement();
			$equipements = $table->findByIdHobbit($this->view->user->id_hobbit);
		} else {
			Zend_Loader::loadClass("CharretteEquipement");
			$table = new CharretteEquipement();
			$equipements = $table->findByIdCharrette($endroit["id_charrette"]);
		}

		Zend_Loader::loadClass("Bral_Util_Equipement");

		if (count($equipements) > 0) {
			$this->view->vendreOk = true;
			foreach ($equipements as $e) {
				$tabEquipements[$e["id_".$endroit["suffixe"]."_equipement"]] = array(
						"id_equipement" => $e["id_".$endroit["suffixe"]."_equipement"],
						"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_".$endroit["suffixe"]."_equipement"]),
						"qualite" => $e["nom_type_qualite"],
						"niveau" => $e["niveau_recette_equipement"],
						"nb_runes" => $e["nb_runes_".$endroit["suffixe"]."_equipement"],
						"suffixe" => $e["suffixe_mot_runique"],
						"nb_runes" => $e["nb_runes_".$endroit["suffixe"]."_equipement"],
						"id_fk_mot_runique" => $e["id_fk_mot_runique_".$endroit["suffixe"]."_equipement"], 
						"id_fk_recette" => $e["id_fk_recette_".$endroit["suffixe"]."_equipement"] ,
						"id_fk_region" => $e["id_fk_region_".$endroit["suffixe"]."_equipement"],
				);
			}
		} else {
			$this->view->vendreOk = false;
		}
		$this->view->equipements = $tabEquipements;
	}

	private function deposeTypeEquipements($endroit) {

		$idEquipement = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));

		if (!array_key_exists($idEquipement, $this->view->equipements)) {
			throw new Zend_Exception(get_class($this)." ID Equipement invalide : ".$idEquipement);
		}

		$idVente = $this->initVente("equipement");

		$equipement = $this->view->equipements[$idEquipement];

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanEquipement");
			$table = new LabanEquipement();
		} else {
			Zend_Loader::loadClass("CharretteEquipement");
			$table = new CharretteEquipement();
		}

		$where = "id_".$endroit["suffixe"]."_equipement=".$idEquipement;
		$table->delete($where);

		Zend_Loader::loadClass("VenteEquipement");
		$venteEquipementTable = new VenteEquipement();
		$data = array (
			"id_vente_equipement" => $equipement["id_equipement"],
			"id_fk_vente_equipement" => $idVente,
			"id_fk_recette_vente_equipement" => $equipement["id_fk_recette"],
			"nb_runes_vente_equipement" => $equipement["nb_runes"],
			"id_fk_mot_runique_vente_equipement" => $equipement["id_fk_mot_runique"],
			"id_fk_region_vente_equipement" => $equipement["id_fk_region"],
		);
		$venteEquipementTable->insert($data);
		$this->view->objetVente = $equipement["nom"]. " n°".$equipement["id_equipement"]. " de qualité ".$equipement["qualite"];
	}

	private function prepareTypePotions($endroit) {

		$tabPotions = null;
		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanPotion");
			$table = new LabanPotion();
			$potions = $table->findByIdHobbit($this->view->user->id_hobbit);
		} else {
			Zend_Loader::loadClass("CharrettePotion");
			$table = new CharrettePotion();
			$potions = $table->findByIdCharrette($endroit["id_charrette"]);
		}

		if (count($potions) > 0) {
			$this->view->vendreOk = true;
			foreach ($potions as $p) {
				$tabPotions[$p["id_".$endroit["suffixe"]."_potion"]] = array(
					"id_potion" => $p["id_".$endroit["suffixe"]."_potion"],
					"nom" => $p["nom_type_potion"],
					"qualite" => $p["nom_type_qualite"],
					"niveau" => $p["niveau_".$endroit["suffixe"]."_potion"],
					"caracteristique" => $p["caract_type_potion"],
					"bm_type" => $p["bm_type_potion"],
					"id_fk_type_qualite" => $p["id_fk_type_qualite_".$endroit["suffixe"]."_potion"],
					"id_fk_type" => $p["id_fk_type_".$endroit["suffixe"]."_potion"]
				);
			}
		} else {
			$this->view->vendreOk = false;
		}
		$this->view->potions = $tabPotions;
	}

	private function deposeTypePotions($endroit) {
		$idPotion = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));

		if (!array_key_exists($idPotion, $this->view->potions)) {
			throw new Zend_Exception(get_class($this)." ID Potion invalide : ".$idPotion);
		}

		$idVente = $this->initVente("potion");

		$potion = $this->view->potions[$idPotion];

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanPotion");
			$table = new LabanPotion();
		} else {
			Zend_Loader::loadClass("CharrettePotion");
			$table = new CharrettePotion();
		}

		$where = "id_".$endroit["suffixe"]."_potion=".$idPotion;
		$table->delete($where);

		Zend_Loader::loadClass("VentePotion");
		$ventePotionTable = new VentePotion();
		$data = array (
			"id_vente_potion" => $potion["id_potion"],
			"id_fk_vente_potion" => $idVente,
			"niveau_vente_potion" => $potion["niveau"],
			"id_fk_type_qualite_vente_potion" => $potion["id_fk_type_qualite"],
			"id_fk_type_vente_potion" => $potion["id_fk_type"],
		);
		$ventePotionTable->insert($data);

		$this->view->objetVente = " la potion n°".$potion["id_potion"]. ", ".$potion["nom"]." de qualité ".$potion["qualite"];
	}

	private function prepareTypeRunes($endroit) {

		$tabRunes = null;

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanRune");
			$table = new LabanRune();
			$runes = $table->findByIdHobbit($this->view->user->id_hobbit);
		} else {
			Zend_Loader::loadClass("CharretteRune");
			$table = new CharretteRune();
			$runes = $table->findByIdCharrette($endroit["id_charrette"]);
		}

		if (count($runes) > 0) {
			$this->view->vendreOk = true;
			foreach ($runes as $r) {
				$tabRunes[$r["id_rune_".$endroit["suffixe"]."_rune"]] = array(
					"id_rune" => $r["id_rune_".$endroit["suffixe"]."_rune"],
					"type" => $r["nom_type_rune"],
					"image" => $r["image_type_rune"],
					"est_identifiee" => $r["est_identifiee_".$endroit["suffixe"]."_rune"],
					"effet_type_rune" => $r["effet_type_rune"],
					"id_fk_type_rune" => $r["id_fk_type_".$endroit["suffixe"]."_rune"],
				);
			}
		} else {
			$this->view->vendreOk = false;
		}
		$this->view->runes = $tabRunes;
	}


	private function deposeTypeRunes($endroit) {
		$idRune = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));

		if (!array_key_exists($idRune, $this->view->runes)) {
			throw new Zend_Exception(get_class($this)." ID Rune invalide : ".$idRune);
		}

		$idVente = $this->initVente("rune");

		$rune = $this->view->runes[$idRune];

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanRune");
			$table = new LabanRune();
		} else {
			Zend_Loader::loadClass("CharretteRune");
			$table = new CharretteRune();
		}

		$where = "id_rune_".$endroit["suffixe"]."_rune=".$idRune;
		$table->delete($where);

		Zend_Loader::loadClass("VenteRune");
		$venteRuneTable = new VenteRune();
		$data = array (
			"id_rune_vente_rune" => $rune["id_rune"],
			"id_fk_vente_rune" => $idVente,
			"id_fk_type_vente_rune" => $rune["id_fk_type_rune"],
			"est_identifiee_vente_rune" => $rune["est_identifiee"],
		);
		$venteRuneTable->insert($data);

		$this->view->objetVente = " la rune n°".$rune["id_rune"];
	}

	private function prepareTypeMunitions($endroit) {
		$tabMunitions = null;

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanMunition");
			$table = new LabanMunition();
			$munitions = $table->findByIdHobbit($this->view->user->id_hobbit);
		} else {
			Zend_Loader::loadClass("CharretteMunition");
			$table = new CharretteMunition();
			$munitions = $table->findByIdCharrette($endroit["id_charrette"]);
		}

		if (count($munitions) > 0) {
			$this->view->vendreOk = true;

			foreach ($munitions as $m) {
				if ($m["quantite_".$endroit["suffixe"]."_munition"] > 0) {
					$tabMunitions[$m["id_fk_type_".$endroit["suffixe"]."_munition"]] = array(
						"id_type_munition" => $m["id_fk_type_".$endroit["suffixe"]."_munition"],
						"type" => $m["nom_type_munition"],
						"nom_pluriel" => $m["nom_pluriel_type_munition"],
						"quantite" => $m["quantite_".$endroit["suffixe"]."_munition"],
					);
				}
			}
		} else {
			$this->view->vendreOk = false;
		}
		$this->view->munitions = $tabMunitions;
	}

	private function deposeTypeMunitions($endroit) {

		$idMunition = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
		$nbMunition = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));

		if (!array_key_exists($idMunition, $this->view->munitions)) {
			throw new Zend_Exception(get_class($this)." ID Munition invalide : ".$idMunition);
		}

		$munition = $this->view->munitions[$idMunition];

		if ($nbMunition > $munition["quantite"] || $nbMunition < 0) {
			throw new Zend_Exception(get_class($this)." Quantite Munition invalide : ".$nbMunition);
		}

		$idVente = $this->initVente("munition");

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanMunition");
			$table = new LabanMunition();
			$nomFk = "id_fk_hobbit_laban_munition";
			$valeurFk = $this->view->user->id_hobbit;
		} else {
			Zend_Loader::loadClass("CharretteMunition");
			$table = new CharretteMunition();
			$nomFk = "id_fk_charrette_munition";
			$valeurFk = $endroit["id_charrette"];
		}

		$data = array(
				"quantite_".$endroit["suffixe"]."_munition" => -$nbMunition,
				"id_fk_type_".$endroit["suffixe"]."_munition" => $munition["id_type_munition"],
		);
		$data[$nomFk] = $valeurFk;
		$table->insertOrUpdate($data);

		Zend_Loader::loadClass("VenteMunition");
		$venteMunitionTable = new VenteMunition();
		$data = array (
				"id_fk_vente_munition" => $idVente,
				"id_fk_type_vente_munition" => $munition["id_type_munition"],
				"quantite_vente_munition" => $nbMunition,
		);
		$venteMunitionTable->insert($data);

		$keyTexte = "type";
		if ($nbMunition > 1) {
			$keyTexte = "nom_pluriel";
		}
		$this->view->objetVente =  $nbMunition. " ".$munition[$keyTexte];
	}

	private function prepareTypeMinerais($endroit) {
		$tabMinerais = null;

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanMinerai");
			$table = new LabanMinerai();
			$minerais = $table->findByIdHobbit($this->view->user->id_hobbit);
		} else {
			Zend_Loader::loadClass("CharretteMinerai");
			$table = new CharretteMinerai();
			$minerais = $table->findByIdCharrette($endroit["id_charrette"]);
		}

		if (count($minerais) > 0) {
			foreach ($minerais as $m) {
				if ($m["quantite_brut_".$endroit["suffixe"]."_minerai"] > 0) {
					$tabMinerais[] = array(
						"id_type_minerai" => $m["id_fk_type_".$endroit["suffixe"]."_minerai"],
						"type" => $m["nom_type_minerai"],
						"quantite" => $m["quantite_brut_".$endroit["suffixe"]."_minerai"],
						"type_forme" => "brut",
						"nom_forme" => "Minerai Brut",
						"nom_forme_pluriel" => "Minerais Bruts",
					);
					$this->view->vendreOk = true;
				}

				if ($m["quantite_lingots_".$endroit["suffixe"]."_minerai"] > 0) {
					$tabMinerais[] = array(
						"id_type_minerai" => $m["id_fk_type_".$endroit["suffixe"]."_minerai"],
						"type" => $m["nom_type_minerai"],
						"quantite" => $m["quantite_lingots_".$endroit["suffixe"]."_minerai"],
						"type_forme" => "lingot",
						"nom_forme" => "Lingot",
						"nom_forme_pluriel" => "Lingots",
					);
					$this->view->vendreOk = true;
				}
			}
		} else {
			$this->view->vendreOk = false;
		}
		$this->view->minerais = $tabMinerais;
	}

	private function deposeTypeMinerais($endroit) {
		$idMinerai = null;
		$nbMinerai = null;

		$idMinerai = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
		$nbMinerai = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));

		if (!array_key_exists($idMinerai, $this->view->minerais)) {
			throw new Zend_Exception(get_class($this)." ID Minerai invalide : ".$idMinerai);
		}

		$minerai = $this->view->minerais[$idMinerai];

		if ($nbMinerai > $minerai["quantite"] || $nbMinerai < 0) {
			throw new Zend_Exception(get_class($this)." Quantite Minerai Brut invalide : ".$nbMinerai);
		}

		$idVente = $this->initVente("minerai");

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanMinerai");
			$table = new LabanMinerai();
			$nomFk = "id_fk_hobbit_laban_minerai";
			$valeurFk = $this->view->user->id_hobbit;
		} else {
			Zend_Loader::loadClass("CharretteMinerai");
			$table = new CharretteMinerai();
			$nomFk = "id_fk_charrette_minerai";
			$valeurFk = $endroit["id_charrette"];
		}

		if ($minerai["type_forme"] == "lingot") {
			$prefix = "lingots";
		} else {
			$prefix = "brut";
		}

		$data = array(
			"quantite_".$prefix."_".$endroit["suffixe"]."_minerai" => -$nbMinerai,
			"id_fk_type_".$endroit["suffixe"]."_minerai" => $minerai["id_type_minerai"],
		);
		$data[$nomFk] = $valeurFk;
		$table->insertOrUpdate($data);

		Zend_Loader::loadClass("VenteMinerai");
		$venteMineraiTable = new VenteMinerai();
		$data = array (
				"id_fk_vente_minerai" => $idVente,
				"type_vente_minerai" => $minerai["type_forme"],
				"id_fk_type_vente_minerai" => $minerai["id_type_minerai"],
				"quantite_vente_minerai" => $nbMinerai,
		);
		$venteMineraiTable->insert($data);

		$keyTexte = "nom_forme";
		if ($nbMinerai > 1) {
			$keyTexte = "nom_forme_pluriel";
		}

		$this->view->objetVente = $minerai["type"] . " : ".$nbMinerai. " ".$minerai[$keyTexte];
	}

	private function prepareTypePartiesPlantes($endroit) {

		$tabPartiePlantes = null;

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanPartieplante");
			$table = new LabanPartieplante();
			$partiesPlantes = $table->findByIdHobbit($this->view->user->id_hobbit);
		} else {
			Zend_Loader::loadClass("CharrettePartieplante");
			$table = new CharrettePartieplante();
			$partiesPlantes = $table->findByIdCharrette($endroit["id_charrette"]);
		}

		if (count($partiesPlantes) > 0) {
			foreach ($partiesPlantes as $m) {
				if ($m["quantite_".$endroit["suffixe"]."_partieplante"] > 0) {
					$tabPartiePlantes[] = array(
						"id_type_partieplante" => $m["id_fk_type_".$endroit["suffixe"]."_partieplante"],
						"id_type_plante" => $m["id_fk_type_plante_".$endroit["suffixe"]."_partieplante"],
						"type" => $m["nom_type_partieplante"],
						"type_plante" => $m["nom_type_plante"],
						"quantite" => $m["quantite_".$endroit["suffixe"]."_partieplante"],
						"type_forme" => "brute",
						"nom_forme" => "Plante Brute",
					);
					$this->view->vendreOk = true;
				}

				if ($m["quantite_preparee_".$endroit["suffixe"]."_partieplante"] > 0) {
					$tabPartiePlantes[] = array(
						"id_type_partieplante" => $m["id_fk_type_".$endroit["suffixe"]."_partieplante"],
						"id_type_plante" => $m["id_fk_type_plante_".$endroit["suffixe"]."_partieplante"],
						"type" => $m["nom_type_partieplante"],
						"type_plante" => $m["nom_type_plante"],
						"quantite" => $m["quantite_preparee_".$endroit["suffixe"]."_partieplante"],
						"type_forme" => "preparee",
						"nom_forme" => "Plante Préparée",
					);
					$this->view->vendreOk = true;
				}
			}
		} else {
			$this->view->vendreOk = false;
		}
		$this->view->partiePlantes = $tabPartiePlantes;
	}

	private function deposeTypePartiesPlantes($endroit) {
		$idPartiePlante = null;
		$nbPartiePlante = null;

		$idPartiePlante = $this->request->get("valeur_3");
		$nbPartiePlante = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));

		if (!array_key_exists($idPartiePlante, $this->view->partiePlantes)) {
			throw new Zend_Exception(get_class($this)." ID PartiePlante invalide : ".$idPartiePlante);
		}

		$partiePlante = $this->view->partiePlantes[$idPartiePlante];

		if ($nbPartiePlante > $partiePlante["quantite"] || $nbPartiePlante < 0) {
			throw new Zend_Exception(get_class($this)." Quantite PartiePlante invalide : ".$nbPartiePlante);
		}

		$idVente = $this->initVente("partieplante");

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanPartieplante");
			$table = new LabanPartieplante();
			$nomFk = "id_fk_hobbit_laban_partieplante";
			$valeurFk = $this->view->user->id_hobbit;
		} else {
			Zend_Loader::loadClass("CharrettePartieplante");
			$table = new CharrettePartieplante();
			$nomFk = "id_fk_charrette_partieplante";
			$valeurFk = $endroit["id_charrette"];
		}

		if ($partiePlante["type_forme"] == "brute") {
			$prefix = "";
		} else {
			$prefix = "_preparee";
		}
		$data = array(
			"quantite".$prefix."_".$endroit["suffixe"]."_partieplante" => -$nbPartiePlante,
			"id_fk_type_".$endroit["suffixe"]."_partieplante" => $partiePlante["id_type_partieplante"],
			"id_fk_type_plante_".$endroit["suffixe"]."_partieplante" => $partiePlante["id_type_plante"],
		);
		$data[$nomFk] = $valeurFk;
		$table->insertOrUpdate($data);

		Zend_Loader::loadClass("VentePartieplante");
		$ventePartiePlanteTable = new VentePartieplante();
		$data = array (
				"id_fk_vente_partieplante" => $idVente,
				"id_fk_type_vente_partieplante" => $partiePlante["id_type_partieplante"],
				"id_fk_type_plante_vente_partieplante" => $partiePlante["id_type_plante"],
				"quantite_vente_partieplante" => $nbPartiePlante,
				"type_vente_partieplante" => $partiePlante["type_forme"],
		);
		$ventePartiePlanteTable->insert($data);

		$s = "";
		if ($nbPartiePlante > 1) {
			$s = "s";
		}
		$this->view->objetVente = $partiePlante["type_plante"]. " - ".$partiePlante["nom_forme"]. " : ".$nbPartiePlante. " ".$partiePlante["type"].$s;
	}

	private function prepareTypeMateriels($endroit) {
		$tabMateriels = null;
		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanMateriel");
			$table = new LabanMateriel();
			$materiels = $table->findByIdHobbit($this->view->user->id_hobbit);
		} else {
			Zend_Loader::loadClass("CharretteMateriel");
			$table = new CharretteMateriel();
			$materiels = $table->findByIdCharrette($endroit["id_charrette"]);
		}

		if (count($materiels) > 0) {
			$this->view->vendreOk = true;
			foreach ($materiels as $m) {
				$tabMateriels[$m["id_".$endroit["suffixe"]."_materiel"]] = array(
					"id_materiel" => $m["id_".$endroit["suffixe"]."_materiel"],
					"nom" => $m["nom_type_materiel"],
					"id_fk_type_materiel" => $m["id_type_materiel"],
				);
			}
		} else {
			$this->view->vendreOk = false;
		}
		$this->view->materiels = $tabMateriels;
	}

	private function deposeTypeMateriels($endroit) {

		$idMateriel = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));

		if (!array_key_exists($idMateriel, $this->view->materiels)) {
			throw new Zend_Exception(get_class($this)." ID Materiel invalide : ".$idMateriel);
		}

		$idVente = $this->initVente("materiel");

		$materiel = $this->view->materiels[$idMateriel];

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanMateriel");
			$table = new LabanMateriel();
		} else {
			Zend_Loader::loadClass("CharretteMateriel");
			$table = new CharretteMateriel();
		}

		$where = "id_".$endroit["suffixe"]."_materiel=".$idMateriel;
		$table->delete($where);

		Zend_Loader::loadClass("VenteMateriel");
		$venteMaterielTable = new VenteMateriel();
		$data = array (
			"id_vente_materiel" => $materiel["id_materiel"],
			"id_fk_vente_materiel" => $idVente,
			"id_fk_type_vente_materiel" => $materiel["id_fk_type_materiel"],
		);
		$venteMaterielTable->insert($data);

		$this->view->objetVente = " le matériel n°".$materiel["id_materiel"]. " ".$materiel["nom"];
	}

	private function prepareTypeAliments($endroit) {
		$tabAliments = null;

		if ($endroit["nom_systeme"] == "Laban") {
			Zend_Loader::loadClass("LabanAliment");
			$table = new LabanAliment();
			$aliments = $table->findByIdHobbit($this->view->user->id_hobbit);
		} else {
			Zend_Loader::loadClass("CharretteAliment");
			$table = new CharretteAliment();
			$aliments = $table->findByIdCharrette($endroit["id_charrette"]);
		}

		if (count($aliments) > 0) {
			$this->view->vendreOk = true;
			foreach ($aliments as $p) {
				$tabAliments[$p["id_".$endroit["suffixe"]."_aliment"]] = array(
					"id_aliment" => $p["id_".$endroit["suffixe"]."_aliment"],
					"nom" => $p["nom_type_aliment"],
					"qualite" => $p["nom_type_qualite"],
					"bbdf" => $p["bbdf_".$endroit["suffixe"]."_aliment"],
					"id_fk_type_qualite" => $p["id_fk_type_qualite_".$endroit["suffixe"]."_aliment"],
					"id_fk_type" => $p["id_fk_type_".$endroit["suffixe"]."_aliment"]
				);
			}
		} else {
			$this->view->vendreOk = false;
		}
		$this->view->aliments = $tabAliments;
	}

	private function deposeTypeAliments($endroit) {
		$aliments = array();
		$aliments = $this->request->get("valeur_3");

		$this->view->objetVente = "";
		foreach ($aliments as $idAliment) {
			if (!array_key_exists($idAliment, $this->view->aliments)) {
				throw new Zend_Exception(get_class($this)." ID Aliment invalide : ".$idAliment);
			}
		}

		$idVente = $this->initVente("aliment");

		foreach ($aliments as $idAliment) {
			$aliment = $this->view->aliments[$idAliment];

			if ($endroit["nom_systeme"] == "Laban") {
				Zend_Loader::loadClass("LabanAliment");
				$table = new LabanAliment();
			} else {
				Zend_Loader::loadClass("CharretteAliment");
				$table = new CharretteAliment();
			}

			$where = "id_".$endroit["suffixe"]."_aliment=".$idAliment;
			$table->delete($where);

			Zend_Loader::loadClass("VenteAliment");
			$venteAlimentTable = new VenteAliment();
			$data = array (
				"id_vente_aliment" => $aliment["id_aliment"],
				"id_fk_vente_aliment" => $idVente,
				"bbdf_vente_aliment" => $aliment["bbdf"],
				"id_fk_type_qualite_vente_aliment" => $aliment["id_fk_type_qualite"],
				"id_fk_type_vente_aliment" => $aliment["id_fk_type"],
			);
			$venteAlimentTable->insert($data);

			$this->view->objetVente .= " le ".$aliment["nom"]. " n°".$aliment["id_aliment"]. " (+".$aliment["bbdf"]."%), ";
		}
		if ($this->view->objetVente != "") {
			$this->view->objetVente = substr($this->view->objetVente, 0, strlen($this->view->objetVente) -2);
		}
	}

	private function verificationPrix() {
		$prix_1 = $this->request->get("valeur_5");
		$unite_1 = $this->request->get("valeur_6");
		$prix_2 = $this->request->get("valeur_7");
		$unite_2 = $this->request->get("valeur_8");
		$prix_3 = $this->request->get("valeur_9");
		$unite_3 = $this->request->get("valeur_10");

		if ((int) $prix_1."" != $this->request->get("valeur_5")."") {
			throw new Zend_Exception(get_class($this)." prix 1 invalide=".$prix_1);
		} else {
			$prix_1 = (int)$prix_1;
		}
		if ((int) $prix_2."" != $this->request->get("valeur_7")."") {
			$prix_2 = null;
			$unite_2 = null;
		} else {
			$prix_2 = (int)$prix_2;
		}
		if ((int) $prix_3."" != $this->request->get("valeur_9")."") {
			$prix_3 = null;
			$unite_3 = null;
		} else {
			$prix_3 = (int)$prix_3;
		}

		$tabPrix["prix_1"] = $prix_1;
		$tabPrix["prix_2"] = $prix_2;
		$tabPrix["prix_3"] = $prix_3;

		$tabPrix["unite_1"] = $unite_1;
		$tabPrix["unite_2"] = $unite_2;
		$tabPrix["unite_3"] = $unite_3;

		return $tabPrix;
	}

	private function initVente($typeVente) {
		Zend_Loader::loadClass("Vente");
		Zend_Loader::loadClass("Bral_Util_BBParser");

		$venteTable = new Vente();

		$dateDebut = date("Y-m-d H:0:0");
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateDebut, 30);
		$commentaire = stripslashes(Bral_Util_BBParser::bbcodeStripPlus($this->request->get('valeur_11')));

		$tabPrix = $this->verificationPrix();

		$this->view->textePrixVente = array();

		$unite_1 = 0;
		$unite_2 = 0;
		$unite_3 = 0;
		$prix_1 = 0;
		$prix_2 = 0;
		$prix_3 = 0;
		foreach($this->view->unites as $k => $u) {
			if ($tabPrix["unite_1"] == $k && mb_substr($tabPrix["unite_1"], 0, 6) != "plante" && mb_substr($tabPrix["unite_1"], 0, 7) != "minerai") {
				$prix_1 = $tabPrix["prix_1"];
				$unite_1 = $u["id_type_unite"];
				if ($prix_1 > 1) {
					$keyTexte = "nom_pluriel_type_unite";
				} else {
					$keyTexte = "nom_type_unite";
				}
				$this->view->textePrixVente[] = array("texte" => $prix_1." ".$u[$keyTexte]);
			}
			if ($tabPrix["unite_2"] == $k && mb_substr($tabPrix["unite_2"], 0, 6) != "plante" && mb_substr($tabPrix["unite_2"], 0, 7) != "minerai") {
				$prix_2 = $tabPrix["prix_2"];
				$unite_2 = $u["id_type_unite"];
				if ($prix_2 > 1) {
					$keyTexte = "nom_pluriel_type_unite";
				} else {
					$keyTexte = "nom_type_unite";
				}
				$this->view->textePrixVente[] = array("texte" => $prix_2." ".$u[$keyTexte]);
			}
			if ($tabPrix["unite_3"] == $k && mb_substr($tabPrix["unite_3"], 0, 6) != "plante" && mb_substr($tabPrix["unite_3"], 0, 7) != "minerai") {
				$prix_3 = $tabPrix["prix_3"];
				$unite_3 = $u["id_type_unite"];
				if ($prix_3 > 1) {
					$keyTexte = "nom_pluriel_type_unite";
				} else {
					$keyTexte = "nom_type_unite";
				}
				$this->view->textePrixVente[] = array("texte" => $prix_3." ".$u[$keyTexte]);
			}
		}

		$data = array(
			"id_fk_hobbit_vente" => $this->view->user->id_hobbit,
			"date_debut_vente" => $dateDebut,
			"date_fin_vente" => $dateFin, 
			"commentaire_vente" => $commentaire,
			"unite_1_vente" => $unite_1,
			"unite_2_vente" => $unite_2,
			"unite_3_vente" => $unite_3,
			"prix_1_vente" => $prix_1,
			"prix_2_vente" => $prix_2,
			"prix_3_vente" => $prix_3,
			"type_vente" => $typeVente,
		);

		$idVente = $venteTable->insert($data);

		$this->calculPrixMinerai($idVente, $tabPrix["prix_1"], $tabPrix["prix_2"], $tabPrix["prix_3"], $tabPrix["unite_1"], $tabPrix["unite_2"], $tabPrix["unite_3"]);
		$this->calculPrixPartiePlante($idVente, $tabPrix["prix_1"], $tabPrix["prix_2"], $tabPrix["prix_3"], $tabPrix["unite_1"], $tabPrix["unite_2"], $tabPrix["unite_3"]);

		$this->view->idVente = $idVente;
		$this->view->dateFinVente = Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y à H\h ', $dateFin);
		return $idVente;
	}

	private function calculPrixMinerai($idVente, $prix_1, $prix_2, $prix_3, $unite_1, $unite_2, $unite_3) {
		Zend_Loader::loadClass("VentePrixMinerai");
		$ventePrixMineraiTable = new VentePrixMinerai();

		foreach($this->view->unites as $k => $u) {
			if ($unite_1 == $k && mb_substr($unite_1, 0, 7) == "minerai") {
				$data = array("prix_vente_prix_minerai" => $prix_1,
							  "id_fk_type_vente_prix_minerai" => $u["id_type_minerai"],
							  "id_fk_vente_prix_minerai" => $idVente,
							  "type_prix_minerai" => $u["type_forme"]);
				$ventePrixMineraiTable->insert($data);
				if ($prix_1 > 1) {
					$keyTexte = "texte_forme_singulier";
				} else {
					$keyTexte = "texte_forme_pluriel";
				}
				$this->view->textePrixVente[] = array("texte" => $u["nom_type_unite"]. " : ". $prix_1." ".$u[$keyTexte]);
			}
			if ($unite_2 == $k && mb_substr($unite_2, 0, 7) == "minerai") {
				$data = array("prix_vente_prix_minerai" => $prix_2,
							  "id_fk_type_vente_prix_minerai" => $u["id_type_minerai"],
							  "id_fk_vente_prix_minerai" => $idVente,
							  "type_prix_minerai" => $u["type_forme"]);
				$ventePrixMineraiTable->insert($data);
				if ($prix_2 > 1) {
					$keyTexte = "texte_forme_singulier";
				} else {
					$keyTexte = "texte_forme_pluriel";
				}
				$this->view->textePrixVente[] = array("texte" => $u["nom_type_unite"]. " : ". $prix_2." ".$u[$keyTexte]);
			}
			if ($unite_3 == $k && mb_substr($unite_3, 0, 7) == "minerai") {
				$data = array("prix_vente_prix_minerai" => $prix_3,
							  "id_fk_type_vente_prix_minerai" => $u["id_type_minerai"],
							  "id_fk_vente_prix_minerai" => $idVente,
							  "type_prix_minerai" => $u["type_forme"]);
				$ventePrixMineraiTable->insert($data);
				if ($prix_3 > 1) {
					$keyTexte = "texte_forme_singulier";
				} else {
					$keyTexte = "texte_forme_pluriel";
				}
				$this->view->textePrixVente[] = array("texte" => $u["nom_type_unite"]. " : ". $prix_3." ".$u[$keyTexte]);
			}
		}
	}

	private function calculPrixPartiePlante($idVente, $prix_1, $prix_2, $prix_3, $unite_1, $unite_2, $unite_3) {
		Zend_Loader::loadClass("VentePrixPartiePlante");
		$ventePrixPartiePlanteTable = new VentePrixPartiePlante();

		foreach($this->view->unites as $k => $u) {
			if ($unite_1 == $k && mb_substr($unite_1, 0, 6) == "plante") {
				$data = array("prix_vente_prix_partieplante" => $prix_1,
							  "id_fk_type_vente_prix_partieplante" => $u["id_type_partieplante"],
							  "id_fk_type_plante_vente_prix_partieplante" => $u["id_type_plante"],
							  "id_fk_vente_prix_partieplante" => $idVente,
							  "type_prix_partieplante" => $u["type_forme"]);
				$ventePrixPartiePlanteTable->insert($data);
				$this->view->textePrixVente[] = array("texte" => $u["nom_type_unite"]. " : ". $prix_1);
			}
			if ($unite_2 == $k && mb_substr($unite_2, 0, 6) == "plante") {
				$data = array("prix_vente_prix_partieplante" => $prix_2,
							  "id_fk_type_vente_prix_partieplante" => $u["id_type_partieplante"],
							  "id_fk_type_plante_vente_prix_partieplante" => $u["id_type_plante"],
							  "id_fk_vente_prix_partieplante" => $idVente,
							  "type_prix_partieplante" => $u["type_forme"]);
				$ventePrixPartiePlanteTable->insert($data);
				$this->view->textePrixVente[] = array("texte" => $u["nom_type_unite"]. " : ". $prix_2);
			}
			if ($unite_3 == $k && mb_substr($unite_3, 0, 6) == "plante") {
				$data = array("prix_vente_prix_partieplante" => $prix_3,
							  "id_fk_type_vente_prix_partieplante" => $u["id_type_partieplante"],
							  "id_fk_type_plante_vente_prix_partieplante" => $u["id_type_plante"],
							  "id_fk_vente_prix_partieplante" => $idVente,
							  "type_prix_partieplante" => $u["type_forme"]);
				$ventePrixPartiePlanteTable->insert($data);
				$this->view->textePrixVente[] = array("texte" => $u["nom_type_unite"]. " : ". $prix_3);
			}
		}
	}

	function getListBoxRefresh() {
		if ($this->boxHotelToRefresh == true) {
			$box[] = "box_hotel";

			if ($this->view->idTypeCourantDepart > 0) {
				$box[] = $this->view->typeDepart[$this->view->idTypeCourantDepart]["box"];
			}
			return $this->constructListBoxRefresh($box);
		} else {
			return $this->constructListBoxRefresh();
		}

	}
}