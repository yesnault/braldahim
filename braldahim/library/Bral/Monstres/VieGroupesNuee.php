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
	
	public function action() {
		 $this->vieGroupesAction($this->config->game->groupe_monstre->type->nuee);
	}

    /**
     * Attaque de la cible.
     */
    protected function attaqueGroupe(&$monstre_role_a, &$groupe, &$monstres, &$cible) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - enter");
        $mort_cible = false;

        $vieMonstre = Bral_Monstres_VieMonstre::getInstance();

        foreach($monstres as $m) {
            if ($cible != null) {
                $vieMonstre->setMonstre($m);
                $mortCible = false;
                $cibleDuMonstre = null;
            	// on regarde si la cible demandée est bien la cible du monstre
				if ($cible["id_hobbit"] == $m["id_fk_hobbit_cible_monstre"] || $m["id_fk_hobbit_cible_monstre"] == null) {
					Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - cible du groupe (".$groupe["id_groupe_monstre"].") : ".$cible["id_hobbit"]);
					$mortCible = $vieMonstre->attaqueCible($cible, $this->view);
				} else {
					Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - cible du monstre (".$m["id_monstre"].") : ".$m["id_fk_hobbit_cible_monstre"]);
					$hobbitTable = new Hobbit();
           			$cibleDuMonstre = $hobbitTable->findById($m["id_fk_hobbit_cible_monstre"]);
           			$cibleDuMonstre = $cibleDuMonstre->toArray();
					$vieMonstre->attaqueCible($cibleDuMonstre, $this->view);
				}
                
				if ($cibleDuMonstre != null) {
					$vieMonstre->deplacementMonstre($cibleDuMonstre["x_hobbit"], $cibleDuMonstre["y_hobbit"]);
				} else if ($mortCible == null) { // null => cible hors vue
                    $vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
                } else if ($mortCible === true) {
                    $groupe["id_fk_hobbit_cible_groupe_monstre"] = null;
                    $cible = $this->rechercheNouvelleCible($monstre_role_a, $groupe, $monstres);
                }
            } else {
                $vieMonstre->deplacementMonstre($groupe["x_direction_groupe_monstre"], $groupe["y_direction_groupe_monstre"]);
            }
        }
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueGroupe - exit");
    }

    /**
     * Deplacement du groupe.
     */
    protected function deplacementGroupe(&$monstre_role_a, &$groupe, &$monstres) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementGroupe - enter");
        // si le role_a est sur la direction, on deplacement le groupe
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