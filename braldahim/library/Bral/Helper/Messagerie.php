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
class Bral_Helper_Messagerie {

	public static function destinatairesZoneJs($xMin, $yMin, $xMax, $yMax, $idHobbit, $texte) {

		$hobbitTable = new Hobbit();
		$hobbits = $hobbitTable->selectVue($xMin, $yMin, $xMax, $yMax, $idHobbit);
		unset($hobbitTable);
		
		$js = "";
		foreach($hobbits as $h) {
			if ($h["id_fk_jos_users_hobbit"] != null) {
				$nom = $h['prenom_hobbit']. " ". $h['nom_hobbit'];
				$js .= "makeJsListeAvecSupprimer('valeur_2_dest', ";
				$js .= "'".addslashes($nom)."', ";
				$js .= "'".$h["id_fk_jos_users_hobbit"]."',";
				$js .= "'".$h["id_hobbit"]."');";
			}
		}
		
		$retour = "";
		if ($js != "") {
			$retour = '<label id="message_nb_label" class="alabel" style="text-decoration: underline;" onClick="'.$js.'">'.$texte.'</span>';
		}
		
		return $retour;
	}

}


