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
class BrasserieController extends Bral_Controller_Box
{

	function indexAction()
	{

		$this->view->affichageInterne = true;
		$this->view->nom_interne = "box_brasserie";
		$this->view->display = "block";
		$this->view->directFlux = null;
		$this->view->directFluxErreur = false;

		$idMatch = null;
		$accesDirect = false;
		if ($this->_request->get("match") != null) {
			$idMatch = intval($this->_request->get("match"));
			$accesDirect = true;
		}

		$matchTrouve = $this->prepareMatchs($idMatch);

		if ($accesDirect && $matchTrouve == false) {
			$this->view->directFlux = "Match inconnu";
			$this->view->directFluxErreur = true;
		} else if ($idMatch > 0 && $matchTrouve == true) {
			$this->_request->setParam('id_match', $idMatch);
			$this->_request->setParam('caction', 'do_brasserie_match');
			$box = Bral_Brasserie_Factory::getBox($this->_request, $this->view, true);
			$this->view->directFlux = $box->render();
			$this->view->idMatchDirect = $idMatch;
		}

		$this->render();
	}

	function loadAction()
	{
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
		Zend_Layout::resetMvcInstance();
		$this->xml_response = new Bral_Xml_Response();
		$xml_entry = new Bral_Xml_Entry();
		$xml_entry->set_type("display");
		$box = Bral_Brasserie_Factory::getBox($this->_request, $this->view, true);
		$xml_entry->set_box($box);
		$xml_entry->set_valeur($box->getNomInterne());
		$this->xml_response->add_entry($xml_entry);
		unset($xml_entry);
		$this->xml_response->render();
	}

	private function prepareMatchs($idMatch = null)
	{
		Zend_Loader::loadClass("SouleMatch");
		$souleMatchTable = new SouleMatch();
		$matchs = $souleMatchTable->fetchAllAvecTerrain();
		$retour = false;
		if ($idMatch != null) {
			foreach ($matchs as $m) {
				if ($m["id_soule_match"] == $idMatch) {
					$retour = true;
					break;
				}
			}
		}
		$this->view->matchs = $matchs;
		return $retour;
	}
}