<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Monstres_Competences_Reperage extends Bral_Monstres_Competences_Competence
{

	/*
		  * Après 3 attaques ratées (2 DLA différentes pour les monstres attaquant plusieurs fois par DLA) :
		  * Si nivH > nivM+5 :
		  * On calcule nivH-nivM=DiffNiv. On lance un D10.
		  * - Si le résultat est < à DiffNiv alors le monstre lache la cible et ne pourra pas la recibler pendant 1D3+1 DLA (effet de zone fonctionne toujours bien sûr car il ne cible pas directement).
		  * - Si le résultat est inférieur, le monstre jour sa DLA normalement et refera le test à la DLA suivante.
		  */
	public static function peutAttaquer($cible, $monstre)
	{
		Bral_Util_Log::viemonstres()->trace("Bral_Monstres_Competences_Reperage - peutAttaquer - enter - (idm:" . $monstre["id_monstre"] . ") cible:" . $cible["id_braldun"]);

		$retour = true;

		// Recuperation du nombre d'attaque esquive par le Braldûn dans les 3 dernières DLA.
		$evenementTable = new Evenement();
		$nbAttaqueEsquivee = $evenementTable->countByIdMonstreIdBraldunLast3tours($monstre["nb_dla_jouees_monstre"], $monstre["id_monstre"], $cible["id_braldun"], Bral_Util_Evenement::ATTAQUE_ESQUIVEE);

		if ($nbAttaqueEsquivee >= 3 && $cible["niveau_braldun"] > $monstre["niveau_monstre"] + 5) {
			Bral_Util_Log::viemonstres()->trace("Bral_Monstres_Competences_Reperage - (idm:" . $monstre["id_monstre"] . ") " . $cible["niveau_braldun"] . " > " . $monstre["niveau_monstre"] . "+5");
			$diffNiv = $cible["niveau_braldun"] - $monstre["niveau_monstre"];
			$de = Bral_Util_De::get_1D10();
			if ($de < $diffNiv) {
				$retour = false;
				Bral_Util_Log::viemonstres()->trace("Bral_Monstres_Competences_Reperage - peutAttaquer - (idm:" . $monstre["id_monstre"] . ") ne peut pas attaquer " . $cible["id_braldun"]);
			} else {
				$retour = true;
			}
		}

		if ($retour) {
			Bral_Util_Log::viemonstres()->trace("Bral_Monstres_Competences_Reperage - peutAttaquer - (idm:" . $monstre["id_monstre"] . ") peut attaquer " . $cible["id_braldun"]);
		}

		Bral_Util_Log::viemonstres()->trace("Bral_Monstres_Competences_Reperage - peutAttaquer - exit - (idm:" . $monstre["id_monstre"] . ")");
		return $retour;
	}
}