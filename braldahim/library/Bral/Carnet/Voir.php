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
class Bral_Carnet_Voir extends Bral_Carnet_Carnet {


	function prepareCommun() {
		Zend_Loader::loadClass("Carnet");

		$idCarnet = 1;
		if ($this->request->get("carnet") != "" && ((int)$this->request->get("carnet")."" == $this->request->get("carnet")."")) {
			$idCarnet = (int)$this->request->get("carnet");
		} else {
			$idCarnet = 1;
		}

		$this->view->nbMaxNote = Carnet::MAX_NOTE;
		$this->view->idCarnet = $idCarnet;

		if ($idCarnet > $this->view->nbMaxNote) {
			throw new Zend_Exception("Carnet invalide : ".$idCarnet);
		}

		$carnetTable = new Carnet();

		$noteInfo = "";
		
		if ($this->request->get("mode") == "editer") {
			$data["id_carnet"] = $idCarnet;
			$data["id_fk_braldun_carnet"] = $this->view->user->id_braldun;
				
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');

			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim());
				
			$data["texte_carnet"] = stripslashes(htmlspecialchars($filter->filter($this->request->get('texte_carnet'))));

			$carnetTable->insertOrUpdate($data);
			$noteInfo = "Enregistrement effectué à ".date("H:i:s");
		}

		$carnet = $carnetTable->findByIdBraldunAndIdCarnet($this->view->user->id_braldun, $idCarnet);

		$htmlCarnet = "vide";

		if ($carnet != null && count($carnet) == 1) {
			$carnet = $carnet[0];
			$htmlCarnet = $carnet["texte_carnet"];
		}
		$this->view->htmlCarnet = $htmlCarnet;
		$this->view->noteInfo = $noteInfo;
	}

	function prepareFormulaire() {
	}

	function render() {
		return $this->view->render("carnet/voir.phtml");
	}

	function prepareResultat() {


	}

}