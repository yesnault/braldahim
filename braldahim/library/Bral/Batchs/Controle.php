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
		$this->controleBatchs();
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - calculBatchImpl - exit -");
		return ;
	}

	public function controleBatchs() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - controleBatchs - enter -");

		$batchTable = new Batch();
		$dateDebut = date("Y-m-d H:i:s");
		$dateFin = date("Y-m-d H:i:s");
		$dateDebut = Bral_Util_ConvertDate::get_date_add_day_to_date($dateDebut, -1);
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateFin, 0);
		$nbOkjours = $batchTable->countByDate($dateDebut, $dateFin, Bral_Batchs_Batch::ETAT_OK);
		$nbKojours = $batchTable->countByDate($dateDebut, $dateFin, Bral_Batchs_Batch::ETAT_KO);
		$nbEnCoursjours = $batchTable->countByDate($dateDebut, $dateFin, Bral_Batchs_Batch::ETAT_EN_COURS);

		$texte = "Debut:".$dateDebut." Fin:".$dateFin.PHP_EOL;
		$texte .= " Batchs : ".PHP_EOL;
		$texte .= $nbOkjours." OK, ".$nbKojours." KO, ".$nbEnCoursjours." EN_COURS".PHP_EOL.PHP_EOL;
		$titre = "";
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
			$titre .=  $nbEnCoursjours." EN_COURS";
		}
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

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Controle - controleBatchs - exit -");
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