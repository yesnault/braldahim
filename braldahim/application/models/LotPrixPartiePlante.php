<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class LotPrixPartiePlante extends Zend_Db_Table {
	protected $_name = 'lot_prix_partieplante';
	protected $_primary = array("id_fk_type_lot_prix_partieplante","id_fk_type_plante_lot_prix_partieplante", "id_fk_lot_prix_partieplante");

	function findByIdLot($idLot) {

		$liste = "";
		$nomChamp = "id_fk_lot_prix_partieplante";

		if (is_array($idLot)) {
			foreach($idLot as $id) {
				if ((int) $id."" == $id."") {
					if ($liste == "") {
						$liste = $id;
					} else {
						$liste = $liste." OR ".$nomChamp."=".$id;
					}
				}
			}
		}

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('lot_prix_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->where('lot_prix_partieplante.id_fk_type_lot_prix_partieplante = type_partieplante.id_type_partieplante')
		->where('lot_prix_partieplante.id_fk_type_plante_lot_prix_partieplante = type_plante.id_type_plante');

		if ($liste != "") {
			$select->where($nomChamp .'='. $liste);
		} else {
			$select->where('id_fk_lot_prix_partieplante = ?', intval($idLot));
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
