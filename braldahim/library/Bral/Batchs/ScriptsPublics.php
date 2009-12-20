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
class Bral_Batchs_ScriptsPublics extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - calculBatchImpl - enter -");
		$retour = null;

		$retour = $this->genereFichierPublique();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - calculBatchImpl - exit -");
		return $retour;
	}


	private function genereFichierPublique() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierPublique - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->findAllJoueursAvecPnj();

		$contenu = "id_hobbit;prenom_hobbit;nom_hobbit;niveau_hobbit;";
		$contenu .= "nb_ko_hobbit;nb_hobbit_ko_hobbit;nb_plaque_hobbit;nb_hobbit_plaquage_hobbit";
		$contenu .= "nb_monstre_kill_hobbit;id_fk_mere_hobbit;id_fk_pere_hobbit;id_fk_communaute_hobbit";
		$contenu .= "id_fk_rang_communaute_hobbit;url_blason_hobbit;url_avatar_hobbit;est_pnj_hobbit";
		
		$contenu .= PHP_EOL;
		
		if (count($hobbits) > 0) {
			//id; nom; prenom; niveau;
			foreach ($hobbits as $h) {
				$contenu .= $h["id_hobbit"].';';
				$contenu .= $h["prenom_hobbit"].';';
				$contenu .= $h["nom_hobbit"].';';
				$contenu .= $h["niveau_hobbit"].';';
				$contenu .= $h["nb_ko_hobbit"].';';
				$contenu .= $h["nb_hobbit_ko_hobbit"].';';
				$contenu .= $h["nb_plaque_hobbit"].';';
				$contenu .= $h["nb_hobbit_plaquage_hobbit"].';';
				$contenu .= $h["nb_monstre_kill_hobbit"].';';
				$contenu .= $h["id_fk_mere_hobbit"].';';
				$contenu .= $h["id_fk_pere_hobbit"].';';
				$contenu .= $h["id_fk_communaute_hobbit"].';';
				$contenu .= $h["id_fk_rang_communaute_hobbit"].';';
				$contenu .= $h["url_blason_hobbit"].';';
				$contenu .= $h["url_avatar_hobbit"].';';
				$contenu .= $h["est_pnj_hobbit"];
				
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_hobbits, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierPublique - exit -");
		return $retour;
	}
}