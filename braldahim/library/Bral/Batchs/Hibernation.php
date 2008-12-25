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
class Bral_Batchs_Hibernation extends Bral_Batchs_Batch {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hibernation - calculBatchImpl - enter -");
		
		$aujourdhui = date("Y-m-d 0:0:0");
		
		$hobbitTable = new Hobbit();
		
		$where = 'date_fin_hibernation_hobbit < \''.$aujourdhui.'\' AND date_fin_hibernation_hobbit != \'0000-00-00 00:00:00\'';
		$data = array(
			'date_fin_hibernation_hobbit' => 'NULL',
			'est_en_hibernation_hobbit' => 'non',
			'date_fin_tour_hobbit' => $aujourdhui,
		);
		$nbSortis = $hobbitTable->update($data, $where);
		
		$where = 'date_fin_hibernation_hobbit >= \''.$aujourdhui.'\'';
		$data = array(
			'est_en_hibernation_hobbit' => 'oui',
		);
		$nbEntres = $hobbitTable->update($data, $where);
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hibernation - nbHibernationEntres:".$nbEntres." - nbHibernationSortis:".$nbSortis);
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Hibernation - exit -");
		return "nbHibernationEntres:".$nbEntres. " nbHibernationSortis:".$nbSortis;
	}
}