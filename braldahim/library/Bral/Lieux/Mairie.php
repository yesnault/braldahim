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
class Bral_Lieux_Mairie extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;
	private $_tabDestinations = null;

	function prepareCommun() {
		Zend_Loader::loadClass("Communaute");
		Zend_Loader::loadClass("RangCommunaute");
		Zend_Loader::loadClass("TypeRangCommunaute");

		$this->_coutCastars = $this->calculCoutCastars();
		$this->_utilisationPossible = (($this->view->user->castars_braldun -  $this->_coutCastars) >= 0);

		$this->view->braldunAvecCommunaute = false;
		$this->view->gestionnaireCommunaute = false;
		$this->idCommunauteCourante = -1;

		if ($this->view->user->id_fk_communaute_braldun != null) {
			$this->idCommunauteCourante = $this->view->user->id_fk_communaute_braldun;
			$this->view->braldunAvecCommunaute = true;
		}

		$communauteTable = new Communaute();
		$communautes = $communauteTable->fetchAll();
		$communautes = $communautes->toArray();

		$tabCommunaute = null;
		foreach($communautes as $c) {
			$tabCommunaute[$c["id_communaute"]] = array(
							'id_communaute' => $c["id_communaute"], 
							'nom_communaute' => $c["nom_communaute"],
						    'id_fk_braldun_gestionnaire_communaute' => $c["id_fk_braldun_gestionnaire_communaute"]
			);
			if ($c["id_fk_braldun_gestionnaire_communaute"] == $this->view->user->id_braldun) {
				$this->view->gestionnaireCommunaute = true;
			}
		}
		$this->view->communautes = $tabCommunaute;
	}

	function prepareFormulaire() {
		$this->view->utilisationPossible = $this->_utilisationPossible;
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat() {
		$this->view->creerCommunaute = false;
		$this->view->entrerCommunaute = false;
		$this->view->sortirCommunaute = false;
		$this->view->supprimerCommunaute = false;
		$this->view->communaute = null;

		$communaute = null;

		if ($this->_utilisationPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : castars:".$this->view->user->castars_braldun." cout:".$this->_coutCastars);
		}
		if ($this->view->utilisationPaPossible == false) {
			throw new Zend_Exception(get_class($this)." Utilisation impossible : pa:".$this->view->user->pa_braldun." cout:".$this->$this->view->paUtilisationLieu);
		}

		$idCommunaute = null;
		if (((int)$this->request->get("valeur_1").""!=$this->request->getPost("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Val 1 invalide : ".$this->request->getPost("valeur_1"));
		} else {
			$idCommunaute = (int)$this->request->getPost("valeur_1");
			if ($idCommunaute != -1) {
				$this->view->entrerCommunaute = true;
			}
		}

		$nomCommunaute = null;
		if (((int)$this->request->getPost("valeur_2").""!=$this->request->getPost("valeur_2")."")) {
			throw new Zend_Exception(get_class($this)." Val 2 invalide : ".$this->request->getPost("valeur_2"));
		} else {
			if ((int)$this->request->getPost("valeur_2") != -1) {
				Zend_Loader::loadClass('Zend_Filter');
				Zend_Loader::loadClass('Zend_Filter_StripTags');
				Zend_Loader::loadClass('Zend_Filter_StringTrim');
				$filter = new Zend_Filter();
				$nom = $this->request->getPost('valeur_3');
				$filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
				$nomCommunaute = stripslashes($filter->filter($nom));

				if (mb_strlen($nomCommunaute) > 0) {
					//$nomCommunaute =$nomCommunaute
					$this->view->creerCommunaute = true;
				} else {
					throw new Zend_Exception(get_class($this)." Nom invalide:".$nomCommunaute);
				}

			}
		}

		if (((int)$this->request->getPost("valeur_4").""!=$this->request->getPost("valeur_4")."")) {
			throw new Zend_Exception(get_class($this)." Val 4 invalide : ".$this->request->getPost("valeur_4"));
		} else {
			if ((int)$this->request->getPost("valeur_4") != -1) {
				$idCommunaute = $this->idCommunauteCourante;
				$this->view->sortirCommunaute = true;
			}
		}

		if ($this->view->entrerCommunaute === true || $this->view->sortirCommunaute === true) {
			foreach ($this->view->communautes as $c) {
				if ($c["id_communaute"] == $idCommunaute) {
					$communaute = $c;
					break;
				}
			}

			if ($communaute == null) {
				throw new Zend_Exception(get_class($this)." Communaute invalide (".$idCommunaute.")");
			}
		}

		if ($this->view->entrerCommunaute === true) {
			$communaute = $this->entrerCommunaute($idCommunaute);
		} else if ($this->view->creerCommunaute === true) {
			$communaute = $this->creerCommunaute($nomCommunaute);
		} else if ($this->view->sortirCommunaute === true) {
			$communaute = $this->sortirCommunaute($idCommunaute);
		} else {
			throw new Zend_Exception(get_class($this)." Action invalide");
		}

		$this->view->communaute = $communaute;

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->_coutCastars;
		$this->majBraldun();
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_laban", "box_competences_metiers", "box_communaute"));
	}

	private function calculCoutCastars() {
		return 0;
	}

	private function creerCommunaute($nomCommunaute) {
		$communauteTable = new Communaute();
		$data = array('nom_communaute' => $nomCommunaute,
			'date_creation_communaute' => date("Y-m-d H:i:s"),
			'id_fk_braldun_gestionnaire_communaute' => $this->view->user->id_braldun,
			'description_communaute' => '',
		);
		$communaute = $data;
		$communaute["id_communaute"] = $communauteTable->insert($data);

		$idRangCreateur = $this->creerRangsDefaut($communaute["id_communaute"]);

		$braldunTable = new Braldun();
		$this->view->user->id_fk_communaute_braldun = $communaute["id_communaute"];
		$this->view->user->date_entree_communaute_braldun = date("Y-m-d H:i:s");
		$this->view->user->id_fk_rang_communaute_braldun = $idRangCreateur;
		$data = array('id_fk_communaute_braldun' => $this->view->user->id_fk_communaute_braldun,
			'date_entree_communaute_braldun' => $this->view->user->date_entree_communaute_braldun,
			'id_fk_rang_communaute_braldun' => $this->view->user->id_fk_rang_communaute_braldun,
		);
		$where = "id_braldun=".$this->view->user->id_braldun;
		$braldunTable->update($data, $where);

		return $communaute;
	}

	private function entrerCommunaute($idCommunaute) {
		$communaute = $this->view->communautes[$idCommunaute];

		$braldunTable = new Braldun();
		$this->view->user->id_fk_communaute_braldun = $communaute["id_communaute"];
		$this->view->user->date_entree_communaute_braldun = date("Y-m-d H:i:s");

		$rangCommunauteTable = new RangCommunaute();
		$rowSet = $rangCommunauteTable->findRangNouveau($communaute["id_communaute"]);

		$this->view->user->id_fk_rang_communaute_braldun = $rowSet["id_rang_communaute"];

		$data = array('id_fk_communaute_braldun' => $this->view->user->id_fk_communaute_braldun,
			'date_entree_communaute_braldun' => $this->view->user->date_entree_communaute_braldun,
			'id_fk_rang_communaute_braldun' => $this->view->user->id_fk_rang_communaute_braldun,
		);
		$where = "id_braldun=".$this->view->user->id_braldun;
		$braldunTable->update($data, $where);

		$message = "[Ceci est un message automatique de communauté]".PHP_EOL;
		$message .= $this->view->user->prenom_braldun. " ". $this->view->user->nom_braldun;
		$e = "";
		if ($this->view->user->sexe_braldun == "feminin") {
			$e = "e";
		}
		$message .= " (".$this->view->user->id_braldun.") est entré".$e." dans votre communauté.".PHP_EOL;

		Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $communaute["id_fk_braldun_gestionnaire_communaute"], $message, $this->view);

		return $communaute;
	}

	private function sortirCommunaute($idCommunaute) {
		$communaute = $this->view->communautes[$idCommunaute];

		$braldunTable = new Braldun();
		$this->view->user->id_fk_communaute_braldun = null;
		$this->view->user->date_entree_communaute_braldun = null;
		$this->view->user->id_fk_rang_communaute_braldun = null;

		$data = array('id_fk_communaute_braldun' => null,
			'date_entree_communaute_braldun' => null,
			'id_fk_rang_communaute_braldun' => null,
		);
		$where = "id_braldun=".$this->view->user->id_braldun;
		$braldunTable->update($data, $where);

		if ($this->view->gestionnaireCommunaute === true) {
			$this->supprimerCommunaute($idCommunaute);
		} else {

			$message = "[Ceci est un message automatique de communauté]".PHP_EOL;
			$message .= $this->view->user->prenom_braldun. " ". $this->view->user->nom_braldun;
			$e = "";
			if ($this->view->user->sexe_braldun == "feminin") {
				$e = "e";
			}
			$message .= " (".$this->view->user->id_braldun.") est sorti".$e." de votre communauté.".PHP_EOL;

			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $communaute["id_fk_braldun_gestionnaire_communaute"], $message, $this->view);
		}

		return $communaute;
	}

	private function supprimerCommunaute($idCommunaute) {
		$communauteTable = new Communaute();
		$where = "id_communaute = ".$idCommunaute;
		$communauteTable->delete($where);
		$this->view->supprimerCommunaute = true;
	}

	private function creerRangsDefaut($idCommunaute) {
		$rangCommunauteTable = new RangCommunaute();

		$typeRangTable = new TypeRangCommunaute();
		$typeRangRowset = $typeRangTable->fetchAll();
		$typeRangRowset = $typeRangRowset->toArray();

		$ordre = 0;
		foreach ($typeRangRowset as $t) {
			$ordre++;
			$data = array('ordre_rang_communaute' => $ordre,
				'id_fk_communaute_rang_communaute' => $idCommunaute,
				'nom_rang_communaute' => $t["nom_type_rang_communaute"],
			);
			$id = $rangCommunauteTable->insert($data);
			if ($ordre == 1) {
				$idRangCreateur = $id;
			}
		}
		return $idRangCreateur;
	}
}