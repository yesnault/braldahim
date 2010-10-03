<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Image {

	private function __construct(){}

	public static function controlAvatar($filename) {
		return self::controlImage($filename, 110, 110);
	}
	
	public static function controlBlason($filename) {
		return self::controlImage($filename, 300, 400);
	}
	
	private static function controlImage($filename, $width, $height) {
		$retour = false;
		
		$size = @getimagesize($filename); // @ pour supprimer les warnings
		$fp = @fopen($filename, "rb"); // @ pour supprimer les warnings
		
		if ($size && $fp) {
			if ($size[0] == $width && $size[1] == $height
				&& 
					($size["mime"] == "image/gif" 
					|| $size["mime"] == "image/jpeg" 
					|| $size["mime"] == "image/png" 
					)
				) {
				$retour = true;
			} else {
				Bral_Util_Log::tech()->err("Bral_Util_Image - taille ou mime KO - height=".$size[0] . " width=".$size[1]. " mime=".$size["mime"]);
			}
		} else {
			Bral_Util_Log::tech()->err("Bral_Util_Image - size ou KO - filname=".$filename);
		}
		return $retour;
	}
}