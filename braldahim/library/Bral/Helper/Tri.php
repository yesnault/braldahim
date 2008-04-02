<?php
class Bral_Helper_Tri {

	 public static function sens($colonne, $colonneTri, $sens) {
	 	
	 	if (( $colonne == $colonneTri) ) {
	 		if (($sens % 2 == 0)) {
		 		echo "[^]";
		 	} else {
	 			echo "[v]";
		 	}
	 	} else {
	 		echo "";
	 	}
	 }
}