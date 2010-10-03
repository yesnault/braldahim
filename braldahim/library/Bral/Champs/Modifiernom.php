<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Champs_Modifiernom extends Bral_Champs_Champ {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Champ");
		
		$idChamp = $this->request->get("valeur_1");
		
		if ($idChamp == "" || $idChamp == null) {
			throw new Zend_Exception(get_class($this)." Champ invalide=".$idChamp);
		}
		
		$champTable = new Champ();
		$champs = $champTable->findByIdBraldun($this->view->user->id_braldun);
		
		$tabChamp = null;
		foreach ($champs as $e) {
			if ($e["id_champ"] == $idChamp && 
				$e["x_champ"] == $this->view->user->x_braldun && 
				$e["y_champ"] == $this->view->user->y_braldun && 
				$e["z_champ"] == $this->view->user->z_braldun) {
				$tabChamp = array(
					'id_champ' => $e["id_champ"],
					'nom_champ' => $e["nom_champ"],
				);
				break;
			}
		}
		if ($tabChamp == null) {
			throw new Zend_Exception(get_class($this)." Champ invalide idh:".$this->view->user->id_braldun." ide:".$idChamp);
		}
		
		$this->view->champ = $tabChamp;
		$this->view->idChamp = $idChamp;
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
		return array("box_lieu", "box_vue");
	}
}