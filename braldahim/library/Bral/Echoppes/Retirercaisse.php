<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id:$
 * $Author:$
 * $LastChangedDate:$
 * $LastChangedRevision:$
 * $LastChangedBy:$
 */
class Bral_Echoppes_Retirercaisse extends Bral_Echoppes_Echoppe {

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
		
		$echoppeTable = new Echoppe();
		$echoppes = $echoppeTable->findByIdHobbit($this->view->user->id_hobbit);

		$tabEchoppe = null;
		foreach ($echoppes as $e) {
			if ($e["id_echoppe"] == $id_echoppe && 
				$e["x_echoppe"] == $this->view->user->x_hobbit && 
				$e["y_echoppe"] == $this->view->user->y_hobbit) {
				$tabEchoppe = array(
				'id_echoppe' => $e["id_echoppe"],
				'quantite_rondin_caisse_echoppe' => $e["quantite_rondin_caisse_echoppe"],
				'quantite_peau_caisse_echoppe' => $e["quantite_peau_caisse_echoppe"],
				'quantite_castar_caisse_echoppe' => $e["quantite_castar_caisse_echoppe"],
				);
				break;
			}
		}
		if ($tabEchoppe == null) {
			throw new Zend_Exception(get_class($this)." Echoppe invalide idh:".$this->view->user->id_hobbit." ide:".$id_echoppe);
		}
		
		$this->view->echoppe = $tabEchoppe;
		
		$this->view->retirerCaisseOk = false;
		if ($this->view->echoppe["quantite_rondin_caisse_echoppe"] > 0 ||
			$this->view->echoppe["quantite_peau_caisse_echoppe"] > 0 ||
			$this->view->echoppe["quantite_castar_caisse_echoppe"] > 0) {
			$this->view->retirerCaisseOk = true;
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
		if ($this->view->retirerCaisseOk == false) {
			throw new Zend_Exception(get_class($this)." Retirer interdit");
		}
		
		$nb_castars = $this->request->get("valeur_2");
		$nb_rondins = $this->request->get("valeur_3");
		$nb_peau = $this->request->get("valeur_4");
		
		if ((int) $nb_castars."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." NB Castars invalide=".$nb_castars);
		} else {
			$nb_castars = (int)$nb_castars;
		}
		if ($nb_castars > $this->view->echoppe["quantite_castar_caisse_echoppe"]) {
			throw new Zend_Exception(get_class($this)." NB Castars interdit=".$nb_castars);
		}
		if ((int) $nb_rondins."" != $this->request->get("valeur_3")."") {
			throw new Zend_Exception(get_class($this)." NB Rondins invalide=".$nb_rondins);
		} else {
			$nb_rondins = (int)$nb_rondins;
		}
		if ($nb_rondins > $this->view->echoppe["quantite_rondin_caisse_echoppe"]) {
			throw new Zend_Exception(get_class($this)." NB Rondin interdit=".$nb_rondins);
		}
		if ((int) $nb_peau."" != $this->request->get("valeur_4")."") {
			throw new Zend_Exception(get_class($this)." NB Peau invalide=".$nb_peau);
		} else {
			$nb_peau = (int)$nb_peau;
		}
		if ($nb_peau > $this->view->echoppe["quantite_peau_caisse_echoppe"]) {
			throw new Zend_Exception(get_class($this)." NB Peau interdit=".$nb_peau);
		}
		
		$this->view->elementsRetires = "";
		$this->calculEchoppe($nb_rondins, $nb_peau, $nb_castars);
		$this->calculPartiesPlantes();
		$this->calculMinerais();
		if ($this->view->elementsRetires != "") {
			$this->view->elementsRetires = mb_substr($this->view->elementsRetires, 0, -2);
		}
		
		$this->calculPoids();
		$this->majHobbit();
	}
	
