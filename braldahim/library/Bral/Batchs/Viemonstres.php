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
class Bral_Batchs_Viemonstres extends Bral_Batchs_Batch {
	
	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Viemonstres - calculBatchImpl - enter -");
		$retour = null;

        Zend_Loader::loadClass('GroupeMonstre');
        Zend_Loader::loadClass('Monstre');
        Zend_Loader::loadClass("Bral_Monstres_VieGroupes");
        Zend_Loader::loadClass("Bral_Monstres_VieGroupesNuee");
        Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
        Zend_Loader::loadClass("Bral_Monstres_VieSolitaire");
        Zend_Loader::loadClass("Bral_Util_Evenement");
        Zend_Loader::loadClass("Bral_Util_Attaque");
        Zend_Loader::loadClass("Bral_Util_Vie");
        Zend_Loader::loadClass("Ville");

        $villeTable = new Ville();
		$villes = $villeTable->fetchAll();
		
		$vieGroupe = new Bral_Monstres_VieGroupesNuee($this->view, $villes);
       	$vieGroupe->action();
        
        $vieSolitaire = new Bral_Monstres_VieSolitaire($this->view, $villes);
        $vieSolitaire->action();
		
		Bral_Util_Log::batchs()->trace("Bral_Batchs_Viemonstres - calculBatchImpl - exit -");
		return $retour;
	}
	
	private function purgeBatch() {
        
	}
}