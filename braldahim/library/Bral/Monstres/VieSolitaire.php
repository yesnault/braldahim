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
class Bral_Monstres_VieSolitaire {
	
    public function __construct($view, $villes) {
        $this->config = Zend_Registry::get('config');
        $this->view = $view;
        $this->villes = $villes;
    }
    
    public function action() {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitairesAction - enter");
        try {
            // recuperation des monstres a jouer
			$monstreTable = new Monstre();
			$monstres = $monstreTable->findMonstresAJouerSansGroupe(true, $this->config->game->monstre->nombre_groupe_a_jouer);
			$this->traiteSolitaires($monstres, true);
			$monstres = $monstreTable->findMonstresAJouerSansGroupe(false, $this->config->game->monstre->nombre_groupe_a_jouer);
			$this->traiteSolitaires($monstres, false);
        } catch (Exception $e) {
            Bral_Util_Log::erreur()->err(get_class($this)." - vieSolitairesAction - Erreur:".$e->getTraceAsString());
            throw new Zend_Exception($e);
        }
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitairesAction - exit");
    }
	
    private function traiteSolitaires($solitaires, $aleatoire1D2) {
    	foreach($solitaires as $s) {
    		if ($aleatoire1D2 == false || ($aleatoire1D2 == true && Bral_Util_De::get_1d2() == 1)) {
                $this->vieSolitaireAction($s);
    		}
		}
    }
    
    private function vieSolitaireAction(&$monstre) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitaireAction - enter (id=".$monstre["id_monstre"].")");
		
