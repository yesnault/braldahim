<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Filatures_Liste extends Bral_Filatures_Filatures
{

	function getNomInterne()
	{
		return "box_quete_interne";
	}

	function render()
	{
		return $this->view->render("filatures/liste.phtml");
	}

	function getTitreAction()
	{
	}

	public function calculNbPa()
	{
	}

	function prepareCommun()
	{

		Zend_Loader::loadClass("Filature");
		Zend_Loader::loadClass("Bral_Util_Lien");

		$filatureTable = new Filature();
		$filatures = $filatureTable->findByIdBraldun($this->view->user->id_braldun);

		$idFilatureEnCours = null;

		$tabFilatures = null;
		if ($filatures != null && count($filatures) > 0) {
			foreach ($filatures as $f) {
				$cible = Bral_Util_Lien::remplaceBaliseParNomEtJs("[b" . $f["id_braldun"] . "]", true);
				$filature = array(
					'id_filature' => $f["id_filature"],
					'cible' => $cible,
					'date_creation_filature' => $f["date_creation_filature"],
					'date_fin_filature' => $f["date_fin_filature"],
					'etape_filature' => $f["etape_filature"],
				);

				if ($f["date_fin_filature"] == null) {
					$idFilatureEnCours = $f["id_filature"];
				}

				$tabFilatures[] = $filature;
			}
		}

		$this->view->filatures = $tabFilatures;

		$this->view->htmlFilature = "";
		$this->view->idFilatureEnCours = $idFilatureEnCours;

		if ($idFilatureEnCours != null) {
			Zend_Loader::loadClass("Bral_Filature_Factory");
			$voir = Bral_Filature_Factory::getVoir($this->request, $this->view, $idFilatureEnCours);
			$this->view->htmlFilature = $voir->render();
		}

	}

	function prepareFormulaire()
	{
	}

	function prepareResultat()
	{
	}

	function getListBoxRefresh()
	{
	}

}