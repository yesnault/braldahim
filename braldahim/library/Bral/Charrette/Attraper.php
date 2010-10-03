<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Charrette_Attraper extends Bral_Charrette_Charrette {

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		return "Attraper une charrette";
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");

		$tabCharrettes = null;
		$this->view->possedeCharrette = false;
		$this->view->attraperCharrettePossible = false;

		$charretteTable = new Charrette();

		$nombre = $charretteTable->countByIdBraldun($this->view->user->id_braldun);
		if ($nombre > 0) {
			$this->view->possedeCharrette = true;
			return;
		}

		$provenance = $this->request->get("provenance");

		$charrettes = null;
		if ($provenance == "echoppe") {
			Zend_Loader::loadClass("Echoppe");
			// On regarde si le Braldûn est dans une de ses echopppes
			$echoppeTable = new Echoppe();

			$echoppes = $echoppeTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

			if (count($echoppes) == 1) {
				$echoppe = $echoppes[0];
				if ($echoppe["x_echoppe"] != $this->view->user->x_braldun || $echoppe["y_echoppe"] != $this->view->user->y_braldun) {
					throw new Zend_Exception(get_class($this)." Echoppe invalide. idh:".$this->view->user->id_braldun);
				}

				Zend_Loader::loadClass("EchoppeMateriel");

				$echoppeMaterielTable = new EchoppeMateriel();
				$materiels = $echoppeMaterielTable->findByIdEchoppe($echoppe["id_echoppe"]);
				foreach ($materiels as $m) {
					if (substr($m["nom_systeme_type_materiel"], 0, 9) == "charrette") {
						$charrettes[] = $m;
					}
				}
				$typeProvenance = "echoppe";
				$nomIdCharrette = "id_echoppe_materiel";
			}
		} else {
			$charrettes = $charretteTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
			$typeProvenance = "sol";
			$nomIdCharrette = "id_charrette";
		}

		if (count($charrettes) > 0) {
			Zend_Loader::loadClass("Bral_Util_Metier");
			$tab = Bral_Util_Metier::prepareMetier($this->view->user->id_braldun, $this->view->user->sexe_braldun);
			$estMenuisierOuBucheron = false;
			if ($tab["tabMetierCourant"]["nom_systeme"] == "bucheron" || $tab["tabMetierCourant"]["nom_systeme"] == "menuisier") {
				$estMenuisierOuBucheron = true;
			}

			Zend_Loader::loadClass("Bral_Util_Charrette");
			foreach ($charrettes as $c) {
				$this->view->attraperCharrettePossible = true;

				$tab = Bral_Util_Charrette::calculAttraperPossible($c, $this->view->user, $estMenuisierOuBucheron);
				$possible = $tab["possible"];
				$detail = $tab["detail"];

				$possedeSabot = false;
				if (Bral_Util_Charrette::possedeSabot($c[$nomIdCharrette])) {
					$possedeSabot = true;
				}

				$tabCharrettes[] = array (
					"id_charrette" => $c[$nomIdCharrette],
					"nom" => $c["nom_type_materiel"], 
					"possible" => $possible, 
					"detail" => $detail, 
					"provenance" => $typeProvenance,
					"possede_sabot" => $possedeSabot,
					"id_type_materiel" => $c["id_type_materiel"],
					"durabilite_type_materiel" => $c["durabilite_type_materiel"],
					"capacite_type_materiel" => $c["capacite_type_materiel"],
					"sabot_1_charrette" => $c["sabot_1_charrette"],
					"sabot_2_charrette" => $c["sabot_2_charrette"],
					"sabot_3_charrette" => $c["sabot_3_charrette"],
					"sabot_4_charrette" => $c["sabot_4_charrette"],
				);
			}
		}
		$this->view->charrettes = $tabCharrettes;
		$this->view->provenance = $provenance;
		$tabChiffres = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
		$this->view->chiffres = $tabChiffres;
	}

	function prepareFormulaire() {
	}

	function prepareResultat() {

		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this)." Pas assez de PA : ".$this->view->user->pa_braldun);
		}

		if ($this->view->possedeCharrette == true) {
			throw new Zend_Exception(get_class($this)." Possede deja charrette ");
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Charrette invalide : ".$this->request->get("valeur_1"));
		} else {
			$this->view->idCharrette = (int)$this->request->get("valeur_1");
		}

		$charrette = null;

		foreach ($this->view->charrettes as $c) {
			if ($this->view->idCharrette == $c["id_charrette"] && $c["possible"] == true) {
				$charrette = $c;
				break;
			}
		}
		if ($charrette == null) {
			throw new Zend_Exception(get_class($this)." Charrette invalide idh:".$this->view->user->pa_braldun. " ihc:".$this->view->idCharrette);
		}

		$sabotOk = false;

		if ($charrette["possede_sabot"] == true) {
			$chiffre_1 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
			$chiffre_2 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));
			$chiffre_3 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_5"));
			$chiffre_4 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_6"));
				
			if ($charrette["sabot_1_charrette"] == $chiffre_1 &&
			$charrette["sabot_2_charrette"] == $chiffre_2 &&
			$charrette["sabot_3_charrette"] == $chiffre_3 &&
			$charrette["sabot_4_charrette"] == $chiffre_4) {
				$sabotOk = true;
			}
		} else {
			$sabotOk = true;
		}

		if ($sabotOk == true) {
			$this->calculAttrapperCharrette($charrette);
			$this->calculBalanceFaim();

			$id_type = $this->view->config->game->evenements->type->ramasser;
			$details = "[b".$this->view->user->id_braldun."] a attrapé une charrette";
			$this->setDetailsEvenement($details, $id_type);

			$details = "[b".$this->view->user->id_braldun."] a attrapé la charrette n°".$charrette["id_charrette"];
			Zend_Loader::loadClass("Bral_Util_Materiel");
			Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_UTILISER_ID, $charrette["id_charrette"], $details);
		} else {
			$this->setEstEvenementAuto(false);
		}
		
		$this->view->sabotOk = $sabotOk;
	}

	private function calculAttrapperCharrette($charrette) {

		$charretteTable = new Charrette();

		$dataUpdate = array(
			"id_fk_braldun_charrette" => $this->view->user->id_braldun,
			"x_charrette" => null,
			"y_charrette" => null,
			"z_charrette" => null,
		);
			
		if ($charrette["provenance"] == "sol") {
			$where = "id_charrette = ".$charrette["id_charrette"];
			$charretteTable->update($dataUpdate, $where);
		} else if ($this->view->provenance == "echoppe") {
			$dataUpdate["id_charrette"] = $charrette["id_charrette"];

			$dataUpdate["durabilite_max_charrette"] = $charrette["durabilite_type_materiel"];
			$dataUpdate["durabilite_actuelle_charrette"] = $charrette["durabilite_type_materiel"];
			$dataUpdate["poids_transportable_charrette"] = $charrette["capacite_type_materiel"];
			$dataUpdate["poids_transporte_charrette"] = 0;

			$where = "id_charrette = ".$charrette["id_charrette"];
			$charretteTable->insert($dataUpdate);

			$echoppeMaterielTable = new EchoppeMateriel();
			$where = "id_echoppe_materiel=".$charrette["id_charrette"];
			$echoppeMaterielTable->delete($where);
		}

		Zend_Loader::loadClass("Bral_Util_Charrette");
		Bral_Util_Charrette::calculAmeliorationsCharrette($this->view->user->id_braldun);
	}

	function getListBoxRefresh() {
		if ($this->view->provenance == "echoppe") {
			$tab = array("box_echoppes");
		} else {
			$tab = array("box_vue");
		}
		return $this->constructListBoxRefresh($tab);
	}
}
