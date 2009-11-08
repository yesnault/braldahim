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
class Bral_Champs_Modifiernom extends Bral_Champs_Champ {

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
		$champs = $champTable->findByIdHobbit($this->view->user->id_hobbit);
		
		$tabChamp = null;
		foreach ($champs as $e) {
			if ($e["id_champ"] == $id_champ && 
				$e["x_champ"] == $this->view->user->x_hobbit && 
				$e["y_champ"] == $this->view->user->y_hobbit) {
				$tabChamp = array(
					'id_champ' => $e["id_champ"],
					'nom_champ' => $e["nom_champ"],
				);
				break;
			}
		}
		if ($tabChamp == null) {
			throw new Zend_Exception(get_class($this)." Champ invalide idh:".$this->view->user->id_hobbit." ide:".$id_champ);
		}
		
		$this->view->champ = $tabChamp;
		$this->view->idChamp = $id_champ;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		Zend_Loader::loadClass('Zend_Filter');
		Zend_Loader::loadClass('Zend_Filter_StripTags');
		Zend_Loader::loadClass('Zend_Filter_StringTrim');
			
		$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_StringTrim())
			->addFilter(new Zend_Filter_StripTags());

		
		$nom = stripslashes($filter->filter(mb_substr($this->request->getPost("valeur_2"), 0, 30)));
		$data = array("nom_champ" => $nom);
		$champTable = new Champ();
		$where = "id_champ = ".$this->view->idChamp;
		$champTable->update($data, $where);
		
		$this->view->nom = $nom;
	}
	
	public function getIdChampCourant() {
		if (isset($this->view->idChamp)) {
			return $this->view->idChamp;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return array();
	}
}