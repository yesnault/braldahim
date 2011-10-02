<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Champs extends Bral_Batchs_Batch
{

	public function calculBatchImpl()
	{
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - calculBatchImpl - enter -");

		Zend_Loader::loadClass('Champ');

		$retour = null;
		$retour .= $this->calculChamps();

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - calculBatchImpl - exit -");
		return $retour;
	}

	private function calculChamps()
	{
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - calculChamp - enter -");
		$retour = "";

		// les champs semés => à récolter

		$champTable = new Champ();
		$champs = $champTable->selectSemerARecolter();
		if ($champs != null && count($champs) > 0) {
			foreach ($champs as $c) {
				$retour .= $this->updateChampARecolter($c);
			}
		}

		$champs = $champTable->selectFinARecolter();
		if ($champs != null && count($champs) > 0) {
			foreach ($champs as $c) {
				$retour .= $this->updateChampVersJachere($c);
			}
		}

		$datePurge = Bral_Util_ConvertDate::get_date_add_day_to_date(date('Y-m-d 00:00:00'), -60);
		$champs = $champTable->selectPourPurgeOuPrevention($datePurge);
		if ($champs != null && count($champs) > 0) {
			foreach ($champs as $c) {
				$retour .= $this->purgeChamps($c);
			}
		}

		$datePrevention = Bral_Util_ConvertDate::get_date_add_day_to_date(date('Y-m-d 00:00:00'), -40);
		$champs = $champTable->selectPourPurgeOuPrevention($datePrevention, true);
		if ($champs != null && count($champs) > 0) {
			foreach ($champs as $c) {
				$retour .= $this->preventionChamps($c, 20);
			}
		}
		$datePrevention = Bral_Util_ConvertDate::get_date_add_day_to_date(date('Y-m-d 00:00:00'), -50);
		$champs = $champTable->selectPourPurgeOuPrevention($datePrevention, true);
		if ($champs != null && count($champs) > 0) {
			foreach ($champs as $c) {
				$retour .= $this->preventionChamps($c, 10);
			}
		}

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - calculChamp - exit -");
		return $retour;
	}

	private function purgeChamps($champ)
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - purgeChamps - enter -");

		Zend_Loader::loadClass('Champ');
		$retour = "";
		$champTable = new Champ();

		$date = date("Y-m-d H:i:s");
		$where = "id_champ=" . $champ["id_champ"];
		$nb = $champTable->delete($where);
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - Purge - id:" . $champ["id_champ"] . " - where:" . $where);
		$retour = " Champ:nb delete id:" . $champ["id_champ"] . " date:" . $date;

		$detailsBot = "Bonjour," . PHP_EOL . PHP_EOL . "Votre champ (x:" . $champ["x_champ"] . " y:" . $champ["y_champ"] . ") a été en jachère trop longtemps, il a été supprimé.";
		Zend_Loader::loadClass("Bral_Util_Messagerie");
		$message = $detailsBot . PHP_EOL . PHP_EOL . " Signé José le Faucheur" . PHP_EOL . "Inutile de répondre à ce message.";

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - updateChampARecolter - avant message mail -" . $champ["id_braldun"]);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->config->game->pnj->jose->id_braldun, $champ["id_braldun"], $message, $this->view);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - purgeChamps - exit -");
		return $retour;
	}

	private function preventionChamps($champ, $nbJours)
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - preventionChamps - enter -");

		$detailsBot = "Bonjour," . PHP_EOL . PHP_EOL . "Vous avez un champ (x:" . $champ["x_champ"] . " y:" . $champ["y_champ"] . ") à l'abandon. Si vous ne le semez pas avant " . $nbJours . " jours, il sera supprimé.";
		Zend_Loader::loadClass("Bral_Util_Messagerie");
		$message = $detailsBot . PHP_EOL . PHP_EOL . " Signé José le Faucheur" . PHP_EOL . "Inutile de répondre à ce message.";

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - updateChampARecolter - avant message mail -" . $champ["id_braldun"]);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->config->game->pnj->jose->id_braldun, $champ["id_braldun"], $message, $this->view);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - preventionChamps - exit -");
	}

	private function updateChampARecolter($champ)
	{
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - updateChampARecolter - enter -");
		$retour = "";

		$champTable = new Champ();
		$data = array(
			'phase_champ' => 'a_recolter',
		);
		$where = 'id_champ = ' . $champ["id_champ"];

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - updateChampARecolter - avant maj -" . $champ["id_champ"]);
		$champTable->update($data, $where);
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - updateChampARecolter - apres maj -" . $champ["id_champ"]);

		$retour .= ' champ:' . $champ["id_champ"] . '->a_recolter';

		$detailsBot = "Bonjour," . PHP_EOL . PHP_EOL . "Vous avez un champ (x:" . $champ["x_champ"] . " y:" . $champ["y_champ"] . ") à récolter. Si vous ne le récoltez pas avant 5 jours, il repassera en jachère et la récolte sera perdue.";
		Zend_Loader::loadClass("Bral_Util_Messagerie");
		$message = $detailsBot . PHP_EOL . PHP_EOL . " Signé José le Faucheur" . PHP_EOL . "Inutile de répondre à ce message.";

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - updateChampARecolter - avant message mail -" . $champ["id_braldun"]);
		Bral_Util_Messagerie::envoiMessageAutomatique($this->config->game->pnj->jose->id_braldun, $champ["id_braldun"], $message, $this->view);

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - updateChampARecolter - exit -");
		return $retour;
	}

	private function updateChampVersJachere($champ)
	{
		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - updateChampVersJachere - enter -");
		$retour = "";

		$dateUtilisation = date("Y-m-d 00:00:00");

		$champTable = new Champ();
		$data = array(
			'phase_champ' => 'jachere',
			'date_seme_champ' => null,
			'date_fin_recolte_champ' => null,
			'date_fin_seme_champ' => null,
			//'id_fk_type_graine_champ' => null, ==> on ne vide pas, c'est utile pour le % quantité à la prochaine action semer
			'quantite_champ' => 0,
			'date_utilisation_champ' => $dateUtilisation,
		);

		$where = 'id_champ = ' . $champ["id_champ"];
		$champTable->update($data, $where);

		$retour .= ' champ:' . $champ["id_champ"] . '->jachere';

		$detailsBot = "Bonjour," . PHP_EOL . PHP_EOL . "Votre champ (x:" . $champ["x_champ"] . " y:" . $champ["y_champ"] . ") est passé automatiquement en jachère. La récolte est perdue.";
		Zend_Loader::loadClass("Bral_Util_Messagerie");
		$message = $detailsBot . PHP_EOL . PHP_EOL . " Signé José le Faucheur" . PHP_EOL . "Inutile de répondre à ce message.";
		Bral_Util_Messagerie::envoiMessageAutomatique($this->config->game->pnj->jose->id_braldun, $champ["id_braldun"], $message, $this->view);

		Bral_Util_Log::batchs()->notice("Bral_Batchs_Champs - updateChampVersJachere - exit -");
		return $retour;
	}
}