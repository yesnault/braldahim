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
class Bral_Monstres_VieGroupesNuee extends Bral_Monstres_VieGroupes {
	
	const PHASE_NOMINAL = 1;
	const PHASE_ATTAQUE_1 = 2;
	const PHASE_ATTAQUE_2 = 3;
	const PHASE_ATTAQUE_3 = 4;
	
	public function action() {
		 $this->vieGroupesAction($this->config->game->groupe_monstre->type->nuee);
	}

    /**
     * Attaque de la cible.
     */
    protected function attaqueGroupe(&$monstre_role_a, &$groupe, &$monstres, &$cible) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - enter");
		
        if ($groupe["date_phase_tactique_groupe_monstre"] < $this->dateCourante) {
        	Bral_Util_Log::viemonstres()->debug(get_class($this)." - changement phase tactique (".$groupe["date_phase_tactique_groupe_monstre"].") (".$groupe["phase_tactique_groupe_monstre"].")");
			if ($groupe["phase_tactique_groupe_monstre"] <= self::PHASE_NOMINAL) {
				$groupe["phase_tactique_groupe_monstre"] = self::PHASE_ATTAQUE_1;
			} else if ($groupe["phase_tactique_groupe_monstre"] == self::PHASE_ATTAQUE_1) {
				$groupe["phase_tactique_groupe_monstre"] = self::PHASE_ATTAQUE_2;
			} else if ($groupe["phase_tactique_groupe_monstre"] == self::PHASE_ATTAQUE_2) {
				$groupe["phase_tactique_groupe_monstre"] = self::PHASE_ATTAQUE_3;
			} else if ($groupe["phase_tactique_groupe_monstre"] == self::PHASE_ATTAQUE_3) {
				$groupe["phase_tactique_groupe_monstre"] = self::PHASE_ATTAQUE_1;
			}
        }
        
        if ($groupe["phase_tactique_groupe_monstre"] == self::PHASE_ATTAQUE_3) {
        	$this->phaseTactique3(&$monstre_role_a, &$groupe, &$monstres, &$cible);
        	$this->majDateTactique(&$groupe, &$monstres);
        	return;
        }
            
        $vieMonstre = Bral_Monstres_VieMonstre::getInstance();

        foreach($monstres as $m) {
            if ($cible != null) {
                $vieMonstre->setMonstre($m);
                $koCible = false;
                $cibleDuMonstre = null;
            	// on regarde si la cible demandée est bien la cible du monstre
				if ($cible["id_hobbit"] == $m["id_fk_hobbit_cible_monstre"] || $m["id_fk_hobbit_cible_monstre"] == null) {
					Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - cible du groupe (".$groupe["id_groupe_monstre"].") : ".$cible["id_hobbit"]);
					$koCible = $vieMonstre->attaqueCible($cible, $this->view);
				} else {
					Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - cible du monstre (".$m["id_monstre"].") : ".$m["id_fk_hobbit_cible_monstre"]);
					$hobbitTable = new Hobbit();
           			$cibleDuMonstre = $hobbitTable->findById($m["id_fk_hobbit_cible_monstre"]);
           			$cibleDuMonstre = $cibleDuMonstre->toArray();
					$vieMonstre->attaqueCible($cibleDuMonstre, $this->view);
				}
                
				if ($cibleDuMonstre != null) {
					$vieMonstre->deplacementMonstre($cibleDuMonstre["x_hobbit"], $cibleDuMonstre["y_hobbit"]);
				} else if ($koCible == null) { // null => cible hors vue
                    $vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
                } else if ($koCible === true) {
                    $groupe["id_fk_hobbit_cible_groupe_monstre"] = null;
                    $cible = $this->rechercheNouvelleCible($monstre_role_a, $groupe, $monstres);
                }
            } else {
                $vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
            }
        }
        
