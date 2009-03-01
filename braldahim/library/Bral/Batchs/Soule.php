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
class Bral_Batchs_Soule extends Bral_Batchs_Batch {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculBatchImpl - enter -");
		
		Zend_Loader::loadClass("SouleEquipe");
		Zend_Loader::loadClass("SouleNomEquipe");
		Zend_Loader::loadClass("SouleMatch");
		
		$retour = $this->calculCreationMatchs();
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculBatchImpl - exit -");
		return $retour;
	}
	
	private function calculCreationMatchs() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMatchs - enter -");
		
		$retour = "";
		
		$souleMatch = new SouleMatch();
		$matchs = $souleMatch->findNonDebutes();
		
		$souleEquipe = new SouleEquipe();
		
		
		if ($matchs != null) {
			foreach($matchs as $m) {
				$equipes = $souleEquipe->countInscritsNonDebuteByIdMatch($m["id_soule_match"]);
				$retour .= $this->calculCreationMath($m);
			}
		}
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMatchs - exit -");
		return $retour;
	}
	
	private function calculCreationMath($match) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMath - enter -");
		$retour = "";
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMath - enter -");
		return $retour;
	}
}