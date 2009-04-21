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
		Zend_Loader::loadClass("Bral_Util_Lien");

		$verbes = array(
			1 => "annonce",
			2 => "crie",
			3 => "hurle",
			4 => "se dit",
			5 => "chuchote",
			6 => "chantonne",
			7 => "grommèle",
			8 => "s'étonne",
			9 => "se réjouit",
			10 => "fanfaronne",
			11 => "relate",
			12 => "a dit",
		);
		asort($verbes);

		$this->texte = null;
		$this->texte_original = null;
		$idVerbe = -1;
		
		if ((((int)$this->request->get("valeur_1")."" == $this->request->get("valeur_1")."")) 
		 && ($this->request->get('valeur_2') != null || $this->request->get('valeur_2') != "" )) {
			Zend_Loader::loadClass('Zend_Filter');
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');

			$filter = new Zend_Filter();
			$filter->addFilter(new Zend_Filter_StringTrim())
			->addFilter(new Zend_Filter_StripTags());

			$this->texte_original = stripslashes($filter->filter($this->request->get('valeur_2')));
			$idVerbe = (int)$this->request->get("valeur_1");
			
			if (array_key_exists($idVerbe, $verbes)) {
				$this->view->texte_transforme = "[h".$this->view->user->id_hobbit."] ".$verbes[$idVerbe]." : \"".$this->texte_original."\"";
			}
		}
		$this->view->idVerbe = $idVerbe;
		$this->view->texte_original = $this->texte_original;

		$this->view->texte = Bral_Util_Lien::remplaceBaliseParNomEtJs($this->view->texte_transforme, true);
		$this->view->verbes = $verbes;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {

		$id_type = $this->view->config->game->evenements->type->evenement;
		$details = $this->view->texte_transforme;
		$this->setDetailsEvenement($details, $id_type);
		$this->setEvenementQueSurOkJet1(false);
		
		if ($this->view->user->est_soule_hobbit == "oui") {
			$this->idMatchSoule = $this->view->user->id_fk_soule_match_hobbit;
		}

		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh();
	}

}