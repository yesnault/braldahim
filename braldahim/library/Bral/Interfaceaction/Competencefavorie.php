<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Interfaceaction_Competencefavorie extends Bral_Interfaceaction_Interfaceaction {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Compétence Favorie";
	}

	function prepareCommun() {
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		$idCompetence = Bral_Util_Controle::getValeurIntVerif($this->request->getPost("valeur_1"));
		
		Zend_Loader::loadClass("BraldunsCompetencesFavories");
		$favoriesTable = new BraldunsCompetencesFavories();
		$favoriesRowset = $favoriesTable->findByIdBraldunAndIdCompetence($this->view->user->id_braldun, $idCompetence);
		
		if ($favoriesRowset == null) {
			$data["id_fk_braldun_hcompf"] = $this->view->user->id_braldun;
			$data["id_fk_competence_hcompf"] = $idCompetence;
			$favoriesTable->insert($data);
		} else {
			$where = "id_fk_competence_hcompf = ".intval($idCompetence). " and id_fk_braldun_hcompf=".$this->view->user->id_braldun;
			$favoriesTable->delete($where);
		}
		
	}

	function getListBoxRefresh() {
		return array("box_competences");
	}
}