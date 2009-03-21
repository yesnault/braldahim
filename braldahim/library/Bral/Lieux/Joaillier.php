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
class Bral_Lieux_Joaillier extends Bral_Lieux_Lieu {

	private $_utilisationPossible = false;
	private $_coutCastars = null;
	private $_tabCompetences = null;

	function prepareCommun() {
		
		$this->_coutCastars = $this->calculCoutCastars();
		$this->view->coutCastars = $this->_coutCastars;
		$this->view->achatPossibleCastars = ($this->view->user->castars_hobbit - $this->_coutCastars >= 0);
		// $this->view->utilisationPaPossible initialisé dans Bral_Lieux_Lieu
		
		if ($this->view->utilisationPaPossible == false) {
			return;
		}
		
		Zend_Loader::loadClass("LabanEquipement");
		Zend_Loader::loadClass("MotRunique");
		
		$this->view->effetMotF = false;

		$id_equipement_courant = $this->request->get("id_equipement");

		$tabEquipementsLaban = null;
		$labanEquipementTable = new LabanEquipement();
		$equipements = $labanEquipementTable->findByIdHobbit($this->view->user->id_hobbit);

		Zend_Loader::loadClass("Bral_Util_Equipement");
		
		if (count($equipements) > 0) {
			foreach($equipements as $e) {
				$selected = "";
				if ($id_equipement_courant == $e["id_laban_equipement"]) {
					$selected = "selected";
				}
			
				$t = array(
					"id_laban_equipement" => $e["id_laban_equipement"],
					"id_fk_recette_laban_equipement" => $e["id_fk_recette_laban_equipement"],
					"nom" => Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_laban_equipement"]),
					"qualite" => $e["nom_type_qualite"],
					"niveau" => $e["niveau_recette_equipement"],
					"nb_runes" => $e["nb_runes_laban_equipement"],
					"id_fk_type_piece" => $e["id_fk_type_piece_type_equipement"],
					"nom_systeme_type_piece" => $e["nom_systeme_type_piece"],
					"selected" => $selected
				);
				
				if ($id_equipement_courant == $e["id_laban_equipement"]) {
					$equipementCourant = $t;
				}
				$tabEquipementsLaban[] = $t;
			}
		}
		
		$this->view->nbEquipementsLaban = count($tabEquipementsLaban);
		$this->view->equipementsLaban = $tabEquipementsLaban;

		$this->view->equipementEnCours = null;
		
		if (isset($equipementCourant)) {
			Zend_Loader::loadClass("EquipementRune");
			$tabEquipementsRune = null;
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdEquipement($id_equipement_courant);
			
			foreach($equipementRunes as $e) {
				$tabEquipementsRune[] = array(
					"id_rune_equipement_rune" => $e["id_rune_equipement_rune"],
					"id_fk_type_rune_equipement_rune" => $e["id_fk_type_rune_equipement_rune"],
					"nom_type_rune" => $e["nom_type_rune"],
					"effet_type_rune" => $e["effet_type_rune"],
				);
			}
			
			$this->view->nbEquipementRune = count($tabEquipementsRune);
			$this->view->equipementRunes = $tabEquipementsRune;
			$this->view->equipementCourant = $equipementCourant;
			
			Zend_Loader::loadClass("LabanRune");
			$tabLabanRune = null;
			$labanRuneTable = new LabanRune();
			$labanRunes = $labanRuneTable->findByIdHobbit($this->view->user->id_hobbit);
			
			foreach($labanRunes as $l) {
				if ($l["est_identifiee_rune"] == "oui") {
					$tabLabanRune[$l["id_rune_laban_rune"]] = array(
						"id_fk_type_rune_laban_rune" => $l["id_fk_type_laban_rune"],
						"nom_type_rune" => $l["nom_type_rune"],
						"image_type_rune" => $l["image_type_rune"],
						"effet_type_rune" => $l["effet_type_rune"],
						"id_rune_laban_rune" => $l["id_rune_laban_rune"],
					);
				}
			}
			$this->view->nbLabanRune = count($tabLabanRune);
			$this->view->labanRunes = $tabLabanRune;
		}
	}

	function prepareFormulaire() {
		$this->view->coutCastars = $this->_coutCastars;
	}

	function prepareResultat() {
	
		$idEquipementLaban = $this->request->get("valeur_1");
		$nbRunes = $this->request->get("valeur_2");
		$runes = $this->request->get("valeur_3");
		
		if ((int) $idEquipementLaban."" != $this->request->get("valeur_1")."") {
			throw new Zend_Exception(get_class($this)." Equipement Laban invalide=".$idEquipementLaban);
		} else {
			$idEquipementLaban = (int)$idEquipementLaban;
		}
		
		if ($idEquipementLaban != $this->view->equipementCourant["id_laban_equipement"]) {
			throw new Zend_Exception(get_class($this)." idEquipement interdit A=".$idEquipementLaban. " B=".$this->view->equipementCourant["id_laban_equipement"]);
		}
		
		if ((int) $nbRunes."" != $this->request->get("valeur_2")."") {
			throw new Zend_Exception(get_class($this)." Nb Rune invalide=".$nbRunes);
		} else {
			$nbRunes = (int)$nbRunes;
		}
		
		if ($runes == "" || $runes == null) {
			throw new Zend_Exception(get_class($this)." Runes invalides=".$runes);
		}
		
		$tabRunesJs = explode(",", $runes);
		$tabRunes = null;
		// on regarde si les runes sont présentes dans le laban
		
		$tmp = $this->view->labanRunes;
		$nb = 0;
		foreach($tabRunesJs as $u) {
			$trouve = false;
			foreach($tmp as $k => $r) {
				if ((int)$u == $k) {
					$nb++;
					$tabRunes[$nb] = $r;
					$trouve = true;
					break;
				}
			}
			if ($trouve == false) {
				throw new Zend_Exception(get_class($this)." Rune invalide =".$u);
			}
		}
		
		if ($nb != $nbRunes) {
				throw new Zend_Exception(get_class($this)." Nombre de runes invalides A n1=".$nb. " n2=".$nbRunes);
		}
		
		// tous les emplacements runiques doivent etre utilises
		if ($nb == 0 || $nb != $this->view->equipementCourant["nb_runes"]) {
				throw new Zend_Exception(get_class($this)." Nombre de runes invalides B n1=".$nb. " n2=".$this->view->equipementCourant["nb_runes"]);
		}
		
		$this->view->suffixe = "";
		$this->calculSertir($tabRunes);
		$this->view->nbRunes = $nb;
		$this->view->tabRunes = $tabRunes;
		$this->view->user->castars_hobbit = $this->view->user->castars_hobbit - $this->_coutCastars;
		$this->majHobbit();
	}

