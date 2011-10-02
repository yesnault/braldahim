<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Entretenir extends Bral_Competences_Competence
{

	function prepareCommun()
	{
		Zend_Loader::loadClass("Champ");
		Zend_Loader::loadClass("Bral_Util_Communaute");
		Zend_Loader::loadClass("TypeLieu");
		Zend_Loader::loadClass("Bral_Util_Messagerie");

		if ($this->verificationChamp() == false) {
			return null;
		}

		$this->verificationChamp();
		$this->preparePositions();
	}

	private function verificationChamp()
	{
		$this->view->entretenirChampOk = false;

		$champTable = new Champ();

		$niveauGrenier = Bral_Util_Communaute::getNiveauDuLieu($this->view->user->id_fk_communaute_braldun, TypeLieu::ID_TYPE_GRENIER);

		if ($niveauGrenier != null && $niveauGrenier >= Bral_Util_Communaute::NIVEAU_GRENIER_RECOLTER) {
			$champs = $champTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, null, null, $this->view->user->id_fk_communaute_braldun);
		} else {
			$champs = $champTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_braldun);
		}

		$retour = false;
		if (count($champs) == 1) {
			$this->view->champ = $champs[0];
			if ($this->view->champ["phase_champ"] == "seme") {
				$this->view->entretenirChampOk = true;
				$this->idProprietaire = $this->view->champ["id_braldun"];
				$retour = true;
			}
		}

		return $retour;
	}

	private function preparePositions()
	{

		$selection = $this->request->get("position"); // si l'on vient de la vue (clic sur l'icone marcher)
		$tabPositions = null;

		for ($i = 1; $i <= 10; $i++) {
			for ($j = 1; $j <= 10; $j++) {
				$selected = "";
				if ($selection == $i . "-" . $j) {
					$selected = "selected";
				}
				$tabPositions[$i . 't' . $j] = array(
					'possible' => true,
					'selected' => $selected,
					'x' => $i,
					'y' => $j,
				);
			}
		}

		Zend_Loader::loadClass("ChampTaupe");
		$champTaupeTable = new ChampTaupe();
		$taupes = $champTaupeTable->findByIdChamp($this->view->champ["id_champ"]);

		$tabTaupes = array();
		if ($this->view->champ["phase_champ"] == 'seme') {
			if ($taupes != null) {
				foreach ($taupes as $t) {
					if ($t["etat_champ_taupe"] != 'vivant') {
						$tabPositions[$t["x_champ_taupe"] . 't' . $t["y_champ_taupe"]]["possible"] = false;
						$tabPositions[$t["x_champ_taupe"] . 't' . $t["y_champ_taupe"]]["selected"] = "";
					}
				}
			}
		}

		$this->view->positions = $tabPositions;
	}

	function prepareFormulaire()
	{
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat()
	{
		// Verification des Pa
		if ($this->view->assezDePa == false) {
			throw new Zend_Exception(get_class($this) . " Pas assez de PA : " . $this->view->user->pa_braldun);
		}

		// Verification semer
		if ($this->view->entretenirChampOk == false) {
			throw new Zend_Exception(get_class($this) . " Entretenir Champ interdit");
		}

		$x_y = $this->request->get("valeur_1");
		list ($x, $y) = preg_split("/t/", $x_y);

		if ($x < 0 || $x > 10 || $y < 0 || $y > 10) {
			throw new Zend_Exception(get_class($this) . " XY invalides : " . $x_y);
		}

		if ($this->view->positions[$x . "t" . $y]["possible"] !== true) {
			throw new Zend_Exception(get_class($this) . " XY impossibles : " . $x_y);
		}

		$this->entretenir($x, $y);

		$idType = $this->view->config->game->evenements->type->competence;
		$details = "[b" . $this->view->user->id_braldun . "] a entretenu son champ";
		$this->setDetailsEvenement($details, $idType);
		$this->setEvenementQueSurOkJet1(false);

		//message pour le braldûn propriétaire
		if ($this->idProprietaire != $this->view->user->id_braldun) {
			Zend_Loader::loadClass("Bral_Util_Messagerie");
			$message = "[Ceci est un message automatique d'agriculture]" . PHP_EOL;
			$message .= $this->view->user->prenom_braldun . " " . $this->view->user->nom_braldun . " a entretenu votre champ en x:" . $this->view->champ["x_champ"] . ", y:" . $this->view->champ["y_champ"] . PHP_EOL;
			$message .= "Zone entretenue : x: " . $this->view->etatZone["x"] . " y:" . $this->view->etatZone["y"] . "." . PHP_EOL;
			if ($this->view->etatZone["etat"] == ChampTaupe::ETAT_ENTRETENU) {
				$message .= "Aucun taupe n'a été trouvée à cet endroit." . PHP_EOL;
			} else {
				$message .= "Une partie de taupe de taille " . $this->view->etatZone["taille"] . " a été trouvée !" . PHP_EOL;
				if ($this->view->taupeDetruite) {
					$message .= "La taupe est occise." . PHP_EOL;
				} else {
					$message .= "Consultez le champ pour voir ce qu'il reste à trouver de cette taupe pour l'éliminer complètement." . PHP_EOL;
				}
			}
			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $this->idProprietaire, $message, $this->view);
		}

		$this->calculPx();
		$this->calculBalanceFaim();
		$this->majBraldun();
	}

	private function entretenir($x, $y)
	{
		Zend_Loader::loadClass("ChampTaupe");
		$champTaupeTable = new ChampTaupe();

		$data = array(
			'x_champ_taupe' => $x,
			'y_champ_taupe' => $y,
			'id_fk_champ_taupe' => $this->view->champ["id_champ"],
			'date_entretien_champ_taupe' => date('Y-m-d H:i:s'),
		);

		$etatZone = $champTaupeTable->entretenir($data);

		$etatZone["x"] = $x;
		$etatZone["y"] = $y;

		$this->view->taupeDetruite = false;

		if ($etatZone["etat"] == ChampTaupe::ETAT_DETRUIT) {
			$taupe = $champTaupeTable->findByIdChampNumeroTaupeVivant($this->view->champ["id_champ"], $etatZone["numero"]);
			if ($taupe == null || count($taupe) < 1) {
				$this->view->taupeDetruite = true;
			}
		}

		$champTable = new Champ();
		$data = array(
			'date_utilisation_champ' => date("Y-m-d 00:00:00"),
		);

		$where = 'id_champ=' . $this->view->champ["id_champ"];
		$champTable->update($data, $where);

		$this->view->etatZone = $etatZone;
	}

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_competences", "box_champs"));
	}

	public function calculPx()
	{
		$this->view->nb_px_commun = 0;
		$this->view->nb_px_perso = 0;
		$this->view->calcul_px_generique = true;

		if ($this->view->taupeDetruite == true) {
			if ($this->view->etatZone["taille"] == 4) {
				$this->view->nb_px_perso = 4;
			} elseif ($this->view->etatZone["taille"] == 3) {
				$this->view->nb_px_perso = 5;
			} elseif ($this->view->etatZone["taille"] == 2) {
				$this->view->nb_px_perso = 8;
			}
		}
		$this->view->nb_px = floor($this->view->nb_px_perso + $this->view->nb_px_commun);
	}
}