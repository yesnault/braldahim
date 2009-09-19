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
class Bral_Batchs_MotsRuniques extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_MotsRuniques - calculBatchImpl - enter -");

		$aujourdhui = date("Y-m-d 0:0:0");

		$retour = $this->calculMotsRuniques();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_MotsRuniques - exit -");
		return $retour;
	}

	private function calculMotsRuniques() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_MotsRuniques - calculMotsRuniques - enter -");

		Zend_Loader::loadClass("Bral_Util_Lune");
		Zend_Loader::loadClass("MotRunique");
		Zend_Loader::loadClass("TypeRune");
		
		$retour = "";
		
		$annee = date('Y');
		$mois = date('m');
		$jour = date('d');
		$heure = date('H');
		$mine = date('i');
		$seconde = date('s');

		list($moonPhase, $moonAge, $moonDist, $moonAng, $sunDist, $sunAng, $mpfrac) = Bral_Util_Lune::calculPhase($annee, $mois, $jour, $heure, $mine, $seconde);
		// coef lune : $mpfrac
		$coefLune = floor($mpfrac * 100);

		$motRuniqueTable = new MotRunique();
		$mots = $motRuniqueTable->findARegenerer($coefLune);

		if ($mots != null && count($mots) > 0) {
			$typeRuneTable = new TypeRune();
			$typesRunesA = $typeRuneTable->findByNiveau('a');
			$typesRunesB = $typeRuneTable->findByNiveau('b');
			$typesRunesC = $typeRuneTable->findByNiveau('c');
			$typesRunesD = $typeRuneTable->findByNiveau('d');
			foreach($mots as $mot) {
				$this->regenereMot($mot, $typesRunesA, $typesRunesB, $typesRunesC, $typesRunesD);
				$this->sauvegardeMot($mot, $motRuniqueTable);
			}
		} else {
			$retour = " aucun mot à regénérer";
		}

		Bral_Util_Log::batchs()->trace("Bral_Batchs_MotsRuniques - calculMotsRuniques - exit -".$retour);
		return $retour;
	}

	private function regenereMot(&$mot, &$typesRunesA, &$typesRunesB, &$typesRunesC, &$typesRunesD) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_MotsRuniques - regenereMot - enter -".$mot["nom_systeme_mot_runique"]);

		$retour = "Mot:";$mot["nom_systeme_mot_runique"];

		srand((float)microtime() * 1000000);

		shuffle($typesRunesA);
		shuffle($typesRunesB);
		shuffle($typesRunesC);
		shuffle($typesRunesD);
		$tabTypes = array(
			"a" => $typesRunesA,
			"b" => $typesRunesB,
			"c" => $typesRunesC,
			"d" => $typesRunesD,
		);

		$mot["id_fk_type_rune_1_mot_runique"] = null;
		$mot["id_fk_type_rune_2_mot_runique"] = null;
		$mot["id_fk_type_rune_3_mot_runique"] = null;
		$mot["id_fk_type_rune_4_mot_runique"] = null;
		$mot["id_fk_type_rune_5_mot_runique"] = null;
		$mot["id_fk_type_rune_6_mot_runique"] = null;

		$indice = 0;

		$retour .= $this->calculRuneNiveau($mot, $indice, 'a', $tabTypes);
		$retour .= $this->calculRuneNiveau($mot, $indice, 'b', $tabTypes);
		$retour .= $this->calculRuneNiveau($mot, $indice, 'c', $tabTypes);
		$retour .= $this->calculRuneNiveau($mot, $indice, 'd', $tabTypes);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_MotsRuniques - regenereMot - exit -".$retour);
		return $retour;
	}

	private function calculRuneNiveau(&$mot, &$indice, $niveau, $tabTypes) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_MotsRuniques - calculRuneNiveau - enter -".$niveau."- ind:".$indice);
		
		for ($i = 1; $i <= $mot["nb_rune_niveau_".$niveau."_mot_runique"]; $i++) {
			$indice = $indice + 1;
			$mot["id_fk_type_rune_".$indice."_mot_runique"] = $tabTypes[$niveau][$i-1]["id_type_rune"];
		}
		$retour = " n:".$niveau."- ind:".$indice;
		Bral_Util_Log::batchs()->trace("Bral_Batchs_MotsRuniques - calculRuneNiveau - exit -".$niveau."- ind:".$indice);
		return $retour;
	}

	private function sauvegardeMot(&$mot, $motRuniqueTable) {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_MotsRuniques - sauvegardeMot - enter -".$mot["nom_systeme_mot_runique"]);

		$retour = "update ".$mot["nom_systeme_mot_runique"];

		$data = array(
			"id_fk_type_rune_1_mot_runique" => $mot["id_fk_type_rune_1_mot_runique"],
			"id_fk_type_rune_2_mot_runique" => $mot["id_fk_type_rune_2_mot_runique"],
			"id_fk_type_rune_3_mot_runique" => $mot["id_fk_type_rune_3_mot_runique"],
			"id_fk_type_rune_4_mot_runique" => $mot["id_fk_type_rune_4_mot_runique"],
			"id_fk_type_rune_5_mot_runique" => $mot["id_fk_type_rune_5_mot_runique"],
			"id_fk_type_rune_6_mot_runique" => $mot["id_fk_type_rune_6_mot_runique"],
			"date_generation_mot_runique" => date("Y-m-d H:i:s"),
		);
		$where = " id_mot_runique = ".$mot["id_mot_runique"];
		$motRuniqueTable->update($data, $where);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_MotsRuniques - sauvegardeMot - exit -".$retour);
		return $retour;
	}
}