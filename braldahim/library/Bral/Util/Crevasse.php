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
class Bral_Util_Crevasse {

	public static function calculCrevasse(&$braldun) {
		Zend_Loader::loadClass("Crevasse");

		$estCrevasseEvenement = false;

		$crevasseTable = new Crevasse();
		$nbCrevasses = $crevasseTable->countByCase($braldun->x_braldun, $braldun->y_braldun, $braldun->z_braldun);

		if ($nbCrevasses > 0) {

			$data['est_decouverte_crevasse']  = 'oui';

			$where = 'x_crevasse = '.$braldun->x_braldun.' AND ';
			$where .= 'y_crevasse = '.$braldun->y_braldun.' AND ';
			$where .= 'z_crevasse = '.$braldun->z_braldun;
			$crevasseTable->update($data, $where);

			$estCrevasseEvenement = true;
			$braldun->z_braldun = $braldun->z_braldun - 1;
			$braldun->pv_restant_braldun = $braldun->pv_restant_braldun - floor($braldun->pv_restant_braldun / 2);
			if ($braldun->pv_restant_braldun < 1) {
				$braldun->pv_restant_braldun = 1;
			}
		}

		return $estCrevasseEvenement;
	}
}
