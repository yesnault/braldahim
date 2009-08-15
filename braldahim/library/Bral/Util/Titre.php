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
class Bral_Util_Titre {

	function __construct() {
	}

	public static function prepareTitre($idHobbit, $sexeHobbit) {
		Zend_Loader::loadClass("HobbitsTitres");
		$hobbitsTitresTable = new HobbitsTitres();
		$hobbitsTitreRowset = $hobbitsTitresTable->findTitresByHobbitId($idHobbit);
		unset($hobbitsTitresTable);
		$tabTitres = null;
		$possedeTitre = false;

		foreach($hobbitsTitreRowset as $t) {
			$possedeTitre = true;

			if ($sexeHobbit == 'feminin') {
				$nom_titre = $t["nom_feminin_type_titre"];
			} else {
				$nom_titre = $t["nom_masculin_type_titre"];
			}

			$tabTitres[] = array(
				"nom" => $nom_titre,
				"nom_systeme" => $t["nom_systeme_type_titre"],
				"description" => $t["description_type_titre"],
				"date_acquis_htitre" => Bral_Util_ConvertDate::get_date_mysql_datetime("d/m/Y", $t["date_acquis_htitre"]),
				"niveau_acquis_htitre" => $t["niveau_acquis_htitre"],
			);

		}
		unset($hobbitsTitreRowset);

		$retour["tabTitres"] = $tabTitres;
		$retour["possedeTitre"] = $possedeTitre;
		return $retour;
	}

	public static function calculNouveauTitre(&$hobbit, $typeTitre) {
		switch($typeTitre->nom_systeme_type_titre) {
			case "sagesse" :
				$hobbit->sagesse_base_hobbit = $hobbit->sagesse_base_hobbit + 1;
				break;
			case "vigueur":
				$hobbit->vigueur_base_hobbit = $hobbit->vigueur_base_hobbit + 1;
				$pvAvant = $hobbit->pv_max_hobbit;
				$hobbit->pv_max_hobbit = Bral_Util_Commun::calculPvMaxBaseSansEffetMotE(Zend_Registry::get('config'), $hobbit->vigueur_base_hobbit);
				$hobbit->pv_restant_hobbit = $hobbit->pv_restant_hobbit  + ($hobbit->pv_max_hobbit - $pvAvant);
				if ($hobbit->pv_restant_hobbit > $hobbit->pv_max_hobbit + $hobbit->hobbit->pv_max_bm_hobbit) {
					$hobbit->pv_restant_hobbit = $hobbit->pv_max_hobbit + $hobbit->hobbit->pv_max_bm_hobbit;
				}
				break;
			case "force" :
				$hobbit->force_base_hobbit = $hobbit->force_base_hobbit + 1;
				$hobbit->poids_transportable_hobbit = Bral_Util_Poids::calculPoidsTransportable($hobbit->force_base_hobbit);
				break;
			case "agilite" :
				$hobbit->agilite_base_hobbit = $hobbit->agilite_base_hobbit + 1;
				break;
			default:
				throw new Zend_Exception("Titre nom systeme inconnu :".$typeTitre->nom_systeme_type_titre);
		}

		$hobbit->armure_naturelle_hobbit = Bral_Util_Commun::calculArmureNaturelle($hobbit->force_base_hobbit, $hobbit->vigueur_base_hobbit);

		$config = Zend_Registry::get('config');

		$idType = $config->game->evenements->type->special;
		$details = "[h".$hobbit->id_hobbit."] a reçu un titre. ";

		if ($hobbit->sexe_hobbit == "feminin") {
			$details .= "Elle est maintenant ";
		} else {
			$details .= "Il est maintenant ";
		}

		$nom = $hobbit->prenom_hobbit. " " .$hobbit->nom_hobbit.", ".$hobbit->titre_courant_hobbit;

		$details .= $nom;

		if ($hobbit->niveau_hobbit > 10) {
			$detailsBot = "Vous avez un nouveau titre !";
		} else {
			$detailsBot = "Vous avez gagné un titre !";
		}
		$detailsBot .= PHP_EOL."Vous êtes maintenant ".$nom;

		Bral_Util_Evenement::majEvenements($hobbit->id_hobbit, $idType, $details, $detailsBot, $hobbit->niveau_hobbit);

		return;
	}
}