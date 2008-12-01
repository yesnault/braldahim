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
class Bral_Helper_Tooltip {
	private static $CLOSE_YES = true;
	private static $CLOSE_NO = false;
	
    public static function maketip($contenu, $titre="", $close = false, $justify=false, $width='280') {
    	if ($justify) {
    		$width = '300';
    	}
    	$retour = "<div id=\'_tip\' class=\'yoo-tooltip\'>";
			$retour .= "<div class=\'yoodefault\'>";
				$retour .= "<div class=\'tooltip-tl\' style=\'width: ".$width."px;\'>";
					$retour .= "<div class=\'tooltip-tr\'>";
						$retour .= "<div class=\'tooltip-t\' style=\'height: 15px;\'><div class=\'tooltip-arrow\' style=\'height: 23px;\'></div></div>";
					$retour .= "</div>";
				$retour .= "</div>";
				$retour .= "<div class=\'tooltip-l\' style=\'width: ".$width."px;\'>";
					$retour .= "<div class=\'tooltip-r\'>";
						$retour .= "<div class=\'tooltip-m\'>".$titre."<br />";
				    	if ($justify === true) {
							$retour .= "<div ><p style=\'text-align:justify\'>";
						}
						$retour .= $contenu;
						if ($justify === true) {
							$retour .= "</p></div>";
						}
						if ($close) {
							$retour .= "<center><a href=\'javascript:void(0);\' onClick=\'return cClick();\'> Fermer</a></center>";
						}
						$retour .= "</div>";
					$retour .= "</div>";
				$retour .= "</div>";
				$retour .= "<div class=\'tooltip-bl\' style=\'width: ".$width."px;\'>";
					$retour .= "<div class=\'tooltip-br\'>";
						$retour .= "<div class=\'tooltip-b\' style=\'height: 15px;\'></div>";
					$retour .= "</div>";
				$retour .= "</div>";
			$retour .= "</div>";
		$retour .= "</div>";
		
		return $retour;
    }
    
	public static function jsTip($contenu, $titre="", $justify = false, $click = true, $width = '250') {
		$retour = " onmouseover=\"return overlib('".self::maketip($contenu, $titre, self::$CLOSE_NO, $justify, $width)."');\" ";
		if ($click == true) {
			$retour .= " onclick=\"return overlib('".self::maketip($contenu, $titre, self::$CLOSE_YES, $justify, $width)."', STICKY, DRAGCAP, CAPICON,'/public/images/pixel.gif', CAPTION,  ' ', CLOSECLICK, EXCLUSIVE, POSITIONCAP,'bottom');\" ";
		}
		$retour .= " onmouseout=\"return nd();\" style=\"cursor:pointer\"";
      	return $retour;
	}
}
