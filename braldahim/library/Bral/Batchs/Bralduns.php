<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Bralduns extends Bral_Batchs_Batch
{

    public function calculBatchImpl()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculBatchImpl - enter -");
        $retour = null;

        $retour .= $this->distinctionsReputation();
        $retour .= $this->distinctionsPalmares();
        $retour .= $this->calculPointsDistinctions();
        $retour .= $this->calculPointsReputation();
        $retour .= $this->suppression();
        $retour .= $this->preventionSuppression();

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculBatchImpl - exit -");
        return $retour;
    }

    private function distinctionsReputation()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculPointsDistinctions - enter -");

        Zend_Loader::loadClass("Bral_Util_Distinction");
        Zend_Loader::loadClass("Bral_Util_Evenement");
        Zend_Loader::loadClass("TypeDistinction");
        Zend_Loader::loadClass("Bral_Util_Distinction");

        $retour = "";
        $braldunTable = new Braldun();
        $bralduns = $braldunTable->fetchall("est_pnj_braldun = 'non'");

        Zend_Loader::loadClass("BraldunsDistinction");
        $braldunsDistinctionTable = new BraldunsDistinction();

        if (count($bralduns) > 0) {
            foreach ($bralduns as $b) {
                self::calculDistinctionsReputation($b);
            }
        }

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculPointsDistinctions - exit -" . $retour);
        return $retour;
    }

    private function distinctionsPalmares()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - distinctionsPalmares - enter -");

        $retour = "";

        Zend_Loader::loadClass("Bral_Util_Distinction");
        Zend_Loader::loadClass("BraldunsDistinction");
        $braldunsDistinctionsTable = new BraldunsDistinction();

        $moisPrecedent = mktime(0, 0, 0, date("m") - 1, 1, date("Y"));
        $moisEnCours = mktime(0, 0, 0, date("m"), 1, date("Y"));
        $moisSuivant = mktime(0, 0, 0, date("m") + 1, 1, date("Y"));

        $mois = date("m/Y", $moisPrecedent);
        $moisDebut = date("Y-m-d H:i:s", $moisPrecedent);
        $moisFin = date("Y-m-d H:i:s", $moisEnCours);
        $moisFin2 = date("Y-m-d H:i:s", $moisSuivant);

        $retour .= " moisDebut:" . $moisDebut . " moisFin:" . $moisFin;

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - distinctionsPalmares " . $retour);

        // Exemple : si l'on veut attribuer des distinctions du mois de septembre, on regarde si elles ont
        // été attribuées au mois d'octobre (le 1er par exemple)
        $nbDistinctions = $braldunsDistinctionsTable->countIdTypeDistinctionByDate(Bral_Util_Distinction::ID_TYPE_EXPERIENCE_MOIS, $moisFin, $moisFin2);

        if ($nbDistinctions > 0) {
            $retour .= " distinctions palmares pour " . $moisDebut . "/" . $moisFin . " deja calculees nb:" . $nbDistinctions;
            Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - distinctionsPalmares - exit A -" . $retour);
            return $retour;
        }

        Zend_Loader::loadClass("TypeEvenement");
        Zend_Loader::loadClass("Evenement");
        Zend_Loader::loadClass("Bral_Util_Evenement");
        Zend_Loader::loadClass("StatsRecolteurs");
        Zend_Loader::loadClass("StatsFabricants");
        Zend_Loader::loadClass("StatsReputation");
        Zend_Loader::loadClass("StatsRoutes");
        Zend_Loader::loadClass("Bral_Util_Metier");

        // Général
        self::calculDistinctionPalmaresEvenement(TypeEvenement::ID_TYPE_KILLMONSTRE, Bral_Util_Distinction::ID_TYPE_GRANDSCOMBATTANTSPVE_MOIS, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresEvenement(TypeEvenement::ID_TYPE_KOBRALDUN, Bral_Util_Distinction::ID_TYPE_GRANDSCOMBATTANTSPVP_MOIS, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresEvenement(TypeEvenement::ID_TYPE_KILLGIBIER, Bral_Util_Distinction::ID_TYPE_GRANDSCHASSEURSDEGIBIERS_MOIS, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresEvenement(TypeEvenement::ID_TYPE_KO, Bral_Util_Distinction::ID_TYPE_KO_MOIS, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresExperience(Bral_Util_Distinction::ID_TYPE_EXPERIENCE_MOIS, $moisDebut, $moisFin, $moisFin2, $mois);

        // Récolteurs
        self::calculDistinctionPalmaresRecolteurs(Bral_Util_Metier::METIER_MINEUR_ID, Bral_Util_Distinction::ID_TYPE_RECOLTEUR_MOIS_MINEUR, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresRecolteurs(Bral_Util_Metier::METIER_HERBORISTE_ID, Bral_Util_Distinction::ID_TYPE_RECOLTEUR_MOIS_HERBORISTE, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresRecolteurs(Bral_Util_Metier::METIER_CHASSEUR_ID, Bral_Util_Distinction::ID_TYPE_RECOLTEUR_MOIS_CHASSEUR, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresRecolteurs(Bral_Util_Metier::METIER_BUCHERON_ID, Bral_Util_Distinction::ID_TYPE_RECOLTEUR_MOIS_BUCHERON, $moisDebut, $moisFin, $moisFin2, $mois);

        // Fabricants
        self::calculDistinctionPalmaresFabricants(Bral_Util_Metier::METIER_APOTHICAIRE_ID, Bral_Util_Distinction::ID_TYPE_FABRIQUANT_MOIS_APOTHICAIRE, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresFabricants(Bral_Util_Metier::METIER_MENUISIER_ID, Bral_Util_Distinction::ID_TYPE_FABRIQUANT_MOIS_MENUISIER, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresFabricants(Bral_Util_Metier::METIER_FORGERON_ID, Bral_Util_Distinction::ID_TYPE_FABRIQUANT_MOIS_FORGERON, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresFabricants(Bral_Util_Metier::METIER_TANNEUR_ID, Bral_Util_Distinction::ID_TYPE_FABRIQUANT_MOIS_TANNEUR, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresFabricants(Bral_Util_Metier::METIER_BUCHERON_ID, Bral_Util_Distinction::ID_TYPE_FABRIQUANT_MOIS_PALISSADE, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresFabricants(Bral_Util_Metier::METIER_CUISINIER_ID, Bral_Util_Distinction::ID_TYPE_FABRIQUANT_MOIS_CUISINIER, $moisDebut, $moisFin, $moisFin2, $mois);

        // Route
        self::calculDistinctionPalmaresRoutes(Bral_Util_Distinction::ID_TYPE_FABRIQUANT_MOIS_SENTIER, $moisDebut, $moisFin, $moisFin2, $mois);

        // Réputation
        self::calculDistinctionPalmaresReputation("gredin", Bral_Util_Distinction::ID_TYPE_GREDIN_MOIS, $moisDebut, $moisFin, $moisFin2, $mois);
        self::calculDistinctionPalmaresReputation("redresseur", Bral_Util_Distinction::ID_TYPE_REDRESSEUR_MOIS, $moisDebut, $moisFin, $moisFin2, $mois);

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - distinctionsPalmares - exit -" . $retour);
        return $retour;
    }

    private function calculDistinctionPalmaresEvenement($idTypeEvenement, $idTypeDistinction, $moisDebut, $moisFin, $moisFin2, $mois)
    {
        $evenementTable = new Evenement();
        $bralduns = $evenementTable->findTopPalmaresBraldun($moisDebut, $moisFin, $idTypeEvenement);
        if ($bralduns == null) {
            return;
        }
        foreach ($bralduns as $b) {
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($b["id_braldun"], $b["niveau_braldun"], $idTypeDistinction, $moisFin, $moisFin2, " $mois (score:" . $b["nombre"] . ")");
        }
    }

    private function calculDistinctionPalmaresExperience($idTypeDistinction, $moisDebut, $moisFin, $moisFin2, $mois)
    {
        Zend_Loader::loadClass("StatsExperience");
        $statsExperienceTable = new StatsExperience();
        $bralduns = $statsExperienceTable->findTopPalmaresBraldun($moisDebut, $moisFin);
        if ($bralduns == null) {
            return;
        }
        foreach ($bralduns as $b) {
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($b["id_braldun"], $b["niveau_braldun"], $idTypeDistinction, $moisFin, $moisFin2, " $mois (score:" . $b["nombre"] . ")");
        }
    }

    private function calculDistinctionPalmaresRecolteurs($type, $idTypeDistinction, $moisDebut, $moisFin, $moisFin2, $mois)
    {
        $statsRecolteurs = new StatsRecolteurs();
        $bralduns = $statsRecolteurs->findTopPalmaresBraldun($moisDebut, $moisFin, $type);
        if ($bralduns == null) {
            return;
        }
        foreach ($bralduns as $b) {
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($b["id_braldun"], $b["niveau_braldun"], $idTypeDistinction, $moisFin, $moisFin2, " $mois (score:" . $b["nombre"] . ")");
        }
    }

    private function calculDistinctionPalmaresFabricants($type, $idTypeDistinction, $moisDebut, $moisFin, $moisFin2, $mois)
    {
        $statsFabricants = new StatsFabricants();
        $bralduns = $statsFabricants->findTopPalmaresBraldun($moisDebut, $moisFin, $type);
        if ($bralduns == null) {
            return;
        }
        foreach ($bralduns as $b) {
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($b["id_braldun"], $b["niveau_braldun"], $idTypeDistinction, $moisFin, $moisFin2, " $mois (score:" . $b["nombre"] . ")");
        }
    }

    private function calculDistinctionPalmaresRoutes($idTypeDistinction, $moisDebut, $moisFin, $moisFin2, $mois)
    {
        $statsRoutes = new StatsRoutes();
        $bralduns = $statsRoutes->findTopPalmaresBraldun($moisDebut, $moisFin);
        if ($bralduns == null) {
            return;
        }
        foreach ($bralduns as $b) {
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($b["id_braldun"], $b["niveau_braldun"], $idTypeDistinction, $moisFin, $moisFin2, " $mois (score:" . $b["nombre"] . ")");
        }
    }

    private function calculDistinctionPalmaresReputation($type, $idTypeDistinction, $moisDebut, $moisFin, $moisFin2, $mois)
    {
        $statsReputation = new StatsReputation();
        $bralduns = $statsReputation->findTopPalmaresBraldun($moisDebut, $moisFin, $type);
        if ($bralduns == null) {
            return;
        }
        foreach ($bralduns as $b) {
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($b["id_braldun"], $b["niveau_braldun"], $idTypeDistinction, $moisFin, $moisFin2, " $mois (score:" . $b["nombre"] . ")");
        }
    }

    private function calculDistinctionsReputation($braldun)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculDistinctionsReputation - enter");

        self::calculDistinctionUnique($braldun, 1, 'neutre', Bral_Util_Distinction::ID_TYPE_KO_1_NEUTRE);
        self::calculDistinctionUnique($braldun, 10, 'neutre', Bral_Util_Distinction::ID_TYPE_KO_10_NEUTRE);
        self::calculDistinctionUnique($braldun, 20, 'neutre', Bral_Util_Distinction::ID_TYPE_KO_20_NEUTRE);
        self::calculDistinctionUnique($braldun, 50, 'neutre', Bral_Util_Distinction::ID_TYPE_KO_50_NEUTRE);
        self::calculDistinctionUnique($braldun, 100, 'neutre', Bral_Util_Distinction::ID_TYPE_KO_100_NEUTRE);
        self::calculDistinctionUnique($braldun, 500, 'neutre', Bral_Util_Distinction::ID_TYPE_KO_500_NEUTRE);
        self::calculDistinctionUnique($braldun, 1000, 'neutre', Bral_Util_Distinction::ID_TYPE_KO_1000_NEUTRE);

        self::calculDistinctionUnique($braldun, 1, 'redresseur', Bral_Util_Distinction::ID_TYPE_KO_1_REDRESSEUR);
        self::calculDistinctionUnique($braldun, 10, 'redresseur', Bral_Util_Distinction::ID_TYPE_KO_10_REDRESSEUR);
        self::calculDistinctionUnique($braldun, 20, 'redresseur', Bral_Util_Distinction::ID_TYPE_KO_20_REDRESSEUR);
        self::calculDistinctionUnique($braldun, 50, 'redresseur', Bral_Util_Distinction::ID_TYPE_KO_50_REDRESSEUR);
        self::calculDistinctionUnique($braldun, 100, 'redresseur', Bral_Util_Distinction::ID_TYPE_KO_100_REDRESSEUR);
        self::calculDistinctionUnique($braldun, 500, 'redresseur', Bral_Util_Distinction::ID_TYPE_KO_500_REDRESSEUR);
        self::calculDistinctionUnique($braldun, 1000, 'redresseur', Bral_Util_Distinction::ID_TYPE_KO_1000_REDRESSEUR);

        self::calculDistinctionUnique($braldun, 1, 'gredin', Bral_Util_Distinction::ID_TYPE_KO_1_GREDIN);
        self::calculDistinctionUnique($braldun, 10, 'gredin', Bral_Util_Distinction::ID_TYPE_KO_10_GREDIN);
        self::calculDistinctionUnique($braldun, 20, 'gredin', Bral_Util_Distinction::ID_TYPE_KO_20_GREDIN);
        self::calculDistinctionUnique($braldun, 50, 'gredin', Bral_Util_Distinction::ID_TYPE_KO_50_GREDIN);
        self::calculDistinctionUnique($braldun, 100, 'gredin', Bral_Util_Distinction::ID_TYPE_KO_100_GREDIN);
        self::calculDistinctionUnique($braldun, 500, 'gredin', Bral_Util_Distinction::ID_TYPE_KO_500_GREDIN);
        self::calculDistinctionUnique($braldun, 1000, 'gredin', Bral_Util_Distinction::ID_TYPE_KO_1000_GREDIN);

        self::calculDistinctionUnique($braldun, 5, 'redresseurs_suite', Bral_Util_Distinction::ID_TYPE_KO_5_REDRESSEURS_SUITE);
        self::calculDistinctionUnique($braldun, 5, 'gredins_suite', Bral_Util_Distinction::ID_TYPE_KO_5_GREDINS_SUITE);

        /*TODO
           *
           *
           Bral_Util_Distinction::ID_TYPE_KO_1_GREDIN_TOP;
           Bral_Util_Distinction::ID_TYPE_KO_1_REDRESSEUR_TOP;

           Bral_Util_Distinction::ID_TYPE_KO_1_WANTED;
           Bral_Util_Distinction::ID_TYPE_MEILLEUR_GREDIN;
           Bral_Util_Distinction::ID_TYPE_MEILLEUR_REDRESSEUR;
           */

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculDistinctionsReputation - exit");
    }

    private function calculDistinctionUnique($braldun, $nb, $type, $idTypeDistinction)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculDistinctionUnique - enter - b:" . $braldun["id_braldun"]);
        if ($braldun["nb_ko_" . $type . "_braldun"] >= $nb) {
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($braldun["id_braldun"], $braldun["niveau_braldun"], $idTypeDistinction);
        }
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculDistinctionUnique - exit");
    }

    private function calculPointsDistinctions()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculPointsDistinctions - enter -");

        $retour = "";
        $braldunTable = new Braldun();
        $bralduns = $braldunTable->fetchall("est_pnj_braldun = 'non'");

        Zend_Loader::loadClass("BraldunsDistinction");
        $braldunsDistinctionTable = new BraldunsDistinction();

        Zend_Loader::loadClass("StatsDistinction");
        $statsDistinction = new StatsDistinction();

        Zend_Loader::loadClass("StatsReputation");
        $statsReputation = new StatsReputation();

        if (count($bralduns) > 0) {

            foreach ($bralduns as $h) {
                $points = 0;

                //Profil
                $braldunsDistinctionRowset = $braldunsDistinctionTable->findDistinctionsByBraldunId($h["id_braldun"]);

                if (count($braldunsDistinctionRowset) > 0) {
                    foreach ($braldunsDistinctionRowset as $t) {
                        $points = $points + $t["points_type_distinction"];
                    }
                }

                $data = array('points_distinctions_braldun' => $points);
                $where = "id_braldun=" . intval($h["id_braldun"]);
                $braldunTable->update($data, $where);

                /*
                     * Code pour rattrapage
                     * for($annee = 2010; $annee <= date("Y"); $annee++) {

                        for($mois = 1; $mois <= 12; $mois++) {
                            if ($annee == 2010 && $mois < 5) continue;
                            if ($annee == 2011 && $mois > date("m")) continue;
                    */
                //Stats
                $mois = date("m");
                $moisEnCours = mktime(0, 0, 0, $mois, 2, date("Y"));
                $moisDebut = mktime(0, 0, 0, $mois, 1, date("Y"));
                $moisFin = mktime(0, 0, 0, $mois + 1, 1, date("Y"));

                /*Code pour rattrapage
                            $moisEnCours  = mktime(0, 0, 0, $mois, 2, $annee);
                            $moisDebut =  mktime(0, 0, 0, $mois, 1, $annee);
                            $moisFin =  mktime(0, 0, 0, $mois+1, 1, $annee);
                            */

                $braldunsDistinctionRowset = $braldunsDistinctionTable->findDistinctionsByBraldunId($h["id_braldun"], date("Y-m-d", $moisDebut), date("Y-m-d", $moisFin));

                $points = 0;
                if (count($braldunsDistinctionRowset) > 0) {
                    foreach ($braldunsDistinctionRowset as $t) {
                        $points = $points + $t["points_type_distinction"];
                    }
                }

                $data = null;
                $data["points_stats_distinction"] = $points;
                $data["id_fk_braldun_stats_distinction"] = $h["id_braldun"];
                $data["niveau_braldun_stats_distinction"] = $h["niveau_braldun"];
                $data["mois_stats_distinction"] = date("Y-m-d", $moisEnCours);
                $statsDistinction->deleteAndInsert($data);
                /*}
                    }
                    */

            }
        }
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculPointsDistinctions - exit -" . $retour);
        return $retour;
    }

    private function calculPointsReputation()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculPointsReputation - enter -");

        $retour = "";
        $braldunTable = new Braldun();
        $bralduns = $braldunTable->fetchall("est_pnj_braldun = 'non'");

        Zend_Loader::loadClass("StatsReputation");
        $statsReputation = new StatsReputation();

        if (count($bralduns) > 0) {

            foreach ($bralduns as $h) {

                $statsReputation = new StatsReputation();
                $mois = date("m");
                $moisEnCours = mktime(0, 0, 0, $mois, 2, date("Y"));
                $moisDebut = mktime(0, 0, 0, $mois - 1, 2, date("Y"));
                $moisFin = mktime(0, 0, 0, $mois + 1, 2, date("Y"));
                $reputation = $statsReputation->findByIdBraldun($h["id_braldun"], date("Y-m-d", $moisDebut), date("Y-m-d", $moisFin));

                if ($reputation != null) { // Si le Braldûn avait des points avant, on fait le delta
                    $reputation = $reputation[0];
                    $pointsGredinAvant = $reputation["points_gredin_total_stats_reputation"];
                    $pointsRedresseurAvant = $reputation["points_redresseur_total_stats_reputation"];

                    $pointsGredinDelta = $h["points_gredin_braldun"] - $pointsGredinAvant;
                    $pointsRedresseurDelta = $h["points_redresseur_braldun"] - $pointsRedresseurAvant;
                } else { // S'il n'y a pas de Stats, on prend les points actuels
                    $pointsGredinDelta = $h["points_gredin_braldun"];
                    $pointsRedresseurDelta = $h["points_redresseur_braldun"];
                }

                $data = null;

                if ($pointsGredinDelta < 0) {
                    $pointsGredinDelta = 0;
                }
                if ($pointsRedresseurDelta < 0) {
                    $pointsRedresseurDelta = 0;
                }

                $data["points_gredin_stats_reputation"] = $pointsGredinDelta;
                $data["points_redresseur_stats_reputation"] = $pointsRedresseurDelta;
                $data["points_gredin_total_stats_reputation"] = $h["points_gredin_braldun"];
                $data["points_redresseur_total_stats_reputation"] = $h["points_redresseur_braldun"];

                $data["id_fk_braldun_stats_reputation"] = $h["id_braldun"];
                $data["niveau_braldun_stats_reputation"] = $h["niveau_braldun"];
                $data["mois_stats_reputation"] = date("Y-m-d", $moisEnCours);
                $statsReputation->deleteAndInsert($data);

            }
        }

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - calculPointsReputation - exit -" . $retour);
        return $retour;
    }

    private function preventionSuppression()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - preventionSuppression - enter -");
        Zend_Loader::loadClass("Bral_Util_Mail");

        $retour = "";

        $braldunTable = new Braldun();
        $date = date("Y-m-d H:i:s");
        $add_day = -(Bral_Batchs_Batch::PURGE_BRALDUN_SUPPRESSION_NBJOURS - Bral_Batchs_Batch::PURGE_BRALDUN_PREVENTION_NBJOURS);
        $dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);
        $bralduns = $braldunTable->findAllBatchByDateFin($dateFin);

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - preventionSuppression - date:" . $date . " dateFin -" . $dateFin);

        if (count($bralduns) > 0) {
            foreach ($bralduns as $h) {
                $retour .= $this->envoiMailPrevention($h);
            }
        }

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - preventionSuppression - exit -" . $retour);
        return $retour;
    }

    private function envoiMailPrevention($braldun)
    {
        $retour = "";

        $this->view->braldun = $braldun;
        $add_day = Bral_Batchs_Batch::PURGE_BRALDUN_SUPPRESSION_NBJOURS;
        $this->view->dateSuppression = Bral_Util_ConvertDate::get_date_add_day_to_date($braldun["date_fin_tour_braldun"], $add_day);
        if ($this->view->dateSuppression < date("Y-m-d H:i:s")) {
            $this->view->dateSuppression = date("Y-m-d 0:0:0");
        }
        $this->view->dateSuppression = Bral_Util_ConvertDate::get_datetime_mysql_datetime('d/m/y', $this->view->dateSuppression);
        $this->view->urlJeu = $this->config->general->url;
        $this->view->adresseSupport = $this->config->general->adresseSupport;

        $contenuText = $this->view->render("batchs/bralduns/mailPreventionText.phtml");
        $contenuHtml = $this->view->render("batchs/bralduns/mailPreventionHtml.phtml");

        if ($this->config->mail->envoi->automatique->actif == true) {
            $mail = Bral_Util_Mail::getNewZendMail();
            $mail->setFrom($this->config->general->mail->from_email, $this->config->general->mail->from_nom);
            $mail->addTo($braldun["email_braldun"], $braldun["prenom_braldun"] . " " . $braldun["nom_braldun"]);
            $mail->setSubject($this->config->mail->prevention->titre);
            $mail->setBodyText($contenuText);
            if ($this->config->general->envoi_mail_html == true) {
                $mail->setBodyHtml($contenuHtml);
            }

            $mail->send();
            Bral_Util_Log::mail()->trace("Bral_Batchs_Bralduns - envoiMailPrevention -" . $braldun["email_braldun"] . " " . $braldun["prenom_braldun"] . " " . $braldun["nom_braldun"]);
        }

        $retour = "Prevention.H:" . $braldun["email_braldun"] . "(" . $braldun["id_braldun"] . ") ";
        return $retour;
    }

    private function suppression()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - suppression - enter -");
        Zend_Loader::loadClass("Bral_Util_Mail");
        Zend_Loader::loadClass("AncienBraldun");
        Zend_Loader::loadClass("BraldunsMetiers");
        Zend_Loader::loadClass("BraldunsTitres");
        Zend_Loader::loadClass("BraldunsDistinction");

        $retour = "";
        $nb = 0;

        $braldunTable = new Braldun();
        $date = date("Y-m-d H:i:s");
        $add_day = -Bral_Batchs_Batch::PURGE_BRALDUN_SUPPRESSION_NBJOURS;
        $dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($date, $add_day);

        $bralduns = $braldunTable->findAllBatchByDateFin($dateFin);
        $retour .= $this->calculSuppressionBralduns($bralduns);
        $nb = $nb + $braldunTable->deleteAllBatchByDateFin($dateFin);

        $bralduns = $braldunTable->findAllCompteInactif($dateFin);
        $retour .= $this->calculSuppressionBralduns($bralduns);
        $nb = $nb + $braldunTable->deleteAllCompteInactif($dateFin);

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Bralduns - suppression - exit -");
        return $retour;
    }

    private function calculSuppressionBralduns($bralduns)
    {
        $retour = "";
        if (count($bralduns) > 0) {
            foreach ($bralduns as $h) {
                $retour .= $this->envoiMailSuppression($h);
                $this->copieVersAncien($h);
                $this->forceSuppression($h);
            }
        }
        return $retour;
    }

    // Pour éviter SQLSTATE[HY000]: General error: 1030 Got error -1 from storage engine
    private function forceSuppression($braldun)
    {

        Zend_Loader::loadClass('BraldunsCompetences');
        $braldunsCompetencesTable = new BraldunsCompetences();
        $where = 'id_fk_braldun_hcomp = ' . $braldun['id_braldun'];
        $braldunsCompetencesTable->delete($where);

        Zend_Loader::loadClass('Charrette');
        $charretteTable = new Charrette();
        $where = 'id_fk_braldun_charrette = ' . $braldun['id_braldun'];
        $charretteTable->delete($where);

        Zend_Loader::loadClass('Champ');
        $champTable = new Champ();
        $where = 'id_fk_braldun_champ = ' . $braldun['id_braldun'];
        $champTable->delete($where);

        Zend_Loader::loadClass('Coffre');
        $coffreTable = new Coffre();
        $where = 'id_fk_braldun_coffre = ' . $braldun['id_braldun'];
        $coffreTable->delete($where);

        Zend_Loader::loadClass('Echoppe');
        $echoppeTable = new Echoppe();
        $where = 'id_fk_braldun_echoppe = ' . $braldun['id_braldun'];
        $echoppeTable->delete($where);

        Zend_Loader::loadClass('Message');
        $messageTable = new Message();
        $where = 'fromid = ' . $braldun['id_braldun'] . ' OR toid=' . $braldun['id_braldun'];
        $messageTable->delete($where);

        Zend_Loader::loadClass('Contrat');
        $contratTable = new Contrat();
        $where = 'id_fk_braldun_contrat  = ' . $braldun['id_braldun'] . ' OR id_fk_cible_braldun_contrat =' . $braldun['id_braldun'];
        $contratTable->delete($where);

        Zend_Loader::loadClass('BraldunsDistinction');
        $braldunsDistinctionTable = new BraldunsDistinction();
        $where = 'id_fk_braldun_hdistinction = ' . $braldun['id_braldun'];
        $braldunsDistinctionTable->delete($where);

        Zend_Loader::loadClass('BraldunsEquipement');
        $baldunsEquipementTable = new BraldunsEquipement();
        $where = 'id_fk_braldun_hequipement = ' . $braldun['id_braldun'];
        $baldunsEquipementTable->delete($where);

        Zend_Loader::loadClass('BraldunsMetiers');
        $braldunsMetiersTable = new BraldunsMetiers();
        $where = 'id_fk_braldun_hmetier = ' . $braldun['id_braldun'];
        $braldunsMetiersTable->delete($where);

        Zend_Loader::loadClass('Communaute');
        $communauteTable = new Communaute();
        $where = 'id_fk_braldun_gestionnaire_communaute = ' . $braldun['id_braldun'];
        $communauteTable->delete($where);

        Zend_Loader::loadClass('Butin');
        $butinTable = new Butin();
        $where = 'id_fk_braldun_butin = ' . $braldun['id_braldun'];
        $butinTable->delete($where);

        Zend_Loader::loadClass('Laban');
        $labanTable = new Laban();
        $where = 'id_fk_braldun_laban  = ' . $braldun['id_braldun'];
        $labanTable->delete($where);

        Zend_Loader::loadClass('Evenement');
        $evenementTable = new Evenement();
        $where = 'id_fk_braldun_evenement  = ' . $braldun['id_braldun'];
        $evenementTable->delete($where);

    }

    private function copieVersAncien($braldun)
    {

        // s'il est dans une communaute
        if ($braldun['id_fk_communaute_braldun'] != null) {
            // S'il est le gestionnaire de la communauté
            Zend_Loader::loadClass('Communaute');
            Zend_Loader::loadClass('Bral_Util_Communaute');
            $communauteTable = new Communaute();
            $communaute = $communauteTable->findById($braldun['id_fk_communaute_braldun']);
            if ($communaute != null && $communaute[0]['id_fk_braldun_gestionnaire_communaute'] == $braldun['id_braldun']) {
                Bral_Util_Communaute::calculNouveauGestionnaire($braldun['id_fk_communaute_braldun'], $braldun['id_fk_rang_communaute_braldun'], $braldun['prenom_braldun'], $braldun['nom_braldun'], $braldun['sexe_braldun'], $braldun['id_braldun'], $this->view);
            }
        }

        $braldunsMetiersTable = new BraldunsMetiers();
        $braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($braldun["id_braldun"]);
        $metiers = "";
        if ($braldunsMetierRowset != null) {
            foreach ($braldunsMetierRowset as $m) {
                if ($braldun["sexe_braldun"] == 'feminin') {
                    $metiers .= $m["nom_feminin_metier"];
                } else {
                    $metiers .= $m["nom_masculin_metier"];
                }
                $metiers .= ", ";
            }
            if ($metiers != "") {
                $metiers = substr($metiers, 0, strlen($metiers) - 2);
            }
        }

        $braldunsTitresTable = new BraldunsTitres();
        $braldunsTitreRowset = $braldunsTitresTable->findTitresByBraldunId($braldun["id_braldun"]);
        $titres = "";
        if ($braldunsTitreRowset != null) {
            foreach ($braldunsTitreRowset as $t) {
                if ($braldun["sexe_braldun"] == 'feminin') {
                    $titres .= $t["nom_feminin_type_titre"];
                } else {
                    $titres .= $t["nom_masculin_type_titre"];
                }
                $titres .= ", ";
            }
            if ($titres != "") {
                $titres = substr($titres, 0, mb_strlen($titres) - 2);
            }
        }

        $braldunsDistinctionTable = new BraldunsDistinction();
        $braldunsDistinctionRowset = $braldunsDistinctionTable->findDistinctionsByBraldunId($braldun["id_braldun"]);
        $distinctions = "";
        if ($braldunsDistinctionRowset != null) {
            foreach ($braldunsDistinctionRowset as $d) {
                $distinctions .= $d["texte_hdistinction"] . ", ";
            }
            if ($distinctions != "") {
                $distinctions = substr($distinctions, 0, mb_strlen($distinctions) - 2);
            }
        }

        $ancienBraldunTable = new AncienBraldun();
        $data = array(
            "id_braldun_ancien_braldun" => $braldun["id_braldun"],
            "nom_ancien_braldun" => $braldun["nom_braldun"],
            "prenom_ancien_braldun" => $braldun["prenom_braldun"],
            "id_fk_nom_initial_ancien_braldun" => $braldun["id_fk_nom_initial_braldun"],
            "email_ancien_braldun" => $braldun["email_braldun"],
            "sexe_ancien_braldun" => $braldun["sexe_braldun"],
            "niveau_ancien_braldun" => $braldun["niveau_braldun"],
            "nb_ko_ancien_braldun" => $braldun["nb_ko_braldun"],
            "nb_braldun_ko_ancien_braldun" => $braldun["nb_braldun_ko_braldun"],
            "nb_plaque_ancien_braldun" => $braldun["nb_plaque_braldun"],
            "nb_braldun_plaquage_ancien_braldun" => $braldun["nb_braldun_plaquage_braldun"],
            "nb_monstre_kill_ancien_braldun" => $braldun["nb_monstre_kill_braldun"],
            "id_fk_mere_ancien_braldun" => $braldun["id_fk_mere_braldun"],
            "id_fk_pere_ancien_braldun" => $braldun["id_fk_pere_braldun"],
            "date_creation_ancien_braldun" => $braldun["date_creation_braldun"],
            "metiers_ancien_braldun" => $metiers,
            "titres_ancien_braldun" => $titres,
            "distinctions_ancien_braldun" => $distinctions,
        );

        $ancienBraldunTable->insert($data);

        Zend_Loader::loadClass("Couple");
        $coupleTable = new Couple();
        $data = array('est_valide_couple' => 'non');

        if ($braldun["sexe_braldun"] == "masculin") {
            $where = 'id_fk_m_braldun_couple = ' . $braldun["id_braldun"];
        } else {
            $where = 'id_fk_f_braldun_couple = ' . $braldun["id_braldun"];
        }
        $coupleTable->update($data, $where);

    }

    private function envoiMailSuppression($braldun)
    {
        $retour = "";

        if ($this->config->mail->envoi->automatique->actif == true) {
            $this->view->braldun = $braldun;
            $this->view->urlJeu = $this->config->general->url;
            $this->view->adresseSupport = $this->config->general->adresseSupport;
            $this->view->nbJours = Bral_Batchs_Batch::PURGE_BRALDUN_SUPPRESSION_NBJOURS;

            $contenuText = $this->view->render("batchs/bralduns/mailSuppressionText.phtml");
            $contenuHtml = $this->view->render("batchs/bralduns/mailSuppressionHtml.phtml");

            $mail = Bral_Util_Mail::getNewZendMail();
            $mail->setFrom($this->config->general->mail->from_email, $this->config->general->mail->from_nom);
            $mail->addTo($braldun["email_braldun"], $braldun["prenom_braldun"] . " " . $braldun["nom_braldun"]);
            $mail->setSubject($this->config->mail->suppression->titre);
            $mail->setBodyText($contenuText);
            if ($this->config->general->envoi_mail_html == true) {
                $mail->setBodyHtml($contenuHtml);
            }

            $mail->send();
            Bral_Util_Log::mail()->trace("Bral_Batchs_Bralduns - envoiMailSuppression -" . $braldun["email_braldun"] . " " . $braldun["prenom_braldun"] . " " . $braldun["nom_braldun"]);
        }
        $retour = "Suppression.H:" . $braldun["email_braldun"] . "(" . $braldun["id_braldun"] . ") ";
        return $retour;
    }
}