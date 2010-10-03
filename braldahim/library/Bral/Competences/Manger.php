<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Manger extends Bral_Competences_Competence {

	function prepareCommun() {
		Zend_Loader::loadClass("LabanAliment");
		Zend_Loader::loadClass("Ville");
		Zend_Loader::loadClass("Bral_Util_Quete");
		Zend_Loader::loadClass("Bral_Util_Aliment");

		$labanAlimentTable = new LabanAliment();
		$aliments = $labanAlimentTable->findByIdBraldun($this->view->user->id_braldun);

		$tabAliments = null;
		$tabBoissons = null;
		foreach ($aliments as $p) {
			$tab = array(
					"id_aliment" => $p["id_aliment"],
					"id_fk_type_aliment" => $p["id_fk_type_aliment"],
					"id_fk_type_qualite_aliment" => $p["id_fk_type_qualite_aliment"],
					"nom" => $p["nom_type_aliment"],
					"recette" => Bral_Util_Aliment::getNomType($p["type_bbdf_type_aliment"]),
					"qualite" => $p["nom_type_qualite"],
					"bbdf" => $p["bbdf_aliment"],
					"id_fk_effet_braldun_aliment" => $p["id_fk_effet_braldun_aliment"],
			);

			if ($p["type_type_aliment"] == "manger") {
				$tabAliments[$p["id_aliment"]] = $tab;
			} else {
				$tabBoissons[$p["id_aliment"]] = $tab;
			}
		}

		if (isset($tabAliments) && count($tabAliments) > 0) {
			$this->view->mangerNbAlimentOk = true;
		}

		$this->view->tabAliments = $tabAliments;
		$this->view->tabBoissons = $tabBoissons;
	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		// Verification cuisiner
		if ($this->view->mangerNbAlimentOk == false) {
			throw new Zend_Exception(get_class($this)." Manger interdit ");
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Aliment invalide : ".$this->request->get("valeur_1"));
		} else {
			$idAliment = (int)$this->request->get("valeur_1");
		}

		$aliment = null;
		foreach ($this->view->tabAliments as $a) {
			if ($a["id_aliment"] == $idAliment) {
				$aliment = $a;
				break;
			}
		}

		if ($aliment == null) {
			throw new Zend_Exception(get_class($this)." Aliment invalide (".$idAliment.")");
		}

		if (((int)$this->request->get("valeur_2").""!=$this->request->get("valeur_2")."")) {
			throw new Zend_Exception(get_class($this)." Boisson invalide : ".$this->request->get("valeur_2"));
		} else {
			$idBoisson = (int)$this->request->get("valeur_2");
		}

		$boisson = null;
		if ($idBoisson != -1) {
			foreach ($this->view->tabBoissons as $a) {
				if ($a["id_aliment"] == $idBoisson) {
					$boisson = $a;
					break;
				}
			}
		}

		if ($boisson == null && $idBoisson != -1) {
			throw new Zend_Exception(get_class($this)." Boisson invalide (".$idBoisson.")");
		}

		// cacul de la quete avant, pour avoir le controle sur l'état repu ou affame.
		$this->view->estQueteEvenement = Bral_Util_Quete::etapeManger($this->view->user, false);

		$this->calculManger($aliment, $boisson);

		$idType = $this->view->config->game->evenements->type->competence;
		$details = "[b".$this->view->user->id_braldun."] a mangé";
		$this->setDetailsEvenement($details, $idType);
		$this->setEvenementQueSurOkJet1(false);

		Zend_Loader::loadClass("Bral_Util_Quete");

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majBraldun();
	}

	private function calculManger($aliment, $boisson) {
		Zend_Loader::loadClass("LabanAliment");
		Zend_Loader::loadClass("TypeAliment");

		$labanAlimentTable = new LabanAliment();
		$where = 'id_laban_aliment = '.(int)$aliment["id_aliment"];
		$labanAlimentTable->delete($where);

		$braldunTable = new Braldun();
		$braldunRowset = $braldunTable->find($this->view->user->id_braldun);
		$braldun = $braldunRowset->current();

		$coef = 1;
		if ($boisson != null) {
			if ($boisson["id_fk_type_aliment"] == TypeAliment::ID_TYPE_LAGER) {
				$coef = 1.5;
			} elseif ($boisson["id_fk_type_aliment"] == TypeAliment::ID_TYPE_ALE) {
				$coef = 2;
			} elseif ($boisson["id_fk_type_aliment"] == TypeAliment::ID_TYPE_STOUT) {
				$coef = 2;
			} elseif ($boisson["id_fk_type_aliment"] == TypeAliment::ID_TYPE_JOUR_MILIEU) {
				$coef = 2;
			}
		}

		$this->view->user->balance_faim_braldun = $this->view->user->balance_faim_braldun + floor($aliment["bbdf"] * $coef);

		if ($this->view->user->balance_faim_braldun > 100) {
			$this->view->user->balance_faim_braldun = 100;
		}

		$data = array(
			'balance_faim_braldun' => $this->view->user->balance_faim_braldun,
		);
		$where = "id_braldun=".$this->view->user->id_braldun;
		$braldunTable->update($data, $where);

		$this->view->aliment = $aliment;

		$this->view->avecEffet = false;
		Zend_Loader::loadClass("Bral_Util_Effets");
		Zend_Loader::loadClass("EffetBraldun");
		$effetBraldunTable = new EffetBraldun();

		if ($aliment["id_fk_effet_braldun_aliment"] != null) {
			$data = array("id_fk_braldun_cible_effet_braldun" => $this->view->user->id_braldun);
			$where = "id_effet_braldun = ".intval($aliment["id_fk_effet_braldun_aliment"]);
			$effetBraldunTable->update($data, $where);
			Bral_Util_Effets::calculEffetBraldun($this->view->user, true, $aliment["id_fk_effet_braldun_aliment"]);
			$this->view->avecEffet = true;
		}

		if ($boisson["id_fk_effet_braldun_aliment"] != null) {
			$data = array("id_fk_braldun_cible_effet_braldun" => $this->view->user->id_braldun);
			$where = "id_effet_braldun = ".intval($boisson["id_fk_effet_braldun_aliment"]);
			$effetBraldunTable->update($data, $where);
			Bral_Util_Effets::calculEffetBraldun($this->view->user, true, $boisson["id_fk_effet_braldun_aliment"]);
			$this->view->avecEffet = true;
		}

		Zend_Loader::loadClass("Aliment");
		$alimentTable = new Aliment();
		$where = 'id_aliment = '.(int)$aliment["id_aliment"];
		$alimentTable->delete($where);

		if ($boisson != null) {
			$where = 'id_laban_aliment = '.(int)$boisson["id_aliment"];
			$labanAlimentTable->delete($where);
			$where = 'id_aliment = '.(int)$boisson["id_aliment"];
			$alimentTable->delete($where);
		}
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_laban", "box_effets"));
	}
}
