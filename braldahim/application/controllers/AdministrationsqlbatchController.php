<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class AdministrationsqlbatchController extends Zend_Controller_Action {

	function init() {
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_redirect('/');
		}

		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlAdmin();

		$this->initView();
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->config = Zend_Registry::get('config');
	}

	function indexAction() {
		$this->ajoutCompetence();
	/*	$this->eauCreation(-10);
		$this->eauCreation(-11);
		$this->eauCreation(-12);
		$this->eauCreation(-13);*/
		$this->render();
	}

	function ajoutCompetence() {
		
		$idMetier = 1;
		$idCompetence = 72;
		
		Zend_Loader::loadClass("Competence");
		$competenceTable = new Competence();
		$data = array(
			"id_competence" => "72",
            "nom_systeme_competence" => "creuser",
            "nom_competence" => "Creuser un tunnel",
            "description_competence" => "Description Creuser",
            "niveau_requis_competence" => "0",
            "niveau_sagesse_requis_competence" => "0",
            "pi_cout_competence" => "0",
            "px_gain_competence" => "1",
            "balance_faim_competence" => "-3",
            "pourcentage_max_competence" => "90",
            "pourcentage_init_competence" => "10",
            "pa_utilisation_competence" => "3",
            "pa_manquee_competence" => "2",
            "type_competence" => "metier",
            "id_fk_metier_competence" => "1",
            "id_fk_type_tabac_competence" => "1",
            "ordre_competence" => "0",
		);
		$competenceTable->insert($data);
		
		Zend_Loader::loadClass("BraldunsCompetences");
		$braldunsCompetencesTable = new BraldunsCompetences();
		
		Zend_Loader::loadClass("BraldunsMetiers");
		$braldunsMetierTable = new BraldunsMetiers();
		$bralduns = $braldunsMetierTable->fetchall("id_fk_metier_hmetier=".$idMetier);
		
		foreach($bralduns as $b) {
			$data = array(
			'id_fk_braldun_hcomp' => $b["id_fk_braldun_hmetier"],
			'id_fk_competence_hcomp' => $idCompetence,
			);
			if ($b["id_fk_braldun_hmetier"] == 8) {
				$data["pourcentage_hcomp"] = 80;
			}
			$braldunsCompetencesTable->insert($data);
		}
		
	}
	
	function biereDuMilieuAction() {

		return;

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->fetchall("est_pnj_braldun = 'non'");

		Zend_Loader::loadClass("ElementAliment");
		$elementAlimentTable = new ElementAliment();

		Zend_Loader::loadClass("IdsAliment");
		$idsAliment = new IdsAliment();

		Zend_Loader::loadClass('Aliment');
		$alimentTable = new Aliment();

		Zend_Loader::loadClass("LabanAliment");
		$labanTable = new LabanAliment();

		Zend_Loader::loadClass("TypeAliment");
		Zend_Loader::loadClass("Bral_Util_Effets");
			
		foreach ($bralduns as $h) {

			$idAliment = $idsAliment->prepareNext();

			$idEffetBraldun = null;

			$idTypeAliment = TypeAliment::ID_TYPE_JOUR_MILIEU;
			$idEffetBraldun = Bral_Util_Effets::ajouteEtAppliqueEffetBraldun(null, Bral_Util_Effets::CARACT_BBDF, Bral_Util_Effets::TYPE_BONUS, 100, 5, 'Je bois, je mincis et ça se voit. Ah non ... tant pis !');

			$data = array(
				"id_aliment" => $idAliment,
				"id_fk_type_aliment" => $idTypeAliment,
				"id_fk_type_qualite_aliment" => 2,
				"bbdf_aliment" => 0,
				"id_fk_effet_braldun_aliment" => $idEffetBraldun,
			);
			$alimentTable->insert($data);

			$data = null;
			$data["id_fk_braldun_laban_aliment"] = $h["id_braldun"];
			$data['id_laban_aliment'] = $idAliment;
			$labanTable->insert($data);

			$data = null;
			$data["balance_faim_braldun"] = 100;
			$where = "id_braldun=".$h["id_braldun"];
			$braldunTable->update($data, $where);

		}

		//$this->message();
	}

	private function message() {
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->fetchall("est_pnj_braldun = 'non'");
		Zend_Loader::loadClass("Bral_Util_Messagerie");

		foreach ($bralduns as $h) {
			$detailsBot = "Oyez Braldûns !".PHP_EOL.PHP_EOL."C'est aujourd'hui la fête du jour du milieu !";
			$detailsBot .= PHP_EOL."Je vous invite à boire un coup pour fêter la moitié de l'année.".PHP_EOL.PHP_EOL;
			$detailsBot .= "Jetez un oeil à votre laban je crois qu'il y a une surprise !".PHP_EOL.PHP_EOL;
			$detailsBot .= "A la votre,";

			$message = $detailsBot.PHP_EOL.PHP_EOL." Huguette Ptipieds".PHP_EOL."Inutile de répondre à ce message.";

			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->huguette->id_braldun, $h["id_braldun"], $message, $this->view);
		}
			
	}

	function md5Action() {
		$this->render();
	}

	function eauCreation($z) {
		$nom_image="niveau-10.png";
		$image=imagecreatefrompng($nom_image);
		
		Zend_Loader::loadClass("Eau");
		$eauTable = new Eau();
		//$eauTable->delete(false);

		Zend_Loader::loadClass("Route");
		$routeTable = new Route();

		Zend_Loader::loadClass("Bosquet");
		$bosquetTable = new Bosquet();

		Zend_Loader::loadClass("Buisson");
		$buissonTable = new Buisson();

		Zend_Loader::loadClass("Monstre");
		$monstreTable = new Monstre();

		Zend_Loader::loadClass("Plante");
		$planteTable = new Plante();

		Zend_Loader::loadClass("Filon");
		$filonTable = new Filon();

		Zend_Loader::loadClass("Tunnel");
		$tunnelTable = new Tunnel();

		for ($x = 0; $x < 1600; $x++) {
			for ($y = 0; $y <1000; $y++) {
				$couleur=imagecolorat($image,$x,$y);
				$couleursRVB=imagecolorsforindex($image,$couleur);
				$typeEau = null;

				$xEau = $x - 800;
				$yEau = 500 - $y;

				if ($couleursRVB["red"] == 255 && $couleursRVB["green"] == 0 && $couleursRVB["blue"] == 0) { // rouge, fleuve
					printf("fleuve : x:%d, y:%d RVB: (%d, %d, %d)<br>".PHP_EOL, $xEau, $yEau, $couleursRVB["red"], $couleursRVB["green"],$couleursRVB["blue"]);
					$typeEau = "profonde";
				} elseif ($couleursRVB["red"] == 0 && $couleursRVB["green"] == 0 && $couleursRVB["blue"] == 255) { // bleu, lac
					printf("lac : x:%d, y:%d RVB: (%d, %d, %d)<br>".PHP_EOL, $xEau, $yEau, $couleursRVB["red"], $couleursRVB["green"],$couleursRVB["blue"]);
					$typeEau = "lac";
				} elseif ($couleursRVB["red"] == 255 && $couleursRVB["green"] == 255 && $couleursRVB["blue"] == 0) { // jaune, mer
					printf("lac : x:%d, y:%d RVB: (%d, %d, %d)<br>".PHP_EOL, $xEau, $yEau, $couleursRVB["red"], $couleursRVB["green"],$couleursRVB["blue"]);
					$typeEau = "mer";
				} elseif ($couleursRVB["red"] == 0 && $couleursRVB["green"] == 255 && $couleursRVB["blue"] == 255) { // peuprofonde
					printf("lac : x:%d, y:%d RVB: (%d, %d, %d)<br>".PHP_EOL, $xEau, $yEau, $couleursRVB["red"], $couleursRVB["green"],$couleursRVB["blue"]);
					$typeEau = "peuprofonde";
				} else {
					//printf("x:%d, y:%d RVB: (%d, %d, %d)<br>".PHP_EOL, $x, $y, $couleursRVB["red"], $couleursRVB["green"],$couleursRVB["blue"]);
				}

				if ($typeEau != null) {
					$data = array(
						"x_eau" => $xEau, 	 	 	
						"y_eau" => $yEau,
						"z_eau" => $z,		 	 	 	 	 	 	
						"type_eau" => $typeEau,	
					);
					$eauTable->insert($data);
					
					$dataTunnel = array(
							"x_tunnel" => $xEau,
							"y_tunnel" => $xEau,
							"z_tunnel" => $xEau,
							"date_tunnel" => date("Y-m-d H:i:s"),
							"est_eboulable_tunnel" => 'non',
					);

					$tunnelTable->insert($dataTunnel);

					$this->deleteEltEau($xEau, $yEau, $routeTable, $bosquetTable, $buissonTable, $monstreTable, $planteTable, $filonTable, $z);
				}
			}
		}

		$where = "type_eau not like 'peuprofonde' and z_eau=".$z;
		$eaux = $eauTable->fetchAll($where);

		foreach($eaux as $e) {

			for ($x = -1; $x <= 1; $x++) {
				for ($y = -1; $y <=1; $y++) {
					$xEau = $e["x_eau"] + $x;
					$yEau = $e["y_eau"] + $y;
					$eau = $eauTable->findByCase($xEau, $yEau, $z);

					if ($eau == null &&
					$xEau > -800 && $xEau < 800 &&
					$yEau > -500 && $yEau < 500
					) {
						$data = array(
							"x_eau" => $xEau,
							"y_eau" => $yEau,
							"z_eau" => $z,		 	 	 	 	 	 	
							"type_eau" => "peuprofonde",	
						);
						$eauTable->insert($data);

						$dataTunnel = array(
							"x_tunnel" => $xEau,
							"y_tunnel" => $xEau,
							"z_tunnel" => $xEau,
							"date_tunnel" => date("Y-m-d H:i:s"),
							"est_eboulable_tunnel" => 'non',
						);

						$tunnelTable->insert($dataTunnel);

					//	$this->deleteEltEau($xEau, $yEau, $routeTable, $bosquetTable, $buissonTable, $monstreTable, $planteTable, $filonTable, $z);
					}
				}
			}
		}
	}

	private function deleteEltEau($xEau, $yEau, $routeTable, $bosquetTable, $buissonTable, $monstreTable, $planteTable, $filonTable, $z) {

		$where = "x_bosquet=".$xEau." and y_bosquet=".$yEau. " and z_bosquet=".$z; // suppression des bosquets
		$bosquetTable->delete($where);

		$where = "x_buisson=".$xEau." and y_buisson=".$yEau. " and z_buisson=".$z; // suppression des buissons
		$buissonTable->delete($where);

		$where = "x_monstre=".$xEau." and y_monstre=".$yEau. " and z_monstre=".$z; // suppression des monstres
		$monstreTable->delete($where);

		$where = "x_plante=".$xEau." and y_plante=".$yEau. " and z_plante=".$z; // suppression des plantes
		$planteTable->delete($where);

		$where = "x_filon=".$xEau." and y_filon=".$yEau. " and z_filon=".$z; // suppression des filons
		$filonTable->delete($where);

	}

}
