<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Butin_Partage extends Bral_Butin_Butin
{

	function getNomInterne()
	{
		return "box_action";
	}

	function getTitreAction()
	{
		return "Butin : Gestion des partages";
	}

	function prepareCommun()
	{
		Zend_Loader::loadClass("ButinPartage");

		$butinPartageTable = new ButinPartage();
		$partages = $butinPartageTable->findByIdBraldun($this->view->user->id_braldun);
		$listBralduns = null;
		if (count($partages) > 0) {
			foreach ($partages as $b) {
				$listBralduns .= $b["id_fk_autorise_butin_partage"] . ",";
			}
		}

		$tabBralduns["aff_js_destinataires"] = "";
		$tabBralduns["destinataires"] = "";
		if ($listBralduns != null) {
			$tabBralduns = Bral_Util_Messagerie::constructTabBraldun($listBralduns, "valeur_2_partage");
		}
		$this->view->tabBralduns = $tabBralduns;
	}

	function prepareFormulaire()
	{
	}

	function prepareResultat()
	{

		if ($this->request->get("valeur_1") != "oui" && $this->request->get("valeur_1") != "non") {
			throw new Zend_Exception(get_class($this) . " Butin valeur_1 invalide : " . $this->request->get("valeur_1"));
		} else {
			$partageCommunaute = $this->request->get("valeur_1");
		}

		$this->calculPartage($partageCommunaute);
	}

	private function calculPartage($partageCommunaute)
	{
		$braldunTable = new Braldun();
		$data = array(
			'est_partage_communaute_butin_braldun' => $partageCommunaute,
		);
		$where = "id_braldun = " . $this->view->user->id_braldun;
		$braldunTable->update($data, $where);

		$butinPartageTable = new ButinPartage();
		$where = "id_fk_braldun_butin_partage = " . $this->view->user->id_braldun;
		$butinPartageTable->delete($where);

		$bralduns = $this->recupereBraldunFromValeur2();

		if (count($bralduns) > 0) {
			foreach ($bralduns as $b) {
				$data = array(
					'id_fk_braldun_butin_partage' => $this->view->user->id_braldun,
					'id_fk_autorise_butin_partage' => $b["id_braldun"],
				);
				$butinPartageTable->insert($data);
			}
		}
	}

	private function recupereBraldunFromValeur2()
	{
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		$filter = new Zend_Filter_StripTags();
		$braldunsList = $filter->filter(trim($this->request->get('valeur_2')));

		$idBraldunsTab = preg_split("/,/", $braldunsList);

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findByIdList($idBraldunsTab);

		return $bralduns;
	}

	function getListBoxRefresh()
	{
		$tab = array("box_laban");
		return $this->constructListBoxRefresh($tab);
	}
}
