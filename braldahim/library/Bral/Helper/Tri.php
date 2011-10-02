<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_Tri
{

	public static function sens($colonne, $colonneTri, $sens)
	{

		if (($colonne == $colonneTri)) {
			if (($sens % 2 == 0)) {
				echo "[^]";
			} else {
				echo "[v]";
			}
		} else {
			echo "";
		}
	}
}