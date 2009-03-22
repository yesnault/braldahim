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
class Bral_Util_Quete {

	private static function estQueteEnCours($hobbit) {
		if ($hobbit->est_quete_hobbit == "oui") {
			return true;
		} else {
			return false;
		}
	}

	private static function getEtapeCourante($hobbit, $idTypeEtape) {
		Zend_Loader::loadClass("Etape");
		$etapeTable = new Etape();
		return $etapeTable->findByIdHobbitAndIdTypeEtape($idHobbit, $idTypeEtape);
	}

	public static function etapeTuer($hobbit, $config, $tailleMonstre, $typeMonstre, $niveauMonstre) {
		if (self::estQueteEnCours($hobbit)) {
			$etape = self::getEtapeCourante($hobbit, $config->game->quete->etape->tuer->id);
			if ($etape == null) {
				return null;
			} else {
				return self::calculEtapeTuer($etape, $hobbit, $config, $tailleMonstre, $typeMonstre, $niveauMonstre);
			}
		} else {
			return null;
		}
	}

	private function calculEtapeTuer($etape, $hobbit, $config, $tailleMonstre, $typeMonstre, $niveauMonstre) {
		if (self::calculEtapeTuerParam1($etape, $hobbit, $config, $tailleMonstre, $typeMonstre, $niveauMonstre)
		&& self::calculEtapeTuerParam3($etape, $hobbit, $config, $tailleMonstre, $typeMonstre, $niveauMonstre)) {
			return self::calculEtapeTuerFin();
		}
	}

	private function calculEtapeTuerParam1($etape, $hobbit, $config, $tailleMonstre, $typeMonstre, $niveauMonstre) {
		$retour = false;
		if ($etape["param_1_etape"] == $config->game->quete->etape->tuer->param1->nombre) {
			$retour = true;
		} else if ($etape["param_1_etape"] == $config->game->quete->etape->tuer->param1->jour && $etape["param_2_etape"] == date('N')) {
			$retour = true;
		} else if ($etape["param_1_etape"] == $config->game->quete->etape->tuer->param1->etat) {
			if ($etape["param_2_etape"] == $config->game->quete->etape->tuer->param2->etat->affame && $hobbit->balance_faim_hobbit < 1) {
				$retour = true;
			} elseif ($etape["param_2_etape"] == $config->game->quete->etape->tuer->param2->etat->repu && $hobbit->balance_faim_hobbit >= 95) {
				$retour = true;
			}
		} else {
			throw new Zend_Exception("::calculEtapeTuerParam1 param1 invalide:".$etape["param_1_etape"]);
		}
		return $retour;
	}

	private function calculEtapeTuerParam3($etape, $hobbit, $config, $tailleMonstre, $typeMonstre, $niveauMonstre) {
		$retour = false;
		if ($etape["param_3_etape"] == $this->view->config->game->quete->etape->tuer->param3->taille) {
			//TODO
		} else if ($etape["param_3_etape"] == $this->view->config->game->quete->etape->tuer->param3->type) {
			//TODO
		} else if ($etape["param_3_etape"] == $this->view->config->game->quete->etape->tuer->param3->niveau) {
			//TODO
		} else {
			throw new Zend_Exception("::calculEtapeTuerParam3 param3 invalide:".$etape["param_3_etape"]);
		}
		return $retour;
	}
}
