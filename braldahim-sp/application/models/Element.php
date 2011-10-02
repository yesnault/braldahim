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
class Element extends Zend_Db_Table
{
	protected $_name = 'element';
	protected $_primary = array('x_element', 'y_element');

	function selectVue($x_min, $y_min, $x_max, $y_max, $z)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element', '*')
			->where('x_element <= ?', $x_max)
			->where('x_element >= ?', $x_min)
			->where('y_element <= ?', $y_max)
			->where('y_element >= ?', $y_min)
			->where('z_element = ?', $z);
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function findByCase($x, $y, $z)
	{
		return $this->selectVue($x, $y, $x, $y, $z);
	}

	function insertOrUpdate($data)
	{
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('element', 'count(*) as nombre,
		quantite_peau_element as quantitePeau, 
		quantite_cuir_element as quantiteCuir,
		quantite_castar_element as quantiteCastar,
		quantite_fourrure_element as quantiteFourrure,
		quantite_planche_element as quantitePlanche,
		quantite_rondin_element as quantiteRondin')
			->where('x_element = ?', $data["x_element"])
			->where('y_element = ?', $data["y_element"])
			->group(array('quantitePeau', 'quantiteCuir', 'quantiteCastar', 'quantiteFourrure', 'quantitePlanche', 'quantiteRondin'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$this->insert($data);
		} else { // update
			$nombre = $resultat[0]["nombre"];
			$quantitePeau = $resultat[0]["quantitePeau"];
			$quantiteCuir = $resultat[0]["quantiteCuir"];
			$quantiteCastar = $resultat[0]["quantiteCastar"];
			$quantiteFourrure = $resultat[0]["quantiteFourrure"];
			$quantitePlanche = $resultat[0]["quantitePlanche"];
			$quantiteRondin = $resultat[0]["quantiteRondin"];

			$dataUpdate['quantite_peau_element'] = $quantitePeau;
			$dataUpdate['quantite_cuir_element'] = $quantiteCuir;
			$dataUpdate['quantite_fourrure_element'] = $quantiteFourrure;
			$dataUpdate['quantite_castar_element'] = $quantiteCastar;
			$dataUpdate['quantite_planche_element'] = $quantitePlanche;
			$dataUpdate['quantite_rondin_element'] = $quantiteRondin;

			if (isset($data["quantite_peau_element"])) {
				$dataUpdate['quantite_peau_element'] = $quantitePeau + $data["quantite_peau_element"];
			}
			if (isset($data['quantite_cuir_element'])) {
				$dataUpdate['quantite_cuir_element'] = $quantiteCuir + $data["quantite_cuir_element"];
			}
			if (isset($data['quantite_fourrure_element'])) {
				$dataUpdate['quantite_fourrure_element'] = $quantiteFourrure + $data["quantite_fourrure_element"];
			}
			if (isset($data['quantite_castar_element'])) {
				$dataUpdate['quantite_castar_element'] = $quantiteCastar + $data["quantite_castar_element"];
			}
			if (isset($data['quantite_planche_element'])) {
				$dataUpdate['quantite_planche_element'] = $quantitePlanche + $data["quantite_planche_element"];
			}
			if (isset($data['quantite_rondin_element'])) {
				$dataUpdate['quantite_rondin_element'] = $quantiteRondin + $data["quantite_rondin_element"];
			}

			$where = ' x_element = ' . $data["x_element"];
			$where .= ' AND y_element = ' . $data["y_element"];

			if ($dataUpdate['quantite_peau_element'] <= 0 &&
				$dataUpdate['quantite_cuir_element'] <= 0 &&
				$dataUpdate['quantite_fourrure_element'] <= 0 &&
				$dataUpdate['quantite_planche_element'] <= 0 &&
				$dataUpdate['quantite_castar_element'] <= 0 &&
				$dataUpdate['quantite_rondin_element'] <= 0
			) { // delete
				$this->delete($where);
			} else { // update
				$this->update($dataUpdate, $where);
			}
		}
	}
}
