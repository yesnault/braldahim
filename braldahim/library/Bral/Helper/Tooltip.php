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

	public static function maketip($contenu, $titre="", $close = false, $justify=false, $width='280', $boutonCopier=true, $backslashes = true, $functionCopierToolTip = "copierTooltip()") {
		$b = "\\";
		if ($backslashes != true) {
			$b = "";
		}
		
		if ($justify) {
			$width = '300';
		}
		$retour = "<div id=".$b."'_tip".$b."' class=".$b."'yoo-tooltip".$b."'>";
		$retour .= "<div class=".$b."'yoodefault".$b."'>";
		$retour .= "<div class=".$b."'tooltip-tl".$b."' style=".$b."'width: ".$width."px;".$b."'>";
		$retour .= "<div class=".$b."'tooltip-tr".$b."'>";
		$retour .= "<div class=".$b."'tooltip-t".$b."' style=".$b."'height: 15px;".$b."'><div class=".$b."'tooltip-arrow".$b."' style=".$b."'height: 23px;".$b."'></div></div>";
		$retour .= "</div>";
		$retour .= "</div>";
		$retour .= "<div class=".$b."'tooltip-l".$b."' style=".$b."'width: ".$width."px;".$b."'>";
		$retour .= "<div class=".$b."'tooltip-r".$b."'>";
		$retour .= "<div class=".$b."'tooltip-m".$b."'>".$titre."<br />";
		if ($justify === true) {
			$retour .= "<div ><p style=".$b."'text-align:justify".$b."'>";
		}
		$retour .= "<div id=".$b."'contenuTooltip".$b."'>".$contenu."</div>";
		if ($boutonCopier) {
			$retour .= "<div id=".$b."'contenuTooltipCopie".$b."' style=".$b."'display:none".$b."'><textarea onClick=".$b."'javascript:this.select();".$b."' rows=".$b."'4".$b."' cols=".$b."'33".$b."' id=".$b."'contenuTooltipCopieText".$b."'>".preg_replace('/\<br(\s*)?\/?\>/i', "<br>", (strip_tags($titre."<br>".$contenu, "<br>")))."</textarea></div>";
		}
		if ($justify === true) {
			$retour .= "</p></div>";
		}

		if ($close) {
			$retour .= "<center>";
			if ($boutonCopier) {
				$retour .= "<a href=".$b."'javascript:void(0);".$b."' onClick=".$b."'return ".$functionCopierToolTip.";".$b."'>Copier</a> ";
			}
			$retour .= " <a href=".$b."'javascript:void(0);".$b."' onClick=".$b."'return cClick();".$b."'>Fermer</a>";
			$retour .= "</center>";
		}

		$retour .= "</div>";
		$retour .= "</div>";
		$retour .= "</div>";
		$retour .= "<div class=".$b."'tooltip-bl".$b."' style=".$b."'width: ".$width."px;".$b."'>";
		$retour .= "<div class=".$b."'tooltip-br".$b."'>";
		$retour .= "<div class=".$b."'tooltip-b".$b."' style=".$b."'height: 15px;".$b."'></div>";
		$retour .= "</div>";
		$retour .= "</div>";
		$retour .= "</div>";
		$retour .= "</div>";

		return $retour;
	}

	public static function jsTip($contenu, $titre="", $justify = false, $click = true, $width = '250', $boutonCopier = true, $jsBefore="") {
		$retour = " onmouseover=\"".$jsBefore."return overlib('".self::maketip($contenu, $titre, self::$CLOSE_NO, $justify, $width, $boutonCopier)."');\" ";
		if ($click == true) {
			$retour .= " onclick=\"return overlib('".self::maketip($contenu, $titre, self::$CLOSE_YES, $justify, $width, $boutonCopier)."', STICKY, DRAGCAP, CAPICON,'/public/images/pixel.gif', CAPTION,  ' ', CLOSECLICK, EXCLUSIVE, POSITIONCAP,'bottom');\" ";
		}
		$retour .= " onmouseout=\"return nd();\" style=\"cursor:pointer\"";
		return $retour;
	}
	
	public static function jsTipFromDiv($divId, $jsBeforeMouseOver) {
		$retour = " onmouseover=\"".$jsBeforeMouseOver."\"";
		$retour .= " onclick=\"return overlib($('".$divId."').innerHTML, STICKY, DRAGCAP, CAPICON,'/public/images/pixel.gif', CAPTION,  ' ', CLOSECLICK, EXCLUSIVE, POSITIONCAP,'bottom');\" ";
		$retour .= " onmouseout=\"return nd();\" style=\"cursor:pointer\"";
		return $retour;
	}
}
