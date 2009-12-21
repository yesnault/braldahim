<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Box.php 1049 2009-01-24 15:31:36Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-01-24 16:31:36 +0100 (sam., 24 janv. 2009) $
 * $LastChangedRevision: 1049 $
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