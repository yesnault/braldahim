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
class Bral_Echoppes_Retirerressources extends Bral_Echoppes_Echoppe {

	function getNomInterne() {
		return "box_action";
	}

	function prepareCommun() {
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass("Echoppe");

		$id_echoppe = $this->request->get("valeur_1");

		if ($id_echoppe == "" || $id_echoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide=".$id_echoppe);
		}

		$this->poidsRestant = $this->view->user->poids_transportable_hobbit - $this->view->user->poids_transporte_hobbit;

		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdHobbit($this->view->user->id_hobbit);

		$tabEchoppe = null;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe &&
			$e["x_echoppe"] == $this->view->user->x_hobbit &&
			$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$tabEchoppe = array(
					'id_echoppe' => $e["id_echoppe"],
					'quantite_peau_arriere_echoppe' => $e["quantite_peau_arriere_echoppe"],
					'quantite_rondin_arriere_echoppe' => $e["quantite_rondin_arriere_echoppe"],
					'quantite_cuir_arriere_echoppe' => $e["quantite_cuir_arriere_echoppe"],
					'quantite_fourrure_arriere_echoppe' => $e["quantite_fourrure_arriere_echoppe"],
					'quantite_planche_arriere_echoppe' => $e["quantite_planche_arriere_echoppe"]
				);
				break;
			}
		}
		if ($tabEchoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide idh:".$this->view->user->id_hobbit." ide:".$id_echoppe);
		}

		$this->view->echoppe = $tabEchoppe;

		$this->view->retirerRessourcesOk = false;
		if ($this->view->echoppe["quantite_peau_arriere_echoppe"] > 0 ||
		$this->view->echoppe["quantite_rondin_arriere_echoppe"] > 0 ||
		$this->view->echoppe["quantite_cuir_arriere_echoppe"] > 0 ||
		$this->view->echoppe["quantite_fourrure_arriere_echoppe"] > 0 ||
		$this->view->echoppe["quantite_planche_arriere_echoppe"] > 0) {
			$this->view->retirerRessourcesOk = true;
		}
		$this->prepareCommunRessources($tabEchoppe["id_echoppe"]);

		$this->view->charretteOk = false;
		$charretteTable = new Charrette();
		$nombre = $charretteTable->countByIdHobbit($this->view->user->id_hobbit);
		if ($nombre > 0) {
			$this->view->charretteOk = true;
		}

		$this->view->idEchoppe = $id_echoppe;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {
		if ($this->view->retirerRessourcesOk == false) {
			throw new Zend_Exception(get_class($this)." Retirer interdit");
		}

		$nb_rondins = $this->request->get("valeur_2");
		$nb_peau = $this->request->get("valeur_3");
		$nb_cuir = $this->request->get("valeur_4");
		$nb_fourrure = $this->request->get("valeur_5");
		$nb_planche = $this->request->get("valeur_6");

		if ((int) $nb_rondins."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." NB Rondins invalide=".$nb_rondins);
		} else {
			$nb_rondins = (int)$nb_rondins;
		}
		if ($nb_rondins > $this->view->echoppe["quantite_rondin_arriere_echoppe"]) {
			throw new Zend_Exception(get_class($this)." NB Rondin interdit=".$nb_rondins);
		}
		if ((int) $nb_peau."" != $this->request->get("valeur_3")."") {
			throw new Zend_Exception(get_class($this)." NB Peau invalide=".$nb_peau);
		} else {
			$nb_peau = (int)$nb_peau;
		}
		if ($nb_peau > $this->view->echoppe["quantite_peau_arriere_echoppe"]) {
			throw new Zend_Exception(get_class($this)." NB Peau interdit=".$nb_peau);
		}
		if ((int) $nb_cuir."" != $this->request->get("valeur_4")."") {
			throw new Zend_Exception(get_class($this)." NB Cuir invalide=".$nb_cuir);
		} else {
			$nb_cuir = (int)$nb_cuir;
		}
		if ($nb_cuir > $this->view->echoppe["quantite_cuir_arriere_echoppe"]) {
			throw new Zend_Exception(get_class($this)." NB Cuir interdit=".$nb_cuir);
		}
		if ((int) $nb_fourrure."" != $this->request->get("valeur_5")."") {
			throw new Zend_Exception(get_class($this)." NB Fourrure invalide=".$nb_fourrure);
		} else {
			$nb_fourrure = (int)$nb_fourrure;
		}
		if ($nb_fourrure > $this->view->echoppe["quantite_fourrure_arriere_echoppe"]) {
			throw new Zend_Exception(get_class($this)." NB Fourrure interdit=".$nb_fourrure);
		}
		if ((int) $nb_planche."" != $this->request->get("valeur_6")."") {
			throw new Zend_Exception(get_class($this)." NB Planche invalide=".$nb_planche);
		} else {
			$nb_planche = (int)$nb_planche;
		}
		if ($nb_planche > $this->view->echoppe["quantite_planche_arriere_echoppe"]) {
			throw new Zend_Exception(get_class($this)." NB Planche interdit=".$nb_planche);
		}

		$this->view->elementsRetires = "";
		
		if ($nb_rondins < 0) $nb_rondins = 0;
		if ($nb_peau < 0) $nb_peau = 0;
		if ($nb_cuir < 0) $nb_cuir = 0;
		if ($nb_fourrure < 0) $nb_fourrure = 0;
		if ($nb_planche < 0) $nb_planche = 0;

		$this->calculEchoppe($nb_rondins, $nb_peau, $nb_cuir, $nb_fourrure, $nb_planche);
		$this->calculPartiesPlantes();
		$this->calculMinerais();
		if ($this->view->elementsRetires != "") {
			$this->view->elementsRetires = mb_substr($this->view->elementsRetires, 0, -2);
		}
	}

	private function calculEchoppe($nb_rondins, $nb_peau, $nb_cuir, $nb_fourrure, $nb_planche) {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Laban");

		$echoppeTable = new Echoppe();

		$nb_peau = $this->calculNbPoidsPossible($nb_peau, Bral_Util_Poids::POIDS_PEAU);

		if ($nb_peau > 0) {
			$this->view->elementsRetires .= $nb_peau. " peau";
			if ($nb_peau > 1) $this->view->elementsRetires .= "x";
			$this->view->elementsRetires .= ", ";
		}

		$nb_cuir = $this->calculNbPoidsPossible($nb_cuir, Bral_Util_Poids::POIDS_CUIR);

		if ($nb_cuir > 0) {
			$this->view->elementsRetires .= $nb_cuir. " cuir";
			if ($nb_cuir > 1) $this->view->elementsRetires .= "s";
			$this->view->elementsRetires .= ", ";
		}

		$nb_fourrure = $this->calculNbPoidsPossible($nb_fourrure, Bral_Util_Poids::POIDS_FOURRURE);

		if ($nb_fourrure > 0) {
			$this->view->elementsRetires .= $nb_fourrure. " fourrure";
			if ($nb_fourrure > 1) $this->view->elementsRetires .= "s";
			$this->view->elementsRetires .= ", ";
		}

		$nb_planche = $this->calculNbPoidsPossible($nb_planche, Bral_Util_Poids::POIDS_PLANCHE);

		if ($nb_planche > 0) {
			$this->view->elementsRetires .= $nb_planche. " planche";
			if ($nb_planche > 1) $this->view->elementsRetires .= "s";
			$this->view->elementsRetires .= ", ";
		}

		if ($nb_rondins > 0 && $this->view->charretteOk === true) {

			$tabPoidsCharrette = Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit);
			$nbPossibleDansCharretteMaximum = floor($tabPoidsCharrette["place_restante"] / Bral_Util_Poids::POIDS_RONDIN);

			if ($nb_rondins > $nbPossibleDansCharretteMaximum) {
				$nb_rondins = $nbPossibleDansCharretteMaximum;
			}

			if ($nb_rondins > 0) {
				// on place dans la charette
				$charretteTable = new Charrette();
				$data = array(
					'quantite_rondin_charrette' => $nb_rondins,
					'id_fk_hobbit_charrette' => $this->view->user->id_hobbit,
				);
				$charretteTable->updateCharrette($data);

				Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_hobbit, true);
				
				$this->view->elementsRetires .= $nb_rondins. " rondin";
				if ($nb_rondins > 1) $this->view->elementsRetires .= "s";
				$this->view->elementsRetires .= ", ";
			}
		}

		$data = array(
				'quantite_peau_arriere_echoppe' => $this->view->echoppe["quantite_rondin_arriere_echoppe"] - $nb_rondins,
				'quantite_cuir_arriere_echoppe' => $this->view->echoppe["quantite_cuir_arriere_echoppe"] - $nb_cuir,
				'quantite_fourrure_arriere_echoppe' => $this->view->echoppe["quantite_fourrure_arriere_echoppe"] - $nb_fourrure,
				'quantite_planche_arriere_echoppe' => $this->view->echoppe["quantite_planche_arriere_echoppe"] - $nb_planche,
				'quantite_peau_arriere_echoppe' => $this->view->echoppe["quantite_peau_arriere_echoppe"] - $nb_peau,
				'quantite_rondin_arriere_echoppe' => $this->view->echoppe["quantite_rondin_arriere_echoppe"] - $nb_rondins,
		);
		$where = "id_echoppe=".$this->view->echoppe["id_echoppe"];
		$echoppeTable->update($data, $where);

		// on ajoute dans le laban
		$labanTable = new Laban();
		$data = array(
			'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
			'quantite_peau_laban' => $nb_peau,
			'quantite_cuir_laban' => $nb_cuir,
			'quantite_fourrure_laban' => $nb_fourrure,
			'quantite_planche_laban' => $nb_planche,
		);
		$labanTable->insertOrUpdate($data);
	}

	private function calculNbPoidsPossible($quantite, $poidsType) {
		if ($quantite < 0) $quantite = 0;
		if ($this->poidsRestant < 0) $this->poidsRestant = 0;
		$quantitePossible = floor($this->poidsRestant / $poidsType);
		if ($quantite > $quantitePossible) $quantite = $quantitePossible;
		$this->poidsRestant = $this->poidsRestant - ($poidsType * $quantite);
		return $quantite;
	}

	private function calculPartiesPlantes() {
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass('LabanPartieplante');

		$echoppePartiePlanteTable = new EchoppePartieplante();
		$labanPartiePlanteTable = new LabanPartieplante();

		for($i=7; $i<=$this->view->valeur_fin_partieplantes; $i = $i + 2) {
			$indice = $i;
			$indiceBrutes = $i;
			$indicePreparees = $i + 1;
			$nbBrutes = $this->request->get("valeur_".$indiceBrutes);
			$nbPreparees = $this->request->get("valeur_".$indicePreparees);

			if ((int) $nbBrutes."" != $this->request->get("valeur_".$indiceBrutes)."") {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Brute invalide=".$nbBrutes);
			} else {
				$nbBrutes = (int)$nbBrutes;
			}
			if ($nbBrutes > $this->view->partieplantes[$indice]["quantite_arriere_echoppe_partieplante"]) {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Brute interdit=".$nbBrutes);
			}
			if ((int) $nbPreparees."" != $this->request->get("valeur_".$indicePreparees)."") {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Preparee invalide=".$nbPreparees);
			} else {
				$nbPreparees = (int)$nbPreparees;
			}
			if ($nbPreparees > $this->view->partieplantes[$indice]["quantite_preparee_echoppe_partieplante"]) {
				throw new Zend_Exception(get_class($this)." NB Partie Plante Preparee interdit=".$nbPreparees);
			}

			$nbBrutes = $this->calculNbPoidsPossible($nbBrutes, Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE);
			$nbPreparees = $this->calculNbPoidsPossible($nbPreparees, Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE);

			if ($nbBrutes > 0 || $nbPreparees > 0) {
				$data = array('quantite_arriere_echoppe_partieplante' => -$nbBrutes,
							  'quantite_preparee_echoppe_partieplante' => -$nbPreparees,
							  'id_fk_type_echoppe_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_echoppe_partieplante"],
							  'id_fk_type_plante_echoppe_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_echoppe_partieplante"],
							  'id_fk_echoppe_echoppe_partieplante' => $this->view->partieplantes[$indice]["id_fk_echoppe_echoppe_partieplante"]);
				$echoppePartiePlanteTable->insertOrUpdate($data);

				$data = array(
						'id_fk_type_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_echoppe_partieplante"],
						'id_fk_type_plante_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_echoppe_partieplante"],
						'id_fk_hobbit_laban_partieplante' => $this->view->user->id_hobbit,
						'quantite_laban_partieplante' => $nbBrutes,
						'quantite_preparee_laban_partieplante' => $nbPreparees,
				);
				$labanPartiePlanteTable->insertOrUpdate($data);

				$sbrute = "";
				$spreparee = "";
				if ($nbBrutes > 1) $sbrute = "s";
				if ($nbPreparees > 1) $spreparee = "s";
				$this->view->elementsRetires .= $this->view->partieplantes[$indice]["nom_plante"]. " : ";
				$this->view->elementsRetires .= $nbBrutes. " ".$this->view->partieplantes[$indice]["nom_type"]. " brute".$sbrute;
				$this->view->elementsRetires .=  " et ".$nbPreparees. " ".$this->view->partieplantes[$indice]["nom_type"]. " préparée".$spreparee;
				$this->view->elementsRetires .= ", ";
			}
		}
	}

	private function calculMinerais() {
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass('LabanMinerai');

		$echoppeMineraiTable = new EchoppeMinerai();
		$labanMineraiTable = new LabanMinerai();

		for($i=$this->view->valeur_fin_partieplantes + 1; $i<=$this->view->nb_valeurs; $i = $i + 2) {
			$indice = $i;
			$indiceBrut = $i;
			$indiceLingot = $i+1;
			$nbBrut = $this->request->get("valeur_".$indiceBrut);
			$nbLingot = $this->request->get("valeur_".$indiceLingot);

			if ((int) $nbBrut."" != $this->request->get("valeur_".$indiceBrut)."") {
				throw new Zend_Exception(get_class($this)." NB Minerai brut invalide=".$nbBrut. " indice=".$indiceBrut);
			} else {
				$nbBrut = (int)$nbBrut;
			}
			if ($nbBrut > $this->view->minerais[$indice]["quantite_brut_arriere_echoppe_minerai"]) {
				throw new Zend_Exception(get_class($this)." NB Minerai brut interdit=".$nbBrut);
			}

			if ((int) $nbLingot."" != $this->request->get("valeur_".$indiceLingot)."") {
				throw new Zend_Exception(get_class($this)." NB Minerai lingot invalide=".$nbLingot. " indice=".$indiceLingot);
			} else {
				$nbLingot = (int)$nbLingot;
			}
			if ($nbLingot > $this->view->minerais[$indice]["quantite_lingots_echoppe_minerai"]) {
				throw new Zend_Exception(get_class($this)." NB Minerai lingot interdit=".$nbLingot);
			}

			$nbBrut = $this->calculNbPoidsPossible($nbBrut, Bral_Util_Poids::POIDS_MINERAI);
			$nbLingot = $this->calculNbPoidsPossible($nbLingot, Bral_Util_Poids::POIDS_LINGOT);

			if ($nbBrut > 0 || $nbLingot > 0) {
				$data = array('quantite_brut_arriere_echoppe_minerai' => -$nbBrut,
							  'quantite_lingots_echoppe_minerai' => -$nbLingot,
							  'id_fk_type_echoppe_minerai' => $this->view->minerais[$indice]["id_fk_type_echoppe_minerai"],
							  'id_fk_echoppe_echoppe_minerai' => $this->view->minerais[$indice]["id_fk_echoppe_echoppe_minerai"]);
				$echoppeMineraiTable->insertOrUpdate($data);

				$data = array(
					'id_fk_type_laban_minerai' => $this->view->minerais[$indice]["id_fk_type_echoppe_minerai"],
					'id_fk_hobbit_laban_minerai' => $this->view->user->id_hobbit,
					'quantite_brut_laban_minerai' => $nbBrut,
					'quantite_lingots_laban_minerai' => $nbLingot,
				);

				$labanMineraiTable->insertOrUpdate($data);
				$sbrut = "";
				$slingot = "";
				if ($nbBrut > 1) $sbrut = "s";
				if ($nbLingot > 1) $slingot = "s";
				$this->view->elementsRetires .= $this->view->minerais[$indice]["type"]. " : ".$nbBrut. " minerai".$sbrut." brut".$sbrut." et ".$nbLingot." lingot".$slingot;
				$this->view->elementsRetires .= ", ";
			}
		}
	}

	private function prepareCommunRessources($idEchoppe) {
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass("EchoppeMinerai");

		$tabPartiePlantes = null;
		$echoppePartiePlanteTable = new EchoppePartieplante();
		$partiePlantes = $echoppePartiePlanteTable->findByIdEchoppe($idEchoppe);

		$this->view->nb_valeurs = 6;
		$this->view->nb_arrierePartiePlantes = 0;
		$this->view->nb_prepareesPartiePlantes = 0;

		if ($partiePlantes != null) {
			foreach ($partiePlantes as $p) {
				if ($p["quantite_arriere_echoppe_partieplante"] > 0 || $p["quantite_preparee_echoppe_partieplante"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brutes
					$tabPartiePlantes[$this->view->nb_valeurs] = array(
						"nom_type" => $p["nom_type_partieplante"],
						"nom_plante" => $p["nom_type_plante"],
						"id_fk_type_echoppe_partieplante" => $p["id_fk_type_echoppe_partieplante"],
						"id_fk_type_plante_echoppe_partieplante" => $p["id_fk_type_plante_echoppe_partieplante"],
						"id_fk_echoppe_echoppe_partieplante" => $p["id_fk_echoppe_echoppe_partieplante"],
						"quantite_arriere_echoppe_partieplante" => $p["quantite_arriere_echoppe_partieplante"],
						"quantite_preparee_echoppe_partieplante" => $p["quantite_preparee_echoppe_partieplante"],
						"indice_valeur" => $this->view->nb_valeurs,
					);
					$this->view->retirerRessourcesOk = true;
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // preparees
					$this->view->nb_arrierePartiePlantes = $this->view->nb_arrierePartiePlantes + $p["quantite_arriere_echoppe_partieplante"];
					$this->view->nb_prepareesPartiePlantes = $this->view->nb_prepareesPartiePlantes + $p["quantite_preparee_echoppe_partieplante"];
				}
			}
		}

		$this->view->valeur_fin_partieplantes = $this->view->nb_valeurs;

		$tabMinerais = null;
		$echoppeMineraiTable = new EchoppeMinerai();
		$minerais = $echoppeMineraiTable->findByIdEchoppe($idEchoppe);

		$this->view->nb_arriereMinerai = 0;
		$this->view->nb_arriereLingot = 0;

		if ($minerais != null) {
			foreach ($minerais as $m) {
				if ($m["quantite_brut_arriere_echoppe_minerai"] > 0 || $m["quantite_lingots_echoppe_minerai"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brut
					$tabMinerais[$this->view->nb_valeurs] = array(
						"type" => $m["nom_type_minerai"],
						"id_fk_echoppe_echoppe_minerai" => $m["id_fk_echoppe_echoppe_minerai"],
						"id_fk_type_echoppe_minerai" => $m["id_fk_type_echoppe_minerai"],
						"quantite_brut_arriere_echoppe_minerai" => $m["quantite_brut_arriere_echoppe_minerai"],
						"quantite_lingots_echoppe_minerai" => $m["quantite_lingots_echoppe_minerai"],
						"indice_valeur" => $this->view->nb_valeurs,
					);
					$this->view->retirerRessourcesOk = true;
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // lingot
					$this->view->nb_arriereMinerai = $this->view->nb_arriereMinerai + $m["quantite_brut_arriere_echoppe_minerai"];
					$this->view->nb_arriereLingot = $this->view->nb_arriereLingot + $m["quantite_lingots_echoppe_minerai"];
				}
			}
		}

		$this->view->partieplantes = $tabPartiePlantes;
		$this->view->minerais = $tabMinerais;
	}

	public function getIdEchoppeCourante() {
		if (isset($this->view->idEchoppe)) {
			return $this->view->idEchoppe;
		} else {
			return false;
		}
	}

	function getListBoxRefresh() {
		if ($this->view->charretteOk === true) {
			return array("box_laban", "box_charrette", "box_profil");
		} else {
			return array("box_laban", "box_profil");
		}
	}
}