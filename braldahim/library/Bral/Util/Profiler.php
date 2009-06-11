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
class Bral_Util_Profiler {

	private function __construct(){}

	public static function traite($dbAdapterGame) {

		$profileur = $dbAdapterGame->getProfiler();
		$tempsTotal = $profileur->getTotalElapsedSecs();
		$nombreRequetes = $profileur->getTotalNumQueries();
		$tempsLePlusLong = 0;
		$requeteLaPlusLongue = null;

		$nb = 0;
		$liste = "";
		foreach ($profileur->getQueryProfiles() as $query) {
			if ($query->getElapsedSecs() > $tempsLePlusLong) {
				$tempsLePlusLong  = $query->getElapsedSecs();
				$requeteLaPlusLongue = $query->getQuery();
			}
			$nb++;
			$liste .= $nb. ":".$query->getQuery()."\n"; 
		}

		$texte = 'Exécution de '. $nombreRequetes . ' requêtes en '. $tempsTotal. ' secondes' . "\n";
		$texte .= 'Temps moyen : '. $tempsTotal / $nombreRequetes. ' secondes' . "\n";
		$texte .= 'Requêtes par seconde: '. $nombreRequetes / $tempsTotal. ' seconds' . "\n";
		$texte .= 'Requête la plus lente (secondes) : '. $tempsLePlusLong . "\n";
		$texte .= "Requête la plus lente (SQL) : \n". $requeteLaPlusLongue . "\n";
		$texte .= "Liste des requêtes utilisées :\n".$liste;
		Bral_Util_Log::profiler()->info($texte);

	}
}