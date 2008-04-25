<?php

class VieMonstresController extends Zend_Controller_Action {

	function init() {
		// TODO a remplacer par controlScript
		Bral_Util_Securite::controlAdmin();
	
		$this->initView();
		$this->view->config = Zend_Registry::get('config');

		Zend_Loader::loadClass('GroupeMonstre');
		Zend_Loader::loadClass('Monstre');
		Zend_Loader::loadClass("Bral_Monstres_VieGroupesNuee");
		Zend_Loader::loadClass("Bral_Util_ConvertDate");
	}

	function vieAction() {
		$vieGroupe = new Bral_Monstres_VieGroupesNuee($this->view);
		$vieGroupe->vieGroupesAction();
		$this->render();
	}
}