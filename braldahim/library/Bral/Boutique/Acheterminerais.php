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
class Bral_Boutique_Acheterminerais extends Bral_Boutique_Boutique {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Acheter du minerai";
	}

	function prepareCommun() {
		Zend_Loader::loadClass('Bral_Util_BoutiqueMinerais');
		Zend_Loader::loadClass('Region');
		Zend_Loader::loadClass('StockMinerai');
		Zend_Loader::loadClass('BoutiqueMinerai');
		Zend_Loader::loadClass('Charrette');

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

		$this->view->acheterPossible = true;
		$this->view->minerais = Bral_Util_BoutiqueMinerais::construireTabPrix(true, $this->idRegion);

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

		for ($i = 2; $i <= count($this->view->minerais) + 1; $i++) {
			if (((int)$this->request->get("valeur_".$i).""!=$this->request->get("valeur_".$i)."")) {
				throw new Zend_Exception("Bral_Boutique_Acheterminerais :: Nombre invalide (".$i.") : ".$this->request->get("valeur_".$i));
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

		foreach($this->view->minerais as $m) {
			$quantite = (int)$this->request->get($m["id_champ"]);

			$idTypeMinerai = $m["id_type_minerai"];
			$nomTypeMinerai = $m["type"];
			$idStock = $m["idStock"];

			$prixUnitaire = $m["prixUnitaireVente"];

			if ($quantite > $m["nbStockRestant"]) {
				$quantite = $m["nbStockRestant"];
			}

			$this->transfertElement($idDestination, $quantite, $prixUnitaire, $idTypeMinerai, $nomTypeMinerai, $idStock);
		}
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->view->coutCastars;

		if ($this->view->elementsAchetes != "") {
			$this->view->elementsAchetes = mb_substr($this->view->elementsAchetes, 0, -2);
		} else { // rien n'a pu etre achete
			$this->view->nb_pa = 0;
		}
	}

	private function transfertElement($idDestination, $quantite, $prixUnitaire, $idTypeMinerai, $nomTypeMinerai, $idStock) {

		if ($this->view->poidsRestant < 0) $this->view->poidsRestant = 0;
		$nbPossible = floor($this->view->poidsRestant / Bral_Util_Poids::POIDS_MINERAI);

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
			$this->view->poidsRestant = floor($this->view->poidsRestant - ($quantite * Bral_Util_Poids::POIDS_MINERAI));
			$this->view->poidsRestant = $this->view->poidsRestant + ($prixTotal * Bral_Util_Poids::POIDS_CASTARS); // on enleve le poids des castars enleves

			$this->transfertEnBase($idDestination, $quantite, $idTypeMinerai, $prixUnitaire, $idStock);

			if ($quantite > 1) {$s = 's';} else {$s = '';};
			$this->view->elementsAchetes .= "<br>".$quantite;
			$this->view->elementsAchetes .= " minerai".$s." ".$nomTypeMinerai;
			if ($prixTotal > 1) {$s = 's';} else {$s = '';};
			$this->view->elementsAchetes .= " pour ".$prixTotal." castar".$s.", ";
		}
	}

	private function transfertEnBase($idDestination, $quantite, $idTypeMinerai, $prixUnitaire, $idStock) {
		if ($idDestination == "charrette") {
			Zend_Loader::loadClass("CharretteMinerai");
			$table = new CharretteMinerai();
			$suffixe = "charrette";
		} else {
			Zend_Loader::loadClass("LabanMinerai");
			$table = new LabanMinerai();
			$suffixe = "laban";
		}

		$data = array(
			"quantite_brut_".$suffixe."_minerai" => $quantite,
			"id_fk_type_".$suffixe."_minerai" => $idTypeMinerai,
		);

		if ($idDestination == "charrette") {
			$data["id_fk_charrette_minerai"] = $this->view->charrette["id_charrette"];
		} else {
			$data["id_fk_hobbit_laban_minerai"] = $this->view->user->id_hobbit;
		}
		$table->insertOrUpdate($data);
			
		$data = array(
			"date_achat_boutique_minerai" => date("Y-m-d H:i:s"),
			"id_fk_type_boutique_minerai" => $idTypeMinerai,
			"id_fk_lieu_boutique_minerai" => $this->view->idBoutique,
			"id_fk_hobbit_boutique_minerai" => $this->view->user->id_hobbit,
			"quantite_brut_boutique_minerai" => $quantite,
			"prix_unitaire_boutique_minerai" => $prixUnitaire,
			"id_fk_region_boutique_minerai" => $this->idRegion,
			"action_boutique_minerai" => "vente",
		);
		$boutiqueMineraiTable = new BoutiqueMinerai();
		$boutiqueMineraiTable->insert($data);

		$data = array(
			"id_stock_minerai" => $idStock,
			"nb_brut_restant_stock_minerai" => -$quantite,
		);
		$stockMineraiTable = new StockMinerai();
		$stockMineraiTable->updateStock($data);

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
		}
	}

	function getListBoxRefresh() {
		if ($this->view->destination["id_destination"] == "charrette") {
			$boxToRefresh = "box_charrette";
			return $this->constructListBoxRefresh(array("box_laban", $boxToRefresh, "box_bminerais"));
		} else {
			$boxToRefresh = "box_laban";
			return $this->constructListBoxRefresh(array($boxToRefresh, "box_bminerais"));
		}
	}
}