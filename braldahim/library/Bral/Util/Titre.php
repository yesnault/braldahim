<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Titre
{

	function __construct()
	{
	}

	public static function prepareTitre($idBraldun, $sexeBraldun)
	{
		Zend_Loader::loadClass("BraldunsTitres");
		$braldunsTitresTable = new BraldunsTitres();
		$braldunsTitreRowset = $braldunsTitresTable->findTitresByBraldunId($idBraldun);
		unset($braldunsTitresTable);
		$tabTitres = null;
		$possedeTitre = false;

		foreach ($braldunsTitreRowset as $t) {
			$possedeTitre = true;

			if ($sexeBraldun == 'feminin') {
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
		unset($braldunsTitreRowset);

		$retour["tabTitres"] = $tabTitres;
		$retour["possedeTitre"] = $possedeTitre;
		return $retour;
	}

	public static function calculNouveauTitre(&$braldun, $typeTitre)
	{
		switch ($typeTitre->nom_systeme_type_titre) {
			case "sagesse" :
				$braldun->sagesse_base_braldun = $braldun->sagesse_base_braldun + 1;
				break;
			case "vigueur":
				$braldun->vigueur_base_braldun = $braldun->vigueur_base_braldun + 1;
				$pvAvant = $braldun->pv_max_braldun;
				$braldun->pv_max_braldun = Bral_Util_Commun::calculPvMaxBaseSansEffetMotE(Zend_Registry::get('config'), $braldun->vigueur_base_braldun);
				$braldun->pv_restant_braldun = $braldun->pv_restant_braldun + ($braldun->pv_max_braldun - $pvAvant);
				if ($braldun->pv_restant_braldun > $braldun->pv_max_braldun + $braldun->pv_max_bm_braldun) {
					$braldun->pv_restant_braldun = $braldun->pv_max_braldun + $braldun->pv_max_bm_braldun;
				}
				break;
			case "force" :
				$braldun->force_base_braldun = $braldun->force_base_braldun + 1;
				$braldun->poids_transportable_braldun = Bral_Util_Poids::calculPoidsTransportable($braldun->force_base_braldun);
				break;
			case "agilite" :
				$braldun->agilite_base_braldun = $braldun->agilite_base_braldun + 1;
				break;
			default:
				throw new Zend_Exception("Titre nom systeme inconnu :" . $typeTitre->nom_systeme_type_titre);
		}

		$braldun->armure_naturelle_braldun = Bral_Util_Commun::calculArmureNaturelle($braldun->force_base_braldun, $braldun->vigueur_base_braldun);

		$config = Zend_Registry::get('config');

		$idType = $config->game->evenements->type->special;
		$details = "[b" . $braldun->id_braldun . "] a reçu un titre. ";

		if ($braldun->sexe_braldun == "feminin") {
			$details .= "Elle est maintenant ";
		} else {
			$details .= "Il est maintenant ";
		}

		$nom = $braldun->prenom_braldun . " " . $braldun->nom_braldun . ", " . $braldun->titre_courant_braldun;

		$details .= $nom;

		if ($braldun->niveau_braldun > 10) {
			$detailsBot = "Vous avez un nouveau titre !";
		} else {
			$detailsBot = "Vous avez gagné un titre !";
		}
		$detailsBot .= PHP_EOL . "Vous êtes maintenant " . $nom;

		Bral_Util_Evenement::majEvenements($braldun->id_braldun, $idType, $details, $detailsBot, $braldun->niveau_braldun);

		return;
	}
}