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
		
		$this->view->texte = $this->transforme($this->texte_original);
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		
		$id_type = $this->view->config->game->evenements->type->evenement;
		$details = $this->view->user->prenom_hobbit ." ". $this->view->user->nom_hobbit ." (".$this->view->user->id_hobbit.") a dit : \"".$this->view->texte."\"";
		$this->setDetailsEvenement($details, $id_type);
		$this->setEvenementQueSurOkJet1(false);
		
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function transforme($texteOriginal) {
		
		// Monstre
		$texte = preg_replace_callback("/\[m(.*?)]/si", 
		create_function(
			'$matches', '
			$m = new Monstre();
			$nom = $m->findNomById($matches[1]);
			return $nom;'
		)
		, $texteOriginal);
		
		// Hobbit
		$texte = preg_replace_callback("/\[h(.*?)]/si", 
		create_function(
			'$matches', '
			$h = new Hobbit();
			$nom = $h->findNomById($matches[1]);
			return $nom;'
		)
		, $texte);
		
		// Lieu
		$texte = preg_replace_callback("/\[l(.*?)]/si", 
		create_function(
			'$matches', '
			$l = new Lieu();
			$nom = $l->findNomById($matches[1]);
			return $nom;'
		)
		, $texte);
		
		return $texte;
	}
	
	static function transformeMonstre($matches) {
		
	}
	
	function getListBoxRefresh() {
		return $this->constructListBoxRefresh();
	}

}