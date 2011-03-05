<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Equipements.php 2806 2010-07-14 22:13:50Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2010-07-15 00:13:50 +0200 (jeu., 15 juil. 2010) $
 * $LastChangedRevision: 2806 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Scripts_Equipements extends Bral_Scripts_Script {

	public function getType() {
		return self::TYPE_STATIQUE;
	}

	public function getEtatService() {
		return self::SERVICE_ACTIVE;
	}

	public function getVersion() {
		return 1;
	}

	public function calculScriptImpl() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Equipements - calculScriptImpl - enter -");

		$retour = null;
		$retour .= $this->calculEquipement();

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Equipements - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculEquipement() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Equipements - calculEquipement - enter -");
		$retour = "";
		$this->calculEquipementBraldun($retour);
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Equipements - calculEquipement - exit -");
		return $retour;
	}

	private function calculEquipementBraldun(&$retour) {
		Zend_Loader::loadClass("BraldunEquipement");
		Zend_Loader::loadClass("Bral_Util_Equipement");
		Zend_Loader::loadClass("EquipementBonus");
		Zend_Loader::loadClass("EquipementRune");

		$equipementTable = new BraldunEquipement();
		$equipementsRowset = $equipementTable->findByIdBraldun($this->braldun->id_braldun);

		$retour .= "EQUIPEMENT;id_equipement;";
		$retour .= "nom;qualite;emplacement;niveau;id_type_equipement;id_type_emplacement;";
		$retour .= "nom_type_emplacement;nom_systeme_type_emplacement;nb_runes;armure;force;agilite;vigueur;sagesse;vue;attaque;degat;defense;suffixe;poids;etat_courant;etat_initial;ingredient;nom_systeme_type_ingredient;";
		$retour .= "armure_equipement_bonus;vernis_bm_armure_equipement_bonus;agilite_equipement_bonus;vernis_bm_agilite_equipement_bonus;";
		$retour .= "force_equipement_bonus;vernis_bm_force_equipement_bonus;sagesse_equipement_bonus;vernis_bm_sagesse_equipement_bonus;";
		$retour .= "vigueur_equipement_bonus;vernis_bm_vigueur_equipement_bonus;vernis_bm_vue_equipement_bonus;vernis_bm_attaque_equipement_bonus;";
		$retour .= "vernis_bm_degat_equipement_bonus;vernis_bm_defense_equipement_bonus;";
		$retour .= "id_rune_equipement_rune1;nom_type_rune1;";
		$retour .= "id_rune_equipement_rune2;nom_type_rune2;";
		$retour .= "id_rune_equipement_rune3;nom_type_rune3;";
		$retour .= "id_rune_equipement_rune4;nom_type_rune4;";
		$retour .= "id_rune_equipement_rune5;nom_type_rune5;";
		$retour .= "id_rune_equipement_rune6;nom_type_rune6;";
		$retour .= PHP_EOL;

		$equipementBonusTable = new EquipementBonus();
		$equipementRuneTable = new EquipementRune();

		if ($equipementsRowset != null) {
			foreach($equipementsRowset as $e) {
				$retour .= "EQUIPEMENT;".$e["id_equipement_hequipement"].';';

				$retour .= Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]).';';
				$retour .= $e["nom_type_qualite"].';';
				$retour .= $e["nom_type_emplacement"].';';
				$retour .= $e["niveau_recette_equipement"].';';
				$retour .= $e["id_type_equipement"].';';
				$retour .= $e["id_type_emplacement"].';';
				$retour .= $e["nom_type_emplacement"].';';
				$retour .= $e["nom_systeme_type_emplacement"].';';
				$retour .= $e["nb_runes_equipement"].';';
				$retour .= $e["armure_equipement"].';';
				$retour .= $e["force_equipement"].';';
				$retour .= $e["agilite_equipement"].';';
				$retour .= $e["vigueur_equipement"].';';
				$retour .= $e["sagesse_equipement"].';';
				$retour .= $e["vue_recette_equipement"].';';
				$retour .= $e["attaque_equipement"].';';
				$retour .= $e["degat_equipement"].';';
				$retour .= $e["defense_equipement"].';';
				$retour .= $e["suffixe_mot_runique"].';';
				$retour .= $e["poids_equipement"].';';
				$retour .= $e["etat_courant_equipement"].';';
				$retour .= $e["etat_initial_equipement"].';';
				$retour .= $e["nom_type_ingredient"].';';
				$retour .= $e["nom_systeme_type_ingredient"].';';

				$equipementBonus = $equipementBonusTable->findByIdsEquipement(array($e["id_equipement_hequipement"]));

				if (count($equipementBonus) > 0) {
					foreach($equipementBonus as $b) {
						$retour .= $b["armure_equipement_bonus"].';';
						$retour .= $b["vernis_bm_armure_equipement_bonus"].';';
						$retour .= $b["agilite_equipement_bonus"].';';
						$retour .= $b["vernis_bm_agilite_equipement_bonus"].';';
						$retour .= $b["force_equipement_bonus"].';';
						$retour .= $b["vernis_bm_force_equipement_bonus"].';';
						$retour .= $b["sagesse_equipement_bonus"].';';
						$retour .= $b["vernis_bm_sagesse_equipement_bonus"].';';
						$retour .= $b["vigueur_equipement_bonus"].';';
						$retour .= $b["vernis_bm_vigueur_equipement_bonus"].';';

						$retour .= $b["vernis_bm_vue_equipement_bonus"].';';
						$retour .= $b["vernis_bm_attaque_equipement_bonus"].';';
						$retour .= $b["vernis_bm_degat_equipement_bonus"].';';
						$retour .= $b["vernis_bm_defense_equipement_bonus"].';';
					}
				}
					
				$equipementRunes = $equipementRuneTable->findByIdsEquipement(array($e["id_equipement_hequipement"]));

				$i = 0;
				if ($equipementRunes != null) {
					foreach($equipementRunes as $e) {
						$retour .= $e["id_rune_equipement_rune"].';';
						$retour .= $e["nom_type_rune"].';';
						$i++;
					}
				}
				if ($i < 6) {
					while($i <= 6) {
						$i++;
						$retour .= ";;";
					}
				}

				$retour .= PHP_EOL;
			}
		} else {
			$retour .= "AUCUN_EQUIPEMENT";
		}
	}
}