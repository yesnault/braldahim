<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Hotel extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace('Bral_Batchs_Hotel - calculBatchImpl - enter -');
		Zend_Loader::loadClass('Lot');
		Zend_Loader::loadClass('Bral_Util_Lot');

		$lots = Bral_Util_Lot::getLotsByHotel(true);
		$nb = 0;

		if ($lots != null && count($lots["lots"]) > 0) {
			$nb = count($lots["lots"]);
			foreach($lots["lots"] as $lot) {
				
				if ($lot["id_fk_vendeur_braldun_lot"] == null) {
					throw new Zend_Exception('Bral_Batchs_Hotel::calculBatchImpl id_fk_vendeur_braldun_lot null pour idLot:'.$lot['id_lot']);
				}
				Bral_Util_Lot::transfertLot($lot['id_lot'], 'coffre', $lot['id_fk_vendeur_braldun_lot']);

				$s = "";
				if ($lot["prix_1_lot"] > 1) {
					$s = "s";
				}

				$message = '[Hôtel des Ventes]'.PHP_EOL.PHP_EOL;
				$message .= 'Le lot n°'.$lot['id_lot']. ', en vente pour '.$lot['prix_1_lot'].' castar'.$s.', n\'a pas été vendu durant les 60 derniers jours.'.PHP_EOL;
				$message .= 'Le contenu du lot est placé dans votre coffre.'.PHP_EOL.PHP_EOL;
				$message .= 'Détails du lot : '.$lot['details']. PHP_EOL.PHP_EOL;
				$message .= '&Eacute;mile Claclac, gestionnaire de l\'Hôtel des ventes.'.PHP_EOL;
				$message .= 'Inutile de répondre à ce message.';
				Bral_Util_Messagerie::envoiMessageAutomatique($this->config->game->pnj->hotel->id_braldun, $lot['id_fk_vendeur_braldun_lot'], $message, $this->view);

			}
		}

		Bral_Util_Log::batchs()->trace('Bral_Batchs_Hotel - calculBatchImpl - exit -');
		return ' nb:'.$nb;
	}
}