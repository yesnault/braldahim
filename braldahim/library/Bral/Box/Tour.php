<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Tour extends Bral_Box_Box
{

    function __construct($request, $view, $interne)
    {
        Zend_Loader::loadClass("Bral_Util_Log");

        $this->_request = $request;
        $this->view = $view;
        $this->view->affichageInterne = $interne;

        $braldunTable = new Braldun();
        $braldunRowset = $braldunTable->find($this->view->user->id_braldun);
        $this->braldun = $braldunRowset->current();

        $this->nomsTour = Zend_Registry::get('nomsTour');
        $this->view->user->nom_tour = $this->nomsTour[$this->view->user->tour_position_braldun];
        $this->calculInfoTour();
    }

    function getTitreOnglet()
    {
        return false;
    }

    function getNomInterne()
    {
        return "box_tour";
    }

    function setDisplay($display)
    {
        $this->view->display = $display;
    }

    function render()
    {
        $paginator = null;
        $this->view->messages = Bral_Util_Messagerie::prepareMessages($this->view->user->id_braldun, $paginator, null, 1, 5, true);
        Zend_Loader::loadClass("Message");
        $messageTable = new Message();
        $this->view->nbMessagesNonLus = $messageTable->countByToIdNotRead($this->view->user->id_braldun);
        $this->view->user->nom_tour = $this->nomsTour[$this->view->user->tour_position_braldun];
        return $this->view->render("interface/tour.phtml");
    }

    public function modificationTour()
    {
        Bral_Util_Log::tour()->debug(get_class($this) . " modificationTour - enter - user=" . $this->view->user->id_braldun);

        $this->is_update_tour = false;
        $this->is_nouveau_tour = false;

        if ($this->view->user->activation === false) {
            return false;
        }

        // Calcul de la nouvelle date de fin
        $date_courante = date("Y-m-d H:i:s");
        $this->is_nouveau_tour = $this->calcul_debut_nouveau($date_courante);

        // nouveau tour (ou ko : en cas de ko : la date de fin de tour doit aªtre positionnee au ko)
        if ($this->is_nouveau_tour) {
            Bral_Util_Log::tour()->debug(get_class($this) . " Nouveau tour");
            $this->calculDLA();

            $this->braldun->tour_position_braldun = $this->view->config->game->tour->position_latence;
            $this->is_update_tour = true;
        }

        /* Si des DLA ont ete manquees, on prend comme date de debut la date courante
           * et la date de fin, la date courante + 6 heures, le joueur se trouve
           * directement en position de cumul
           */

        Bral_Util_Log::tour()->debug(get_class($this) . " date_fin_latence=" . $this->braldun->date_fin_latence_braldun);
        Bral_Util_Log::tour()->debug(get_class($this) . " date_debut_cumul" . $this->braldun->date_debut_cumul_braldun);
        Bral_Util_Log::tour()->debug(get_class($this) . " date_courante=" . $date_courante);
        Bral_Util_Log::tour()->debug(get_class($this) . " date fin tour=" . $this->braldun->date_fin_tour_braldun);
        Bral_Util_Log::tour()->debug(get_class($this) . " tour position=" . $this->braldun->tour_position_braldun);

        $this->is_tour_manque = false;
        // Mise a jour du nombre de PA + position tour
        if ($this->braldun->est_ko_braldun == "oui") {
            Bral_Util_Log::tour()->debug(get_class($this) . " KO du braldun");
            $mdate = date("Y-m-d H:i:s");
            $this->braldun->date_debut_cumul_braldun = $mdate;
            $this->braldun->date_fin_tour_braldun = Bral_Util_ConvertDate::get_date_add_time_to_date($mdate, $this->view->config->game->tour->inscription->duree_base_cumul);
            $this->braldun->date_debut_tour_braldun = Bral_Util_ConvertDate::get_date_remove_time_to_date($mdate, $this->view->config->game->tour->inscription->duree_base_cumul);
            $this->braldun->date_fin_latence_braldun = Bral_Util_ConvertDate::get_date_remove_time_to_date($mdate, $this->view->config->game->tour->inscription->duree_base_milieu);
            $this->braldun->tour_position_braldun = $this->view->config->game->tour->position_cumul;
            $this->is_update_tour = true;
            $this->braldun->pa_braldun = $this->view->config->game->pa_max_cumul;
        } else if ($date_courante > $this->braldun->date_fin_tour_braldun) { // Perte d'un tour
            Bral_Util_Log::tour()->debug(get_class($this) . " Perte d'un tour");
            $this->braldun->date_fin_tour_braldun = Bral_Util_ConvertDate::get_date_add_time_to_date($date_courante, $this->view->config->game->tour->duree_tour_manque);
            $this->braldun->tour_position_braldun = $this->view->config->game->tour->position_cumul;
            $this->braldun->pa_braldun = $this->view->config->game->pa_max_cumul;
            $this->braldun->est_engage_next_dla_braldun = "non";
            $this->braldun->est_engage_braldun = "non";
            $this->is_tour_manque = true;
            $this->is_update_tour = true;
        } elseif (($date_courante < $this->braldun->date_fin_latence_braldun) // Latence
                && $this->is_nouveau_tour
        ) {
            Bral_Util_Log::tour()->debug(get_class($this) . " Latence Tour");
            $this->braldun->tour_position_braldun = $this->view->config->game->tour->position_latence;
            $this->braldun->pa_braldun = 0;
            $this->is_update_tour = true;
        } elseif (($date_courante >= $this->braldun->date_fin_latence_braldun && $date_courante < $this->braldun->date_debut_cumul_braldun) // Milieu
                && ((!$this->is_nouveau_tour && ($this->braldun->tour_position_braldun != $this->view->config->game->tour->position_milieu))
                        || ($this->is_nouveau_tour))
        ) {
            Bral_Util_Log::tour()->debug(get_class($this) . " Milieu Tour");
            $this->braldun->tour_position_braldun = $this->view->config->game->tour->position_milieu;
            $this->braldun->pa_braldun = $this->view->config->game->pa_max;
            $this->is_update_tour = true;
        } elseif (($date_courante >= $this->braldun->date_debut_cumul_braldun && $date_courante < $this->braldun->date_fin_tour_braldun) // Cumul
                && ((!$this->is_nouveau_tour && ($this->braldun->tour_position_braldun != $this->view->config->game->tour->position_cumul))
                        || ($this->is_nouveau_tour))
        ) {
            Bral_Util_Log::tour()->debug(get_class($this) . " Cumul tour");
            // Si le joueur a deja  eu des PA
            if ($this->braldun->tour_position_braldun == $this->view->config->game->tour->position_milieu && !$this->is_nouveau_tour) {
                Bral_Util_Log::tour()->debug(get_class($this) . " Le joueur a deja eu des PA");
                $this->braldun->pa_braldun = $this->braldun->pa_braldun + $this->view->config->game->pa_max;
            } else { // S'il vient d'activer et qu'il n'a jamais eu de PA dans ce tour
                Bral_Util_Log::tour()->debug(get_class($this) . " Le joueur n'a pas encore eu de PA");
                $this->braldun->pa_braldun = $this->view->config->game->pa_max_cumul;
            }
            $this->braldun->tour_position_braldun = $this->view->config->game->tour->position_cumul;
            $this->is_update_tour = true;
        }

        if (($this->is_update_tour) || ($this->is_nouveau_tour) || ($this->braldun->est_ko_braldun == "oui")) {
            if ($this->braldun->est_pnj_braldun == 'oui') {
                $this->braldun->pa_braldun = 100;
            }
            Bral_Util_Log::tour()->debug(get_class($this) . " modificationTour - exit - true");
            return true;
        } else {
            Bral_Util_Log::tour()->debug(get_class($this) . " modificationTour - exit - false");
            return false;
        }
    }

    public function getWarningFinTour()
    {
        $retour = null;
        $date_courante = date("Y-m-d H:i:s");

        $dateFin = Bral_Util_ConvertDate::get_date_add_time_to_date($date_courante, '00:30:00');

        if ($this->braldun->date_fin_tour_braldun < $dateFin && $this->braldun->pa_braldun > 0) {
            $retour = "Votre tour se termine bientôt et il vous reste " . $this->braldun->pa_braldun . " PA à jouer ! ";
        }

        return $retour;
    }

    public function activer()
    {
        Bral_Util_Log::tour()->trace(get_class($this) . " activer - enter -");

        if ($this->view->user->activation === false) {
            Bral_Util_Log::tour()->trace(get_class($this) . " le joueur n'a pas activé la DLA");
            return false;
        }

        $this->view->effetPotion = false;
        $this->view->effetBraldun = false;

        $this->view->effetMotB = false;
        $this->view->effetMotE = false;
        $this->view->effetMotK = false;
        $this->view->effetMotM = false;
        $this->view->effetMotN = false;
        $this->view->effetMotO = false;
        $this->view->effetMotU = false;
        $this->view->effetMotV = false;

        $this->view->ciblesEffetN = null;
        $this->view->ciblesEffetO = null;
        $this->view->ciblesEffetU = null;

        $this->is_tour_manque = false;

        $this->view->charretteDetruite = false;

        $this->modificationTour();

        // Mise a jour en cas de KO
        $this->calculKo();

        // Si c'est un nouveau tour, on met les BM de force, agi, sag, vue, vig a  0
        // Ensuite, on les recalcule suivant l'equipement porte et les potions en cours
        if ($this->is_nouveau_tour) {
            Bral_Util_Log::tour()->trace(get_class($this) . " activer - is_nouveau_tour - true");
            $this->braldun->force_bm_braldun = 0;
            $this->braldun->agilite_bm_braldun = 0;
            $this->braldun->vigueur_bm_braldun = 0;
            $this->braldun->sagesse_bm_braldun = 0;
            $this->braldun->vue_bm_braldun = 0;
            $this->braldun->regeneration_braldun = 0;
            $this->braldun->armure_naturelle_braldun = 0;
            $this->braldun->armure_equipement_braldun = 0;
            $this->braldun->armure_bm_braldun = 0;
            $this->braldun->pv_max_bm_braldun = 0;
            $this->braldun->bm_attaque_braldun = 0;
            $this->braldun->bm_degat_braldun = 0;
            $this->braldun->bm_defense_braldun = 0;
            $this->braldun->duree_bm_tour_braldun = 0;
            $this->braldun->bm_marcher_braldun = 0;
            $this->braldun->nb_tour_blabla_braldun = 0;

            // Nouvelle DLA
            $this->braldun->nb_dla_jouees_braldun = $this->braldun->nb_dla_jouees_braldun + 1;

            // Recalcul de l'armure naturelle
            $this->braldun->armure_naturelle_braldun = Bral_Util_Commun::calculArmureNaturelle($this->braldun->force_base_braldun, $this->braldun->vigueur_base_braldun);

            /* Application du malus de vue. */
            $this->braldun->vue_bm_braldun = $this->braldun->vue_malus_braldun;
            /* Remise a  zero du malus de vue. */
            $this->braldun->vue_malus_braldun = 0;

            /* Application du malus d'agilite. */
            $this->braldun->agilite_bm_braldun = $this->braldun->agilite_malus_braldun;
            /* Remise a  zero du malus d'agilite. */
            $this->braldun->agilite_malus_braldun = 0;

            // Calcul du poids transportable. // c'est aussi mis a  jour dans l'eujimnasiumne
            Zend_Loader::loadClass("Bral_Util_Poids");
            $this->braldun->poids_transportable_braldun = Bral_Util_Poids::calculPoidsTransportable($this->braldun->force_base_braldun);
            $this->braldun->poids_transporte_braldun = Bral_Util_Poids::calculPoidsTransporte($this->braldun->id_braldun, $this->braldun->castars_braldun);

            $this->calculBMEquipement();
            $this->calculBMPotion();
            $this->calculBMEffet();
            $this->calculBMSpecialisation();

            // Mise a  jour de la regeneration // c'est aussi mis a  jour dans l'eujimnasiumne
            $this->braldun->regeneration_braldun = floor($this->braldun->vigueur_base_braldun / 4) + 1;

            // calcul des pvs restants avec la regeneration
            $this->braldun->pv_max_braldun = Bral_Util_Commun::calculPvMaxBaseSansEffetMotE($this->view->config, $this->braldun->vigueur_base_braldun);

            $this->braldun->est_engage_braldun = $this->braldun->est_engage_next_dla_braldun;
            $this->braldun->est_engage_next_dla_braldun = 'non';

            $effetMotE = Bral_Util_Commun::getEffetMotE($this->view->user->id_braldun);
            if ($effetMotE != null) {
                Bral_Util_Log::tour()->trace(get_class($this) . " activer - effetMotE Actif - effetMotE=" . $effetMotE);
                $this->view->effetMotE = true;
                $this->braldun->pv_max_bm_braldun = $this->braldun->pv_max_bm_braldun - $effetMotE;
            }

            if ($this->braldun->pv_restant_braldun > $this->braldun->pv_max_braldun + $this->braldun->pv_max_bm_braldun) {
                $this->braldun->pv_restant_braldun = $this->braldun->pv_max_braldun + $this->braldun->pv_max_bm_braldun;
            }

            $this->calculPv();

            if ($this->est_ko == false) {
                $this->braldun->est_intangible_braldun = "non";
            }

            if ($this->braldun->est_intangible_prochaine_braldun == "oui") {
                $this->braldun->est_intangible_braldun = "oui";
                $this->braldun->est_intangible_prochaine_braldun = "non";
            }

            Zend_Loader::loadClass("Bral_Util_Faim");
            Bral_Util_Faim::calculBalanceFaim($this->braldun);
            Zend_Loader :: loadClass("Bral_Util_Tour");
            Bral_Util_Tour::updateTourTabac($this->braldun);

            Zend_Loader::loadClass("Bral_Monstres_Util");
            Bral_Monstres_Util::marqueAJouer($this->braldun->x_braldun, $this->braldun->y_braldun);

            Zend_Loader::loadClass("Bral_Util_Charrette");
            $this->view->charretteDetruite = Bral_Util_Charrette::calculNouvelleDlaCharrette($this->braldun->id_braldun, $this->braldun->niveau_braldun, $this->braldun->x_braldun, $this->braldun->y_braldun, $this->braldun->z_braldun);

            Zend_Loader::loadClass("Bral_Util_Equipement");
            $this->view->equipementDetruit = Bral_Util_Equipement::calculNouvelleDlaEquipement($this->braldun->id_braldun, $this->braldun->x_braldun, $this->braldun->y_braldun);

            Zend_Loader::loadClass("Bral_Util_Soule");
            $this->view->sortieSoule = Bral_Util_Soule::calculSortieSoule($this->braldun);
        }

        if ($this->is_update_tour) {
            Bral_Util_Log::tour()->trace(get_class($this) . " activer - is_update_tour - true");
            $this->updateDb();
        }

        $this->view->is_update_tour = $this->is_update_tour;
        $this->view->is_nouveau_tour = $this->is_nouveau_tour;
        $this->view->is_tour_manque = $this->is_tour_manque;
        $this->view->is_ko = $this->est_ko;

        if (($this->is_update_tour) || ($this->is_nouveau_tour)) {
            $this->calculInfoTour();
            Bral_Util_Log::tour()->trace(get_class($this) . " activer - exit - true");
            return true;
        } else {
            Bral_Util_Log::tour()->trace(get_class($this) . " activer - exit - false");
            return false;
        }
    }

    /* Verification que c'est bien le debut d'un
       * nouveau tour pour le joueur
       * @return false si non
       * @return true si oui
       */
    private function calcul_debut_nouveau($date_courante)
    {
        Bral_Util_Log::tour()->trace(get_class($this) . " calcul_debut_nouveau - enter -");
        Bral_Util_Log::tour()->debug(get_class($this) . " calcul_debut_nouveau - this->braldun->date_fin_tour_braldun=" . $this->braldun->date_fin_tour_braldun);
        Bral_Util_Log::tour()->debug(get_class($this) . " calcul_debut_nouveau - date_courante=" . $date_courante);
        Bral_Util_Log::tour()->debug(get_class($this) . " calcul_debut_nouveau - this->braldun->est_ko_braldun=" . $this->braldun->est_ko_braldun);
        if ($this->braldun->date_fin_tour_braldun < $date_courante || $this->braldun->est_ko_braldun == 'oui') {
            Bral_Util_Log::tour()->debug(get_class($this) . " calcul_debut_nouveau - exit - true");
            return true;
        } else {
            Bral_Util_Log::tour()->debug(get_class($this) . " calcul_debut_nouveau - exit - false");
            return false;
        }
    }

    private function calculKo()
    {
        Bral_Util_Log::tour()->trace(get_class($this) . " calculKo - enter -");
        $this->est_ko = ($this->braldun->est_ko_braldun == "oui");

        if ($this->est_ko) {
            Zend_Loader::loadClass('Lieu');
            $this->is_update_tour = true;

            // remise en vu
            $this->braldun->est_ko_braldun = "non";
            $this->braldun->est_intangible_braldun = "oui";

            // perte des PX
            if ($this->braldun->est_soule_braldun == "non") {
                $this->braldun->px_commun_braldun = 0;
                $this->braldun->px_perso_braldun = $this->braldun->px_perso_braldun - floor($this->braldun->px_perso_braldun / 3);
            }

            // balance de faim
            $this->braldun->balance_faim_braldun = 50;

            // points de vie
            $this->braldun->pv_restant_braldun = floor(($this->view->config->game->pv_base + $this->braldun->vigueur_base_braldun * $this->view->config->game->pv_max_coef) / 2);

            // statut engage
            $this->braldun->est_engage_braldun = "non";
            $this->braldun->est_engage_next_dla_braldun = "non";

            // reputation
            $this->braldun->nb_ko_redresseurs_suite_braldun = 0;
            $this->braldun->nb_ko_gredins_suite_braldun = 0;

            $this->calculKoPosition();

            Zend_Loader::loadClass("EffetPotionBraldun");
            $effetPotionBraldunTable = new EffetPotionBraldun();
            $where = "id_fk_braldun_cible_effet_potion_braldun = " . intval($this->braldun->id_braldun);
            $effetPotionBraldunTable->delete($where);

            Zend_Loader::loadClass("EffetBraldun");
            $effetBraldunTable = new EffetBraldun();
            $where = "id_fk_braldun_cible_effet_braldun = " . intval($this->braldun->id_braldun);
            $effetBraldunTable->delete($where);

            Zend_Loader::loadClass("BraldunsCompetences");
            $braldunsCompetencesTable = new BraldunsCompetences();
            $braldunsCompetencesTable->annuleEffetsTabacByIdBraldun($this->braldun->id_braldun);
        }
        Bral_Util_Log::tour()->trace(get_class($this) . " calculKo - exit -");
    }

    // recalcule de la position suite à un KO
    private function calculKoPosition()
    {
        Zend_Loader::loadClass("TypeLieu");

        // recalcul de la position
        $lieuTable = new Lieu();
        $lieuRetour = null;
        if ($this->braldun->est_soule_braldun == "oui" && $this->braldun->id_fk_soule_match_braldun != null) { // match de Soule
            Zend_Loader::loadClass("SouleMatch");
            $souleMatchTable = new SouleMatch();
            $rowset = $souleMatchTable->findByIdMatch($this->braldun->id_fk_soule_match_braldun);
            $match = $rowset[0];

            $x = $match["x_min_soule_terrain"] + ($match["x_max_soule_terrain"] - $match["x_min_soule_terrain"]);
            if ($this->braldun->soule_camp_braldun == "a") {
                $y = $match["y_max_soule_terrain"];
            } else {
                $y = $match["y_min_soule_terrain"];
            }

            $lieuRowset = $lieuTable->findByTypeAndPosition(TypeLieu::ID_TYPE_HOPITAL, $x, $y);
            $lieuRetour = $lieuRowset[0];
        } elseif ($this->braldun->est_donjon_braldun == "oui") { // Donjon
            $lieuRowset = $lieuTable->findByTypeAndPosition(TypeLieu::ID_TYPE_POSTEDEGARDE, $this->braldun->x_braldun, $this->braldun->y_braldun, "non");
            $lieuRowset[0]["z_lieu"] = $lieuRowset[0]["z_lieu"] - 1; // 1 case en dessous le poste de garde
            $lieuRetour = $lieuRowset[0];
        } else {
            // Communaute avec infirmerie
            Zend_Loader::loadClass("Bral_Util_Communaute");
            Zend_Loader::loadClass("TypeLieu");
            if (Bral_Util_Communaute::getNiveauDuLieu($this->view->user->id_fk_communaute_braldun, TypeLieu::ID_TYPE_INFIRMERIE) >= Bral_Util_Communaute::NIVEAU_INFIRMERIE_REVENIR) {
                $lieux = $lieuTable->findByIdCommunaute($this->view->user->id_fk_communaute_braldun, null, null, null, false, TypeLieu::ID_TYPE_INFIRMERIE);
                if ($lieux != null && count($lieux) > 0 && $this->view->user->id_fk_lieu_resurrection_braldun == $lieux[0]["id_lieu"]) {
                    $lieuRetour = $lieux[0];
                }
            }
        }

        // Normal
        if ($lieuRetour == null) {
            $lieuRowset = $lieuTable->findByTypeAndPosition(TypeLieu::ID_TYPE_HOPITAL, $this->braldun->x_braldun, $this->braldun->y_braldun, "non");
            foreach ($lieuRowset as $lieu) {
                if ($lieu["id_fk_ville_lieu"] == null || ($lieu["id_fk_ville_lieu"] != null && $lieu['est_reliee_ville'] == 'oui')) {
                    $lieuRetour = $lieu;
                    break;
                }
            }

            $lieuRetour = $lieuRowset[0];
        }

        $this->braldun->x_braldun = $lieuRetour["x_lieu"];
        $this->braldun->y_braldun = $lieuRetour["y_lieu"];
        $this->braldun->z_braldun = $lieuRetour["z_lieu"];
    }

    private function calculBMEquipement()
    {
        Bral_Util_Log::tour()->trace(get_class($this) . " calculBMEquipement - enter -");
        Zend_Loader::loadClass("BraldunEquipement");
        Zend_Loader::loadClass("EquipementRune");
        Zend_Loader::loadClass("EquipementBonus");
        Zend_Loader::loadClass("Bral_Util_Attaque");

        // on va chercher l'equipement porte et les runes
        $tabEquipementPorte = null;
        $braldunEquipementTable = new BraldunEquipement();
        $equipementPorteRowset = $braldunEquipementTable->findByIdBraldun($this->view->user->id_braldun);
        unset($braldunEquipementTable);
        Zend_Loader::loadClass("Bral_Util_Equipement");
        $tabEquipementPorte = Bral_Util_Equipement::prepareTabEquipements($equipementPorteRowset, false, $this->view->user->niveau_braldun);

        if (count($tabEquipementPorte) > 0) {

            Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement nb equipement porte:" . count($equipementPorteRowset));

            $tabWhere = null;
            $equipementRuneTable = new EquipementRune();
            $equipementBonusTable = new EquipementBonus();
            $equipements = null;

            $idEquipements = null;

            foreach ($tabEquipementPorte as $e) {
                $idEquipements[] = $e["id_equipement"];

                $this->braldun->force_bm_braldun = $this->braldun->force_bm_braldun + $e["force"];
                $this->braldun->agilite_bm_braldun = $this->braldun->agilite_bm_braldun + $e["agilite"];
                $this->braldun->vigueur_bm_braldun = $this->braldun->vigueur_bm_braldun + $e["vigueur"];
                $this->braldun->sagesse_bm_braldun = $this->braldun->sagesse_bm_braldun + $e["sagesse"];
                $this->braldun->vue_bm_braldun = $this->braldun->vue_bm_braldun + $e["vue"];
                $this->braldun->armure_equipement_braldun = $this->braldun->armure_equipement_braldun + $e["armure"];
                $this->braldun->bm_attaque_braldun = $this->braldun->bm_attaque_braldun + $e["attaque"];
                $this->braldun->bm_degat_braldun = $this->braldun->bm_degat_braldun + $e["degat"];
                $this->braldun->bm_defense_braldun = $this->braldun->bm_defense_braldun + $e["defense"];

                if ($e["nom_systeme_mot_runique"] == "mot_b") {
                    $this->view->effetMotB = true;
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotB actif - avant : this->braldun->sagesse_bm_braldun" . $this->braldun->sagesse_bm_braldun);
                    $this->braldun->sagesse_bm_braldun = $this->braldun->sagesse_bm_braldun + (3 * ($e["niveau"] + 1));
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotB actif - apres : this->braldun->sagesse_bm_braldun" . $this->braldun->sagesse_bm_braldun . " ajout de :" . (2 * $e["niveau"]));
                }

                if ($e["nom_systeme_mot_runique"] == "mot_k") {
                    $this->view->effetMotK = true;
                    if ($e["attaque"] > 0) { // positif
                        $val = $e["attaque"];
                    } else { // negatif
                        $val = abs($e["attaque"]) / 2;
                    }
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotK actif - avant : val a ajouer au bm_attaque_braldun=" . $val);
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotK actif - avant : this->braldun->bm_attaque_braldun" . $this->braldun->bm_attaque_braldun);
                    $this->braldun->bm_attaque_braldun = $this->braldun->bm_attaque_braldun + $val;
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotK actif - apres : this->braldun->bm_attaque_braldun" . $this->braldun->bm_attaque_braldun);
                }

                if ($e["nom_systeme_mot_runique"] == "mot_m") {
                    $this->view->effetMotM = true;
                    if ($e["defense"] > 0) { // positif
                        $val = $e["defense"];
                    } else { // negatif
                        $val = abs($e["defense"]) / 2;
                    }
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotM actif - avant : val a ajouer au bm_defense_braldun=" . $val);
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotM actif - avant : this->braldun->bm_defense_braldun" . $this->braldun->bm_defense_braldun);
                    $this->braldun->bm_defense_braldun = $this->braldun->bm_defense_braldun + $val;
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotM actif - apres : this->braldun->bm_defense_braldun" . $this->braldun->bm_defense_braldun);
                }

                if ($e["nom_systeme_mot_runique"] == "mot_n") {
                    $this->view->effetMotN = true;
                    $this->view->effetMotNPointsDegats = Bral_Util_De::getLanceDe6(4 * $e["niveau"]);
                    $this->view->ciblesEffetN = Bral_Util_Attaque::calculDegatCase($this->view->config, $this->braldun, $this->view->effetMotNPointsDegats, $this->view);
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotN actif - logs presents dans bral_attaque.log");
                }

                if ($e["nom_systeme_mot_runique"] == "mot_o") {
                    $this->view->effetMotO = true;
                    $this->view->ciblesEffetO = Bral_Util_Attaque::calculSoinCase($this->view->config, $this->braldun, Bral_Util_De::getLanceDe6(4 * $e["niveau"]));
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotO actif - logs presents dans bral_attaque.log");
                }

                if ($e["nom_systeme_mot_runique"] == "mot_u") {
                    $this->view->effetMotU = true;
                    $this->view->effetMotUPointsDegats = Bral_Util_De::getLanceDe6(3 * $e["niveau"]);
                    $this->view->effetMotUNbPv = 0;
                    $ciblesEffetU = Bral_Util_Attaque::calculDegatCase($this->view->config, $this->braldun, $this->view->effetMotUPointsDegats, $this->view);
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotU actif - avant recuperation pv this->braldun->pv_restant_braldun=" . $this->braldun->pv_restant_braldun);
                    if ($ciblesEffetU != null && $ciblesEffetU["n_cible"] != null) {
                        $this->view->effetMotUNbPv = Bral_Util_De::getLanceDe6($ciblesEffetU["n_cible"] * $e["niveau"]);

                        $this->braldun->pv_restant_braldun = $this->braldun->pv_restant_braldun + $this->view->effetMotUNbPv;
                        if ($this->braldun->pv_restant_braldun > $this->braldun->pv_max_braldun + $this->braldun->pv_max_bm_braldun) {
                            $this->braldun->pv_restant_braldun = $this->braldun->pv_max_braldun + $this->braldun->pv_max_bm_braldun;
                        }
                    }
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotU actif - apres recuperation pv this->braldun->pv_restant_braldun=" . $this->braldun->pv_restant_braldun);
                    $this->view->ciblesEffetU = $ciblesEffetU;
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotU actif - logs presents dans bral_attaque.log");
                }

                if ($e["nom_systeme_mot_runique"] == "mot_v") {
                    $this->view->effetMotV = true;
                    $this->braldun->vue_bm_braldun = $this->braldun->vue_bm_braldun + 2;
                    Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - effetMotV actif - this->braldun->vue_bm_braldun=" . $this->braldun->vue_bm_braldun);
                }

            }

            $equipementBonus = $equipementBonusTable->findByIdsEquipement($idEquipements);
            unset($equipementBonusTable);

            if (count($equipementBonus) > 0) {
                foreach ($equipementBonus as $b) {
                    $this->braldun->armure_equipement_braldun = intval($this->braldun->armure_equipement_braldun + $b["armure_equipement_bonus"] + $b["vernis_bm_armure_equipement_bonus"]);
                    $this->braldun->agilite_bm_braldun = intval($this->braldun->agilite_bm_braldun + $b["agilite_equipement_bonus"] + $b["vernis_bm_agilite_equipement_bonus"]);
                    $this->braldun->force_bm_braldun = intval($this->braldun->force_bm_braldun + $b["force_equipement_bonus"] + $b["vernis_bm_force_equipement_bonus"]);
                    $this->braldun->sagesse_bm_braldun = intval($this->braldun->sagesse_bm_braldun + $b["sagesse_equipement_bonus"] + $b["vernis_bm_sagesse_equipement_bonus"]);
                    $this->braldun->vigueur_bm_braldun = intval($this->braldun->vigueur_bm_braldun + $b["vigueur_equipement_bonus"] + $b["vernis_bm_vigueur_equipement_bonus"]);

                    $this->braldun->vue_bm_braldun = intval($this->braldun->vue_bm_braldun + $b["vernis_bm_vue_equipement_bonus"]);
                    $this->braldun->bm_attaque_braldun = intval($this->braldun->bm_attaque_braldun + $b["vernis_bm_attaque_equipement_bonus"]);
                    $this->braldun->bm_degat_braldun = intval($this->braldun->bm_degat_braldun + $b["vernis_bm_degat_equipement_bonus"]);
                    $this->braldun->bm_defense_braldun = intval($this->braldun->bm_defense_braldun + $b["vernis_bm_defense_equipement_bonus"]);
                }
            }

            $equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);

            unset($equipementRuneTable);

            if (count($equipementRunes) > 0) {
                foreach ($equipementRunes as $r) {
                    if ($r["nom_type_rune"] == "KR") {
                        // KR Bonus de AGI = Niveau d'AGI/3 arrondi inferieur
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune KR active - avant this->braldun->agilite_bm_braldun=" . $this->braldun->agilite_bm_braldun);
                        $this->braldun->agilite_bm_braldun = $this->braldun->agilite_bm_braldun + floor($this->braldun->agilite_base_braldun / 3);
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune KR active - apres this->braldun->agilite_bm_braldun=" . $this->braldun->agilite_bm_braldun);
                    } else if ($r["nom_type_rune"] == "ZE") {
                        // ZE Bonus de FOR = Niveau de FOR/3 arrondi inferieur
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune ZE active - avant this->braldun->force_bm_braldun=" . $this->braldun->force_bm_braldun);
                        $this->braldun->force_bm_braldun = $this->braldun->force_bm_braldun + floor($this->braldun->force_base_braldun / 3);
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune Ze active - apres this->braldun->force_bm_braldun=" . $this->braldun->force_bm_braldun);
                    } else if ($r["nom_type_rune"] == "IL") {
                        // IL Reduit le tour de jeu de 10 minutes
                        $this->braldun->duree_bm_tour_braldun = $this->braldun->duree_bm_tour_braldun - 10;
                    } else if ($r["nom_type_rune"] == "MU") {
                        // MU PV + niveau du Braldun/5 arrondi inferieur
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune MU active - avant this->braldun->pv_max_bm_braldun=" . $this->braldun->pv_max_bm_braldun);
                        $this->braldun->pv_max_bm_braldun = $this->braldun->pv_max_bm_braldun + floor($this->braldun->niveau_braldun / 5) + 1;
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune MU active - apres this->braldun->pv_max_bm_braldun=" . $this->braldun->pv_max_bm_braldun);
                    } else if ($r["nom_type_rune"] == "RE") {
                        // RE ARM NAT + Niveau du Braldun/10 arrondi inferieur
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune RE active - apres this->braldun->armure_naturelle_braldun=" . $this->braldun->armure_naturelle_braldun);
                        $this->braldun->armure_naturelle_braldun = $this->braldun->armure_naturelle_braldun + floor($this->braldun->niveau_braldun / 10);
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune RE active - apres this->braldun->armure_naturelle_braldun=" . $this->braldun->armure_naturelle_braldun);
                    } else if ($r["nom_type_rune"] == "OG") {
                        // OG Bonus de VIG = Niveau de VIG/3 arrondi inferieur
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune OG active - avant this->braldun->vigueur_bm_braldun=" . $this->braldun->vigueur_bm_braldun);
                        $this->braldun->vigueur_bm_braldun = $this->braldun->vigueur_bm_braldun + floor($this->braldun->vigueur_base_braldun / 3);
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune OG active - avant this->braldun->vigueur_bm_braldun=" . $this->braldun->vigueur_bm_braldun);
                    } else if ($r["nom_type_rune"] == "OX") {
                        // OX Poids maximum porte augmente de Niveau du Braldun/10 arrondi inferieur
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune OX active - avant this->braldun->poids_transportable_braldun=" . $this->braldun->poids_transportable_braldun);
                        $this->braldun->poids_transportable_braldun = $this->braldun->poids_transportable_braldun + floor($this->braldun->niveau_braldun / 10);
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune OX active - avant this->braldun->poids_transportable_braldun=" . $this->braldun->poids_transportable_braldun);
                    } else if ($r["nom_type_rune"] == "UP") {
                        // UP Bonus de SAG = Niveau de SAG/3 arrondi inferieur
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune UP active - avant this->braldun->sagesse_bm_braldun=" . $this->braldun->sagesse_bm_braldun);
                        $this->braldun->sagesse_bm_braldun = $this->braldun->sagesse_bm_braldun + floor($this->braldun->sagesse_base_braldun / 3);
                        Bral_Util_Log::tour()->debug(get_class($this) . " calculBMEquipement - rune UP active - avant this->braldun->sagesse_bm_braldun=" . $this->braldun->sagesse_bm_braldun);
                    }
                }
                unset($equipementRunes);
            }
            unset($equipementPorteRowset);
        }
        Bral_Util_Log::tour()->trace(get_class($this) . " calculBMEquipement - exit -");
    }

    private function calculBMPotion()
    {
        Bral_Util_Log::tour()->trace(get_class($this) . " calculBMPotion - enter -");
        Zend_Loader::loadClass("Bral_Util_EffetsPotion");
        $effetsPotions = Bral_Util_EffetsPotion::calculPotionBraldun($this->braldun, true);

        if (count($effetsPotions) > 0) {
            $this->view->effetPotion = true;
            $this->view->effetPotionPotions = $effetsPotions;
        }
        Bral_Util_Log::tour()->trace(get_class($this) . " calculBMPotion - exit -");
    }

    private function calculBMEffet()
    {
        Bral_Util_Log::tour()->trace(get_class($this) . " calculBMEffet - enter -");
        Zend_Loader::loadClass("Bral_Util_Effets");
        $effetsBraldun = Bral_Util_Effets::calculEffetBraldun($this->braldun, true);

        if (count($effetsBraldun) > 0) {
            $this->view->effetBraldun = true;
            $this->view->effetBraldunEffets = $effetsBraldun;
        }
        Bral_Util_Log::tour()->trace(get_class($this) . " calculBMEffet - exit -");
    }

    private function calculBMSpecialisation()
    {
        Bral_Util_Log::tour()->trace(get_class($this) . " calculBMSpecialisation - enter -");
        Zend_Loader::loadClass("Bral_Util_Specialisation");
        Bral_Util_Specialisation::calculSpecialisationBraldun($this->braldun, false);
        Bral_Util_Log::tour()->trace(get_class($this) . " calculBMSpecialisation - exit -");
    }

    private function calculInfoTour()
    {
        Bral_Util_Log::tour()->trace(get_class($this) . " calculInfoTour - enter -");
        $info = "";
        if ($this->view->user->tour_position_braldun == $this->view->config->game->tour->position_latence) {
            $info = "Fin latence &agrave; " . $this->braldun->date_fin_latence_braldun;
        } else if ($this->view->user->tour_position_braldun == $this->view->config->game->tour->position_milieu) {
            $info = "Cumul &agrave; " . $this->braldun->date_debut_cumul_braldun;
        }
        $this->view->user->info_prochaine_position = $info;
        Bral_Util_Log::tour()->trace(get_class($this) . " calculInfoTour - exit -");
    }

    private function calculDLA()
    {
        Bral_Util_Log::tour()->trace(get_class($this) . " calculDLA - enter -");
        $this->braldun->duree_courant_tour_braldun = $this->braldun->duree_prochain_tour_braldun;
        // Ajouter la prise en compte du niveau de sagesse
        //Duree DLA (en minutes) = 1440 â€“ 10 * Niveau SAG

        Bral_Util_Log::tour()->debug(get_class($this) . " this->braldun->duree_prochain_tour_braldun=" . $this->braldun->duree_prochain_tour_braldun);

        Zend_Loader::loadClass("Bral_Util_Tour");
        $tabProchainTour = Bral_Util_Tour::getTabMinutesProchainTour($this->braldun);
        $minutesCourant = $tabProchainTour["minutesBase"];

        Bral_Util_Log::tour()->debug(get_class($this) . " minutesCourant=" . $minutesCourant);
        // Ajouter les blessures : pour chaque PV : Arrondi inf"rieur [duree DLA (+BM) / (4*max PV du Braldun)].

        $minutesAAjouter = 0;
        if (($this->braldun->pv_max_braldun + $this->braldun->pv_max_bm_braldun) - $this->braldun->pv_restant_braldun > 0) {
            $minutesAAjouter = $tabProchainTour["minutesBlessures"];
        }

        Bral_Util_Log::tour()->debug(get_class($this) . " minutesAAjouter=" . $minutesAAjouter);

        $this->braldun->duree_courant_tour_braldun = $tabProchainTour["heureMinuteTotal"];
        Bral_Util_Log::tour()->debug(get_class($this) . " this->braldun->duree_courant_tour_braldun=" . $this->braldun->duree_courant_tour_braldun);

        Zend_Loader::loadClass("Bral_Util_Tour");
        $this->braldun->duree_prochain_tour_braldun = Bral_Util_Tour::getDureeBaseProchainTour($this->braldun, $this->view->config);
        Bral_Util_Log::tour()->debug(get_class($this) . " this->braldun->duree_prochain_tour_braldun=" . $this->braldun->duree_prochain_tour_braldun);

        $this->braldun->date_debut_tour_braldun = $this->braldun->date_fin_tour_braldun;
        $this->braldun->date_fin_tour_braldun = Bral_Util_ConvertDate::get_date_add_time_to_date($this->braldun->date_fin_tour_braldun, $this->braldun->duree_courant_tour_braldun);

        $time_latence = Bral_Util_ConvertDate::get_divise_time_to_time($this->braldun->duree_courant_tour_braldun, $this->view->config->game->tour->diviseur_latence);
        $time_cumul = Bral_Util_ConvertDate::get_divise_time_to_time($this->braldun->duree_courant_tour_braldun, $this->view->config->game->tour->diviseur_cumul);

        $this->braldun->date_fin_latence_braldun = Bral_Util_ConvertDate::get_date_add_time_to_date($this->braldun->date_debut_tour_braldun, $time_latence);
        $this->braldun->date_debut_cumul_braldun = Bral_Util_ConvertDate::get_date_add_time_to_date($this->braldun->date_debut_tour_braldun, $time_cumul);

        Bral_Util_Log::tour()->debug(get_class($this) . " this->braldun->date_fin_tour_braldun=" . $this->braldun->date_fin_tour_braldun);
        Bral_Util_Log::tour()->trace(get_class($this) . " calculDLA - exit -");
    }

    private function calculPv()
    {
        Bral_Util_Log::tour()->trace(get_class($this) . " calculPv - enter -");
        Bral_Util_Log::tour()->trace(get_class($this) . " calculPv - this->braldun->regeneration_bm_braldun=" . $this->braldun->regeneration_bm_braldun);

        $this->view->jetRegeneration = 0;

        Zend_Loader::loadClass("Bral_Util_Vie");
        Bral_Util_Vie::calculRegenerationBraldun(&$this->braldun, $this->view->jetRegeneration);

        /* Remise a  zero du malus de regeneration. */
        $this->braldun->regeneration_bm_braldun = 0;
        Bral_Util_Log::tour()->trace(get_class($this) . " calculPv - exit -");
    }

    private function updateDb()
    {
        Bral_Util_Log::tour()->trace(get_class($this) . " updateDb - enter -");

        // Mise a jour du joueur dans la base de donnees
        $braldunTable = new Braldun();
        $braldunRowset = $braldunTable->find($this->braldun->id_braldun);
        $braldun = $braldunRowset->current();

        $this->view->user->x_braldun = $this->braldun->x_braldun;
        $this->view->user->y_braldun = $this->braldun->y_braldun;
        $this->view->user->z_braldun = $this->braldun->z_braldun;

        $this->view->user->date_debut_tour_braldun = $this->braldun->date_debut_tour_braldun;
        $this->view->user->date_fin_tour_braldun = $this->braldun->date_fin_tour_braldun;
        $this->view->user->date_debut_cumul_braldun = $this->braldun->date_debut_cumul_braldun;
        $this->view->user->date_fin_latence_braldun = $this->braldun->date_fin_latence_braldun;
        $this->view->user->duree_courant_tour_braldun = $this->braldun->duree_courant_tour_braldun;
        $this->view->user->duree_prochain_tour_braldun = $this->braldun->duree_prochain_tour_braldun;
        $this->view->user->duree_bm_tour_braldun = $this->braldun->duree_bm_tour_braldun;
        $this->view->user->tour_position_braldun = $this->braldun->tour_position_braldun;
        $this->view->user->pa_braldun = $this->braldun->pa_braldun;
        $this->view->user->armure_naturelle_braldun = $this->braldun->armure_naturelle_braldun;
        $this->view->user->est_ko_braldun = $this->braldun->est_ko_braldun;
        $this->view->user->px_commun_braldun = $this->braldun->px_commun_braldun;
        $this->view->user->px_perso_braldun = $this->braldun->px_perso_braldun;
        $this->view->user->pv_max_braldun = $this->braldun->pv_max_braldun;
        $this->view->user->pv_restant_braldun = $this->braldun->pv_restant_braldun;
        $this->view->user->pv_max_bm_braldun = $this->braldun->pv_max_bm_braldun;
        $this->view->user->balance_faim_braldun = $this->braldun->balance_faim_braldun;

        $this->view->user->force_bm_braldun = $this->braldun->force_bm_braldun;
        $this->view->user->agilite_bm_braldun = $this->braldun->agilite_bm_braldun;
        $this->view->user->vigueur_bm_braldun = $this->braldun->vigueur_bm_braldun;
        $this->view->user->sagesse_bm_braldun = $this->braldun->sagesse_bm_braldun;
        $this->view->user->vue_bm_braldun = $this->braldun->vue_bm_braldun;
        $this->view->user->poids_transportable_braldun = $this->braldun->poids_transportable_braldun;
        $this->view->user->poids_transporte_braldun = $this->braldun->poids_transporte_braldun;

        $this->view->user->bm_attaque_braldun = $this->braldun->bm_attaque_braldun;
        $this->view->user->bm_degat_braldun = $this->braldun->bm_degat_braldun;
        $this->view->user->bm_defense_braldun = $this->braldun->bm_defense_braldun;

        $this->view->user->regeneration_bm_braldun = $this->braldun->regeneration_bm_braldun;

        $this->view->user->est_engage_braldun = $this->braldun->est_engage_braldun;
        $this->view->user->est_engage_next_dla_braldun = $this->braldun->est_engage_next_dla_braldun;

        $this->view->user->est_intangible_braldun = $this->braldun->est_intangible_braldun;
        $this->view->user->est_intangible_prochaine_braldun = $this->braldun->est_intangible_prochaine_braldun;
        $this->view->user->nb_dla_jouees_braldun = $this->braldun->nb_dla_jouees_braldun;

        $this->view->user->nb_ko_redresseurs_suite_braldun = $this->braldun->nb_ko_redresseurs_suite_braldun;
        $this->view->user->nb_ko_gredins_suite_braldun = $this->braldun->nb_ko_gredins_suite_braldun;

        $data = array(
            'x_braldun' => $this->braldun->x_braldun,
            'y_braldun' => $this->braldun->y_braldun,
            'z_braldun' => $this->braldun->z_braldun,
            'date_debut_tour_braldun' => $this->braldun->date_debut_tour_braldun,
            'date_fin_tour_braldun' => $this->braldun->date_fin_tour_braldun,
            'date_fin_latence_braldun' => $this->braldun->date_fin_latence_braldun,
            'date_debut_cumul_braldun' => $this->braldun->date_debut_cumul_braldun,
            'duree_courant_tour_braldun' => $this->braldun->duree_courant_tour_braldun,
            'duree_prochain_tour_braldun' => $this->braldun->duree_prochain_tour_braldun,
            'duree_bm_tour_braldun' => $this->braldun->duree_bm_tour_braldun,
            'tour_position_braldun' => $this->braldun->tour_position_braldun,
            'pa_braldun' => $this->braldun->pa_braldun,
            'armure_naturelle_braldun' => $this->braldun->armure_naturelle_braldun,
            'armure_equipement_braldun' => $this->braldun->armure_equipement_braldun,
            'armure_bm_braldun' => $this->braldun->armure_bm_braldun,
            'est_ko_braldun' => $this->braldun->est_ko_braldun,
            'px_commun_braldun' => $this->braldun->px_commun_braldun,
            'px_perso_braldun' => $this->braldun->px_perso_braldun,
            'pv_max_braldun' => $this->braldun->pv_max_braldun,
            'pv_restant_braldun' => $this->braldun->pv_restant_braldun,
            'pv_max_bm_braldun' => $this->braldun->pv_max_bm_braldun,
            'balance_faim_braldun' => $this->braldun->balance_faim_braldun,
            'force_bm_braldun' => $this->braldun->force_bm_braldun,
            'force_bbdf_braldun' => $this->braldun->force_bbdf_braldun,
            'agilite_bm_braldun' => $this->braldun->agilite_bm_braldun,
            'agilite_bbdf_braldun' => $this->braldun->agilite_bbdf_braldun,
            'vigueur_bm_braldun' => $this->braldun->vigueur_bm_braldun,
            'vigueur_bbdf_braldun' => $this->braldun->vigueur_bbdf_braldun,
            'sagesse_bm_braldun' => $this->braldun->sagesse_bm_braldun,
            'sagesse_bbdf_braldun' => $this->braldun->sagesse_bbdf_braldun,
            'vue_bm_braldun' => $this->braldun->vue_bm_braldun,
            'poids_transportable_braldun' => $this->braldun->poids_transportable_braldun,
            'poids_transporte_braldun' => $this->braldun->poids_transporte_braldun,
            'regeneration_braldun' => $this->braldun->regeneration_braldun,
            'regeneration_bm_braldun' => $this->braldun->regeneration_bm_braldun,
            'bm_attaque_braldun' => $this->braldun->bm_attaque_braldun,
            'bm_degat_braldun' => $this->braldun->bm_degat_braldun,
            'bm_defense_braldun' => $this->braldun->bm_defense_braldun,
            'bm_marcher_braldun' => $this->braldun->bm_marcher_braldun,
            'est_engage_braldun' => $this->braldun->est_engage_braldun,
            'est_engage_next_dla_braldun' => $this->braldun->est_engage_next_dla_braldun,
            'est_intangible_braldun' => $this->braldun->est_intangible_braldun,
            'est_intangible_prochaine_braldun' => $this->braldun->est_intangible_prochaine_braldun,
            'nb_dla_jouees_braldun' => $this->braldun->nb_dla_jouees_braldun,
            'est_en_sortie_soule_braldun' => $this->braldun->est_en_sortie_soule_braldun,
            'soule_camp_braldun' => $this->braldun->soule_camp_braldun,
            'id_fk_soule_match_braldun' => $this->braldun->id_fk_soule_match_braldun,
            'nb_ko_redresseurs_suite_braldun' => $this->braldun->nb_ko_redresseurs_suite_braldun,
            'nb_ko_gredins_suite_braldun' => $this->braldun->nb_ko_gredins_suite_braldun,
            'nb_tour_blabla_braldun' => $this->braldun->nb_tour_blabla_braldun,
        );
        $where = "id_braldun=" . $this->braldun->id_braldun;
        $braldunTable->update($data, $where);
        Bral_Util_Log::tour()->debug(get_class($this) . " activer() - update braldun " . $this->braldun->id_braldun . " en base");
        Bral_Util_Log::tour()->trace(get_class($this) . " updateDb - exit -");
    }
}

