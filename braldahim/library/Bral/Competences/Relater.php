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
class Bral_Competences_Relater extends Bral_Competences_Competence {
	private $texte = null;
	
	function prepareCommun() {
		Zend_Loader::loadClass("Monstre");
		Zend_Loader::loadClass("Lieu");
		
		$this->texte = null;
		$this->texte_original = null;
		if ($this->request->get('valeur_1') != null || $this->request->get('valeur_1') != "") {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');
			
			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())
			->addFilter(new Zend_Filter_StripTags());
			
			$this->texte_original = stripslashes($filter->filter($this->request->get('valeur_1')));
		}
		$this->view->texte_original = $this->texte_original;
		$this->view->texte_transforme = "[h".$this->view->user->id_hobbit."] a dit : \"".$this->texte_original."\"";
		
		$this->view->texte = Bral_Util_Evenement::remplaceBaliseParNomEtJs($this->view->texte_transforme, false);
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		
		$id_type = $this->view->config->game->evenements->type->evenement;
		$details = $this->view->texte_transforme;
		$this->setDetailsEvenement($details, $id_type);
		$this->setEvenementQueSurOkJet1(false);
		
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh();
	}

}