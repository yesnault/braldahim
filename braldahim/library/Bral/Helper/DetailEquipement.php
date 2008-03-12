<?php
class Bral_Helper_DetailEquipement {
    public static function afficher($e) {
        
    	 $text = "Qualit&eacute; : ".htmlentities($e["qualite"])."<br>";
     	 $text .= "Niveau : ".$e["niveau"]."<br>";
     	 $text .= "Nombre d\'emplacement runique : ".$e["nb_runes"]."<br>";
     	 
     	 if (count($e["runes"]) > 1) $s='s'; else $s="";
     	 
     	 
     	 $text .= count($e["runes"]) ." Rune$s sertie$s "."<br>";
     	 foreach($e["runes"] as $r) {
     	 	$text .= "<img src=\'/public/images/runes/".$r["image_type_rune"]."\'  class=\'rune\' title=\'".$r["nom_type_rune"]."\' n&deg;".$r["id_rune_equipement_rune"]." alt=\'".$r["nom_type_rune"]."\' n&deg;".$r["id_rune_equipement_rune"]."  />";
     	 }
     	 $titre = htmlentities($e["nom"])." n&deg;".$e["id_equipement"]. " - D&eacute;tails";
     	 
        return "<span onmouseover=\"return overlib('".$text."',CAPTION,'".$titre."');\"".
        " onclick=\"return overlib('".$text."', STICKY, CAPTION, '".$titre."', CLOSECLICK, EXCLUSIVE);\"".
        " onmouseout=\"return nd();\">".htmlentities($e["nom"]).", n&deg;".$e["id_equipement"]."</span>";
    }
}
?>