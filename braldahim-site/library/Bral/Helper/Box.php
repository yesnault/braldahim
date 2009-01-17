<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Box.php 595 2008-11-09 11:21:27Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-09 12:21:27 +0100 (Sun, 09 Nov 2008) $
 * $LastChangedRevision: 595 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Helper_Box {
	
    public static function haut() {
		$retour = '<b class="xb_t1"></b><b class="xb_t2"></b><b class="xb_t3"></b><b class="xb_t4"></b><b class="xb_t5"></b><b class="xb_t6"></b><b class="xb_t7"></b>';
		return $retour;
    }
    
    public static function bas() {
		$retour = '<b class="xb_b7"></b><b class="xb_b6"></b><b class="xb_b5"></b><b class="xb_b4"></b><b class="xb_b3"></b><b class="xb_b2"></b><b class="xb_b1"></b>';
		return $retour;
    }
}