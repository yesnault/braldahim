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
class Bral_Helper_DetailHotel {

 	public static function afficherPrix($e) {
 		Zend_Loader::loadClass("Bral_Helper_DetailPrix");
 		return Bral_Helper_DetailPrix::afficherPrix($e, "");
    }
}
