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
abstract class Bral_Carnet_Carnet {

	protected $reloadInterface = false;

	function __construct($nomSysteme, $request, $view, $action) {
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
		$this->nom_systeme = $nomSysteme;

		$this->prepareCommun();

		switch($this->action) {
			case "ask" :
				$this->prepareFormulaire();
				break;
			case "do":
				$this->prepareResultat();
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	abstract function prepareCommun();
	abstract function prepareFormulaire();
	abstract function prepareResultat();

	function getNomInterne() {
		return "box_carnet";
	}

	function getIdEchoppeCourante() {
		return false;
	}

	function render() {
		switch($this->action) {
			case "ask":
				return $this->view->render("carnet/".$this->nom_systeme."_formulaire.phtml");
				break;
			case "do":
				$this->view->reloadInterface = $this->reloadInterface;
				return $this->view->render("carnet/".$this->nom_systeme."_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}

	public function getListBoxRefresh() {
		return null;
	}
}
