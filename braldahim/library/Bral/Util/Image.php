<?php

class Bral_Util_Image {

	private function __construct(){}

	public static function controlAvatar($filename) {
		return self::controlImage($filename, 110, 110);
	}
	
	public static function controlBlason($filename) {
		return self::controlImage($filename, 300, 400);
	}
	
	private static function controlImage($filename, $height, $width) {
		$retour = false;
		
		$size = getimagesize($filename);
		$fp = fopen($filename, "rb");
		
		if ($size && $fp) {
			if ($size[0] == 110 && $size[0]  == 110 
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