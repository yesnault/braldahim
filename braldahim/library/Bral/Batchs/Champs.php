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
class Bral_Batchs_Champs extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - calculBatchImpl - enter -");

		Zend_Loader::loadClass('Champ');

		$retour = null;
		$retour .= $this->calculChamps();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - calculBatchImpl - exit -");
		return $retour;
	}

	private function calculChamps() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - calculChamp - enter -");
		$retour = "";

		// les champs semés => à récolter

		$champTable = new Champ();
		$champs = $champTable->selectSemerARecolter();
		if ($champs != null && count($champs) > 0) {
			foreach($champs as $c) {
				$retour .= $this->updateChampARecolter($c);
			}
		}

		$champs = $champTable->selectFinARecolter();
		if ($champs != null && count($champs) > 0) {
			foreach($champs as $c) {
				$retour .= $this->updateChampVersJachere($c);
			}
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - calculChamp - exit -");
		return $retour;
	}

	private function updateChampARecolter($champ) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - updateChampARecolter - enter -");
		$retour = "";

		$champTable = new Champ();
		$data = array(
			'phase_champ' => 'a_recolter',
		);
		$where = 'id_champ = '.$champ["id_champ"];
		$champTable->update($data, $where);

		$retour = ' champ:'.$champ["id_champ"].'->a_recolter';

		$detailsBot = "Bonjour,".PHP_EOL.PHP_EOL."Vous avez un champ (x:".$champ["x_champ"]." y:".$champ["y_champ"].") à récolter. Si vous ne le récoltez pas avant 5 jours, il repassera en jachère et la récolte sera perdue.";
		Zend_Loader::loadClass("Bral_Util_Messagerie");
		$message = $detailsBot.PHP_EOL.PHP_EOL." Signé José le Faucheur".PHP_EOL."Inutile de répondre à ce message.";
		Bral_Util_Messagerie::envoiMessageAutomatique($this->config->game->pnj->jose->id_hobbit, $champ["id_hobbit"], $message, $this->view);
			
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - updateChampARecolter - exit -");
		return $retour;
	}

	private function updateChampVersJachere($champ) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - updateChampVersJachere - enter -");
		$retour = "";

		$champTable = new Champ();
		$data = array(
			'phase_champ' => 'jachere',
			'date_seme_champ' => null,
			'date_fin_recolte_champ' => null,
			'id_fk_type_graine_champ' => null,
		);

		$where = 'id_champ = '.$champ["id_champ"];
		$champTable->update($data, $where);

		$retour = ' champ:'.$champ["id_champ"].'->jachere';
		
		$detailsBot = "Bonjour,".PHP_EOL.PHP_EOL."Votre champ (x:".$champ["x_champ"]." y:".$champ["y_champ"].") passé automatiquement en jachère. La récolte est perdue.";
		Zend_Loader::loadClass("Bral_Util_Messagerie");
		$message = $detailsBot.PHP_EOL.PHP_EOL." Signé José le Faucheur".PHP_EOL."Inutile de répondre à ce message.";
		Bral_Util_Messagerie::envoiMessageAutomatique($this->config->game->pnj->jose->id_hobbit, $champ["id_hobbit"], $message, $this->view);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_Champs - updateChampVersJachere - exit -");
		return $retour;
	}
}