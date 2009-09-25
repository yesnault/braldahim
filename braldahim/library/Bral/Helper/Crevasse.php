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
class Bral_Helper_Crevasse {

	public static function afficheResultat($hobbit) {

		Zend_Loader::loadClass("Bral_Helper_Profil");
		$config = Zend_Registry::get('config');

		$retour = "";
			
		$retour .= '<b>Vous êtes tombé dans une crevasse.</b> <br><br>Vous vous retrouvez un niveau en dessus';
		$retour .= 'et perdez 50% de votre bonus de balance de faim restant.<br />';

		$retour .= '<table class="table_liste" style="border:0" >';
		$retour .= '<tbody>';
		$retour .= '<tr class="table_liste" align="left" valign="middle">';
		$retour .= '<td width="10%" nowrap>Points de Vie</td>';
		$retour .= '<td width="80%">';

		$retour .= Bral_Helper_Profil::afficheBarreVie($hobbit->pv_restant_hobbit, $config->game->pv_base, $hobbit->vigueur_base_hobbit, $config->game->pv_max_coef,  $hobbit->pv_max_bm_hobbit, $hobbit->duree_prochain_tour_hobbit);
		$retour .= '</td>';
		$retour .= '<td width="10%" nowrap>';
		$retour .= $hobbit->pv_restant_hobbit.' /';
		$retour .= '<span style="cursor:pointer" title="('.($config->game->pv_base + $hobbit->vigueur_base_hobbit * $config->game->pv_max_coef);
		if ($hobbit->pv_max_bm_hobbit >= 0) {
			$retour .= "+"; 	
		}
		$retour .= $hobbit->pv_max_bm_hobbit.')">';
		$retour .= ($config->game->pv_base + ($hobbit->vigueur_base_hobbit * $config->game->pv_max_coef) + $hobbit->pv_max_bm_hobbit);
		$retour .= '</span>';
		$retour .= '</td>';
		$retour .= '</tr>';
		$retour .= '</tbody>';
		$retour .= '</table>';
		return $retour;
	}
}