	private function calculEchoppe($nb_rondins, $nb_peau, $nb_castars) {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Laban");
		
		$echoppeTable = new Echoppe();
		
		if ($nb_peau > 0) {
			// on place dans le laban
			$labanTable = new Laban();
			$data = array(
				'id_fk_hobbit_laban' => $this->view->user->id_hobbit,
				'quantite_peau_laban' => $nb_peau,
			);
			$labanTable->insertOrUpdate($data);
			$this->view->elementsRetires .= $nb_peau. " peau";
			if ($nb_peau > 1) $this->view->elementsRetires .= "x";
			$this->view->elementsRetires .= ", ";
		}
		
		if ($nb_rondins > 0) {
			// on place dans la charette
			if ($this->view->charretteOk === true) {
				$charretteTable = new Charrette();
				$data = array(
					'quantite_rondin_charrette' => $nb_rondins,
					'id_fk_hobbit_charrette' => $this->view->user->id_hobbit,
				);
				$charretteTable->updateCharrette($data);
				
				$this->view->elementsRetires .= $nb_rondins. " rondin";
				if ($nb_rondins > 1) $this->view->elementsRetires .= "s";
				$this->view->elementsRetires .= ", ";
				
				$nb_rondins = $this->view->echoppe["quantite_rondin_caisse_echoppe"] - $nb_rondins;
			} else {
				$nb_rondins = $this->view->echoppe["quantite_rondin_caisse_echoppe"];
			}
		}
		
		if ($nb_peau < 0) $nb_peau = 0;
		if ($nb_castars < 0) $nb_castars = 0;
		
		$data = array(
				'quantite_rondin_caisse_echoppe' => $nb_rondins,
				'quantite_peau_caisse_echoppe' => $this->view->echoppe["quantite_peau_caisse_echoppe"] - $nb_peau,
				'quantite_castar_caisse_echoppe' => $this->view->echoppe["quantite_castar_caisse_echoppe"] - $nb_castars,
		);
		$where = "id_echoppe=".$this->view->echoppe["id_echoppe"];
		$echoppeTable->update($data, $where);
		
		if ($nb_castars > 0) {
			$hobbitTable = new Hobbit();
			$this->view->user->castars_hobbit = $this->view->user->castars_hobbit + $nb_castars;
			$data = array(
			'castars_hobbit'  => $this->view->user->castars_hobbit,
			);
			$where = "id_hobbit=".$this->view->user->id_hobbit;
			$hobbitTable->update($data, $where);
		
			$this->view->elementsRetires .= $nb_castars . " castar";
			if ($nb_castars > 0) $this->view->elementsRetires .= "s";
			$this->view->elementsRetires .= ", ";
			
		}
		
	}
	
	private function calculPartiesPlantes() {
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass('LabanPartieplante');
		
		$echoppePartiePlanteTable = new EchoppePartieplante();
		$labanPartiePlanteTable = new LabanPartieplante();
		
		for($i=5; $i<=$this->view->valeur_fin_partieplantes; $i++) {
			$nb = $this->request->get($indice);
			if ((int) $nb."" != $this->request->get("valeur_".$i)."") {
				throw new Zend_Exception(get_class($this)." NB Partie Plante invalide=".$nb);
			} else {
				$nb = (int)$nb;
			}
			if ($nb > $this->view->partieplantes[$indice]["quantite_caisse"]) {
				throw new Zend_Exception(get_class($this)." NB Partie Plante interdit=".$nb);
			}
			if ($nb > 0) {
				$data = array('quantite_caisse_echoppe_partieplante' => -$nb,
							  'id_fk_type_echoppe_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_echoppe_partieplante"],
							  'id_fk_type_plante_echoppe_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_echoppe_partieplante"],
							  'id_fk_echoppe_echoppe_partieplante' => $this->view->partieplantes[$indice]["id_fk_echoppe_echoppe_partieplante"]);
				$echoppePartiePlanteTable->insertOrUpdate($data);
				
				$data = array(
						'id_fk_type_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_echoppe_partieplante"],
						'id_fk_type_plante_laban_partieplante' => $this->view->partieplantes[$indice]["id_fk_type_plante_echoppe_partieplante"],
						'id_fk_hobbit_laban_partieplante' => $this->view->user->id_hobbit,
						'quantite_laban_partieplante' => $nb,
				);
				$labanPartiePlanteTable->insertOrUpdate($data);
			}
			
			$this->view->elementsRetires .= $this->view->partieplantes[$indice]["nom_plante"]. " : ".$nb. " ".$this->view->partieplantes[$indice]["nom_type"];
			if ($nb > 0) $this->view->elementsRetires .= "s";
			$this->view->elementsRetires .= ", ";
		}
	}