        $estFuite = $this->calculFuiteSolitaire(&$monstre);
        if (!$estFuite) {
			$cible = self::reperageCible($monstre);
			if ($cible != null) { // si une cible est trouvee, on attaque
				$this->attaqueSolitaire($monstre, $cible);
			} else {
				$this->deplacementSolitaire($monstre);
			}
        }
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieSolitaireAction - exit");
    }

    private function reperageCible(&$monstre) {
    	Bral_Util_Log::viemonstres()->trace(get_class($this)." - reperageCible - enter");
    	$cible = null;
    	
		// on regarde s'il y a une cible en cours
		if ($monstre["id_fk_hobbit_cible_monstre"] != null) {
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - cible en cours");
			$hobbitTable = new Hobbit();
			$cible = $hobbitTable->findHobbitAvecRayon($monstre["x_monstre"], $monstre["y_monstre"], $monstre["vue_monstre"], $monstre["id_fk_hobbit_cible_monstre"]);
			if (count($cible) > 0) {
				$cible = $cible[0];
				$monstre["x_direction_monstre"] = $cible["x_hobbit"];
				$monstre["y_direction_monstre"] = $cible["y_hobbit"];
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible trouvee:".$cible["id_hobbit"]. " x=".$monstre["x_direction_monstre"]. " y=".$monstre["y_direction_monstre"]);
			} else {
				Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible trouvee:".$cible["id_hobbit"]. " x=".$monstre["x_direction_monstre"]. " y=".$monstre["y_direction_monstre"]);
			}
		} else { // pas de cible en cours
            $cible = null;
		}
        
        // si la cible n'est pas dans la vue, on en recherche une autre ou l'on se deplace
		if ($cible == null) {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - pas de cible en cours");
			$monstre["id_fk_hobbit_cible_monstre"] = null;
			$cible = $this->rechercheNouvelleCible($monstre);
		}
    	
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - reperageCible - exit");
		return $cible;
    }
    
    private function rechercheNouvelleCible(&$monstre) {
    	Bral_Util_Log::viemonstres()->trace(get_class($this)." - rechercheNouvelleCible - enter");
    	$hobbitTable = new Hobbit();
		$cibles = $hobbitTable->findLesPlusProches($monstre["x_monstre"], $monstre["y_monstre"], $monstre["vue_monstre"], 1, $monstre["id_fk_type_monstre"], false);
		if ($cibles != null) {
			$cible = $cibles[0];
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - nouvelle cible trouvee:".$cible["id_hobbit"]);
			$monstre["id_fk_hobbit_cible_monstre"] = $cible["id_hobbit"];
			$monstre["x_direction_monstre"] = $cible["x_hobbit"];
			$monstre["y_direction_monstre"] = $cible["y_hobbit"];
		} else {
			Bral_Util_Log::viemonstres()->debug(get_class($this)." - aucune cible trouvee x=".$monstre["x_monstre"]." y=".$monstre["y_monstre"]." vue=".$monstre["vue_monstre"]);
			$cible = null;
        }
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - rechercheNouvelleCible - exit");
        return $cible;
    }
    
   /**
     * Attaque de la cible.
     */
    protected function attaqueSolitaire(&$monstre, &$cible) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueSolitaire - enter");
        $mort_cible = false;

        $vieMonstre = Bral_Monstres_VieMonstre::getInstance();

		if ($cible != null) {
			$vieMonstre->setMonstre($monstre);
			$mortCible = false;
			// on regarde si la cible demandée est bien la cible du monstre
			Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueSolitaire - cible du monstre (".$monstre["id_monstre"].") : ".$cible["id_hobbit"]);
			$mortCible = $vieMonstre->attaqueCible($cible, $this->view);
     
			if ($mortCible == null) { // null => cible hors vue
				$vieMonstre->deplacementMonstre($monstre["x_direction_monstre"], $monstre["y_direction_monstre"]);
			} else if ($mortCible === true) {
				$monstre["id_fk_hobbit_cible_monstre"] = null;
				$cible = $this->rechercheNouvelleCible($monstre);
			}
		} else {
			$vieMonstre->deplacementMonstre($monstre["x_direction_monstre"], $monstre["y_direction_monstre"]);
		}
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - attaqueSolitaire - exit");
    }
    
    /**
     * Deplacement du solitaire.
     */
    protected function deplacementSolitaire(&$monstre, $fuite = false) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementSolitaire - enter");
        // si le role_a est sur la direction, on deplacement le groupe
        
        if ($fuite ||
        	(($monstre["x_monstre"] == $monstre["x_direction_monstre"]) && //
            ($monstre["y_monstre"] == $monstre["y_direction_monstre"]))) {
			
            if ($fuite) {
            	$ajoutFuite = 10;
            } else {
            	$ajoutFuite = 0;
            }
            
            $dx = Bral_Util_De::get_1d20() + $ajoutFuite;
            $dy = Bral_Util_De::get_1d20() + $ajoutFuite;
            
            $plusMoinsX = Bral_Util_De::get_1d2();
            $plusMoinsY = Bral_Util_De::get_1d2();

            if ($plusMoinsX == 1) {
                $monstre["x_direction_monstre"] = $monstre["x_direction_monstre"] - $dx;
            } else {
            	$monstre["x_direction_monstre"] = $monstre["x_direction_monstre"] + $dx;
            }
            
            if ($plusMoinsY == 1) {
                $monstre["y_direction_monstre"] = $monstre["y_direction_monstre"] - $dy;
            } else {
                $monstre["y_direction_monstre"] = $monstre["y_direction_monstre"] + $dy;
            }
            
            $tab = Bral_Monstres_VieMonstre::getTabXYRayon($monstre["niveau_monstre"], $this->villes, $monstre["x_direction_monstre"], $monstre["y_direction_monstre"], $dx, $dy);
            $monstre["x_direction_monstre"] = $tab["x_direction"];
            $monstre["y_direction_monstre"] = $tab["y_direction"];
            

            if ($monstre["x_direction_monstre"] <= $this->config->game->x_min) {
                $monstre["x_direction_monstre"] = -$this->config->game->x_min;
            }
            if ($monstre["x_direction_monstre"] >= $this->config->game->x_max) {
                $monstre["x_direction_monstre"] = -$this->config->game->x_max;
            }
            if ($monstre["y_direction_monstre"] <= $this->config->game->y_min) {
                $monstre["y_direction_monstre"] = -$this->config->game->y_min;
            }
            if ($monstre["y_direction_monstre"] >= $this->config->game->y_max) {
                $monstre["y_direction_monstre"] = -$this->config->game->y_max;
            }

            Bral_Util_Log::viemonstres()->debug(get_class($this)." - calcul nouvelle valeur direction x=".$monstre["x_direction_monstre"]." y=".$monstre["y_direction_monstre"]." ");
        }

		$vieMonstre = Bral_Monstres_VieMonstre::getInstance();
		$vieMonstre->setMonstre($monstre);
		$vieMonstre->deplacementMonstre($monstre["x_direction_monstre"], $monstre["y_direction_monstre"]);
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - deplacementSolitaire - exit");
    }
    
    /*
     * Fuite si moins de 20% restants en pv.
     */
    private function calculFuiteSolitaire(&$monstre) {
    	$retour = false;
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculFuiteSolitaire - enter");
		Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculFuiteSolitaire - pvRestants:".$monstre["pv_restant_monstre"]." pvMax:".$monstre["pv_max_monstre"]);
    	if (($monstre["pv_restant_monstre"] * 100 / $monstre["pv_max_monstre"]) <= 20) {
    		Bral_Util_Log::viemonstres()->debug(get_class($this)." - Fuite du monstre - enter");
	    	$monstre["id_fk_hobbit_cible_monstre"] = null;
	    	$this->deplacementSolitaire(&$monstre, $fuite = false);
	    	$retour = true;
    	}
    	Bral_Util_Log::viemonstres()->trace(get_class($this)." - calculFuiteSolitaire - exit (".$retour.")");
    	return $retour;
    }
}