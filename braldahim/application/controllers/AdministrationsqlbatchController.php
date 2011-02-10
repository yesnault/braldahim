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

		/* $this->prepareHashPassword();
		 * $this->messageJeanBernard();
		 * $this->messageJeanBernardRappel();
		 * $this->jourYuleAction();
		 * $this->correctionCoffre();
		 $this->ajoutCompetence();
		 $this->eauCreation(-10);
		 $this->eauCreation(-11);
		 $this->eauCreation(-12);
		 $this->eauCreation(-13);*/
		$this->render();
	}

	function prepareHashPassword() {

		Zend_Loader::loadClass('Bral_Util_Hash');

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->fetchall();

		foreach($bralduns as $b) {
				
			$salt = Bral_Util_Hash::getSalt();
			$passwordHash = Bral_Util_Hash::getHashString($salt, $b["password_braldun"]);
				
			$data = array(
				'password_salt_braldun' => $salt, 
				'password_hash_braldun' => $passwordHash
			);
				
			$where = 'id_braldun = '.$b['id_braldun'];
			$braldunTable->update($data, $where);
		}
	}

	function correctionCoffre() {

		Zend_Loader::loadClass("Communaute");

		Zend_Loader::loadClass("Coffre");
		Zend_Loader::loadClass("CoffreEquipement");
		Zend_Loader::loadClass("CoffreMateriel");
		Zend_Loader::loadClass("CoffreMinerai");
		Zend_Loader::loadClass("CoffrePartieplante");
		Zend_Loader::loadClass("CoffreAliment");
		Zend_Loader::loadClass("CoffreGraine");
		Zend_Loader::loadClass("CoffreIngredient");
		Zend_Loader::loadClass("CoffreMunition");
		Zend_Loader::loadClass("CoffrePotion");
		Zend_Loader::loadClass("CoffreRune");
		Zend_Loader::loadClass("CoffreTabac");

		$coffreTable = new Coffre();

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->fetchall();

		// creation d'un coffre pour tous les Braldûns
		foreach($bralduns as $b) {
			$idBraldun = $b["id_braldun"];
			$coffre = $coffreTable->findByIdBraldun($idBraldun);
			if ($coffre == null || count($coffre) == 0) {
				$data = array(
					"id_fk_braldun_coffre" => $idBraldun,
				);
				echo "Creation coffre pour le braldun $idBraldun <br>";
				$coffreTable->insert($data);
			}
		}

		$communauteTable = new Communaute();
		$communautes = $communauteTable->fetchall();

		// creation d'un coffre pour toutes les Communautes
		foreach($communautes as $c) {
			$idCommunaute = $c["id_communaute"];
			$coffre = $coffreTable->findByIdCommunaute($idCommunaute);
			if ($coffre == null || count($coffre) == 0) {
				$data = array(
					"id_fk_communaute_coffre" => $idCommunaute,
				);
				echo "Creation coffre pour la communaute $idCommunaute <br>";
				$coffreTable->insert($data);
			}
		}

		$coffres = $coffreTable->fetchall();

		// positionnement des FK
		foreach($coffres as $c) {
			$idCoffre = $c["id_coffre"];
			$idBraldun = $c["id_fk_braldun_coffre"];

			if ($idBraldun != null) {
				$table = new CoffreEquipement(); $where = "id_fk_braldun_coffre_equipement = ".$idBraldun; $data = array("id_fk_coffre_coffre_equipement" => $idCoffre); $table->update($data, $where);
				$table = new CoffreMateriel(); $where = "id_fk_braldun_coffre_materiel = ".$idBraldun; $data = array("id_fk_coffre_coffre_materiel" => $idCoffre); $table->update($data, $where);
				$table = new CoffreMinerai(); $where = "id_fk_braldun_coffre_minerai = ".$idBraldun; $data = array("id_fk_coffre_coffre_minerai" => $idCoffre); $table->update($data, $where);
				$table = new CoffrePartieplante(); $where = "id_fk_braldun_coffre_partieplante = ".$idBraldun; $data = array("id_fk_coffre_coffre_partieplante" => $idCoffre); $table->update($data, $where);
				$table = new CoffreAliment(); $where = "id_fk_braldun_coffre_aliment = ".$idBraldun; $data = array("id_fk_coffre_coffre_aliment" => $idCoffre); $table->update($data, $where);
				$table = new CoffreGraine(); $where = "id_fk_braldun_coffre_graine = ".$idBraldun; $data = array("id_fk_coffre_coffre_graine" => $idCoffre); $table->update($data, $where);
				$table = new CoffreIngredient(); $where = "id_fk_braldun_coffre_ingredient = ".$idBraldun; $data = array("id_fk_coffre_coffre_ingredient" => $idCoffre); $table->update($data, $where);
				$table = new CoffreMunition(); $where = "id_fk_braldun_coffre_munition = ".$idBraldun; $data = array("id_fk_coffre_coffre_munition" => $idCoffre); $table->update($data, $where);
				$table = new CoffrePotion(); $where = "id_fk_braldun_coffre_potion = ".$idBraldun; $data = array("id_fk_coffre_coffre_potion" => $idCoffre); $table->update($data, $where);
				$table = new CoffreRune(); $where = "id_fk_braldun_coffre_rune = ".$idBraldun; $data = array("id_fk_coffre_coffre_rune" => $idCoffre); $table->update($data, $where);
				$table = new CoffreTabac(); $where = "id_fk_braldun_coffre_tabac = ".$idBraldun; $data = array("id_fk_coffre_coffre_tabac" => $idCoffre); $table->update($data, $where);
			} else {
				// Communaute
			}
		}

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

		$this->messageBiereDuMilieuAction();
	}

	private function messageBiereDuMilieuAction() {
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

	private function messageJeanBernard() {
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->fetchall("est_pnj_braldun = 'non'");
		Zend_Loader::loadClass("Bral_Util_Messagerie");

		foreach ($bralduns as $h) {
			$detailsBot = "";
			$detailsBot .= "Bien le bonjour à Tous, Amis Braldûns !".PHP_EOL.PHP_EOL;

			$detailsBot .= "[justify]Deux semaines après son lancement, mon grand concours des Troubadours peut s'enorgueillir de ";
			$detailsBot .= " compter trois participants déclarés. Afin de relancer votre imagination, et pour encourager ";
			$detailsBot .= " votre esprit créatif, je reviens vers vous. ".PHP_EOL;
			$detailsBot .= "Merci aux personnes désireuses de participer de se manifester, publiquement ou par message privé, et de me communiquer leurs écrits via MP, avec un titre. [/justify]".PHP_EOL;

			$detailsBot .= "[left]Je vous rappelle les règles : ".PHP_EOL;
			$detailsBot .= "  -  4500 caractères maximum (espaces compris).".PHP_EOL;
			$detailsBot .= "  -  Délai : 1 mois, du 10 janvier au 10 février".PHP_EOL;
			$detailsBot .= "  -  Thème : Votre enfance braldûne".PHP_EOL;
			$detailsBot .= "  -  Nom du PNJ à qui envoyer le texte : Jean-Bernard Dent-sur-Pivot (566)".PHP_EOL;
			$detailsBot .= "  -  Les textes doivent rester anonymes avant le vote, merci de les envoyer par MP et de ne rien ";
			$detailsBot .= "publier sur le forum. Pas de pub ni d'indice de la part d'un auteur (ie : 'Votez pour moi'), le ";
			$detailsBot .= "texte serait dans ce cas retiré du concours. Néanmoins, les participants, ainsi que les amateurs ";
			$detailsBot .= "de lecture peuvent faire des commentaires au sujet du concours ou mentionner qu'ils participent.[/left]".PHP_EOL.PHP_EOL;

			$detailsBot .= "[url=http://forum.braldahim.com/viewtopic.php?f=9&t=619]Topic sur le forum.[/url]".PHP_EOL;

			$detailsBot .= "[left]Bien à vous,[/left]".PHP_EOL;

			$message = $detailsBot.PHP_EOL." Jean-Bernard Dent-sur-Pivot".PHP_EOL."Vous pouvez répondre à ce message !";

			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->jeanbernard->id_braldun, $h["id_braldun"], $message, $this->view);
		}
	}

	private function messageJeanBernardRappel() {
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->fetchall("est_pnj_braldun = 'non'");
		Zend_Loader::loadClass("Bral_Util_Messagerie");

		foreach ($bralduns as $h) {
			$detailsBot = "";
			$detailsBot .= "Bonsoir à tous !          ".PHP_EOL.PHP_EOL;

			$detailsBot .= "[justify]Il ne vous reste plus qu'une journée pour me faire parvenir vos textes pour le Concours des Troubadours. [/justify]";
			$detailsBot .= PHP_EOL;

			$detailsBot .= "[justify]Merci à ceux qui ont fait acte de candidature de m'envoyer leurs œuvres, si d'autres Braldûns souhaitent participer, leurs textes seront les bienvenus.[/justify]";
			$detailsBot .= PHP_EOL;

			$detailsBot .= "[left]Très cordialement,[/left]".PHP_EOL;

			$message = $detailsBot.PHP_EOL." Jean-Bernard Dent-sur-Pivot".PHP_EOL."Vous pouvez répondre à ce message !";

			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->jeanbernard->id_braldun, $h["id_braldun"], $message, $this->view);
		}
	}

	// le 31 décembre
	function jourYuleAction() {

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

			$idTypeAliment = TypeAliment::ID_TYPE_STOUT;
			$idEffetBraldun = Bral_Util_Effets::ajouteEtAppliqueEffetBraldun(null, Bral_Util_Effets::CARACT_STOUT, Bral_Util_Effets::TYPE_BONUS, Bral_Util_De::get_1d3(), 0, 'Lovely day for a Special Yule Stout !');

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

		$this->messageJourYuleAction();
		echo "FIN jourYuleAction";
	}

	private function messageJourYuleAction() {
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->fetchall("est_pnj_braldun = 'non'");
		Zend_Loader::loadClass("Bral_Util_Messagerie");

		foreach ($bralduns as $h) {
			$detailsBot = "Oyez Braldûns !".PHP_EOL.PHP_EOL."C'est aujourd'hui Yule !";
			$detailsBot .= PHP_EOL."Je vous invite à boire un coup pour fêter cette fin d'année et la nouvelle année qui commence.".PHP_EOL.PHP_EOL;
			$detailsBot .= "Hum, je vois que vous avez déjà mangé correctement. Jetez-donc un oeil à votre laban je crois qu'il y a une surprise !".PHP_EOL.PHP_EOL;

			$detailsBot .= "[url=http://forum.braldahim.com/viewtopic.php?f=9&t=588#p5859]Retrouvez plus d'informations sur le forum.[/url]".PHP_EOL.PHP_EOL;

			$detailsBot .= "A la votre,";

			$message = $detailsBot.PHP_EOL.PHP_EOL." Huguette Ptipieds".PHP_EOL."Inutile de répondre à ce message.";

			Bral_Util_Messagerie::envoiMessageAutomatique($this->view->config->game->pnj->huguette->id_braldun, $h["id_braldun"], $message, $this->view);
		}
			
		echo "FIN messageJourYuleAction";
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
