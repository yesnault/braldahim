<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Materiel {

	const HISTORIQUE_CREATION_ID = 1;
	const HISTORIQUE_UTILISER_ID = 2;
	const HISTORIQUE_ACHETER_ID = 3;
	const HISTORIQUE_VENDRE_ID = 4;
	const HISTORIQUE_TRANSBAHUTER_ID = 5;
	const HISTORIQUE_ATTAQUER_ID = 6;
	const HISTORIQUE_DETRUIRE_ID = 7;

	public static function insertHistorique($idTypeHistoriqueMateriel, $idMateriel, $details) {
		Zend_Loader::loadClass("Bral_Util_Lien");
		$detailsTransforme = Bral_Util_Lien::remplaceBaliseParNomEtJs($details);

		Zend_Loader::loadClass('HistoriqueMateriel');
		$historiqueMaterielTable = new HistoriqueMateriel();

		$data = array(
			'date_historique_materiel' => date("Y-m-d H:i:s"),
			'id_fk_type_historique_materiel' => $idTypeHistoriqueMateriel,
			'id_fk_historique_materiel' => $idMateriel,
			'details_historique_materiel' => $detailsTransforme,
		);
		$historiqueMaterielTable->insert($data);
	}

	public static function possedeMateriel($idBraldun, $idMateriel) {
		Zend_Loader::loadClass("CharretteMateriel");
		Zend_Loader::loadClass("EchoppeMateriel");
		Zend_Loader::loadClass("LabanMateriel");
		Zend_Loader::loadClass("Charrette");

		$table = new LabanMateriel();
		$materiel = $table->findByIdBraldun($idBraldun, $idMateriel);
		if ($materiel != null) {
			return true;
		}

		$table = new CharretteMateriel();
		$materiel = $table->findByIdBraldun($idBraldun, $idMateriel);
		if ($materiel != null) {
			return true;
		}

		$table = new EchoppeMateriel();
		$materiel = $table->findByIdEchoppe($idBraldun, null, $idMateriel);
		if ($materiel != null) {
			return true;
		}

		$table = new Charrette();
		$materiel = $table->findByIdBraldun($idBraldun, $idMateriel);
		if ($materiel != null) {
			return true;
		}

		return false;
	}
}
