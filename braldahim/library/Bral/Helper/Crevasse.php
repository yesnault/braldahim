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
class Bral_Helper_Crevasse {

	public static function afficheResultat($braldun) {

		Zend_Loader::loadClass("Bral_Helper_Profil");
		$config = Zend_Registry::get('config');

		$retour = "";
			
		$retour .= '<b>Vous êtes tombé dans une crevasse.</b> <br><br>Vous vous retrouvez un niveau en dessus ';
		$retour .= 'et perdez 50% de vos PV restants.<br />';

		$retour .= '<table class="table_liste" style="border:0" >';
		$retour .= '<tbody>';
		$retour .= '<tr class="table_liste" align="left" valign="middle">';
		$retour .= '<td width="10%" nowrap>Points de Vie</td>';
		$retour .= '<td width="80%">';

		$retour .= Bral_Helper_Profil::afficheBarreVie($braldun->pv_restant_braldun, $config->game->pv_base, $braldun->vigueur_base_braldun, $config->game->pv_max_coef,  $braldun->pv_max_bm_braldun, $braldun->duree_prochain_tour_braldun);
		$retour .= '</td>';
		$retour .= '<td width="10%" nowrap>';
		$retour .= $braldun->pv_restant_braldun.' /';
		$retour .= '<span style="cursor:pointer" title="('.($config->game->pv_base + $braldun->vigueur_base_braldun * $config->game->pv_max_coef);
		if ($braldun->pv_max_bm_braldun >= 0) {
			$retour .= "+"; 	
		}
		$retour .= $braldun->pv_max_bm_braldun.')">';
		$retour .= ($config->game->pv_base + ($braldun->vigueur_base_braldun * $config->game->pv_max_coef) + $braldun->pv_max_bm_braldun);
		$retour .= '</span>';
		$retour .= '</td>';
		$retour .= '</tr>';
		$retour .= '</tbody>';
		$retour .= '</table>';
		return $retour;
	}
}
