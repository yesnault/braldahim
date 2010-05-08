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
class Bral_Champs_Modifierdescription extends Bral_Champs_Champ {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Champ");
		
		$id_champ = $this->request->get("valeur_1");
		
		if ($id_champ == "" || $id_champ == null) {
			throw new Zend_Exception(get_class($this)." Champ invalide=".$id_champ);
		}
		
		$champTable = new Champ();
		$champs = $champTable->findByIdBraldun($this->view->user->id_braldun);
		
		$tabChamp = null;
		foreach ($champs as $e) {
			if ($e["id_champ"] == $id_champ && 
				$e["x_champ"] == $this->view->user->x_braldun && 
				$e["y_champ"] == $this->view->user->y_braldun) {
				$tabChamp = array(
					'id_champ' => $e["id_champ"],
					'commentaire_champ' => stripslashes($e["commentaire_champ"]),
				);
				break;
			}
		}
		if ($tabChamp == null) {
			throw new Zend_Exception(get_class($this)." Champ invalide idh:".$this->view->user->id_braldun." ide:".$id_champ);
		}
		
		$this->view->champ = $tabChamp;
		$this->view->idChamp = $id_champ;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		Zend_Loader::loadClass('Zend_Filter');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');
	
		$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_StringTrim());
		
		$valeur = $filter->filter(htmlspecialchars($this->request->getPost("valeur_2")));

		$data = array("commentaire_champ" => $valeur);
		$champTable = new Champ();
		$where = "id_champ = ".$this->view->idChamp;
		$champTable->update($data, $where);
		
		$this->view->description = $valeur;
	}
	
	public function getIdChampCourant() {
		if (isset($this->view->idChamp)) {
			return $this->view->idChamp;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return array("box_lieu", "box_vue");
	}
}