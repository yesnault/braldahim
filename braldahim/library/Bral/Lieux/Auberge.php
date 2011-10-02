<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Auberge extends Bral_Lieux_Lieu
{

	private $_utilisationPossible = false;
	private $_coutCastars = null;

	function prepareCommun()
	{
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Lieu");
		Zend_Loader::loadClass("LabanAliment");
		Zend_Loader::loadClass("ElementAliment");

		$this->_coutCastars = $this->calculCoutCastars();
		$this->_utilisationPossible = (($this->view->user->castars_braldun - $this->_coutCastars) >= 0);

		$nbPossibleAvecCastars = floor($this->view->user->castars_braldun / $this->_coutCastars);

		$poidsRestantLaban = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
		if ($poidsRestantLaban < 0) $poidsRestantLaban = 0;
		$possible = true;
		if (($poidsRestantLaban - Bral_Util_Poids::POIDS_RATION) < 0) {
			$possible = false;
		}
		$nbPossibleLaban = floor($poidsRestantLaban / Bral_Util_Poids::POIDS_RATION);
		if ($nbPossibleLaban >= $nbPossibleAvecCastars) {
			$nbPossibleLaban = $nbPossibleAvecCastars;
		}
		$tabDestinationTransfert[0] = array("id_destination" => "laban", "texte" => "votre laban", "poids_restant" => $poidsRestantLaban, "possible" => $possible, "nb_possible" => $nbPossibleLaban);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdBraldun($this->view->user->id_braldun);
		$charrette = null;
		$poidsRestantCharrette = 0;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$poidsRestantCharrette = $charrette["poids_transportable_charrette"] - $charrette["poids_transporte_charrette"];
			$possible = true;
			if (($poidsRestantCharrette - Bral_Util_Poids::POIDS_RATION) < 0) {
				$possible = false;
			}
			$nbPossibleCharrette = floor($poidsRestantCharrette / Bral_Util_Poids::POIDS_RATION);
			if ($nbPossibleCharrette >= $nbPossibleAvecCastars) {
				$nbPossibleCharrette = $nbPossibleAvecCastars;
			}
			$tabDestinationTransfert[1] = array("id_destination" => "charrette", "texte" => "votre charrette", "poids_restant" => $poidsRestantCharrette, "possible" => $possible, "nb_possible" => $nbPossibleCharrette);
		}

		$this->view->destinationTransfert = $tabDestinationTransfert;
		$this->view->charrette = $charrette;

		if ($poidsRestantCharrette > $poidsRestantLaban) {
			$this->view->poidsRestant = $poidsRestantCharrette;
		}
		else {
			$this->view->poidsRestant = $poidsRestantLaban;
		}
		$this->view->nbPossible = floor($this->view->poidsRestant / Bral_Util_Poids::POIDS_RATION);

		$this->view->nbDeduction = 0;
		if ($this->view->nbPossible >= $nbPossibleAvecCastars) {
			$this->view->nbPossible = $nbPossibleAvecCastars;
			$this->view->nbDeduction = 1;
		}

		$achatAliment = true;
		if ($this->view->nbPossible < 1) {
			$this->view->nbPossible = 0;
			$achatAliment = false;
		}

		$castarsRestants = $this->view->user->castars_braldun - $this->_coutCastars;
		$achatAlimentEtResto = true;
		if (floor($castarsRestants / $this->_coutCastars) < 1 || $achatAliment == false) {
			$achatAlimentEtResto = false;
		}

		$tabChoix[1]["nom"] = "Se restaurer uniquement";
		$tabChoix[1]["valid"] = $this->_utilisationPossible;
		$tabChoix[1]["bouton"] = "Se Restaurer";
		$tabChoix[2]["nom"] = "Acheter des ragoûts uniquement";
		$tabChoix[2]["valid"] = $achatAliment;
		$tabChoix[2]["bouton"] = "Acheter";
		$tabChoix[3]["nom"] = "Se restaurer et acheter des ragoûts";
		$tabChoix[3]["valid"] = $achatAlimentEtResto;
		$tabChoix[3]["bouton"] = "Se Restaurer et Acheter";

		$this->view->tabChoix = $tabChoix;
	}

	function prepareFormulaire()
	{
		$this->view->utilisationPossible = $this->_utilisationPossible;
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat()
	{

		// verification qu'il y a assez de castars
		if ($this->_utilisationPossible == false) {
			throw new Zend_Exception(get_class($this) . " Achat impossible : castars:" . $this->view->user->castars_braldun . " cout:" . $this->_coutCastars);
		}

		if (((int)$this->request->get("valeur_1") . "" != $this->request->get("valeur_1") . "")) {
			throw new Zend_Exception("Bral_Lieux_Auberge :: Choix invalide : " . $this->request->get("valeur_1"));
		} else {
			$this->view->idChoix = (int)$this->request->get("valeur_1");
		}

		if ($this->view->idChoix == 2 || $this->view->idChoix == 3) {
			if (((int)$this->request->get("valeur_2") . "" != $this->request->get("valeur_2") . "")) {
				throw new Zend_Exception("Bral_Lieux_Auberge :: Nombre invalide : " . $this->request->get("valeur_2"));
			} else {
				$this->view->nbAcheter = (int)$this->request->get("valeur_2");
			}

			$coutAchatEtRepas = ($this->view->nbAcheter + 1) * $this->calculCoutCastars();
			if ($this->view->idChoix == 3 && $coutAchatEtRepas > $this->view->user->castars_braldun) {
				throw new Zend_Exception(get_class($this) . " pas assez de castars ");
			}

			$idDestination = $this->request->get("valeur_3");

			$destination = null;

			// on regarde si l'on connait la destination
			$flag = false;
			foreach ($this->view->destinationTransfert as $d) {
				if ($d["id_destination"] == $idDestination) {
					$destination = $d;
					$flag = true;
					break;
				}
			}

			if ($flag == false) {
				throw new Zend_Exception(get_class($this) . " destination inconnue=" . $idDestination);
			}

			if ($destination["possible"] == false) {
				throw new Zend_Exception(get_class($this) . " destination invalide 3");
			}

			$this->view->destination = $destination["id_destination"];

			if ($this->view->nbAcheter > $this->view->nbPossible || $this->view->nbAcheter > $destination["nb_possible"]) {
				throw new Zend_Exception("Bral_Lieux_Auberge :: Nombre Rations invalide : " . $this->view->nbAcheter . " possible=" . $this->view->nbPossible . " ou " . $destination["nb_possible"]);
			}

			if ($this->view->charrette == null && $this->request->get("valeur_3") == "charrette") {
				throw new Zend_Exception(get_class($this) . " destination invalide 3");
			}
		}

		if ($this->view->idChoix < 1 || $this->view->idChoix > 3) {
			throw new Zend_Exception("Bral_Lieux_Auberge :: Choix invalide 2 : " . $this->request->get("valeur_1"));
		}

		if ($this->view->tabChoix[$this->view->idChoix]["valid"] == false) {
			throw new Zend_Exception("Bral_Lieux_Auberge :: Choix invalide 3 : " . $this->view->tabChoix[$this->view->idChoix]["valid"]);
		}

		if ($this->view->idChoix == 1 || $this->view->idChoix == 3) {

			Zend_Loader::loadClass("TypeAliment");
			$typeAlimentTable = new TypeAliment();
			$aliment = $typeAlimentTable->findById(TypeAliment::ID_TYPE_RAGOUT);

			Zend_Loader::loadClass("Bral_Util_Faim");
			Bral_Util_Faim::calculBalanceFaim($this->view->user, $aliment->bbdf_base_type_aliment);
			Zend_Loader::loadClass("Bral_Util_Quete");
			$this->view->estQueteEvenement = Bral_Util_Quete::etapeManger($this->view->user, true);
		} else {
			$this->_coutCastars = 0;
		}

		if ($this->view->idChoix == 2 || $this->view->idChoix == 3) {
			if ($this->view->nbAcheter > 0) {
				$this->calculAchat();
				$this->_coutCastars = $this->_coutCastars + ($this->calculCoutCastars() * $this->view->nbAcheter);
			}
		}

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->_coutCastars;
		$this->majBraldun();

		$this->view->coutCastars = $this->_coutCastars;
	}

	private function calculAchat()
	{
		Zend_Loader::loadClass("TypeAliment");
		$typeAlimentTable = new TypeAliment();
		$aliment = $typeAlimentTable->findById(TypeAliment::ID_TYPE_RAGOUT);

		$this->view->qualiteAliment = 2; // qualite correcte

		$this->view->bbdfAliment = Bral_Util_De::get_de_specifique(20, 25);
		$this->view->aliment = $aliment;

		$elementAlimentTable = new ElementAliment();
		$destinationAlimentTable = null;
		if ($this->view->destination == "laban") {
			$destinationAlimentTable = new LabanAliment();
		}
		if ($this->view->destination == "charrette") {
			Zend_Loader::loadClass("CharretteAliment");
			$destinationAlimentTable = new CharretteAliment();
		}

		Zend_Loader::loadClass("IdsAliment");
		$idsAlimentTable = new IdsAliment();

		Zend_Loader::loadClass('Aliment');
		$alimentTable = new Aliment();

		for ($i = 1; $i <= $this->view->nbAcheter; $i++) {

			$id_aliment = $idsAlimentTable->prepareNext();

			$data = array(
				'id_aliment' => $id_aliment,
				'id_fk_type_aliment' => TypeAliment::ID_TYPE_RAGOUT,
				'id_fk_type_qualite_aliment' => $this->view->qualiteAliment,
				'bbdf_aliment' => $this->view->bbdfAliment,
			);
			$alimentTable->insert($data);

			if ($this->view->destination == "laban") {
				$data = array(
					'id_laban_aliment' => $id_aliment,
					'id_fk_braldun_laban_aliment' => $this->view->user->id_braldun,
				);
			}

			if ($this->view->destination == "charrette") {
				$data = array(
					'id_charrette_aliment' => $id_aliment,
					'id_fk_charrette_aliment' => $this->view->charrette['id_charrette'],
				);
			}

			$destinationAlimentTable->insert($data);

			if ($this->view->destination == "charrette") {
				Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun, true);
			}

		}
	}

	function getListBoxRefresh()
	{
		return $this->constructListBoxRefresh(array("box_laban"));
	}

	private function calculCoutCastars()
	{
		return 9;
	}
}