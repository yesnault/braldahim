<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Batchs_Controle extends Bral_Batchs_Boutique {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - calculBatchImpl - enter -");

		$titre = "";
		$texte = "";

		$this->controleBatchs($titre, $texte);
		$this->controleMonstres($titre, $texte);
		$this->envoiMail($titre, $texte);

		echo $titre;
		echo $texte;

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - calculBatchImpl - exit -");
		return ;
	}

	public function controleBatchs(&$titre, &$texte) {
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

	public function controleMonstres(&$titre, &$texte) {
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
				$texte .= " cible:".$m["id_fk_hobbit_cible_monstre"];
				$texte .=  PHP_EOL;
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
				$texte .= " cible:".$m["id_fk_hobbit_cible_monstre"];
				$texte .=  PHP_EOL;
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
				$texte .= " cible:".$m["id_fk_hobbit_cible_monstre"];
				$texte .=  PHP_EOL;
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
				$texte .= " cible:".$m["id_fk_hobbit_cible_monstre"];
				$texte .=  PHP_EOL;
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - controleMonstres - exit -");
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