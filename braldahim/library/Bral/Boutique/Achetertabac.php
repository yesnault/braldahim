<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Boutique_Achetertabac extends Bral_Boutique_Boutique
{

	function getNomInterne()
	{
		return "box_action";
	}

	function getTitreAction()
	{
		return "Acheter du tabac";
	}

	function prepareCommun()
	{
		Zend_Loader::loadClass('Bral_Util_BoutiqueTabac');
		Zend_Loader::loadClass('Region');
		Zend_Loader::loadClass('StockTabac');
		Zend_Loader::loadClass('BoutiqueTabac');

		$this->view->acheterPossible = true;
		$this->view->tabac = Bral_Util_BoutiqueTabac::construireTabPrix(true, $this->idRegion);
	}

	function prepareFormulaire()
	{
		// rien ici
	}

	function prepareResultat()
	{
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this) . "::pas assez de PA");
		}

		for ($i = 1; $i <= count($this->view->tabac); $i++) {
			if (((int)$this->request->get("valeur_" . $i) . "" != $this->request->get("valeur_" . $i) . "")) {
				throw new Zend_Exception("Bral_Boutique_Achetertabac :: Nombre invalide (" . $i . ") : " . $this->request->get("valeur_" . $i));
			}
		}

		$this->transfert();
	}

	private function transfert()
	{
		Zend_Loader::loadClass("LabanTabac");
		$this->view->coutCastars = 0;
		$this->view->poidsRestant = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;

		$this->view->elementsAchetes = "";
		$this->view->manqueCastars = false;

		foreach ($this->view->tabac as $m) {
			$quantite = (int)$this->request->get($m["id_champ"]);

			$idTypeTabac = $m["id_type_tabac"];
			$nomTypeTabac = $m["type"];
			$idStock = $m["idStock"];

			$prixUnitaire = $m["prixUnitaireVente"];

			if ($quantite > $m["nbStockRestant"]) {
				$quantite = $m["nbStockRestant"];
			}

			$this->transfertElement($quantite, $prixUnitaire, $idTypeTabac, $nomTypeTabac, $idStock);
		}
		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->view->coutCastars;

		if ($this->view->elementsAchetes != "") {
			$this->view->elementsAchetes = mb_substr($this->view->elementsAchetes, 0, -2);
		} else { // rien n'a pu etre achete
			$this->view->nb_pa = 0;
		}
	}

	private function transfertElement($quantite, $prixUnitaire, $idTypeTabac, $nomTypeTabac, $idStock)
	{

		$nbPossible = $quantite; //floor($this->view->poidsRestant / Bral_Util_Poids::POIDS_MINERAI);

		$prixTotal = $prixUnitaire * $quantite;
		$castarsRestants = $this->view->user->castars_braldun - $this->view->coutCastars;
		if ($prixTotal > $castarsRestants) {
			$quantite = floor($castarsRestants / $prixUnitaire);
			$prixTotal = floor($prixUnitaire * $quantite);
			$this->view->manqueCastars = true;
		}

		if ($quantite >= 1) {
			$this->view->coutCastars += $prixTotal;
			$this->view->poidsRestant = floor($this->view->poidsRestant - ($quantite * Bral_Util_Poids::POIDS_MINERAI));
			$this->view->poidsRestant = $this->view->poidsRestant + ($prixTotal * Bral_Util_Poids::POIDS_CASTARS); // on enleve le poids des castars enleves

			$this->transfertEnBase($quantite, $idTypeTabac, $prixUnitaire, $idStock);

			if ($quantite > 1) {
				$s = 's';
			} else {
				$s = '';
			}
			;
			$this->view->elementsAchetes .= "<br />" . $quantite;
			$this->view->elementsAchetes .= " feuille" . $s . " de tabac " . $nomTypeTabac;
			if ($prixTotal > 1) {
				$s = 's';
			} else {
				$s = '';
			}
			;
			$this->view->elementsAchetes .= " pour " . $prixTotal . " castar" . $s . ", ";
		}
	}

	private function transfertEnBase($quantite, $idTypeTabac, $prixUnitaire, $idStock)
	{
		$data = array(
			"quantite_feuille_laban_tabac" => $quantite,
			"id_fk_type_laban_tabac" => $idTypeTabac,
			"id_fk_braldun_laban_tabac" => $this->view->user->id_braldun,
		);

		$labanTabacTable = new LabanTabac();
		$labanTabacTable->insertOrUpdate($data);

		$data = array(
			"date_achat_boutique_tabac" => date("Y-m-d H:i:s"),
			"id_fk_type_boutique_tabac" => $idTypeTabac,
			"id_fk_lieu_boutique_tabac" => $this->view->idBoutique,
			"id_fk_braldun_boutique_tabac" => $this->view->user->id_braldun,
			"quantite_feuille_boutique_tabac" => $quantite,
			"prix_unitaire_boutique_tabac" => $prixUnitaire,
			"id_fk_region_boutique_tabac" => $this->idRegion,
			"action_boutique_tabac" => "vente",
		);
		$boutiqueTabacTable = new BoutiqueTabac();
		$boutiqueTabacTable->insert($data);

		$data = array(
			"id_stock_tabac" => $idStock,
			"nb_feuille_restant_stock_tabac" => -$quantite,
		);
		$stockTabacTable = new StockTabac();
		$stockTabacTable->updateStock($data);
	}

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_laban", "box_btabac"));
	}
}