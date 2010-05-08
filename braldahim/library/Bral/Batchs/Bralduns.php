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
class Bral_Batchs_Bralduns extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculBatchImpl - enter -");
		$retour = null;

		$retour .= $this->calculPointsDistinctions();
		$retour .= $this->suppression();
		$retour .= $this->preventionSuppression();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculBatchImpl - exit -");
		return $retour;
	}

	private function calculPointsDistinctions() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculPointsDistinctions - enter -");

		$retour = "";
		$braldunTable = new Braldun();
		$bralduns = $braldunTable->fetchall();

		Zend_Loader::loadClass("BraldunsDistinction");
		$braldunsDistinctionTable = new BraldunsDistinction();

		if (count($bralduns) > 0) {
			foreach ($bralduns as $h) {
				$points = 0;
				$braldunsDistinctionRowset = $braldunsDistinctionTable->findDistinctionsByBraldunId($h["id_braldun"]);

				if (count($braldunsDistinctionRowset) > 0) {
					foreach($braldunsDistinctionRowset as $t) {
						$points = $points + $t["points_type_distinction"];
					}
				}

				$data = array('points_distinctions_braldun' => $points);
				$where = "id_braldun=".intval($h["id_braldun"]);
				$braldunTable->update($data, $where);
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculPointsDistinctions - exit -".$retour);
		return $retour;
	}

	private function preventionSuppression() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - preventionSuppression - enter -");
		Zend_Loader::loadClass("Bral_Util_Mail");

		$retour = "";

		$braldunTable = new Braldun();
		$date = date("Y-m-d H:i:s");
		$add_day = - ($this->config->batchs->purge->table->braldun->suppression->nbjours - $this->config->batchs->purge->table->braldun->prevention->nbjours);
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);
		$bralduns = $braldunTable->findAllBatchByDateFin($dateFin);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - preventionSuppression - date:".$date." dateFin -".$dateFin);

		if (count($bralduns) > 0) {
			foreach ($bralduns as $h) {
				$retour .= $this->envoiMailPrevention($h);
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - preventionSuppression - exit -".$retour);
		return $retour;
	}

	private function envoiMailPrevention($braldun) {
		$retour = "";

		$this->view->braldun = $braldun;
		$add_day = $this->config->batchs->purge->table->braldun->suppression->nbjours;
		$this->view->dateSuppression = Bral_Util_ConvertDate::get_date_add_day_to_date($braldun["date_fin_tour_braldun"], $add_day);
		if ( $this->view->dateSuppression < date("Y-m-d H:i:s")) {
			$this->view->dateSuppression = date("Y-m-d 0:0:0");
		}
		$this->view->dateSuppression = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y', $this->view->dateSuppression);
		$this->view->urlJeu = $this->config->general->url;
		$this->view->adresseSupport = $this->config->general->adresseSupport;

		$contenuText = $this->view->render("batchs/bralduns/mailPreventionText.phtml");
		$contenuHtml = $this->view->render("batchs/bralduns/mailPreventionHtml.phtml");

		if ($this->config->mail->envoi->automatique->actif == true) {
			$mail = Bral_Util_Mail::getNewZendMail();
			$mail->setFrom($this->config->general->mail->from_email, $this->config->general->mail->from_nom);
			$mail->addTo($braldun["email_braldun"], $braldun["prenom_braldun"]." ".$braldun["nom_braldun"]);
			$mail->setSubject($this->config->mail->prevention->titre);
			$mail->setBodyText($contenuText);
			if ($this->config->general->envoi_mail_html == true) {
				$mail->setBodyHtml($contenuHtml);
			}

			$mail->send();
			Bral_Util_Log::mail()->trace("Bral_Batchs_Bralduns - envoiMailPrevention -".$braldun["email_braldun"]." ".$braldun["prenom_braldun"]." ".$braldun["nom_braldun"]);
		}

		$retour = "Prevention.H:".$braldun["email_braldun"]."(".$braldun["id_braldun"].") ";
		return $retour;
	}

	private function suppression() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - suppression - enter -");
		Zend_Loader::loadClass("Bral_Util_Mail");
		Zend_Loader::loadClass("AncienBraldun");
		Zend_Loader::loadClass("BraldunsMetiers");
		Zend_Loader::loadClass("BraldunsTitres");
		Zend_Loader::loadClass("BraldunsDistinction");

		$retour = "";
		$nb = 0;

		$braldunTable = new Braldun();
		$date = date("Y-m-d H:i:s");
		$add_day = -$this->config->batchs->purge->table->braldun->suppression->nbjours;
		$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);

		$bralduns = $braldunTable->findAllBatchByDateFin($dateFin);
		$retour .= $this->calculSuppressionBralduns($bralduns);
		$nb = $nb + $braldunTable->deleteAllBatchByDateFin($dateFin);

		$bralduns = $braldunTable->findAllCompteInactif($dateFin);
		$retour .= $this->calculSuppressionBralduns($bralduns);
		$nb = $nb + $braldunTable->deleteAllCompteInactif($dateFin);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - suppression - exit -");
		return $retour;
	}

	private function calculSuppressionBralduns($bralduns) {
		$retour = "";
		if (count($bralduns) > 0) {
			foreach ($bralduns as $h) {
				$retour .= $this->envoiMailSuppression($h);
				$this->copieVersAncien($h);
			}
		}
		return $retour;
	}

	private function copieVersAncien($braldun) {

		$braldunsMetiersTable = new BraldunsMetiers();
		$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($braldun["id_braldun"]);
		$metiers = "";
		if ($braldunsMetierRowset != null) {
			foreach($braldunsMetierRowset as $m) {
				if ($braldun["sexe_braldun"] == 'feminin') {
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

		$braldunsTitresTable = new BraldunsTitres();
		$braldunsTitreRowset = $braldunsTitresTable->findTitresByBraldunId($braldun["id_braldun"]);
		$titres = "";
		if ($braldunsTitreRowset != null) {
			foreach($braldunsTitreRowset as $t) {
				if ($braldun["sexe_braldun"] == 'feminin') {
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

		$braldunsDistinctionTable = new BraldunsDistinction();
		$braldunsDistinctionRowset = $braldunsDistinctionTable->findDistinctionsByBraldunId($braldun["id_braldun"]);
		$distinctions = "";
		if ($braldunsDistinctionRowset != null) {
			foreach($braldunsDistinctionRowset as $d) {
				$distinctions .= $d["texte_hdistinction"].", ";
			}
			if ($distinctions != "") {
				$distinctions = substr($distinctions, 0, mb_strlen($distinctions) -2);
			}
		}

		$ancienBraldunTable = new AncienBraldun();
		$data = array(
			"id_braldun_ancien_braldun" => $braldun["id_braldun"],
			"nom_ancien_braldun" =>  $braldun["nom_braldun"],
			"prenom_ancien_braldun" =>  $braldun["prenom_braldun"],
			"id_fk_nom_initial_ancien_braldun" =>  $braldun["id_fk_nom_initial_braldun"],
			"email_ancien_braldun" =>  $braldun["email_braldun"],
			"sexe_ancien_braldun" =>  $braldun["sexe_braldun"],
			"niveau_ancien_braldun" =>  $braldun["niveau_braldun"],
			"nb_ko_ancien_braldun" =>  $braldun["nb_ko_braldun"],
			"nb_braldun_ko_ancien_braldun" =>  $braldun["nb_braldun_ko_braldun"],
			"nb_plaque_ancien_braldun" =>  $braldun["nb_plaque_braldun"],
			"nb_braldun_plaquage_ancien_braldun" =>  $braldun["nb_braldun_plaquage_braldun"],
			"nb_monstre_kill_ancien_braldun" =>  $braldun["nb_monstre_kill_braldun"],
			"id_fk_mere_ancien_braldun" =>  $braldun["id_fk_mere_braldun"],
			"id_fk_pere_ancien_braldun" =>  $braldun["id_fk_pere_braldun"],
			"date_creation_ancien_braldun" => $braldun["date_creation_braldun"],
			"metiers_ancien_braldun" => $metiers,
			"titres_ancien_braldun" => $titres,
			"distinctions_ancien_braldun" => $distinctions,
		);

		$ancienBraldunTable->insert($data);

		Zend_Loader::loadClass("Couple");
		$coupleTable = new Couple();
		$data = array('est_valide_couple' => 'non');

		if ($braldun["sexe_braldun"] == "masculin") {
			$where = 'id_fk_m_braldun_couple = '.$braldun["id_braldun"];
		} else {
			$where = 'id_fk_f_braldun_couple = '.$braldun["id_braldun"];
		}
		$coupleTable->update($data, $where);

	}

	private function envoiMailSuppression($braldun) {
		$retour = "";

		if ($this->config->mail->envoi->automatique->actif == true) {
			$this->view->braldun = $braldun;
			$this->view->urlJeu = $this->config->general->url;
			$this->view->adresseSupport = $this->config->general->adresseSupport;
			$this->view->nbJours = $this->config->batchs->purge->table->braldun->suppression->nbjours;

			$contenuText = $this->view->render("batchs/bralduns/mailSuppressionText.phtml");
			$contenuHtml = $this->view->render("batchs/bralduns/mailSuppressionHtml.phtml");

			$mail = Bral_Util_Mail::getNewZendMail();
			$mail->setFrom($this->config->general->mail->from_email, $this->config->general->mail->from_nom);
			$mail->addTo($braldun["email_braldun"], $braldun["prenom_braldun"]." ".$braldun["nom_braldun"]);
			$mail->setSubject($this->config->mail->suppression->titre);
			$mail->setBodyText($contenuText);
			if ($this->config->general->envoi_mail_html == true) {
				$mail->setBodyHtml($contenuHtml);
			}

			$mail->send();
			Bral_Util_Log::mail()->trace("Bral_Batchs_Bralduns - envoiMailSuppression -".$braldun["email_braldun"]." ".$braldun["prenom_braldun"]." ".$braldun["nom_braldun"]);
		}
		$retour = "Suppression.H:".$braldun["email_braldun"]."(".$braldun["id_braldun"].") ";
		return $retour;
	}
}