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
class Bral_Helper_Box {
	
    public static function haut($style="") {
		return '<b class="xb_t1"></b><b class="xb_t2"></b><b class="xb_t3'.$style.'"></b><b class="xb_t4'.$style.'"></b><b class="xb_t5'.$style.'"></b><b class="xb_t6'.$style.'"></b><b class="xb_t7'.$style.'"></b>';
    }
    
    public static function bas($style="") {
		return '<b class="xb_b7'.$style.'"></b><b class="xb_b6'.$style.'"></b><b class="xb_b5'.$style.'"></b><b class="xb_b4'.$style.'"></b><b class="xb_b3'.$style.'"></b><b class="xb_b2"></b><b class="xb_b1"></b>';
    }
}