        $this->majDateTactique(&$groupe, &$monstres);
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - exit");
    }
    
	private function phaseTactique3(&$monstre_role_a, &$groupe, &$monstres, &$cible) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - phaseTactique3 - enter");
		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		
		foreach($monstres as $m) {
            if ($cible != null) {
            	// si le monstre est sur la même case que la cible, deplacement a une case
            	if ($m["x_monstre"] == $cible["x_hobbit"] && $m["y_monstre"] == $cible["y_hobbit"]) {
            		Bral_Util_Log::viemonstres()->trace(get_class($this)." - phaseTactique3 - deplacement du monstre a une case");
            		// deplacement
					$dx = -1;
					$dy = -1;
            
					if (Bral_Util_De::get_1d2() == 1) {
						$dx = 1;
					}
					if (Bral_Util_De::get_1d2() == 1) {
						$dy = 1;
					}
            
            		$vieMonstre->setMonstre($m);
            		$vieMonstre->deplacementMonstre($cible["x_hobbit"] + $dx, $cible["y_hobbit"] + $dy);
            	} else {
            		// rien a faire ici
            	}
            }
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - phaseTactique3 - exit");
	}
	
	private function majDateTactique(&$groupe, &$monstres) {
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - majDateTactique - enter");
		
		foreach($monstres as $m) {
			if ($groupe["id_role_a_groupe_monstre"] == $m["id_monstre"]) {
				$groupe["date_phase_tactique_groupe_monstre"] = $m["date_fin_tour_monstre"];
				break;
			}
		}
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - majDateTactique - exit");
	}
	
    /**
     * Deplacement du groupe.
     */
    protected function deplacementGroupe(&$monstre_role_a, &$groupe, &$monstres) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementGroupe - enter");
        // si le role_a est sur la direction, on deplacement le groupe
        
        $groupe["phase_tactique_groupe_monstre"] = self::PHASE_NOMINAL;
        
        if (($monstre_role_a["x_monstre"] == $groupe["x_direction_groupe_monstre"]) && //
            ($monstre_role_a["y_monstre"] == $groupe["y_direction_groupe_monstre"])) {

            $dx = Bral_Util_De::get_1d20();
            $dy = Bral_Util_De::get_1d20();
            
            $plusMoinsX = Bral_Util_De::get_1d2();
            $plusMoinsY = Bral_Util_De::get_1d2();

            if ($plusMoinsX == 1) {
                $groupe["x_direction_groupe_monstre"] = $groupe["x_direction_groupe_monstre"] - $dx;
            } else {
            	$groupe["x_direction_groupe_monstre"] = $groupe["x_direction_groupe_monstre"] + $dx;
            }
            
            if ($plusMoinsY == 1) {
                $groupe["y_direction_groupe_monstre"] = $groupe["y_direction_groupe_monstre"] - $dy;
            } else {
                $groupe["y_direction_groupe_monstre"] = $groupe["y_direction_groupe_monstre"] + $dy;
            }

            $tab = Bral_Monstres_VieMonstre::getTabXYRayon($monstre_role_a["niveau_monstre"], false, $this->villes, $groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"], $dx, $dy);
            $monstre["x_direction_groupe_monstre"] = $tab["x_direction"];
            $monstre["y_direction_groupe_monstre"] = $tab["y_direction"];
            
            if ($groupe["x_direction_groupe_monstre"] <= $this->config->game->x_min) {
                $groupe["x_direction_groupe_monstre"] = -$this->config->game->x_min;
            }
            if ($groupe["x_direction_groupe_monstre"] >= $this->config->game->x_max) {
                $groupe["x_direction_groupe_monstre"] = -$this->config->game->x_max;
            }
            if ($groupe["y_direction_groupe_monstre"] <= $this->config->game->y_min) {
                $groupe["y_direction_groupe_monstre"] = -$this->config->game->y_min;
            }
            if ($groupe["y_direction_groupe_monstre"] >= $this->config->game->y_max) {
                $groupe["y_direction_groupe_monstre"] = -$this->config->game->y_max;
            }

            Bral_Util_Log::viemonstres()->debug(get_class($this)." - calcul nouvelle valeur direction x=".$groupe["x_direction_groupe_monstre"]." y=".$groupe["y_direction_groupe_monstre"]." ");
        }

        $vieMonstre = Bral_Monstres_VieMonstre::getInstance();
        foreach($monstres as $m) {
            $vieMonstre->setMonstre($m);
            $vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
        }
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementGroupe - exit");
    }
}