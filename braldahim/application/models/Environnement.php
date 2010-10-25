<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Environnement extends Zend_Db_Table {
	protected $_name = 'environnement';
	protected $_primary = 'id_environnement';

	const NOM_PLAINE = "Plaine";
	const NOM_MARAIS = "Marais";
	const NOM_MONTAGNE = "Montagne";
	const NOM_GAZON = "Gazon";
	const NOM_CAVERNE = "Caverne";
	const NOM_MINE = "Mine";
	const NOM_TUNNEL = "Tunnel";

	const NOM_SYSTEME_PLAINE = "plaine";
	const NOM_SYSTEME_MARAIS = "marais";
	const NOM_SYSTEME_MONTAGNE = "montagne";
	const NOM_SYSTEME_GAZON = "gazon";
	const NOM_SYSTEME_CAVERNE = "caverne";
	const NOM_SYSTEME_MINE = "mine";
	const NOM_SYSTEME_TUNNEL = "tunnel";

	const INCONNU = "inconnu";

	function findAllQuete() {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('environnement', '*')
		->where('est_quete_environnement like ?', "oui");
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}