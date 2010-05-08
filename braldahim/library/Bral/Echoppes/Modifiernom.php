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
class Bral_Echoppes_Modifiernom extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		
		$id_echoppe = $this->request->get("valeur_1");
		
		if ($id_echoppe == "" || $id_echoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide=".$id_echoppe);
		}
		
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdBraldun($this->view->user->id_braldun);
		
		$tabEchoppe = null;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe && 
				$e["x_echoppe"] == $this->view->user->x_braldun && 
				$e["y_echoppe"] == $this->view->user->y_braldun) {
				$tabEchoppe = array(
					'id_echoppe' => $e["id_echoppe"],
					'nom_echoppe' => $e["nom_echoppe"],
				);
				break;
			}
		}
		if ($tabEchoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide idh:".$this->view->user->id_braldun." ide:".$id_echoppe);
		}
		
		$this->view->echoppe = $tabEchoppe;
		$this->view->idEchoppe = $id_echoppe;
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
		$data = array("nom_echoppe" => $nom);
		$echoppeTable = new Echoppe();
		$where = "id_echoppe = ".$this->view->idEchoppe;
		$echoppeTable->update($data, $where);
		
		$this->view->nom = $nom;
	}
	
	public function getIdEchoppeCourante() {
		if (isset($this->view->idEchoppe)) {
			return $this->view->idEchoppe;
		} else {
			return false;
		}
	}
	
	function getListBoxRefresh() {
		return array();
	}
}