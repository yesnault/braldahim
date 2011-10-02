<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_Bougrie
{

	public static function render()
	{

		$retour = '';

		Zend_Loader::loadClass("Bougrie");
		Zend_Loader::loadClass("Bral_Util_BBParser");
		$bougrieTable = new Bougrie();
		$bougrie = $bougrieTable->findAleatoire();
		if ($bougrie != null) {
			//$retour .= '<p class="titrea textalic titreasizeb cadre" style="margin-left:5%;width:90%">Bougrie</p>';
			$retour .= '<div class="bralannonce" title="Bougrie n°' . $bougrie["id_bougrie"] . '">';

			$retour .= '<p class="bougrie_text" >';
			$retour .= Bral_Util_BBParser::bbcodeReplace($bougrie["texte_bougrie"]);

			if ($bougrie["regle_bougrie"] != null && $bougrie["regle_bougrie"] != '') {
				$retour .= '<br /><br /><a href="' . $bougrie["regle_bougrie"] . '">Voir Règles</a>.';
			}

			$retour .= '</p>';
			$retour .= '</div>';
		}

		return $retour;
	}
}
