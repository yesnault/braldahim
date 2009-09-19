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
class VentePrixPartiePlante extends Zend_Db_Table {
	protected $_name = 'vente_prix_partieplante';
	protected $_primary = array("id_fk_type_vente_prix_partieplante","id_fk_type_plante_vente_prix_partieplante", "id_fk_vente_prix_partieplante");

   function findByIdVente($idVente) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('vente_prix_partieplante', '*')
		->from('type_partieplante', '*')
		->from('type_plante', '*')
		->where('id_fk_vente_prix_partieplante',(int)$idVente)
		->where('vente_prix_partieplante.id_fk_type_vente_prix_partieplante = type_partieplante.id_type_partieplante')
		->where('vente_prix_partieplante.id_fk_type_plante_vente_prix_partieplante = type_plante.id_type_plante');
		$sql = $select->__toString();
		
		return $db->fetchAll($sql);
    }
}