	private function calculMinerais() {
		Zend_Loader::loadClass("EchoppeMinerai");
		Zend_Loader::loadClass('LabanMinerai');
		
		$echoppeMineraiTable = new EchoppeMinerai();
		$labanMineraiTable = new LabanMinerai();
		
		for($i=$this->view->valeur_fin_partieplantes + 1; $i<=$this->view->nb_valeurs; $i++) {
			$indice = "valeur_".$i;
			$nb = $this->request->get($indice);
			if ((int) $nb."" != $this->request->get($indice)."") {
				throw new Zend_Exception(get_class($this)." NB Minerai invalide=".$nb);
			} else {
				$nb = (int)$nb;
			}
			if ($nb > $this->view->minerais[$indice]["quantite_caisse"]) {
				throw new Zend_Exception(get_class($this)." NB Minerais interdit=".$nb);
			}
			if ($nb > 0) {
				$data = array('quantite_caisse_echoppe_minerai' => -$nb,
							  'id_fk_type_echoppe_minerai' => $this->view->minerais[$indice]["id_fk_type_echoppe_minerai"],
							  'id_fk_echoppe_echoppe_minerai' => $this->view->minerais[$indice]["id_fk_echoppe_echoppe_minerai"]);
				$echoppeMineraiTable->insertOrUpdate($data);
				
				$data = array(
				'id_fk_type_laban_minerai' => $this->view->minerais[$indice]["id_fk_type_echoppe_minerai"],
				'id_fk_hobbit_laban_minerai' => $this->view->user->id_hobbit,
				'quantite_brut_laban_minerai' => $nb,
				);
		
				$labanMineraiTable->insertOrUpdate($data);
			}
			
			$this->view->elementsRetires .= $this->view->minerais[$indice]["type"]. " : ".$nb;
			$this->view->elementsRetires .= ", ";
		}
	}
	
	private function prepareCommunRessources($idEchoppe) {
		Zend_Loader::loadClass("EchoppePartieplante");
		Zend_Loader::loadClass("EchoppeMinerai");

		$tabPartiePlantes = null;
		$echoppePartiePlanteTable = new EchoppePartieplante();
		$partiePlantes = $echoppePartiePlanteTable->findByIdEchoppe($idEchoppe);
		
		$this->view->nb_valeurs = 4;
		$this->view->nb_caissePartiePlantes = 0;
		$this->view->nb_arrierePartiePlantes = 0;
		$this->view->nb_prepareePartiePlantes = 0;
		
		if ($partiePlantes != null) {
			foreach ($partiePlantes as $p) {
				if ($p["quantite_caisse_echoppe_partieplante"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
					$tabPartiePlantes["valeur_".$this->view->nb_valeurs] = array(
					"nom_type" => $p["nom_type_partieplante"],
					"nom_plante" => $p["nom_type_plante"],
					"id_fk_type_echoppe_partieplante" => $p["id_fk_type_echoppe_partieplante"],
					"id_fk_type_plante_echoppe_partieplante" => $p["id_fk_type_plante_echoppe_partieplante"],
					"id_fk_echoppe_echoppe_partieplante" => $p["id_fk_echoppe_echoppe_partieplante"],
					"quantite_caisse" => $p["quantite_caisse_echoppe_partieplante"],
					"indice_valeur" => $this->view->nb_valeurs,
					);
					if ($p["quantite_caisse_echoppe_partieplante"] > 0) {
						$this->view->retirerCaisseOk = true;
					}
					$this->view->nb_caissePartiePlantes = $this->view->nb_caissePartiePlantes + $p["quantite_caisse_echoppe_partieplante"];
				}
			}
		}
		
		$this->view->valeur_fin_partieplantes = $this->view->nb_valeurs;
		
		$tabMinerais = null;
		$echoppeMineraiTable = new EchoppeMinerai();
		$minerais = $echoppeMineraiTable->findByIdEchoppe($idEchoppe);

		$this->view->nb_caisseMinerai = 0;
		$this->view->nb_arriereMinerai = 0;
		$this->view->nb_lingotsMinerai = 0;

		if ($minerais != null) {
			foreach ($minerais as $m) {
				if ($m["quantite_caisse_echoppe_minerai"] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
					$tabMinerais["valeur_".$this->view->nb_valeurs] = array(
					"type" => $m["nom_type_minerai"],
					"id_fk_echoppe_echoppe_minerai" => $m["id_fk_echoppe_echoppe_minerai"],
					"id_fk_type_echoppe_minerai" => $m["id_fk_echoppe_echoppe_minerai"],
					"quantite_caisse" => $m["quantite_caisse_echoppe_minerai"],
					"indice_valeur" => $this->view->nb_valeurs,
					);
					if ($m["quantite_caisse_echoppe_minerai"] > 0) {
						$this->view->retirerCaisseOk = true;
					}
					$this->view->nb_caisseMinerai = $this->view->nb_caisseMinerai + $m["quantite_caisse_echoppe_minerai"];
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
		return array("box_laban", "box_charrette", "box_profil");
	}
}