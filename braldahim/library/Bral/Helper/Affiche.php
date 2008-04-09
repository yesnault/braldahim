<?php
class Bral_Helper_Affiche {
	
    public static function copie($texte) {
		return strip_tags(preg_replace('/<br \/> /', "\n\r", $texte));
    }
    
}
