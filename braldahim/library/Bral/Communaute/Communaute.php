<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Communaute_Communaute {

	function __construct($nomSystemeAction, $request, $view, $action) {
		Zend_Loader::loadClass('Bral_Util_Communaute');

		$this->view = $view;
		$this->action = $action;
		$this->nom_systeme = $nomSystemeAction;
		$this->_request = $request;
		
		$this->view->nb_pa = 0;
		$this->view->titreAction = $this->getTitre();

		$this->prepareCommun();
		$this->calculNbPa();

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
	
	abstract function getTitre();

	protected function calculNbPa() {
		if ($this->view->user->pa_braldun - $this->view->nb_pa < 0) {
			$this->view->assezDePa = false;
		} else {
			$this->view->assezDePa = true;
		}
	}

	function anotherXmlEntry() {
		return null;
	}

	public function render() {
		switch($this->action) {
			case "ask":
				$texte = $this->view->render("communaute/".$this->nom_systeme."_formulaire.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				return $this->view->render("commun/commun_formulaire.phtml");
				break;
			case "do":
				$texte = $this->view->render("communaute/".$this->nom_systeme."_resultat.phtml");
				// suppression des espaces : on met un espace à la place de n espaces à suivre
				$this->view->texte = trim(preg_replace('/\s{2,}/', ' ', $texte));
				return $this->view->render("commun/commun_resultat.phtml");
				break;
			default:
				throw new Zend_Exception(get_class($this)."::action invalide :".$this->action);
		}
	}
}