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
class Bral_Util_InfoJeu {

	private function __construct() {
	}

	public static function prepareInfosJeu($type = null, $annee = null) {
		Zend_Loader::loadClass('InfoJeu');
		$infoJeuTable = new InfoJeu();
		
		$dateDebut = null;
		$dateFin = null;
		
		if ($annee != null) {
			$dateFin = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1,  $annee+1));
			$dateDebut = date("Y-m-d H:i:s", mktime(0, 0, 0, 1, 1,  $annee));
		}
		
		$infosRowset = $infoJeuTable->findAll($type, $dateDebut, $dateFin);
		$annonces = null;
		$histoires = null;
		foreach ($infosRowset as $i) {
			$tab = array(
				"id_info_jeu" => $i["id_info_jeu"],
				"date_info_jeu" => $i["date_info_jeu"],
				"titre_info_jeu" => $i["titre_info_jeu"],
				"text_info_jeu" => $i["text_info_jeu"],
				"est_sur_accueil_info_jeu" => $i["est_sur_accueil_info_jeu"],
				"lien_info_jeu" => $i["lien_info_jeu"],
				"lien_wiki_info_jeu" => $i["lien_wiki_info_jeu"],
			);

			if ($i["type_info_jeu"] == "annonce" && ($type == null || $type = "annonce")) {
				$annonces[] = $tab;
			} elseif ($type == null || $type = "histoire") {
				$histoires[] = $tab;
			}
		}

		$retour["annonces"] = $annonces;
		$retour["histoires"] = $histoires;
		return $retour;
	}

}