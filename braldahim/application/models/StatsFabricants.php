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
class StatsFabricants extends Zend_Db_Table {
	protected $_name = 'stats_fabricants';
	protected $_primary = array('id_stats_fabricants');

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_fabricants', 
			'count(*) as nombre, 
			nb_piece_stats_fabricants as quantitePiece, 
			somme_niveau_piece_stats_fabricants as sommeNiveau')
		->where('niveau_braldun_stats_fabricants = '.$data["niveau_braldun_stats_fabricants"]
				.' AND id_fk_braldun_stats_fabricants = '.$data["id_fk_braldun_stats_fabricants"]
				.' AND id_fk_metier_stats_fabricants = '.$data["id_fk_metier_stats_fabricants"]
				.' AND mois_stats_fabricants = \''.$data["mois_stats_fabricants"].'\'')
		->group(array('quantitePiece', 'sommeNiveau'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		
		if (!isset($data["nb_piece_stats_fabricants"])) {
			$data["nb_piece_stats_fabricants"] = 0; 
		}
		
		if (!isset($data["somme_niveau_piece_stats_fabricants"])) {
			$data["somme_niveau_piece_stats_fabricants"] = 0;
		}
		
		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantitePiece = $resultat[0]["quantitePiece"];
			$sommeNiveau = $resultat[0]["sommeNiveau"];
			
			$dataUpdate['nb_piece_stats_fabricants'] = $quantitePiece;
			$dataUpdate['somme_niveau_piece_stats_fabricants'] = $sommeNiveau;
			
			if (isset($data["nb_piece_stats_fabricants"])) {
				$dataUpdate['nb_piece_stats_fabricants'] = $quantitePiece + $data["nb_piece_stats_fabricants"];
				if ($dataUpdate['nb_piece_stats_fabricants'] < 0) {
					$dataUpdate['nb_piece_stats_fabricants'] = 0;
				}
			}
			
			if (isset($data["somme_niveau_piece_stats_fabricants"])) {
				$dataUpdate['somme_niveau_piece_stats_fabricants'] = $sommeNiveau + $data["somme_niveau_piece_stats_fabricants"];
				if ($dataUpdate['somme_niveau_piece_stats_fabricants'] < 0) {
					$dataUpdate['somme_niveau_piece_stats_fabricants'] = 0;
				}
			}
			
			$where = 'niveau_braldun_stats_fabricants = '.$data["niveau_braldun_stats_fabricants"]
				.' AND id_fk_braldun_stats_fabricants = '.$data["id_fk_braldun_stats_fabricants"]
				.' AND id_fk_metier_stats_fabricants = '.$data["id_fk_metier_stats_fabricants"]
				.' AND mois_stats_fabricants = \''.$data["mois_stats_fabricants"].'\'';
			$this->update($dataUpdate, $where);
		}
	}
}