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
class Bral_Util_Contrat {

	public static function action($idBraldunSource, $idBraldunCible) {
		Zend_Loader::loadClass("Contrat");
		$contratTable = new Contrat();
		$contrats = $contratTable->findEnCoursByIdBraldunSourceAndCible($idBraldunSource, $idBraldunCible);

		if (count($contrats) > 1) {
			throw new Zend_Exception("Bral_Util_Contrat erreur nbContrat : ".count($contrats). " idSource:".$idBraldunSource." idCible:".$idBraldunCible);
		}

		if ($contrats != null && count($contrats) == 1) {
			$contrat = $contrats[0];
				
			Zend_Loader::loadClass("Coffre");
			$tableCoffre = new Coffre();
			$data = array(
				"quantite_castar_coffre" => 500,
				"id_fk_braldun_coffre" => $idBraldunSource,
			);
			$tableCoffre->insertOrUpdate($data);
				
			$data = array(
				'date_fin_contrat' => date("Y-m-d H:i:s"),
				'gain_contrat' => "500 castars",
				'etat_contrat' => 'terminé',
			);
			$where = "id_contrat = ".$contrat["id_contrat"];
			$contratTable->update($data, $where);
			return true;
		} else {
			return false;
		}
	}
}