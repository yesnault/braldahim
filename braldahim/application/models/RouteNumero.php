<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class RouteNumero extends Zend_Db_Table {
	protected $_name = 'route_numero';
	protected $_primary = "id_route_numero";

	function findOuverteByIdLieu($idLieu, $estDepartCapitale) {

		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('route_numero', '*')
				->from('lieu', '*')
				->where('est_ouverte_route_numero = ?', 'oui')
				->joinLeft('ville', 'id_fk_ville_lieu = id_ville');

		if ($estDepartCapitale == "oui") {
			$where = "(id_fk_gare_capitale_route_numero = '" . intval($idLieu) . "'";
			$where .= "AND id_lieu = id_fk_gare_province_route_numero) OR";
			$where .= "(id_fk_gare_province_route_numero = '" . intval($idLieu) . "'";
			$where .= "AND id_lieu = id_fk_gare_capitale_route_numero)";
			$select->where($where);
		} else {
			$select->where("id_fk_gare_province_route_numero = ?", $idLieu);
			$select->where('id_lieu = id_fk_gare_capitale_route_numero');
		}

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
