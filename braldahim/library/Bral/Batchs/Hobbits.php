<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Batchs_Hobbits extends Bral_Batchs_Batch {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hobbits - calculBatchImpl - enter -");
		$retour = null;
		
		$retour .= $this->preventionSuppression();
		$retour .= $this->suppression();
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hobbits - calculBatchImpl - exit -");
		return $retour;
	}
	
	
	private function preventionSuppression() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hobbits - preventionSuppression - enter -");
		Zend_Loader::loadClass("Bral_Util_Mail");
		
		$retour = "";
		
		$hobbitTable = new Hobbit();
		$date = date("Y-m-d H:i:s");
		$add_day = - $this->config->batchs->purge->table->hobbit->prevention->nbjours;
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);
		$hobbits = $hobbitTable->findAllBatchByDateFin($dateFin);
		
		if (count($hobbits) > 0) {
			foreach ($hobbits as $h) {
				$retour .= $this->envoiMailPrevention($h);
			}
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hobbits - preventionSuppression - exit -".$retour);
		return $retour;
	}
	
	private function envoiMailPrevention($hobbit) {
		$retour = "";
		
		$this->view->hobbit = $hobbit;
		$add_day = $this->config->batchs->purge->table->hobbit->suppression->nbjours;
		$this->view->dateSuppression = Bral_Util_ConvertDate::get_date_add_day_to_date($hobbit["date_fin_tour_hobbit"], $add_day);
		if ( $this->view->dateSuppression < date("Y-m-d H:i:s")) {
			$this->view->dateSuppression = date("Y-m-d 0:0:0");
		}
		$this->view->dateSuppression = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y', $this->view->dateSuppression);
		$this->view->urlJeu = $this->config->general->url;
		$this->view->adresseSupport = $this->config->general->adresseSupport;
				
		$contenuText = $this->view->render("batchs/hobbits/mailPreventionText.phtml");
		$contenuHtml = $this->view->render("batchs/hobbits/mailPreventionHtml.phtml");
		
		$mail = Bral_Util_Mail::getNewZendMail();
		$mail->setFrom($this->config->general->mail->from_email, $this->config->general->mail->from_nom);
		$mail->addTo($hobbit["email_hobbit"], $hobbit["prenom_hobbit"]." ".$hobbit["nom_hobbit"]);
		$mail->setSubject($this->config->mail->prevention->titre);
		$mail->setBodyText($contenuText);
		if ($this->config->general->envoi_mail_html == true) {
			$mail->setBodyHtml($contenuHtml);
		}
		
		$mail->send();
		
		$retour = "Prevention.H:".$hobbit["email_hobbit"]."(".$hobbit["id_hobbit"].") ";
		return $retour;
	}
	
	private function suppression() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hobbits - suppression - enter -");
		Zend_Loader::loadClass("Bral_Util_Mail");
		
		$retour = "";
		$nb = 0;
		
		$hobbitTable = new Hobbit();
		$date = date("Y-m-d H:i:s");
		$add_day = -$this->config->batchs->purge->table->hobbit->suppression->nbjours;
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);
		
		$hobbits = $hobbitTable->findAllBatchByDateFin($dateFin);
		$retour .= $this->calculSuppressionHobbits($hobbits);
		$nb = $nb + $hobbitTable->deleteAllBatchByDateFin($dateFin);
		
		$hobbits = $hobbitTable->findAllCompteInactif($dateFin);
		$retour .= $this->calculSuppressionHobbits($hobbits);
		$nb = $nb + $hobbitTable->deleteAllCompteInactif($dateFin);
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hobbits - suppression - exit -");
		return $retour;
	}
	
	private function calculSuppressionHobbits($hobbits) {
		$retour = "";
		if (count($hobbits) > 0) {
			foreach ($hobbits as $h) {
				$retour .= $this->envoiMailSuppression($h);
			}
		}
		return $retour;
	}
	
	private function envoiMailSuppression($hobbit) {
		$this->view->hobbit = $hobbit;
		$this->view->urlJeu = $this->config->general->url;
		$this->view->adresseSupport = $this->config->general->adresseSupport;
		$this->view->nbJours = $this->config->batchs->purge->table->hobbit->suppression->nbjours;
				
		$contenuText = $this->view->render("batchs/hobbits/mailSuppressionText.phtml");
		$contenuHtml = $this->view->render("batchs/hobbits/mailSuppressionHtml.phtml");
		
		$mail = Bral_Util_Mail::getNewZendMail();
		$mail->setFrom($this->config->general->mail->from_email, $this->config->general->mail->from_nom);
		$mail->addTo($hobbit["email_hobbit"], $hobbit["prenom_hobbit"]." ".$hobbit["nom_hobbit"]);
		$mail->setSubject($this->config->mail->suppression->titre);
		$mail->setBodyText($contenuText);
		if ($this->config->general->envoi_mail_html == true) {
			$mail->setBodyHtml($contenuHtml);
		}
		
		$mail->send();
		
		$retour = "Suppression.H:".$hobbit["email_hobbit"]."(".$hobbit["id_hobbit"].") ";
		return $retour;
	}
}