	private function calculSertir($tabRunes) {
		$equipementRuneTable = new EquipementRune();
		$labanRuneTable = new LabanRune();
		
		// on regarde si les runes ne signifient pas un mot runique
		$motRuniqueTable = new MotRunique();
		
		$id_fk_mot_runique_laban_equipement = null;
		$nom_mot_runique = null;
		
		if ($this->view->equipementCourant["nom_systeme_type_piece"] == "arme_tir") { // si c'est une "arme de tir", on prend les mots runiques de "arme"
			$nomSystemeTypePiece = "arme";
		} else {
			$nomSystemeTypePiece = $this->view->equipementCourant["nom_systeme_type_piece"];	
		}
		
		$motsRowset = $motRuniqueTable->findByIdTypePieceAndRunes($nomSystemeTypePiece, $tabRunes);
		if (count($motsRowset) > 0) {
			foreach ($motsRowset as $m) {
				$id_fk_mot_runique_laban_equipement = $m["id_mot_runique"];
				$nom_mot_runique = $m["nom_systeme_mot_runique"];
				$this->view->suffixe = $m["suffixe_mot_runique"];
				break; // s'il y a plusieurs mots (ce qui devrait jamais arriver), on prend le premier
			}
		}
		
		$ordre = 0;
		foreach($tabRunes as $k => $v) {
			$ordre++;
			$data = array(
				'id_equipement_rune' => $this->view->equipementCourant["id_laban_equipement"],
				'id_rune_equipement_rune' => $v["id_rune_laban_rune"],
				'id_fk_type_rune_equipement_rune' => $v["id_fk_type_rune_laban_rune"],
				'ordre_equipement_rune' => $ordre
			);
			$equipementRuneTable->insert($data);
			
			// Suppression des runes du laban
			$where = "id_rune_laban_rune = ".$v["id_rune_laban_rune"];
			$labanRunes = $labanRuneTable->delete($where);
		}
		
		if ($id_fk_mot_runique_laban_equipement != null) {
			$labanEquipementTable = new LabanEquipement();
			$data = array(
				'id_fk_mot_runique_laban_equipement' => $id_fk_mot_runique_laban_equipement,
			);
			$where = "id_laban_equipement=".$this->view->equipementCourant["id_laban_equipement"];
			$labanEquipementTable->update($data, $where);
			
			Zend_Loader::loadClass("StatsMotsRuniques");
			$statsMotsRuniques = new StatsMotsRuniques();
			$moisEnCours  = mktime(0, 0, 0, date("m"), 2, date("Y"));
			$dataMotsRuniques["niveau_piece_stats_mots_runiques"] = $this->view->equipementCourant["niveau"];
			$dataMotsRuniques["id_fk_mot_runique_stats_mots_runiques"] = $id_fk_mot_runique_laban_equipement;
			$dataMotsRuniques["mois_stats_mots_runiques"] = date("Y-m-d", $moisEnCours);
			$dataMotsRuniques["nb_piece_stats_mots_runiques"] = 1;
			$dataMotsRuniques["id_fk_type_piece_stats_mots_runiques"] = $this->view->equipementCourant["id_fk_type_piece"];
			$statsMotsRuniques->insertOrUpdate($dataMotsRuniques);
		}
		
		if ($nom_mot_runique != null && $nom_mot_runique == "mot_f") {
			Zend_Loader::loadClass("EffetMotF");
			Zend_Loader::loadClass("TypeMonstre");
			
			$this->view->effetMotF = false;
			$typeMonstreTable = new TypeMonstre();
			
			$typeMonstreRowset = $typeMonstreTable->fetchall();
			$typeMonstreRowset = $typeMonstreRowset->toArray();
			
			$typesMonstre = null;
			
			foreach($typeMonstreRowset as $t) {
				$typesMonstre[] = array(
					"id_type_monstre" => $t["id_type_monstre"],
					//"nom_type_monstre" => $t["nom_type_monstre"],
				);
			}
			
			$nTypeMonstre = Bral_Util_De::get_de_specifique(0, count($typesMonstre)-1);
			$idTypeMonstre = $typesMonstre[$nTypeMonstre]["id_type_monstre"];
			
			$effetMotFTable = new EffetMotF();
			$data = array("id_fk_hobbit_effet_mot_f" => $this->view->user->id_hobbit, 
						  "id_fk_type_monstre_effet_mot_f" => $idTypeMonstre);
			$effetMotDTable->insert($data);
		}
	}
	
	function getListBoxRefresh() {
		return array("box_profil", "box_metier", "box_laban");
	}

	private function calculCoutCastars() {
		return 100;
	}
}