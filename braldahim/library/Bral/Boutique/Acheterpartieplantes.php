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
class Bral_Boutique_Acheterpartieplantes extends Bral_Boutique_Boutique {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Acheter des parties de plantes";
	}

	function prepareCommun() {
		throw new Zend_Exception("Boutique fermee");
		Zend_Loader::loadClass('Bral_Util_BoutiquePlantes');
		Zend_Loader::loadClass('Region');
		Zend_Loader::loadClass('StockPartieplante');
		Zend_Loader::loadClass('BoutiquePartieplante');
		Zend_Loader::loadClass("Charrette");

		$this->view->acheterPossible = true;
		if ($this->view->assezDePa == false) {
			$this->view->acheterPossible = false;
		}

		$idDestinationCourante = $this->request->get("id_destination_courante");

		$selectedLaban = "";
		$selectedCharrette = "";
		if ($idDestinationCourante == "laban") {
			$selectedLaban = "selected";
		} else if ($idDestinationCourante == "charrette") {
			$selectedCharrette = "selected";
		}
		$tabDestinationTransfert[] = array("id_destination" => "laban", "texte" => "votre laban", "selected" => $selectedLaban);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdHobbit($this->view->user->id_hobbit);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$tabDestinationTransfert[] = array("id_destination" => "charrette", "texte" => "votre charrette", "selected" => $selectedCharrette);
		}
		$this->view->destinationTransfertCourante = $idDestinationCourante;
		$this->view->destinationTransfert = $tabDestinationTransfert;
		$this->view->charrette = $charrette;

		$regionTable = new Region();
		$idRegion = $regionTable->findIdRegionByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		$this->view->typePlantes = Bral_Util_BoutiquePlantes::construireTabPrix(true, $idRegion);

		if ($this->view->destinationTransfertCourante == "charrette") {
			$this->view->poidsRestant = $this->view->charrette["poids_transportable_charrette"] - $this->view->charrette["poids_transporte_charrette"];
		} else {
			$this->view->poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;
		}
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this)."::pas assez de PA");
		}

		for ($i = 2; $i <= $this->view->typePlantes["nb_valeurs"] + 1; $i++) {
			if (((int)$this->request->get("valeur_".$i).""!=$this->request->get("valeur_".$i)."")) {
				throw new Zend_Exception("Bral_Boutique_Acheterpartieplantes :: Nombre invalide (".$i.") : ".$this->request->get("valeur_".$i));
			} else {
				$this->view->quantiteAchetee = (int)$this->request->get("valeur_".$i);
			}
		}

		$idDestination = $this->request->get("valeur_1");

		if ($this->request->get("id_destination_courante") != $idDestination) {
			throw new Zend_Exception(get_class($this)." destination invalide 1");
		}

		if ($this->view->charrette == null && $this->request->get("id_destination_courante") == "charrette") {
			throw new Zend_Exception(get_class($this)." destination invalide 2");
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
			throw new Zend_Exception(get_class($this)." destination inconnue=".$destination);
		}

		$this->transfert($idDestination);
		$this->view->destination = $destination;
	}

	private function transfert($idDestination) {
		$this->view->coutCastars = 0;

		$this->view->elementsAchetes = "";
		$this->view->manquePlace = false;
		$this->view->manqueCastars = false;

		for ($i = 2; $i <= $this->view->typePlantes["nb_valeurs"] + 1; $i++) {
			$quantite = (int)$this->request->get("valeur_".$i);
			$idTypePlante = $this->view->typePlantes["valeurs"]["valeur_".$i]["id_type_plante"];
			$idTypePartiePlante = $this->view->typePlantes["valeurs"]["valeur_".$i]["id_type_partieplante"];
			$nomTypePlante = $this->view->typePlantes["valeurs"]["valeur_".$i]["nom_type_plante"];
			$nomTypePartiePlante = $this->view->typePlantes["valeurs"]["valeur_".$i]["nom_type_partieplante"];
			$idStock = $this->view->typePlantes["valeurs"]["valeur_".$i]["idStock"];
			$prixUnitaire = $this->view->typePlantes["valeurs"]["valeur_".$i]["idStock"];

			$prixUnitaire = $this->view->typePlantes["valeurs"]["valeur_".$i]["prixUnitaireVente"];
			$this->transfertElement($idDestination, $quantite, $prixUnitaire, $idTypePlante, $idTypePartiePlante, $nomTypePlante, $nomTypePartiePlante, $prixUnitaire, $idStock);
		}
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;

		if ($this->view->elementsAchetes != "") {
			$this->view->elementsAchetes = mb_substr($this->view->elementsAchetes, 0, -2);
		} else { // rien n'a pu etre achete
			$this->view->nb_pa = 0;
		}
	}

	private function transfertElement($idDestination, $quantite, $prixUnitaire, $idTypePlante, $idTypePartiePlante, $nomTypePlante, $nomTypePartiePlante, $prixUnitaire, $idStock) {

		if ($this->view->poidsRestant < 0) $this->view->poidsRestant = 0;
		$nbPossible = floor($this->view->poidsRestant / Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE);

		if ($quantite > $nbPossible) {
			$quantite = $nbPossible;
			$this->view->manquePlace = true;
		}

		$prixTotal = $prixUnitaire * $quantite;
		$castarsRestants = $this->view->user->castars_hobbit - $this->view->coutCastars;
		if ($prixTotal > $castarsRestants) {
			$quantite = floor($castarsRestants / $prixUnitaire);
			$prixTotal = floor($prixUnitaire * $quantite);
			$this->view->manqueCastars = true;
		}

		if ($quantite >= 1) {
			$this->view->coutCastars += $prixTotal;
			$this->view->poidsRestant = floor($this->view->poidsRestant - ($quantite * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE));
			$this->view->poidsRestant = $this->view->poidsRestant + ($prixTotal * Bral_Util_Poids::POIDS_CASTARS); // on enleve le poids des castars enleves
			$this->transfertEnBase($idDestination, $quantite, $idTypePlante, $idTypePartiePlante, $prixUnitaire, $idStock);

			if ($quantite > 1) {$s = 's';} else {$s = '';};
			$this->view->elementsAchetes .= "<br>". $quantite;
			$this->view->elementsAchetes .= " ".$nomTypePartiePlante.$s;
			$this->view->elementsAchetes .= " ".$nomTypePlante;
			if ($prixTotal > 1) {$s = 's';} else {$s = '';};
			$this->view->elementsAchetes .= " pour ".$prixTotal." castar".$s.", ";
		}
	}

	private function transfertEnBase($idDestination, $quantite, $idTypePlante, $idTypePartiePlante, $prixUnitaire, $idStock) {
		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharrettePartieplante");
			$table = new CharrettePartieplante();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanPartieplante");
			$table = new LabanPartieplante();
			$suffixe = "laban";
		}

		$data = array(
			"quantite_".$suffixe."_partieplante" => $quantite,
			"id_fk_type_".$suffixe."_partieplante" => $idTypePartiePlante,
			"id_fk_type_plante_".$suffixe."_partieplante" => $idTypePlante,
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_partieplante"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban_partieplante"] = $this->view->user->id_hobbit;
		}

		$table->insertOrUpdate($data);

		$data = array(
			"date_achat_boutique_partieplante" => date("Y-m-d H:i:s"),
			"id_fk_type_boutique_partieplante" => $idTypePartiePlante,
			"id_fk_type_plante_boutique_partieplante" => $idTypePlante,
			"id_fk_lieu_boutique_partieplante" => $this->view->idBoutique,
			"id_fk_hobbit_boutique_partieplante" => $this->view->user->id_hobbit,
			"quantite_brut_boutique_partieplante" => $quantite,
			"prix_unitaire_boutique_partieplante" => $prixUnitaire,
			"id_fk_region_boutique_partieplante" => $this->idRegion,
			"action_boutique_partieplante" => "vente",
		);
		$boutiquePartieplanteTable = new BoutiquePartieplante();
		$boutiquePartieplanteTable->insert($data);

		$data = array(
			"id_stock_partieplante" => $idStock,
			"nb_brut_restant_stock_partieplante" => -$quantite,
		);
		$stockPartieplanteTable = new StockPartieplante();
		$stockPartieplanteTable->updateStock($data);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}
	}

	function getListBoxRefresh() {
		if ($this->view->destination["id_destination"] == "charrette") {
			$boxToRefresh = "box_charrette";
			return $this->constructListBoxRefresh(array("box_laban", $boxToRefresh, "box_bpartieplantes"));
		} else {
			$boxToRefresh = "box_laban";
			return $this->constructListBoxRefresh(array($boxToRefresh, "box_bpartieplantes"));
		}
	}
}