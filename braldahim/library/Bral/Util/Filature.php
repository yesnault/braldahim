<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Poids.php 2702 2010-06-02 22:13:28Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-06-03 00:13:28 +0200 (Jeu, 03 jui 2010) $
 * $LastChangedRevision: 2702 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Util_Filature {
	
	public static function action($braldun, $view) {
		Zend_Loader::loadClass("FilatureAction");
		$filatureActionTable = new FilatureAction();
		$nombre = $filatureActionTable->countByIdBraldun($braldun->id_braldun); // Perf
		
		if ($nombre <= 0) {
			return;
		}
		
		$actions = $filatureActionTable->findByIdBraldunAndPosition($braldun->id_braldun, $braldun->x_braldun, $braldun->y_braldun);
		
		if ($actions != null && count($actions) > 0) {
			$config = Zend_Registry::get('config');
			foreach($actions as $a) {
				$message = $a["message_filature_action"].PHP_EOL.PHP_EOL."Inutile de répondre à ce message.";
				Bral_Util_Messagerie::envoiMessageAutomatique($config->game->pnj->inconnu->id_braldun, $a["id_fk_braldun_filature"], $message, $view);
				$where = "id_filature_action = ".$a["id_filature_action"];
				$filatureActionTable->delete($where);
				
				$message = "Il semblerait qu'un inconnu vient de donner des informations à quelqu'un vous concernant".PHP_EOL.PHP_EOL."Inutile de répondre à ce message.";
				Bral_Util_Messagerie::envoiMessageAutomatique($config->game->pnj->inconnu->id_braldun, $braldun->id_braldun, $message, $view);
				
			}
		}
	}
}