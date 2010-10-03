<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_Fermer {

	public static function affiche() {
		return "<input type='button' class='button1' value='Fermer' onclick='this.disabled=true;_get_(\"/interface/clear\");fermeBralBox();'>";
	}
}

