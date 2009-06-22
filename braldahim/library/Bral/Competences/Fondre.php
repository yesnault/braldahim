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
class Bral_Competences_Fondre extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");

		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);

		$this->view->fondreEchoppeOk = false;
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->fondreEchoppeOk = false;
			return;
		}
		$idEchoppe = -1;

		$this->view->nbMineraiMax = $this->view->user->vigueur_base_hobbit;
		if ($this->view->nbMineraiMax < 1) {
			$this->view->nbMineraiMax = 1;
		}

		foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit &&
			$e["nom_systeme_metier"] == "forgeron" &&
			$e["x_echoppe"] == $this->view->user->x_hobbit &&
			$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$this->view->fondreEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];
				break;
			}
		}

		if ($this->view->fondreEchoppeOk == false) {
			return;
		}

		Zend_Loader::loadClass("EchoppeMinerai");
		$tabMinerais = null;
		$echoppeMineraiTable = new EchoppeMinerai();
		$minerais = $echoppeMineraiTable->findByIdEchoppe($idEchoppe);

		$this->view->nbArriereMinerai = 0;
		$this->view->fondreMineraiOk = false;

		if ($minerais != null) {
			foreach ($minerais as $m) {
				if ($m["quantite_brut_arriere_echoppe_minerai"] >= 1) {
					$tabMinerais[] = array(
					"id_type" => $m["id_fk_type_echoppe_minerai"],
					"nom_type" => $m["nom_type_minerai"],
					"quantite_arriere" => $m["quantite_brut_arriere_echoppe_minerai"],
					"quantite_lingots" => $m["quantite_lingots_echoppe_minerai"],
					);
					$this->view->fondreMineraiOk = true;
					$this->view->nbArriereMinerai = $this->view->nbArriereMinerai + $m["quantite_brut_arriere_echoppe_minerai"];
				}
			}
		}
		if ($this->view->nbMineraiMax > $this->view->nbArriereMinerai) {
			$this->view->nbMineraiMax = $this->view->nbArriereMinerai;
		}

		if ($this->view->nbMineraiMax < 1) {
			$this->view->fondreMineraiOk = false;
		}

		$this->view->minerais = $tabMinerais;
		$this->idEchoppe = $idEchoppe;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_hobbit);
		}

		if ($this->view->fondreEchoppeOk == false || $this->view->fondreMineraiOk == false) {
			throw new Zend_Exception(get_class($this)." Fondre interdit ");
		}

		$idTypeMinerai = $this->request->get("valeur_1");
		if ((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."") {
			throw new Zend_Exception(get_class($this)." Minerai inconnu ");
		}

		if ((int)$this->request->get("valeur_2")."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." Nombre invalide");
		} else {
			$nombre = (int)$this->request->get("valeur_2");
		}

		if ($nombre < 0 || $nombre > $this->view->nbMineraiMax) {
			throw new Zend_Exception(get_class($this)." Nombre invalide b");
		}

		$mineraiOk = false;;
		foreach($this->view->minerais as $t) {
			if ($t["id_type"] == $idTypeMinerai) {
				$mineraiOk = true;
				$this->view->mineraiNomType = $t["nom_type"];
				break;
			}
		}
		if ($mineraiOk == false) {
			throw new Zend_Exception(get_class($this)." Minerai invalide");
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculFondre($idTypeMinerai, $nombre);
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}

	private function calculFondre($idTypeMinerai, $nb) {

		$this->view->nbLingots = 0;
		
		for($j = 1; $j <= $nb; $j++) {
			$tirage = 0;
			for ($i=1; $i <= ($this->view->config->game->base_vigueur + $this->view->user->vigueur_base_hobbit) ; $i++) {
				$tirage = $tirage + Bral_Util_De::get_1d6();
			}
			$tirage = $tirage + $this->view->user->vigueur_bm_hobbit + $this->view->user->vigueur_bbdf_hobbit;

			$tirage2 = 0;
			for ($i=1; $i <= ($this->view->config->game->base_vigueur + $this->view->user->vigueur_base_hobbit) ; $i++) {
				$tirage2 = $tirage2 + Bral_Util_De::get_1d6();
			}

			if ($tirage > $tirage2) {
				$this->view->nbLingots = $this->view->nbLingots + 1;
			}
		}

		$echoppeMineraiTable = new EchoppeMinerai();
		$data = array(
			'id_fk_type_echoppe_minerai' => $idTypeMinerai,
			'id_fk_echoppe_echoppe_minerai' => $this->idEchoppe,
			'quantite_lingots_echoppe_minerai' => $this->view->nbLingots,
			'quantite_brut_arriere_echoppe_minerai' => -$nb,
		);
		$echoppeMineraiTable->insertOrUpdate($data);
		
		$this->view->nbMineraiFondus = $nb;
	}

	public function getIdEchoppeCourante() {
		if (isset($this->idEchoppe)) {
			return $this->idEchoppe;
		} else {
			return false;
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_metiers", "box_echoppes"));
	}
}
