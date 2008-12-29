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
abstract class Bral_Monstres_VieGroupes {
	
    public function __construct($view) {
        Zend_Loader::loadClass("Bral_Monstres_VieMonstre");
        Zend_Loader::loadClass("Ville");
        $this->config = Zend_Registry::get('config');
        $this->view = $view;
    }

    abstract function action();
    
    public function vieGroupesAction($type) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGroupesAction - enter");
        try {
            // recuperation des monstres a jouer
			$groupeMonstreTable = new GroupeMonstre();
			$groupes = $groupeMonstreTable->findGroupesAJouer(true, $this->config->game->monstre->nombre_groupe_a_jouer, $type);
			$this->traiteGroupes($groupes, true);
			$groupes = $groupeMonstreTable->findGroupesAJouer(false, $this->config->game->monstre->nombre_groupe_a_jouer, $type);
			$this->traiteGroupes($groupes, false);
        } catch (Exception $e) {
            Bral_Util_Log::erreur()->err(get_class($this)." - vieGroupesAction - Erreur:".$e->getTraceAsString());
            throw new Zend_Exception($e);
        }
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGroupesAction - exit");
    }
	
    private function traiteGroupes($groupes, $aleatoire1D2) {
    	foreach($groupes as $g) {
    		if ($aleatoire1D2 == false || ($aleatoire1D2 == true && Bral_Util_De::get_1d2() == 1)) {
                $this->vieGroupeAction($g);
                $this->updateGroupe($g);
    		}
		}
    }
    
    private function vieGroupeAction(&$groupe) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGroupeAction - enter (id=".$groupe["id_groupe_monstre"].")");
        $monstreTable = new Monstre();
        $monstres = $monstreTable->findByGroupeId($groupe["id_groupe_monstre"]);

        if (count($monstres) == 0) {
            $this->suppressionGroupe($groupe);
            return;
        }

        Bral_Util_Log::viemonstres()->debug(get_class($this)." - nb monstres dans le groupe (".$groupe["id_groupe_monstre"].") = ".count($monstres));

        $monstre_role_a = $this->majRoleA($groupe, $monstres);

        // on regarde s'il y a une cible en cours
        if ($groupe["id_fk_hobbit_cible_groupe_monstre"] != null) {
            Bral_Util_Log::viemonstres()->trace(get_class($this)." - cible en cours");
            $hobbitTable = new Hobbit();
            $cible = $hobbitTable->findHobbitAvecRayon($monstre_role_a["x_monstre"], $monstre_role_a["y_monstre"], $monstre_role_a["vue_monstre"], $groupe["id_fk_hobbit_cible_groupe_monstre"]);
            if (count($cible) > 0) {
                $cible = $cible[0];
                $groupe["x_direction_groupe_monstre"] = $cible["x_hobbit"];
                $groupe["y_direction_groupe_monstre"] = $cible["y_hobbit"];
                Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible trouvee:".$cible["id_hobbit"]. " x=".$groupe["x_direction_groupe_monstre"]. " y=".$groupe["y_direction_groupe_monstre"]);
            } else {
            	Bral_Util_Log::viemonstres()->debug(get_class($this)." - cible trouvee:".$cible["id_hobbit"]. " x=".$groupe["x_direction_groupe_monstre"]. " y=".$groupe["y_direction_groupe_monstre"]);
            }
        } else { // pas de cible en cours
            $cible = null;
        }

        // si la cible n'est pas dans la vue, on en recherche une autre ou l'on se deplace
        if ($cible == null) {
            Bral_Util_Log::viemonstres()->debug(get_class($this)." - pas de cible en cours");
            $groupe["id_fk_hobbit_cible_groupe_monstre"] = null;
            $cible = $this->rechercheNouvelleCible($monstre_role_a, $groupe, $monstres);
        }

        if ($cible != null) { // si une cible est trouvee, on attaque
            $this->attaqueGroupe($monstre_role_a, $groupe, $monstres, $cible);
        } else {
            $this->deplacementGroupe($monstre_role_a, $groupe, $monstres);
        }

        $this->majDlaGroupe($groupe, $monstres);
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - vieGroupeAction - exit");
    }

    /**
     * Mise a jour du role A.
     */
    private function majRoleA(&$groupe, &$monstres) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - majRoleA - enter");
        // on regarde si le role_a est toujours vivant
        $id_role_a = $groupe["id_role_a_groupe_monstre"];
        $vivant = false;
        foreach($monstres as $m) {
            if ($m["id_monstre"] == $id_role_a) {
                $vivant = true;
                $monstre_role_a = $m;
                break;
            }
        }
        // si le role_a est mort, il faut le recreer
        if ($vivant === false) {
            $idx = Bral_Util_De::get_de_specifique(0, count($monstres)-1);
            $id_role_a = $monstres[$idx]["id_monstre"];
            Bral_Util_Log::viemonstres()->debug(get_class($this)." - Nouveau role A =".$id_role_a."");
            $groupe["id_role_a_groupe_monstre"] = $id_role_a;
            $monstre_role_a = $monstres[$idx];
        }
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - majRoleA - exit");
        return $monstre_role_a;
    }

    /**
     * Recherche d'une nouvelle cible.
     *
     * @param monstre $monstre_role_a
     * @param groupeMonstre $groupe
     * @return hobbit : nouvelle cible ou null si non trouvee
     */
    private function rechercheNouvelleCible(&$monstre_role_a, &$groupe, &$monstres) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - rechercheNouvelleCible - exit");
        $hobbitTable = new Hobbit();

        foreach($monstres as $monstre) {
            $cibles = $hobbitTable->findLesPlusProches($monstre["x_monstre"], $monstre["y_monstre"], $monstre["vue_monstre"], 1, $monstre["id_type_monstre"]);
            if ($cibles != null) {
                $cible = $cibles[0];
                Bral_Util_Log::viemonstres()->debug(get_class($this)." - nouvelle cible trouvee:".$cible["id_hobbit"]);
                $groupe["id_fk_hobbit_cible_groupe_monstre"] = $cible["id_hobbit"];
                $groupe["x_direction_groupe_monstre"] = $cible["x_hobbit"];
                $groupe["y_direction_groupe_monstre"] = $cible["y_hobbit"];
                $monstre_role_a = $monstre;
                $groupe["id_role_a_groupe_monstre"] = $monstre["id_monstre"];
                Bral_Util_Log::viemonstres()->debug(get_class($this)." - nouveau role A defini:".$monstre["id_monstre"]);
                break;
            } else {
                Bral_Util_Log::viemonstres()->debug(get_class($this)." - aucune cible trouvee x=".$monstre["x_monstre"]." y=".$monstre["y_monstre"]." vue=".$monstre_role_a["vue_monstre"]);
                $cible = null;
            }
        }

        Bral_Util_Log::viemonstres()->trace(get_class($this)." - rechercheNouvelleCible - exit");
        return $cible;
    }

    /**
     * mise a jour de la DLA du groupe, suivant la dla la plus lointaine d'un
     * membre du groupe.
     */
    private function majDlaGroupe(&$groupe, &$monstres) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - majDlaGroupe - enter");
        foreach($monstres as $m) {
            if ($groupe["date_fin_tour_groupe_monstre"] < $m["date_fin_tour_monstre"]) {
                $groupe["date_fin_tour_groupe_monstre"] = $m["date_fin_tour_monstre"];
                Bral_Util_Log::viemonstres()->trace(get_class($this)." - maj :".$m["date_fin_tour_monstre"]);
            }
        }
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - majDlaGroupe - exit");
    }

    /**
     * Mise à jour du groupe en base.
     */
    private function updateGroupe(&$groupe) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateGroupe - enter");
        $groupeMonstreTable = new GroupeMonstre();
        $data = array(
            "id_fk_hobbit_cible_groupe_monstre" => $groupe["id_fk_hobbit_cible_groupe_monstre"],
            "id_role_a_groupe_monstre" => $groupe["id_role_a_groupe_monstre"],
            "x_direction_groupe_monstre" => $groupe["x_direction_groupe_monstre"],
            "y_direction_groupe_monstre" => $groupe["y_direction_groupe_monstre"],
            "date_fin_tour_groupe_monstre" => $groupe["date_fin_tour_groupe_monstre"],
        	"date_a_jouer_groupe_monstre" => null,
        );
        $where = "id_groupe_monstre=".$groupe["id_groupe_monstre"];
        $groupeMonstreTable->update($data, $where);
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - updateGroupe - exit");
    }

    /**
     * Suppression du groupe de la base.
     */
    private function suppressionGroupe(&$groupe) {
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - suppressionGroupe - enter (id_groupe=".$groupe["id_groupe_monstre"].")");
        $groupeMonstreTable = new GroupeMonstre();
        $where = "id_groupe_monstre=".$groupe["id_groupe_monstre"];
        $groupeMonstreTable->delete($where);
        Bral_Util_Log::viemonstres()->trace(get_class($this)." - suppressionGroupe - exit");
    }
}