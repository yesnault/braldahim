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
class RechercheController extends Zend_Controller_Action {

	function init() {
		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		if (!Zend_Auth::getInstance()->hasIdentity()
		&& $this->_request->action != 'logoutajax') {
			$this->_redirect('/Recherche/logoutajax');
		}
		$this->view->config = Zend_Registry::get('config');
		if ($this->view->config->general->actif != 1) {
			$this->_redirect('/');
		}
	}

	function indexAction() {
		$this->render();
	}

	function logoutajaxAction() {
		$this->render();
	}

	function hobbitAction() {
		$this->rechercheHobbit();
		$this->render();
	}

	function bourlingueurAction() {
		$idTypeBourlingueur = Bral_Util_Controle::getValeurIntVerif($this->_request->get("type"));
		$this->rechercheHobbit($idTypeBourlingueur);
		$this->render();
	}

	private function rechercheHobbit($idTypeDistinction = null) {
		
		if (Bral_Util_String::isChaineValide(stripslashes($this->_request->get("valeur")))) {
			$tabHobbits = null;
			$hobbitTable = new Hobbit();

			if ($idTypeDistinction != null) {

				$hobbitRowset = $hobbitTable->findHobbitsParPrenomAndIdTypeDistinction($this->_request->get("valeur").'%', $idTypeDistinction);
				$hobbits = array();
				foreach ($hobbitRowset as $h) {
					$hobbits[] = $h["id_hobbit"];
				}

				Zend_Loader::loadClass('Bral_Util_Distinction');
				$idTypeDistinctionDonjon = Bral_Util_Distinction::getIdDistinctionDonjonFromIdDistinctionBourlingueur($idTypeDistinction);
				Zend_Loader::loadClass("HobbitsDistinction");
				$hobbitsDistinctionTable = new HobbitsDistinction();
				$distinctionsDonjon = $hobbitsDistinctionTable->countDistinctionByIdHobbitList($hobbits, $idTypeDistinctionDonjon);

				Zend_Loader::loadClass("SouleEquipe");
				$souleEquipeTable = new SouleEquipe();
				$soule = $souleEquipeTable->countNonDebuteByIdHobbitList($hobbits);

			} else {
				$hobbitRowset = $hobbitTable->findHobbitsParPrenom($this->_request->get("valeur").'%');
			}
			$this->view->champ = $this->_request->get("champ");

			foreach ($hobbitRowset as $h) {
				$hobbit = array(
						"id_hobbit" => $h["id_hobbit"],
						"nom" => $h["nom_hobbit"],
						"prenom" => $h["prenom_hobbit"],
				);
					
				if ($idTypeDistinction == null) {
					$tabHobbits[] = $hobbit;
				} else if ($idTypeDistinction != null && $h["id_hobbit"] != $this->view->user->id_hobbit) {
					if ($distinctionsDonjon == null) {
						$tabHobbits[] = $hobbit;
					} else {
						$okD = false;
						$okS = false;
						foreach($distinctionsDonjon as $d) {
							if ($h["est_donjon_hobbit"] == "non" && $d["id_fk_hobbit_hdistinction"] == $h["id_hobbit"] && $d["nombre"] < 1) {
								$okD = true;
								break;
							}
						}
						foreach($soule as $s) {
							if ($h["est_soule_hobbit"] == "non" && $s["id_fk_hobbit_soule_equipe"] == $h["id_hobbit"] && $d["nombre"] < 1) {
								$okS = true;
								break;
							}
						}

						if ($okD == true && $okS == true) {
							$tabHobbits[] = $hobbit;
						}
					}
				}
			}
			$this->view->pattern = $this->_request->get("valeur");
			$this->view->tabHobbits = $tabHobbits;
		}
	}
}
