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
class Bral_Util_BoutiquePlantes {
	
	public static function construireTabPrix($estFormulaire, $idRegion) {
		Zend_Loader::loadClass('TypePartieplante');
		Zend_Loader::loadClass('TypePlante');
		Zend_Loader::loadClass('StockPartieplante');
		
		$stockPartieplanteTable = new StockPartieplante();
		$stockPartieplanteRowset = $stockPartieplanteTable->findDernierStockByIdRegion($idRegion);
		
		if ($stockPartieplanteRowset == null) {
			return null;
		}
		
		$typePlantesTable = new TypePlante();
		$typePlantesRowset = $typePlantesTable->findAll();
		unset($typePlantesTable);
		
		$typePartiePlantesTable = new TypePartieplante();
		$typePartiePlantesRowset = $typePartiePlantesTable->fetchall();
		unset($typePartiePlantesTable);
		$typePartiePlantesRowset = $typePartiePlantesRowset->toArray();
	
		$tabTypePlantes = null;
		
		$numChamp = 0;
		
		foreach($typePartiePlantesRowset as $p) {
			foreach($typePlantesRowset as $t) {
				$val = false;
				$idChamp = "";
				
				if ($t["id_fk_partieplante1_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante2_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante3_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				if ($t["id_fk_partieplante4_type_plante"] == $p["id_type_partieplante"]) {
					$val = true;
				}
				
				if ($val === true) {
					$numChamp++;
					$idChamp = "valeur_".$numChamp;
				}
				
				if (!isset($tabTypePlantes[$t["categorie_type_plante"]][$t["nom_type_plante"]])) {
					$tab = array(
						'nom_type_plante' => $t["nom_type_plante"],
						'nom_systeme_type_plante' => $t["nom_systeme_type_plante"],
					);
					$tabTypePlantes[$t["categorie_type_plante"]][$t["nom_type_plante"]] = $tab;
				}
				
				$prixUnitaireVente = null;
				$prixUnitaireReprise = null;
				
				foreach ($stockPartieplanteRowset as $s) {
					if ($s["id_fk_type_stock_partieplante"] == $p["id_type_partieplante"] &&
						$s["id_fk_type_plante_stock_partieplante"] == $t["id_type_plante"]) {
						$prixUnitaireVente = $s["prix_unitaire_vente_stock_partieplante"];
						$prixUnitaireReprise = $s["prix_unitaire_reprise_stock_partieplante"];
						break;
					}
				}
			
				$tabTypePlantes[$t["categorie_type_plante"]]["a_afficher"] = true;
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["a_afficher"] = true;
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["possible"] = $val;
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["id_type_partieplante"] = $p["id_type_partieplante"];
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["id_type_plante"] = $t["id_type_plante"];
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["prixUnitaireVente"] = $prixUnitaireVente;
				$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["prixUnitaireReprise"] = $prixUnitaireReprise;
				
				if ($estFormulaire) {
					$tabTypePlantes[$t["categorie_type_plante"]]["type_plante"][$t["nom_type_plante"]]["parties"][$p["nom_systeme_type_partieplante"]]["id_champ"] = $idChamp;
					$tabTypePlantes["valeurs"][$idChamp]["id_type_plante"] = $t["id_type_plante"];
					$tabTypePlantes["valeurs"][$idChamp]["id_type_partieplante"] = $p["id_type_partieplante"];
					$tabTypePlantes["valeurs"][$idChamp]["prixUnitaireVente"] = $prixUnitaireVente;
					$tabTypePlantes["valeurs"][$idChamp]["prixUnitaireReprise"] = $prixUnitaireReprise;
					$tabTypePlantes["valeurs"][$idChamp]["nom_type_plante"] = $t["nom_type_plante"];
					$tabTypePlantes["valeurs"][$idChamp]["nom_type_partieplante"] = $p["nom_type_partieplante"];
				}
			}
		}
		
		$tabTypePlantes["nb_valeurs"] = $numChamp;
		unset($typePartiePlantesRowset);
		unset($typePlantesRowset);
		
		return $tabTypePlantes;
	}
}