<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Controle extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - calculBatchImpl - enter -");

		$titre = "";
		$texte = "";

		//$this->supprimeSurRuine();

		$this->controleBatchs($titre, $texte);
		$this->controleMonstres($titre, $texte);
		$this->controleBralduns($titre, $texte);
		$this->envoiMail($titre, $texte);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - calculBatchImpl - exit -");
		return ;
	}

	private function controleBatchs(&$titre, &$texte) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - controleBatchs - enter -");

		$batchTable = new Batch();
		$dateDebut = date("Y-m-d H:i:s");
		$dateFin = date("Y-m-d H:i:s");
		$dateDebut = Bral_Util_ConvertDate::get_date_add_day_to_date($dateDebut, -1);
		$dateFin = Bral_Util_ConvertDate::get_date_remove_time_to_date($dateFin, "00:10:00");
		$nbOkjours = $batchTable->countByDate($dateDebut, $dateFin, Bral_Batchs_Batch::ETAT_OK);
		$nbKojours = $batchTable->countByDate($dateDebut, $dateFin, Bral_Batchs_Batch::ETAT_KO);
		$nbEnCoursjours = $batchTable->countByDate($dateDebut, $dateFin, Bral_Batchs_Batch::ETAT_EN_COURS);

		$texte .= "Debut:".$dateDebut." Fin:".$dateFin.PHP_EOL;
		$texte .= " Batchs : ".PHP_EOL;
		$texte .= $nbOkjours." OK, ".$nbKojours." KO, ".$nbEnCoursjours." EN_COURS".PHP_EOL.PHP_EOL;

		if ($nbKojours > 0) {
			$texte .=  " ------- ".PHP_EOL;
			$texte .=  $nbKojours." KO:".PHP_EOL;
			$this->getDetail($texte, Bral_Batchs_Batch::ETAT_KO, $dateDebut, $dateFin);
			$titre .=  $nbKojours." KO";
		}
		if ($nbEnCoursjours > 0) {
			$texte .=  " ------- ".PHP_EOL;
			$texte .=  $nbKojours." EN_COURS:".PHP_EOL;
			$this->getDetail($texte, Bral_Batchs_Batch::ETAT_EN_COURS, $dateDebut, $dateFin);
			$titre .=  " ".$nbEnCoursjours." EN_COURS";
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - controleBatchs - exit -");
	}

	private function controleMonstres(&$titre, &$texte) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - controleMonstres - enter -");

		Zend_Loader::loadClass("Monstre");
		$monstreTable = new Monstre();
		$solitaireDirectionHorsZone = $monstreTable->findSolitaireDirectionHorsZone();
		$nbsolitaireDirectionHorsZone = count($solitaireDirectionHorsZone);
		$texte .=  " ------- ".PHP_EOL;
		$texte .= " Monstre solitaire, direction en dehors de zone nb: ".$nbsolitaireDirectionHorsZone.PHP_EOL;
		if ($nbsolitaireDirectionHorsZone > 0) {
			$titre .= " SolitaireDirection:".$nbsolitaireDirectionHorsZone." WARN";
			foreach($solitaireDirectionHorsZone as $m) {
				$texte .=  "Monstre n°".$m["id_monstre"]." Direction x/y:".$m["x_direction_monstre"]."/".$m["y_direction_monstre"];
				$texte .= " xMin/xMax:".$m["x_min_monstre"]."/".$m["x_max_monstre"];
				$texte .= " yMin/yMax:".$m["y_min_monstre"]."/".$m["y_max_monstre"];
				$texte .= " cible:".$m["id_fk_braldun_cible_monstre"];
				$texte .=  PHP_EOL;
				$this->purgeMonstreHorsZone($m);
			}
		}

		$nueeDirectionHorsZone = $monstreTable->findNueeDirectionHorsZone();
		$nbnueeDirectionHorsZone = count($nueeDirectionHorsZone);
		$texte .=  " ------- ".PHP_EOL;
		$texte .= " Monstre de nuee, direction en dehors de zone (avec tolérance 20 cases) nb: ".$nbnueeDirectionHorsZone.PHP_EOL;
		if ($nbnueeDirectionHorsZone > 0) {
			$titre .= " monstreNueeDirection:".$nbnueeDirectionHorsZone." WARN";
			foreach($nueeDirectionHorsZone as $m) {
				$texte .=  "Groupe n°".$m["id_fk_groupe_monstre"]." Monstre n°".$m["id_monstre"]." Direction x/y:".$m["x_direction_monstre"]."/".$m["y_direction_monstre"];
				$texte .= " xMin/xMax:".$m["x_min_monstre"]."/".$m["x_max_monstre"];
				$texte .= " yMin/yMax:".$m["y_min_monstre"]."/".$m["y_max_monstre"];
				$texte .= " cible:".$m["id_fk_braldun_cible_monstre"];
				$texte .=  PHP_EOL;
				$this->purgeMonstreHorsZone($m);
			}
		}

		$solitairePositionHorsZone = $monstreTable->findSolitairePositionHorsZone();
		$nbsolitairePositionHorsZone = count($solitairePositionHorsZone);
		$texte .=  " ------- ".PHP_EOL;
		$texte .= " Monstre solitaire, Position en dehors de zone nb: ".$nbsolitairePositionHorsZone.PHP_EOL;
		if ($nbsolitairePositionHorsZone > 0) {
			$titre .= " SolitairePosition:".$nbsolitairePositionHorsZone." WARN";
			foreach($solitairePositionHorsZone as $m) {
				$texte .=  "Monstre n°".$m["id_monstre"]." Position x/y:".$m["x_monstre"]."/".$m["y_monstre"];
				$texte .= " xMin/xMax:".$m["x_min_monstre"]."/".$m["x_max_monstre"];
				$texte .= " yMin/yMax:".$m["y_min_monstre"]."/".$m["y_max_monstre"];
				$texte .= " cible:".$m["id_fk_braldun_cible_monstre"];
				$texte .=  PHP_EOL;
				$this->purgeMonstreHorsZone($m);
			}
		}

		$nueePositionHorsZone = $monstreTable->findNueePositionHorsZone();
		$nbnueePositionHorsZone = count($nueePositionHorsZone);
		$texte .=  " ------- ".PHP_EOL;
		$texte .= " Monstre de nuee, Position en dehors de zone (avec tolérance 20 cases) nb: ".$nbnueePositionHorsZone.PHP_EOL;
		if ($nbnueePositionHorsZone > 0) {
			$titre .= " monstreNueePosition:".$nbnueePositionHorsZone." WARN";
			foreach($nueePositionHorsZone as $m) {
				$texte .=  "Groupe n°".$m["id_fk_groupe_monstre"]." Monstre n°".$m["id_monstre"]." Position x/y:".$m["x_monstre"]."/".$m["y_monstre"];
				$texte .= " xMin/xMax:".$m["x_min_monstre"]."/".$m["x_max_monstre"];
				$texte .= " yMin/yMax:".$m["y_min_monstre"]."/".$m["y_max_monstre"];
				$texte .= " cible:".$m["id_fk_braldun_cible_monstre"];
				$texte .=  PHP_EOL;
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - controleMonstres - exit -");
	}


	private function supprimeSurRuine() {
		//return; // désactivé
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - supprimeSurRuine - enter -");

		Zend_Loader::loadClass("Monstre");
		$monstreTable = new Monstre();

		Zend_Loader::loadClass("Route");
		$routeTable = new Route();

		$paves = $routeTable->findByType("ruine");
		$tabPave = null;
		foreach($paves as $p) {
			$key = $p["x_route"]."-".$p["y_route"]."-".$p["z_route"];
			$tabPave[$key] = true;
		}

		for($idZone = 1; $idZone<=93; $idZone++) {
			$monstres = $monstreTable->findByIdZoneNidMinAndIdZoneNidMax($idZone, $idZone);
			Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - supprimeSurRuine - zone:".$idZone. " nb:".count($monstres));
			if (count($monstres) > 0) {
				foreach($monstres as $m) {
					$key = $m["x_monstre"]."-".$m["y_monstre"]."-".$m["z_monstre"];
					if (array_key_exists($key, $tabPave)) {
						self::purgeMonstreHorsZone($m);
					}
				}
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - supprimeSurRuine - exit -");
	}

	private function purgeMonstreHorsZone($m) {
		//return; // désactivé

		if ($m["id_fk_braldun_cible_monstre"] != null) {
			Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - purge idm(".$m["id_monstre"].") - possede une cible -");
			return;
		}
		$where = "id_monstre=".$m["id_monstre"];
		$nbJours = Bral_Util_De::get_1d2();
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), $nbJours);

		$monstreTable = new Monstre();
		$data = array(
			"date_fin_cadavre_monstre" => $dateFin,
			"est_mort_monstre" => "oui",
			"id_fk_groupe_monstre" => null,
		);
		$monstreTable->update($data, $where);
		$details = "[m".$m["id_monstre"]."] est mort de vieillesse.";

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - purge idm(".$m["id_monstre"].") !");

		Zend_Loader::loadClass('Bral_Util_Evenement');
		Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $m["id_monstre"], $this->config->game->evenements->type->killmonstre, $details, "", $m["niveau_monstre"], $this->view);
	}

	private function controleBralduns(&$titre, &$texte) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - controleInscriptions - enter -");

		$texte .=  PHP_EOL." ------- ".PHP_EOL;
		$texte .=  "Bralduns : ".PHP_EOL;

		$batchTable = new Batch();
		$dateMaintenant = date("Y-m-d H:i:s");
		$dateFin = date("Y-m-d H:i:s");
		$braldunTable = new Braldun();

		// Inscription remonter le nombre d'inscrits et actif dans la semaine
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateMaintenant, -7);
		$nbBralduns = $braldunTable->countAllCompteActifInactif($dateFin, true);
		$texte .= " Nouvelles inscriptions actives depuis 7j : ".$nbBralduns." => ".$dateFin. " à ".$dateMaintenant.PHP_EOL;

		// bralduns non actifs, avec date inscription, depuis 2 j
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateMaintenant, -7);
		$nbBralduns = $braldunTable->countAllCompteActifInactif($dateFin, false);
		$texte .= " Nouvelles inscriptions non validées depuis 2j : ".$nbBralduns." => ".$dateFin. " à ".$dateMaintenant.PHP_EOL;

		// bralduns ayant joué depuis 24 et 48h
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateMaintenant, -1);
		$nbBralduns = $braldunTable->countAllBatchByDateFin($dateFin, true);
		$texte .= " Bralduns ayant joué depuis 24h : ".$nbBralduns." => ".$dateFin. " à ".$dateMaintenant.PHP_EOL;

		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateMaintenant, -2);
		$nbBralduns = $braldunTable->countAllBatchByDateFin($dateFin, true);
		$texte .= " Bralduns ayant joué depuis 48h : ".$nbBralduns." => ".$dateFin. " à ".$dateMaintenant.PHP_EOL;

		// nb Bralduns en hibernation
		$nbBralduns = $braldunTable->countAllHibernation();
		$texte .= " Bralduns en hibernation : ".$nbBralduns.PHP_EOL;

		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateMaintenant, -7);
		$nbBralduns = $braldunTable->countAllHibernation($dateFin);
		$texte .= " Bralduns en hibernation depuis 7j: ".$nbBralduns." => ".$dateFin. " à ".$dateMaintenant.PHP_EOL;

		// nb Bralduns actifs, hors pnj, hibernation, non actifs, desactive
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateMaintenant, -5);
		$nbBralduns = $braldunTable->countAllCompteActif($dateFin);
		$texte .= " Bralduns actifs, hors hibernation depuis 5j: ".$nbBralduns." => ".$dateFin. " à ".$dateMaintenant.PHP_EOL;

		// Bralduns desactives
		$bralduns = $braldunTable->findAllCompteDesactives();
		$nbBraldun = count($bralduns);
		$texte .= " Bralduns désactivés : ".$nbBraldun.PHP_EOL;

		if ($nbBralduns > 0) {
			foreach($bralduns as $h) {
				$texte .= "    Braldûn n°".$h["id_braldun"]. " ".$h["prenom_braldun"]." ".$h["nom_braldun"]." ".$h["email_braldun"];
			}
		}

		$texte .=  PHP_EOL." ------- ".PHP_EOL;


		$braldunsNonActifs = $braldunTable->findAllCompteInactif();
		$texte .= " Bralduns Compte non actif : ".count($braldunsNonActifs). " Details:".PHP_EOL." ------- ".PHP_EOL;
		Zend_Loader::loadClass("Bral_Util_Inscription");
		if ($braldunsNonActifs != null && count($braldunsNonActifs) > 0) {
			foreach($braldunsNonActifs as $h) {
				$texte .= "Braldun n°".$h["id_braldun"]. " ".$h["prenom_braldun"]." ".$h["nom_braldun"]." creation:".$h["date_creation_braldun"].PHP_EOL;
				$texte .= "Mail : ".$h["email_braldun"].PHP_EOL;
				$texte .= "Vous n'avez peut-être pas reçu le mail de validation d'inscription à Braldahim. Le voici ci-dessous :".PHP_EOL.PHP_EOL;
				$this->view->prenom_braldun = $h["prenom_braldun"];
				$this->view->id_braldun = $h["id_braldun"];
				$this->view->urlValidation = Bral_Util_Inscription::getLienValidation($h["id_braldun"], $h["email_braldun"], md5($h["prenom_braldun"]), $h["password_hash_braldun"]);
				$this->view->adresseSupport = $this->config->general->adresseSupport;

				$contenuText = $this->view->render("inscription/mailText.phtml");

				$texte .= $contenuText;
				$texte .= PHP_EOL.PHP_EOL." --------".PHP_EOL.PHP_EOL;
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - controleInscriptions - exit -");
	}

	private function envoiMail($titre, $texte) {
		if ($titre == "") {
			$titre = "OK";
		}

		$config = Zend_Registry::get('config');
		Zend_Loader::loadClass("Bral_Util_Mail");
		$mail = Bral_Util_Mail::getNewZendMail();

		$mail->setFrom($config->general->mail->administration->from, $config->general->mail->administration->nom);
		$mail->addTo($config->general->mail->administration->from, $config->general->mail->administration->nom);
		$mail->setSubject("[Braldahim-Controle] ".$titre);
		$mail->setBodyText("--------> ".$texte);
		$mail->send();
	}

	private function getDetail(&$texte, $etat, $dateDebut, $dateFin) {
		$batchTable = new Batch();
		$batchs = $batchTable->findByDate($dateDebut, $dateFin, $etat);
		foreach($batchs as $b) {
			$texte .= "etat:".$b["etat_batch"]." id:".$b["id_batch"]. " type:".$b["type_batch"];
			$texte .= " debut:".$b["date_debut_batch"]. " fin:".$b["date_fin_batch"]. " message:".$b["message_batch"].PHP_EOL;
		}
		$texte .= PHP_EOL.PHP_EOL;
	}
}