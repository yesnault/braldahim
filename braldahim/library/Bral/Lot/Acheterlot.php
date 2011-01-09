<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lot_Acheterlot extends Bral_Lot_Lot {

	private $lot = null;

	function getNomInterne() {
		return "box_action";
	}

	function getTitreAction() {
		if (count($this->view->lots) > 1) {
			return "Acheter des lots";
		} else {
			return "Acheter un lot";
		}
	}

	function prepareCommun() {
		Zend_Loader::loadClass("Charrette");
		Zend_Loader::loadClass("Bral_Util_Lot");
		Zend_Loader::loadClass("Lot");

		$this->view->idsLots = $this->request->get("idsLots");
		$this->view->tabIdsLots = preg_split("/,/", $this->request->get("idsLots"));
		if (count($this->view->tabIdsLots) == 0) {
			throw new Zend_Exception(get_class($this)."::nombre de lot invalide");
		}

		$this->view->idEchoppe = Bral_Util_Controle::getValeurIntVerifSansException($this->request->get("idEchoppe"), false);

		$poidsRestant = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
		$tabDestinationTransfert[0] = array("id_destination" => "laban", "texte" => "votre laban", "poids_restant" => $poidsRestant, "possible" => false);

		$charretteTable = new Charrette();
		$charrettes = $charretteTable->findByIdBraldun($this->view->user->id_braldun);

		$charrette = null;
		if (count($charrettes) == 1) {
			$charrette = $charrettes[0];
			$poidsRestant = $charrette["poids_transportable_charrette"] - $charrette["poids_transporte_charrette"];
			$tabDestinationTransfert[1] = array("id_destination" => "charrette", "texte" => "votre charrette", "poids_restant" => $poidsRestant, "possible" => false);
		}

		$this->view->destinationTransfert = $tabDestinationTransfert;
		$this->view->charrette = $charrette;

		$this->prepareLots();
	}

	private function prepareLots() {

		$lotTable = new Lot();

		if ($this->view->idEchoppe != null) {
			Zend_Loader::loadClass("Echoppe");
			$echoppesTable = new Echoppe();
			$echoppeRowset = $echoppesTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
			if (count($echoppeRowset) > 1) {
				throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide > 1 !");
			} else if (count($echoppeRowset) == 0) {
				throw new Zend_Exception(get_class($this)."::nombre d'echoppe invalide = 0 !");
			}

			if ($echoppeRowset[0]["id_echoppe"] != $this->view->idEchoppe) {
				throw new Zend_Exception(get_class($this).":: echoppe invalide:".$this->view->idEchoppe. ' x:'.$this->view->user->x_braldun. " y:".$this->view->user->y_braldun);
			}

			$lots = $lotTable->findByIdEchoppe($this->view->idEchoppe, $this->view->idLot);
		} else { // HV
			Zend_Loader::loadClass("Lieu");
			Zend_Loader::loadClass("TypeLieu");
			Zend_Loader::loadClass("TypeLot");
			$lieuxTable = new Lieu();
			$lieuRowset = $lieuxTable->findByTypeAndCase(TypeLieu::ID_TYPE_HOTEL, $this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
			unset($lieuxTable);

			$this->view->idEchoppe = null;
			$this->view->estSurEchoppe = false;

			if (count($lieuRowset) <= 0) {
				throw new Zend_Exception(get_class($this).":: lieu invalide:".$this->view->idEchoppe. ' x:'.$this->view->user->x_braldun. " y:".$this->view->user->y_braldun);
			}

			$lots = $lotTable->findByIdLot($this->view->tabIdsLots, TypeLot::ID_TYPE_VENTE_HOTEL);
		}

		$tabLots = null;
		foreach($this->view->tabIdsLots as $idLot) {
			$trouve = false;
			foreach ($lots as $p) {
				if ($idLot == $p["id_lot"]) {
					$trouve = true;
					break;
				}
			}
			if ($trouve == false) {
				throw new Zend_Exception(get_class($this)."::lot invalide:".$idLot);
			}
		}

		Zend_Loader::loadClass("Bral_Util_Lot");
		$lots = Bral_Util_Lot::getLotByIdsLots($this->view->tabIdsLots);
		if (count($lots) != count($this->view->tabIdsLots)) {
			throw new Zend_Exception(get_class($this)."::lot invalide 2 count1:".count($lots). " count2:".count($this->view->tabIdsLots));
		}

		$tabCharrette["possible"] = true;
		$tabCharrette["detail"] = "";
		$lotCharrette = null;
		$poidsTotal = 0;
		$prixTotal = 0;

		foreach ($lots as $lot) {
			if ($lot["estLotCharrette"] === true) {
				Zend_Loader::loadClass("Bral_Util_Metier");
				$tab = Bral_Util_Metier::prepareMetier($this->view->user->id_braldun, $this->view->user->sexe_braldun);
				$estMenuisierOuBucheron = false;
				if ($tab["tabMetierCourant"]["nom_systeme"] == "bucheron" || $tab["tabMetierCourant"]["nom_systeme"] == "menuisier") {
					$estMenuisierOuBucheron = true;
				}
				Zend_Loader::loadClass("Bral_Util_Charrette");
				$tab = Bral_Util_Charrette::calculAttraperPossible($lot["materiels"][0], $this->view->user, $estMenuisierOuBucheron);

				$charretteTable = new Charrette();
				$nombre = $charretteTable->countByIdBraldun($this->view->user->id_braldun);

				if ($nombre > 0) {
					$tabCharrette["possible"] = false;
					$tabCharrette["detail"] = "Vous possédez déjà une charrette";
				}

				$lotCharrette = $lot;
			}
			$poidsTotal = $poidsTotal + $lot["poids_lot"];
			$prixTotal = $prixTotal + $lot["prix_1_lot"];
		}

		$poidsPrix = $prixTotal * Bral_Util_Poids::getPoidsUnite("castar");

		$placeDispo = false;
		$tabPrix = $this->view->destinationTransfert;

		if ($lotCharrette != null) {
			$placeDispo = true;
		} else {
			$i = 0;
			foreach($this->view->destinationTransfert as $k => $d) {
				$poidsPrix2 = $poidsPrix;
				if ($d["id_destination"] == "charrette") $poidsPrix2 = 0;
				if ($poidsTotal < $d["poids_restant"] - $poidsPrix2) {
					$placeDispo = true;
					$this->view->destinationTransfert[$k]["possible"] = true;
				}
			}
		}

		if ($lotCharrette != null && count($this->view->tabIdsLots) > 1) {
			$tabCharrette["possible"] = false;
			$tabCharrette["detail"] = "Vous ne pouvez pas acheter un lot contenant une charrette avec un autre lot en même temps.";
			$placeDispo = false;
		}

		if ($this->view->user->castars_braldun >= $prixTotal) {
			$detailsLots["prix_possible"] = true;
		} else {
			$detailsLots["prix_possible"] = false;
		}

		$detailsLots["charrette_possible"] = $tabCharrette["possible"];
		$detailsLots["charrette_detail"] = $tabCharrette["detail"];

		$detailsLots["place_dispo"] = $placeDispo;
		$detailsLots["poids_total"] = $poidsTotal;
		$detailsLots["prix_total"] = $prixTotal;

		$this->view->lots = $lots;
		$this->view->detailsLots = $detailsLots;
		$this->view->lotCharrette = $lotCharrette;
	}

	function prepareFormulaire() {
		// rien ici
	}

	function prepareResultat() {
		if ($this->view->assezDePa !== true) {
			throw new Zend_Exception(get_class($this)."::pas assez de PA");
		}

		if ($this->view->detailsLots["place_dispo"] !== true) {
			throw new Zend_Exception(get_class($this)."::place invalide");
		}

		if ($this->view->detailsLots["prix_possible"] == false) {
			throw new Zend_Exception(get_class($this)."::pas assez de ressources");
		}

		$idDestination = $this->request->get("valeur_2");

		if ($this->view->charrette == null && $this->request->get("valeur_2") == "charrette") {
			throw new Zend_Exception(get_class($this)." destination invalide 2");
		}

		if ($this->view->idsLots != $this->request->getPost("valeur_1")) {
			throw new Zend_Exception("Lot invalide : ".$this->view->idsLots. " - ".$this->request->getPost("valeur_1"));
		}

		$destination = null;

		if ($this->view->lotCharrette == null) {
			// on regarde si l'on connait la destination
			$flag = false;
			foreach($this->view->destinationTransfert as $d) {
				if ($d["id_destination"] == $idDestination) {
					$destination = $d;
					$flag = true;
					break;
				}
			}

			if ($flag == false) {
				throw new Zend_Exception(get_class($this)." destination inconnue=".$idDestination);
			}

			if ($destination["possible"] == false) {
				throw new Zend_Exception(get_class($this)." destination invalide 3");
			}
		}

		$this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->view->detailsLots["prix_total"];

		$this->calculTransfert($idDestination);
		$this->view->destination = $destination;

		//TODO		$details = "[b".$this->view->user->id_braldun."] a acheté le lot d'équipement n°".$this->view->lot["id_lot"]. " dans l'échoppe";
		//		Bral_Util_Lot::insertHistorique(Bral_Util_Lot::HISTORIQUE_ACHETER_ID, $this->view->lot["id_lot"], $details);
	}

	private function calculDepotCastars($lot) {
		if ($this->view->estSurEchoppe) {
			$data = array(
				'id_echoppe' => intval($this->view->idEchoppe),
				"quantite_castar_caisse_echoppe" => $lot["prix_1_lot"],
			);
			$echoppeTable = new Echoppe();
			$echoppeTable->insertOrUpdate($data);
		} else { // HV
			$data = array(
				'id_coffre' => $lot["id_fk_vendeur_braldun_lot"],
				'quantite_castar_coffre' => $lot["prix_1_lot"],
			);
			Zend_Loader::loadClass("Coffre");
			$coffreTable = new Coffre();
			$coffreTable->insertOrUpdate($data);
		}
	}

	private function calculTransfert($idDestination) {

		Zend_Loader::loadClass("Bral_Util_Lot");

		foreach ($this->view->lots as $lot) {
			if ($idDestination == -1 && $lot["estLotCharrette"] === true) {
				$this->calculTransfertCharrette($lot);
			} elseif ($idDestination == "charrette") {
				Bral_Util_Lot::transfertLot($lot["id_lot"], "charrette", $this->view->charrette["id_charrette"]);
			} elseif ($idDestination == "laban") {
				Bral_Util_Lot::transfertLot($lot["id_lot"], "laban", $this->view->user->id_braldun);
			} else {
				throw new Zend_Exception(get_class($this)." calculTransfert destination invalide:".$idDestination);
			}

			$this->calculDepotCastars($lot);
			$this->calculDepotMessage($lot);
		}

		if ($idDestination == "charrette") {
			Bral_Util_Poids::calculPoidsCharrette($this->view->user->id_braldun, true);
		}
	}

	private function calculDepotMessage($lot) {
		Zend_Loader::loadClass("Bral_Util_Messagerie");
		$s = "";
		if ($lot["prix_1_lot"] > 1) {
			$s = "s";
		}
		$message = "[Hôtel des Ventes]".PHP_EOL.PHP_EOL;
		$message .=  $this->view->user->prenom_braldun. " ".$this->view->user->nom_braldun;
		$message .= " (".$this->view->user->id_braldun.") a achet&eacute; le lot n°".$lot["id_lot"].PHP_EOL;
		$message .= $lot['details']. PHP_EOL. "pour ".$lot["prix_1_lot"]." castar".$s." (gain placé dans votre coffre).".PHP_EOL.PHP_EOL;
		$message .= "&Eacute;mile Claclac, gestionnaire de l'Hôtel des ventes.".PHP_EOL;
		$message .= "Inutile de répondre à ce message.";
		Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->hotel->id_braldun, $lot["id_fk_vendeur_braldun_lot"], $message, $this->view);
	}

	private function calculTransfertCharrette($lot) {
		$charrette = $lot["materiels"][0];
		$this->calculAttrapperCharrette($charrette, $lot);

		$id_type = $this->view->config->game->evenements->type->ramasser;
		$details = "[b".$this->view->user->id_braldun."] a acheté une charrette";
		//$this->setDetailsEvenement($details, $id_type);

		$details = "[b".$this->view->user->id_braldun."] a acheté la charrette n°".$charrette["id_materiel"];
		Zend_Loader::loadClass("Bral_Util_Materiel");
		Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_UTILISER_ID, $charrette["id_materiel"], $details);
	}

	private function calculAttrapperCharrette($charrette, $lot) {

		$charretteTable = new Charrette();

		$data = array (
			"id_fk_braldun_charrette" => $this->view->user->id_braldun,
			"x_charrette" => null,
			"y_charrette" => null,
			"z_charrette" => null,
		);

		$where = "id_charrette = ".$charrette["id_materiel"];
		$charretteTable->update($data, $where);
			
		Bral_Util_Lot::supprimeLot($lot["id_lot"]);

		Zend_Loader::loadClass("Bral_Util_Charrette");
		Bral_Util_Charrette::calculAmeliorationsCharrette($this->view->user->id_braldun);
	}

	function getListBoxRefresh() {
		$tab = array("box_profil", "box_laban", "box_charrette", "box_evenements");
		if ($this->view->idEchoppe != null) {
			$tab[] = "box_echoppe";
			$tab[] = "box_echoppes";
		} else {
			$tab[] = "box_hotel";
		}
		if ($this->view->lotCharrette != null) {
			$tab[] = "box_charrette";
		}
		return $tab;
	}
}