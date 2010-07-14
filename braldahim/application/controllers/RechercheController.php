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

	function braldunAction() {
		$avecBraldunEnCours = Bral_Util_Controle::getValeurTrueFalseVerifSansException($this->_request->get("avecBraldunEnCours"));
		$avecPnj = Bral_Util_Controle::getValeurTrueFalseVerifSansException($this->_request->get("avecPnj"));
		$this->rechercheBraldun(null, $avecBraldunEnCours, $avecPnj);
		$this->render();
	}

	function bourlingueurAction() {
		$idTypeBourlingueur = Bral_Util_Controle::getValeurIntVerif($this->_request->get("type"));
		$this->rechercheBraldun($idTypeBourlingueur);
		$this->render();
	}

	private function rechercheBraldun($idTypeDistinction = null, $avecBraldunEnCours = null, $avecPnj = null) {

		if (Bral_Util_String::isChaineValide(stripslashes($this->_request->get("valeur")))) {
			$tabBralduns = null;
			$braldunTable = new Braldun();

			if ($idTypeDistinction != null) {

				$braldunRowset = $braldunTable->findBraldunsParPrenomAndIdTypeDistinction($this->_request->get("valeur").'%', $idTypeDistinction);
				$bralduns = array();
				foreach ($braldunRowset as $h) {
					$bralduns[] = $h["id_braldun"];
				}

				Zend_Loader::loadClass('Bral_Util_Distinction');
				$idTypeDistinctionDonjon = Bral_Util_Distinction::getIdDistinctionDonjonFromIdDistinctionBourlingueur($idTypeDistinction);
				Zend_Loader::loadClass("BraldunsDistinction");
				$braldunsDistinctionTable = new BraldunsDistinction();
				$distinctionsDonjon = $braldunsDistinctionTable->countDistinctionByIdBraldunList($bralduns, $idTypeDistinctionDonjon);

				Zend_Loader::loadClass("SouleEquipe");
				$souleEquipeTable = new SouleEquipe();
				$soule = $souleEquipeTable->countNonDebuteByIdBraldunList($bralduns);

			} else {
				$idBraldun = null;
				if ($avecBraldunEnCours === false) {
					$idBraldun = $this->view->user->id_braldun;
				}
				$braldunRowset = $braldunTable->findBraldunsParPrenom($this->_request->get("valeur").'%', $idBraldun, $avecPnj);
			}
			$this->view->champ = $this->_request->get("champ");

			foreach ($braldunRowset as $h) {
				$braldun = array(
						"id_braldun" => $h["id_braldun"],
						"nom" => $h["nom_braldun"],
						"prenom" => $h["prenom_braldun"],
				);
					
				if ($idTypeDistinction == null) {
					$tabBralduns[] = $braldun;
				} else if ($idTypeDistinction != null && $h["id_braldun"] != $this->view->user->id_braldun) {
					$okD = false;
					$okS = false;

					if ($distinctionsDonjon == null) {
						$okD = true;
					} else {
						foreach($distinctionsDonjon as $d) {
							if ($h["est_donjon_braldun"] == "non" && $d["id_fk_braldun_hdistinction"] == $h["id_braldun"] && $d["nombre"] < 1) {
								$okD = true;
								break;
							}
						}
					}

					if ($soule == null) {
						$okS = true;
					} else {
						foreach($soule as $s) {
							if ($h["est_soule_braldun"] == "non" && $s["id_fk_braldun_soule_equipe"] == $h["id_braldun"] && $s["nombre"] < 1) {
								$okS = true;
								break;
							}
						}
					}

					if ($okD == true && $okS == true) {
						$tabBralduns[] = $braldun;
					}
				}
			}
			$this->view->pattern = $this->_request->get("valeur");
			$this->view->tabBralduns = $tabBralduns;
		}
	}
}
