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
class ChampTaupe extends Zend_Db_Table {
	protected $_name = 'champ_taupe';
	protected $_primary = "id_champ_taupe";

	const ETAT_VIVANT = 'vivant';
	const ETAT_DETRUIT = 'detruit';
	const ETAT_ENTRETENU = 'entretenu';


	function findByIdChamp($idChamp) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ_taupe', '*')
		->from('champ', '*')
		->where('id_champ = ?', $idChamp)
		->where('id_fk_champ_taupe = id_champ')
		->order('numero_champ_taupe');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
	
	function findByIdChampNumeroTaupeVivant($idChamp, $numero) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ_taupe', '*')
		->from('champ', '*')
		->where('id_champ = ?', $idChamp)
		->where('id_fk_champ_taupe = id_champ')
		->where('numero_champ_taupe = ?', $numero)
		->where('etat_champ_taupe = ?', 'vivant')
		->order('numero_champ_taupe');

		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}

	function entretenir($data) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('champ_taupe', array('count(*) as nombre', 'id_champ_taupe', 'etat_champ_taupe', 'taille_champ_taupe', 'numero_champ_taupe'))
		->where('x_champ_taupe = ?',$data["x_champ_taupe"])
		->where('y_champ_taupe = ?',$data["y_champ_taupe"])
		->where('id_fk_champ_taupe = ?',$data["id_fk_champ_taupe"])
		->group(array('id_champ_taupe', 'etat_champ_taupe', 'taille_champ_taupe', 'numero_champ_taupe'));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		if (count($resultat) == 0) { // insert
			$data["etat_champ_taupe"] = self::ETAT_ENTRETENU;
			$this->insert($data);

			$retour = array(
				'etat' => self::ETAT_ENTRETENU,
				'taille' => null,
				'numero' => null,
			);

		} else { // update
			if ($resultat[0]["etat_champ_taupe"] != "vivant") {
				throw new Zend_Exception("Erreur ChampTaupe::entretenir etat");
			}

			$idChampTaupe = $resultat[0]["id_champ_taupe"];
			$where = ' id_champ_taupe = '.$idChampTaupe;

			$dataUpdate['date_entretien_champ_taupe'] = $data['date_entretien_champ_taupe'];
			$dataUpdate['etat_champ_taupe'] = self::ETAT_DETRUIT;

			$this->update($dataUpdate, $where);
			$retour = array(
				'etat' => self::ETAT_DETRUIT,
				'taille' => $resultat[0]["taille_champ_taupe"],
				'numero' => $resultat[0]["numero_champ_taupe"],
			);
		}
		return $retour;
	}

}
