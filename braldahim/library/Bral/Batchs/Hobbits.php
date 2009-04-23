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

		$retour .= $this->suppression();
		$retour .= $this->preventionSuppression();

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

		if ($this->config->mail->envoi->automatique->actif == true) {
			$mail = Bral_Util_Mail::getNewZendMail();
			$mail->setFrom($this->config->general->mail->from_email, $this->config->general->mail->from_nom);
			$mail->addTo($hobbit["email_hobbit"], $hobbit["prenom_hobbit"]." ".$hobbit["nom_hobbit"]);
			$mail->setSubject($this->config->mail->prevention->titre);
			$mail->setBodyText($contenuText);
			if ($this->config->general->envoi_mail_html == true) {
				$mail->setBodyHtml($contenuHtml);
			}

			$mail->send();
			Bral_Util_Log::mail()->trace("Bral_Batchs_Hobbits - envoiMailPrevention -".$hobbit["email_hobbit"]." ".$hobbit["prenom_hobbit"]." ".$hobbit["nom_hobbit"]);
		}

		$retour = "Prevention.H:".$hobbit["email_hobbit"]."(".$hobbit["id_hobbit"].") ";
		return $retour;
	}

	private function suppression() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hobbits - suppression - enter -");
		Zend_Loader::loadClass("Bral_Util_Mail");
		Zend_Loader::loadClass("AncienHobbit");
		Zend_Loader::loadClass("HobbitsMetiers");
		Zend_Loader::loadClass("HobbitsTitres");

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
				$this->copieVersAncien($h);
			}
		}
		return $retour;
	}

	private function copieVersAncien($hobbit) {

		$hobbitsMetiersTable = new HobbitsMetiers();
		$hobbitsMetierRowset = $hobbitsMetiersTable->findMetiersByHobbitId($hobbit["id_hobbit"]);
		$metiers = "";
		if ($hobbitsMetierRowset != null) {
			foreach($hobbitsMetierRowset as $m) {
				if ($hobbit["sexe_hobbit"] == 'feminin') {
					$metiers .= $m["nom_feminin_metier"];
				} else {
					$metiers .= $m["nom_masculin_metier"];
				}
				$metiers .= ", ";
			}
			if ($metiers != "") {
				$metiers = substr($metiers, 0, strlen($metiers) -2);
			}
		}

		$hobbitsTitresTable = new HobbitsTitres();
		$hobbitsTitreRowset = $hobbitsTitresTable->findTitresByHobbitId($hobbit["id_hobbit"]);
		$titres = "";
		if ($hobbitsTitreRowset != null) {
			foreach($hobbitsTitreRowset as $t) {
				if ($hobbit["sexe_hobbit"] == 'feminin') {
					$titres .= $t["nom_feminin_type_titre"];
				} else {
					$titres .= $t["nom_masculin_type_titre"];
				}
				$titres .= ", ";
			}
			if ($titres != "") {
				$titres = substr($titres, 0, mb_strlen($titres) -2);
			}
		}

		$ancienHobbitTable = new AncienHobbit();
		$data = array(
			"id_hobbit_ancien_hobbit" => $hobbit["id_hobbit"],
			"nom_ancien_hobbit" =>  $hobbit["nom_hobbit"],
			"prenom_ancien_hobbit" =>  $hobbit["prenom_hobbit"],
			"id_fk_nom_initial_ancien_hobbit" =>  $hobbit["id_fk_nom_initial_hobbit"],
			"email_ancien_hobbit" =>  $hobbit["email_hobbit"],
			"sexe_ancien_hobbit" =>  $hobbit["sexe_hobbit"],
			"niveau_ancien_hobbit" =>  $hobbit["niveau_hobbit"],
			"nb_ko_ancien_hobbit" =>  $hobbit["nb_ko_hobbit"],
			"nb_hobbit_ko_ancien_hobbit" =>  $hobbit["nb_hobbit_ko_hobbit"],
			"nb_plaque_ancien_hobbit" =>  $hobbit["nb_plaque_hobbit"],
			"nb_hobbit_plaquage_ancien_hobbit" =>  $hobbit["nb_hobbit_plaquage_hobbit"],
			"nb_monstre_kill_ancien_hobbit" =>  $hobbit["nb_monstre_kill_hobbit"],
			"id_fk_mere_ancien_hobbit" =>  $hobbit["id_fk_mere_hobbit"],
			"id_fk_pere_ancien_hobbit" =>  $hobbit["id_fk_pere_hobbit"],
			"date_creation_ancien_hobbit" => $hobbit["date_creation_hobbit"],
			"metiers_ancien_hobbit" => $metiers,
			"titres_ancien_hobbit" => $titres,
		);

		$ancienHobbitTable->insert($data);
		
		Zend_Loader::loadClass("Couple");
		$coupleTable = new Couple();
		$data = array('est_valide_couple' => 'oui');
		
		if ($hobbit["sexe_hobbit"] == "masculin") {
			$where = 'id_fk_m_hobbit_couple = '.$hobbit["id_hobbit"];
		} else {
			$where = 'id_fk_f_hobbit_couple = '.$hobbit["id_hobbit"];
		}
		$coupleTable->update($data, $where);
		
	}

	private function envoiMailSuppression($hobbit) {
		$retour = "";

		if ($this->config->mail->envoi->automatique->actif == true) {
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
			Bral_Util_Log::mail()->trace("Bral_Batchs_Hobbits - envoiMailSuppression -".$hobbit["email_hobbit"]." ".$hobbit["prenom_hobbit"]." ".$hobbit["nom_hobbit"]);
		}
		$retour = "Suppression.H:".$hobbit["email_hobbit"]."(".$hobbit["id_hobbit"].") ";
		return $retour;
	}
}