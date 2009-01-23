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
class StatsFabricants extends Zend_Db_Table {
	protected $_name = 'stats_fabricants';
	protected $_primary = array('id_stats_fabricants');

	function insertOrUpdate($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('stats_fabricants', 
			'count(*) as nombre, 
			nb_piece_stats_fabricants as quantitePiece, 
			nb_batiment_stats_fabricants as quantiteBatiment,
			nb_ration_stats_fabricants as quantiteRation,
			somme_niveau_piece_stats_fabricants as sommeNiveau')
		->where('niveau_hobbit_stats_fabricants = '.$data["niveau_hobbit_stats_fabricants"].' AND id_fk_hobbit_stats_fabricants = '.$data["id_fk_hobbit_stats_fabricants"]. ' AND mois_stats_fabricants = \''.$data["mois_stats_fabricants"].'\'')
		->group(array('quantitePiece', 'quantiteBatiment', 'quantiteRation', 'sommeNiveau'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);
		
		if (!isset($data["nb_piece_stats_fabricants"])) {
			$data["nb_piece_stats_fabricants"] = 0; 
		}
		
		if (!isset($data["nb_batiment_stats_fabricants"])) {
			$data["nb_batiment_stats_fabricants"] = 0;
		}
		
		if (!isset($data["nb_ration_stats_fabricants"])) {
			$data["nb_ration_stats_fabricants"] = 0;
		}
		
		if (!isset($data["somme_niveau_piece_stats_fabricants"])) {
			$data["somme_niveau_piece_stats_fabricants"] = 0;
		}
		
		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantitePiece = $resultat[0]["quantitePiece"];
			$quantiteBatiment = $resultat[0]["quantiteBatiment"];
			$quantiteRation = $resultat[0]["quantiteRation"];
			$sommeNiveau = $resultat[0]["sommeNiveau"];
			
			$dataUpdate['nb_piece_stats_fabricants'] = $quantitePiece;
			$dataUpdate['nb_batiment_stats_fabricants'] = $quantiteBatiment;
			$dataUpdate['nb_ration_stats_fabricants'] = $quantiteRation;
			$dataUpdate['somme_niveau_piece_stats_fabricants'] = $sommeNiveau;
			
			if (isset($data["nb_piece_stats_fabricants"])) {
				$dataUpdate['nb_piece_stats_fabricants'] = $quantitePiece + $data["nb_piece_stats_fabricants"];
				if ($dataUpdate['nb_piece_stats_fabricants'] < 0) {
					$dataUpdate['nb_piece_stats_fabricants'] = 0;
				}
			}
			
			if (isset($data["nb_batiment_stats_fabricants"])) {
				$dataUpdate['nb_batiment_stats_fabricants'] = $quantiteBatiment + $data["nb_batiment_stats_fabricants"];
				if ($dataUpdate['nb_batiment_stats_fabricants'] < 0) {
					$dataUpdate['nb_batiment_stats_fabricants'] = 0;
				}
			}
			
			if (isset($data["nb_ration_stats_fabricants"])) {
				$dataUpdate['nb_ration_stats_fabricants'] = $quantiteRation + $data["nb_ration_stats_fabricants"];
				if ($dataUpdate['nb_ration_stats_fabricants'] < 0) {
					$dataUpdate['nb_ration_stats_fabricants'] = 0;
				}
			}
			
			if (isset($data["somme_niveau_piece_stats_fabricants"])) {
				$dataUpdate['somme_niveau_piece_stats_fabricants'] = $sommeNiveau + $data["somme_niveau_piece_stats_fabricants"];
				if ($dataUpdate['somme_niveau_piece_stats_fabricants'] < 0) {
					$dataUpdate['somme_niveau_piece_stats_fabricants'] = 0;
				}
			}
			
			$where = 'niveau_hobbit_stats_fabricants = '.$data["niveau_hobbit_stats_fabricants"].' AND id_fk_hobbit_stats_fabricants = '.$data["id_fk_hobbit_stats_fabricants"]. ' AND mois_stats_fabricants = \''.$data["mois_stats_fabricants"].'\'';
			$this->update($dataUpdate, $where);
		}
	}
}