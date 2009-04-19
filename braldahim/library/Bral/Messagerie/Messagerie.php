<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Competence.php 1439 2009-04-17 20:57:09Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-04-17 22:57:09 +0200 (Fri, 17 Apr 2009) $
 * $LastChangedRevision: 1439 $
 * $LastChangedBy: yvonnickesnault $
 */
abstract class Bral_Messagerie_Messagerie {
	
	protected $view;
	protected $request;
	protected $action;
	
	function __construct($request, $view, $action) {
		$this->view = $view;
		$this->request = $request;
		$this->action = $action;
	}

	abstract function render();
	
	public function getListBoxRefresh($tab = null) {
		$tab = array();
		if ($this->view->estQueteEvenement) {
			$tab[] = "box_quetes";
		}
		return $tab;
	}

	public function getIdEchoppeCourante() {
		return false;
	}
	
	public function getNomInterne() {
		return "messagerie_contenu";
	}
}