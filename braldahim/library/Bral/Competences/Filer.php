<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Filer extends Bral_Competences_Competence {

	function prepareCommun() {

		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("TypeLieu");
		Zend_Loader::loadClass("Filature");
		Zend_Loader::loadClass("Ville");

		$tableFilature = new Filature();

		$this->coutCastars = 50;
		$this->view->positionOk = false;

		$filatureEnCours = $tableFilature->findEnCoursByIdBraldun($this->view->user->id_braldun);
		if ($filatureEnCours != null || count($filatureEnCours) == 1) {
			$this->view->filerEnCours = $filatureEnCours[0];
		} else {
			$this->view->filerEnCours = null;
		}

		if ($this->view->filerEnCours != null) {
			$lieuxTable = new Lieu();
			$lieuRowset = $lieuxTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
			$this->lieuEnCours = $lieuRowset[0];
			if ($this->view->filerEnCours["etape_filature"] == 2 && $lieuRowset[0]["id_type_lieu"] == TypeLieu::ID_TYPE_GARE) {
				$this->view->positionOk = true;
			} elseif ($this->view->filerEnCours["etape_filature"] == 3 && $lieuRowset[0]["id_type_lieu"] == TypeLieu::ID_TYPE_MAIRIE) {
				$this->view->positionOk = true;
			} elseif ($this->view->filerEnCours["etape_filature"] == 4) {
				$this->view->positionOk = true;
			}
		} else { // etape 1 ou 4
			$this->view->positionOk = true;
		}

		if ($this->view->user->castars_braldun >= $this->coutCastars) {
			$this->view->peutCastars = true;
		} else {
			$this->view->peutCastars = false;
		}
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

		if ($this->view->positionOk == false) {
			throw new Zend_Exception(get_class($this)." Position KO");
		}

		if (((int)$this->request->get("valeur_1").""!=$this->request->get("valeur_1")."")) {
			throw new Zend_Exception(get_class($this)." Valeur 1 invalide : ".$this->request->get("valeur_1"));
		}

		$idBraldun = null;
		$braldunCible = null;
		$this->view->enPayant = false;
		// Verification filer
		if ($this->view->filerEnCours == null) {

			$idBraldun = (int)$this->request->get("valeur_1");
			$tableBraldun = new Braldun();
			$braldunCible = $tableBraldun->findById($idBraldun);
			if ($braldunCible == null || $braldunCible["est_pnj_braldun"] == "oui" ||
			$braldunCible["id_braldun"] == $this->view->user->id_braldun) {
				throw new Zend_Exception(get_class($this)." Braldûn invalide 2 : ".$this->request->get("valeur_1"));
			}
		} else {
			if ($this->view->peutCastars && (int)$this->request->get("valeur_1") == 1
			&& $this->view->filerEnCours["etape_filature"] == 2 || $this->view->filerEnCours["etape_filature"] == 3) {
				$this->view->enPayant = true;
			} else {
				$this->view->enPayant = false;
			}
			$braldunCible = $this->view->filerEnCours;
		}

		if ($braldunCible == null) {
			throw new Zend_Exception(get_class($this)." Braldûn invalide 3");
		}

		// calcul des jets
		$this->calculJets();

		if ($this->view->okJet1 === true) {
			$details = "";
			if ($this->view->filerEnCours == null) {
				$this->calculFilerEtape1($braldunCible, $details);
			} elseif ($this->view->filerEnCours["etape_filature"] == 2) {
				$this->calculFilerEtape2($braldunCible, $details);
				$this->insertHistorique($this->view->filerEnCours["id_filature"], $details);
			} elseif ($this->view->filerEnCours["etape_filature"] == 3) {
				$this->calculFilerEtape3($braldunCible, $details);
				$this->insertHistorique($this->view->filerEnCours["id_filature"], $details);
			} elseif ($this->view->filerEnCours["etape_filature"] == 4) {
				$this->calculFilerEtape4($braldunCible, $details);
				$this->insertHistorique($this->view->filerEnCours["id_filature"], $details);
			}

			if ($this->view->enPayant) {
				$this->view->user->castars_braldun = $this->view->user->castars_braldun - 50;
				if ($this->view->user->castars_braldun < 0) {
					$this->view->user->castars_braldun = 0;
				}
			}

			$this->view->braldunCible = $braldunCible;
			$this->view->details = $details;
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	private function calculFilerEtape1($braldunCible, &$details) {
		$filatureTable = new Filature();

		$data = array(
			'id_fk_braldun_filature' => $this->view->user->id_braldun,
			'id_fk_cible_braldun_filature' => $braldunCible["id_braldun"],
			'etape_filature' => 2,
			'date_creation_filature' => date("Y-m-d H:i:s"),
			'date_fin_filature' => null, 
		);
		$idFilature = $filatureTable->insert($data);
		$details = "Création de la filature";
		$this->insertHistorique($idFilature, $details);
	}

	private function calculFilerEtape2($braldunCible, &$details) {
		Zend_Loader::loadClass("Region");

		$regionTable = new Region();
		$regionCourante = $regionTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun);
		if ($regionCourante == null) {
			throw new Zend_Exception(get_class($this)." Region invalide 1");
		}

		$regionCible = $regionTable->findByCase($braldunCible["x_braldun"], $braldunCible["y_braldun"]);
		if ($regionCible == null) {
			throw new Zend_Exception(get_class($this)." Region invalide 2");
		}

		$cible = $braldunCible["prenom_braldun"] . " ". $braldunCible["nom_braldun"] . " (". $braldunCible["id_braldun"] . ")";
		$details .= $cible;
		if ($regionCible["id_region"] == $regionCourante["id_region"]) {
			$details .= " se trouve dans la même Comté que la vôtre (".$regionCourante["nom_region"].").";
			$data = array('etape_filature' => 3);
		} else {
			$details .= " se trouve dans une autre Comté que la vôtre (".$regionCourante["nom_region"].").";
			$data = array('etape_filature' => 2);
		}

		$filatureTable = new Filature();
		$where = "id_filature = ".$this->view->filerEnCours["id_filature"];
		$idFilature = $filatureTable->update($data, $where);

		if ($this->view->enPayant) {
			$message = "[Filature] Vous avez reçu un message par l'inconnu de la gare de ".$this->lieuEnCours["nom_ville"]." :".PHP_EOL;
			$message .= "\"". $cible. " est à la gare ! \"";
			$this->insertFilatureAction($braldunCible["id_braldun"], $this->view->user->x_braldun, $this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->y_braldun, $message);
		}
	}

	private function calculFilerEtape3($braldunCible, &$details) {
		$villeTable = new Ville();
		$villeCourante = $villeTable->findLaPlusProche($this->view->user->x_braldun, $this->view->user->y_braldun);
		$villeCible = $villeTable->findLaPlusProche($braldunCible["x_braldun"], $braldunCible["y_braldun"]);

		$cible = $braldunCible["prenom_braldun"] . " ". $braldunCible["nom_braldun"] . " (". $braldunCible["id_braldun"] . ")";
		$details .= $cible;

		if ($this->view->enPayant) {
			$message = "[Filature] Vous avez reçu un message par l'inconnu de la mairie de ".$this->lieuEnCours["nom_ville"]." :".PHP_EOL;
			$message .= "\"". $cible. " est dans la ville ! \"";
			$this->insertFilatureAction($braldunCible["id_braldun"], $villeCible["x_min_ville"], $villeCible["x_max_ville"], $villeCible["y_min_ville"], $villeCible["y_max_ville"], $message);
		}

		if ($villeCourante["id_ville"] == $villeCible["id_ville"]) {
			$details .= " se trouve dans les environs de votre ville (".$villeCourante["nom_ville"].").";
			$data = array('etape_filature' => 4);

			$filatureTable = new Filature();
			$where = "id_filature = ".$this->view->filerEnCours["id_filature"];
			$idFilature = $filatureTable->update($data, $where);
			return true;
		} else {
			$details .= " ne se trouve pas dans les environs de votre ville (".$villeCourante["nom_ville"].").<br />";
			$this->calculFilerEtape2($braldunCible, $details);
			return false;
		}
	}

	private function calculFilerEtape4($braldunCible, &$details) {
		$retourEtape3 = $this->calculFilerEtape3($braldunCible, $details);

		if ($retourEtape3 == true) {
			$braldunCible["x_braldun"];
			$braldunCible["y_braldun"];

			$this->view->user->x_braldun;
			$this->view->user->y_braldun;

			$distance = sqrt((($this->view->user->x_braldun - $braldunCible["x_braldun"]) * ($this->view->user->x_braldun - $braldunCible["x_braldun"])) + (($this->view->user->y_braldun- $braldunCible["y_braldun"]) * ($this->view->user->y_braldun- $braldunCible["y_braldun"])));

			$x = $this->view->user->x_braldun;
			$y = $this->view->user->y_braldun;
			$z = $this->view->user->z_braldun;
			$bm = $this->view->user->vue_bm_braldun;

			$vue_nb_cases = Bral_Util_Commun::getVueBase($x, $y, $z) + $bm;

			if ($distance <= $vue_nb_cases * 3) {

				if ($this->view->user->x_braldun == $braldunCible["x_braldun"] &&
				$this->view->user->y_braldun == $braldunCible["y_braldun"]) {
					$details .= "<br />".$braldunCible["prenom_braldun"] . " ". $braldunCible["nom_braldun"] . " (". $braldunCible["id_braldun"] . ")";
					$details .= " se trouve sur votre case !";
				} else {
					$details .= "<br />D'après les indices trouvés sur le sol, ";
					$details .= $braldunCible["prenom_braldun"] . " ". $braldunCible["nom_braldun"] . " (". $braldunCible["id_braldun"] . ")";
					$details .= " peut être en (x / y) : <br />";

					$tab[] = $braldunCible["x_braldun"]. " / ". $braldunCible["y_braldun"];
					$tab[] = ($braldunCible["x_braldun"] + (Bral_Util_De::get_1ouMoins1() * Bral_Util_De::get_1d3())). " / ". ($braldunCible["y_braldun"] + (Bral_Util_De::get_1ouMoins1() * Bral_Util_De::get_1d3()));
					$tab[] = ($braldunCible["x_braldun"] + (Bral_Util_De::get_1ouMoins1() * Bral_Util_De::get_1d3())). " / ". ($braldunCible["y_braldun"] + (Bral_Util_De::get_1ouMoins1() * Bral_Util_De::get_1d3()));
					shuffle($tab);
					$details .= $tab[0]." ou en ".$tab[1]." ou en ".$tab[2].".";
				}

				$message = "Quelqu'un est sur vos pas !".PHP_EOL.PHP_EOL."Inutile de répondre à ce message.";
				Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->inconnu->id_braldun, $braldunCible["id_braldun"] , $message, $this->view);
				
			} else {
				$details .= "<br />".$braldunCible["prenom_braldun"] . " ". $braldunCible["nom_braldun"] . " (". $braldunCible["id_braldun"] . ")";
				$details .= " se trouve ";

				if ($this->view->user->x_braldun == $braldunCible["x_braldun"]) {
					if ($braldunCible["y_braldun"] < $this->view->user->y_braldun) {
						$details .= " au Sud de votre position";
					} elseif ($braldunCible["y_braldun"] > $this->view->user->y_braldun) {
						$details .= " au Nord de votre position";
					} else {
						$details .= " sur votre case !";
					}
				} elseif ($this->view->user->y_braldun == $braldunCible["y_braldun"]) {
					if ($braldunCible["x_braldun"] < $this->view->user->x_braldun) {
						$details .= " à l'Ouest de votre position";
					} elseif ($braldunCible["x_braldun"] > $this->view->user->x_braldun) {
						$details .= " à l'Est de votre position";
					} else {
						$details .= " sur votre case !";
					}
				} else {
					if ($braldunCible["y_braldun"] < $this->view->user->y_braldun) {
						$details .= " au Sud";
					} else {
						$details .= " au Nord";
					}

					if ($braldunCible["x_braldun"] < $this->view->user->x_braldun) {
						$details .= "-Ouest de votre position";
					} else {
						$details .= "-Est de votre position";
					}
				}

				if ($this->view->user->z_braldun != $braldunCible["z_braldun"]) {
					$details .= " Attention, la cible n'est pas à votre niveau (z=".$this->view->user->z_braldun.")";
				}
				
				$message = "Le vent vous porte un étrange odeur ...".PHP_EOL.PHP_EOL."Inutile de répondre à ce message.";
				Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->inconnu->id_braldun, $braldunCible["id_braldun"] , $message, $this->view);
			}
		} else {
			// rien à faire ici
		}
	}

	private function insertFilatureAction($idCible, $xMin, $xMax, $yMin, $yMax, $message) {
		Zend_Loader::loadClass("FilatureAction");
		$tableFilatureAction = new FilatureAction();

		$data = array (
			'id_fk_braldun_filature_action' => $idCible,
			'id_fk_filature_action' => $this->view->filerEnCours["id_filature"],
			'x_min_filature_action' => $xMin,
			'x_max_filature_action' => $xMax,
			'y_min_filature_action' => $yMin,
			'y_max_filature_action' => $yMax,
			'message_filature_action' => $message,
		);

		$tableFilatureAction->insert($data);
	}


	private function insertHistorique($idFilature, $details) {
		Zend_Loader::loadClass("HistoriqueFilature");
		$historiqueFilatureTable = new HistoriqueFilature();

		$data = array(
			'id_fk_filature_historique_filature' => $idFilature,
			'date_historique_filature' => date("Y-m-d H:i:s"),
			'details_historique_filature' => $details,
		);

		$historiqueFilatureTable->insert($data);
	}

	function getListBoxRefresh() {
		return $this->constructListBoxRefresh(array("box_competences_communes", "box_filatures"));
	}
}
