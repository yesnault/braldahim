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
class Bral_Competences_Tanner extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("Echoppe");
		
		// On regarde si le hobbit est dans une de ses echopppes
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByCase($this->view->user->x_hobbit, $this->view->user->y_hobbit);
		
		$this->view->tannerEchoppeOk = false;
		$this->view->tannerPeauOk = false;
		
		if ($echoppes == null || count($echoppes) == 0) {
			$this->view->tannerEchoppeOk = false;
			return;
		}
		
		// Le joueur tente de transformer n+1 peaux ou n est son niveau de FOR
		$this->view->nbPeau = $this->view->user->force_base_hobbit + 1;
		
		$idEchoppe = -1;
			foreach($echoppes as $e) {
			if ($e["id_fk_hobbit_echoppe"] == $this->view->user->id_hobbit &&
				$e["nom_systeme_metier"] == "tanneur" &&
				$e["x_echoppe"] == $this->view->user->x_hobbit &&
				$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$this->view->tannerEchoppeOk = true;
				$idEchoppe = $e["id_echoppe"];

				$echoppeCourante = array(
				'id_echoppe' => $e["id_echoppe"],
				'x_echoppe' => $e["x_echoppe"],
				'y_echoppe' => $e["y_echoppe"],
				'id_metier' => $e["id_metier"],
				'quantite_peau_arriere_echoppe' => $e["quantite_peau_arriere_echoppe"],
				);
				if ($e["quantite_peau_arriere_echoppe"] >= $this->view->nbPeau) {
					$this->view->tannerPeauOk = true;
				}
				break;
			}
		}
		
		if ($this->view->tannerEchoppeOk == false) {
			return;
		}
		
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

		// Verification chasse
		if ($this->view->tannerEchoppeOk == false || $this->view->tannerPeauOk == false) {
			throw new Zend_Exception(get_class($this)." tanner interdit ");
		}
		
		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$this->calculTanner();
		}
		
		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majHobbit();
	}
	
	private function calculTanner() {
		//Transforme 2 unités de peau en 1D2 unités de cuir ou de fourrure.
		$quantiteCuir = 0;
		$quantiteFourrure = 0;
	
		// Le joueur tente de transformer n+1 peaux ou n est son niveau de FOR
		$nb = $this->view->nbPeau;
		
		// A partir de la quantité choisie on a un % de perte de peaux : p=0,5-0,002*(jet FOR + BM)
		$tirage = 0;
		for ($i=1; $i <= ($this->view->config->game->base_force + $this->view->user->force_base_hobbit) ; $i++) {
			$tirage = $tirage + Bral_Util_De::get_1d6();
		}
		$perte = 0.5-0.002 * ($tirage + $this->view->user->force_bm_hobbit + $this->view->user->force_bbdf_hobbit);
	
		// Et arrondi ((n+1)-(n+1)*p) /2 cuir en sortie
		$quantiteCuir = round(($nb - $nb * $perte) / 2);
		$quantiteFourrure = round(($nb - $nb * $perte) / 2);
		
		$echoppeTable = new Echoppe();
		$data = array(
				'id_echoppe' => $this->idEchoppe,
				'quantite_peau_arriere_echoppe' => -$nb,
				'quantite_cuir_arriere_echoppe' => $quantiteCuir,
				'quantite_fourrure_arriere_echoppe' => $quantiteFourrure,
		);
		$echoppeTable->insertOrUpdate($data);
		
		$this->view->quantiteCuir = $quantiteCuir;
		$this->view->quantiteFourrure = $quantiteFourrure;
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
