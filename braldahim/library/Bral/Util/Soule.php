<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Soule
{

    const MAIL_SOULE_TITRE = "Braldahim - Message de soule";

    public static function majPlaquage($braldunAttaquant, $braldunCible)
    {
        Zend_Loader::loadClass("SouleEquipe");
        $souleEquipeTable = new SouleEquipe();

        $cible = $souleEquipeTable->findByIdBraldunAndIdMatch($braldunCible->id_braldun, $braldunCible->id_fk_soule_match_braldun);
        $attaquant = $souleEquipeTable->findByIdBraldunAndIdMatch($braldunAttaquant->id_braldun, $braldunAttaquant->id_fk_soule_match_braldun);

        $dataCible = array("nb_plaque_soule_equipe" => $cible["nb_plaque_soule_equipe"] + 1);
        $whereCible = " id_fk_match_soule_equipe = " . $braldunCible->id_fk_soule_match_braldun;
        $whereCible .= " AND id_fk_braldun_soule_equipe=" . $braldunCible->id_braldun;
        $souleEquipeTable->update($dataCible, $whereCible);

        $dataAttaquant = array("nb_braldun_plaquage_soule_equipe" => $attaquant["nb_braldun_plaquage_soule_equipe"] + 1);
        $whereAttaquant = " id_fk_match_soule_equipe = " . $braldunAttaquant->id_fk_soule_match_braldun;
        $whereAttaquant .= " AND id_fk_braldun_soule_equipe=" . $braldunAttaquant->id_braldun;

        $souleEquipeTable->update($dataAttaquant, $whereAttaquant);
    }

    public static function calcuLacheBallon($braldun, $mort)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calcuLacheBallon - enter idBraldun(" . $braldun->id_braldun . ")");

        $retour = false;

        Zend_Loader::loadClass("SouleMatch");
        $souleMatchTable = new SouleMatch();

        $match = $souleMatchTable->findByIdBraldunBallon($braldun->id_braldun);
        if ($match != null && ($mort || Bral_Util_De::get_1d6() == 1)) {
            $data = array(
                "x_ballon_soule_match" => $braldun->x_braldun,
                "y_ballon_soule_match" => $braldun->y_braldun,
                "id_fk_joueur_ballon_soule_match" => null,
            );
            $where = "id_soule_match = " . $match[0]["id_soule_match"];
            $souleMatchTable->update($data, $where);
            Bral_Util_Log::attaque()->debug("Bral_Util_Soule - Match(" . $match[0]["id_soule_match"] . ") Le ballon est lache en x:" . $braldun->x_braldun . " y:" . $braldun->y_braldun . "!");
            $retour = true;
        }

        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calcuLacheBallon - exit (" . $retour . ") -");
        return $retour;
    }

    public static function calculFinMatch(&$braldun, $view, $faireCalculFin)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - enter idBraldun(" . $braldun->id_braldun . ")");
        $retourFinMatch = false;

        Zend_Loader::loadClass("SouleMatch");
        Zend_Loader::loadClass("SouleEquipe");
        Zend_Loader::loadClass("TypeMinerai");
        Zend_Loader::loadClass("TypePlante");
        Zend_Loader::loadClass("TypePartieplante");
        Zend_Loader::loadClass("CoffreMinerai");
        Zend_Loader::loadClass("CoffrePartieplante");
        Zend_Loader::loadClass("Bral_Util_Lien");

        $souleMatchTable = new SouleMatch();
        $matchsRowset = $souleMatchTable->findByIdBraldunBallon($braldun->id_braldun);
        if ($matchsRowset != null && count($matchsRowset) == 1) {
            $match = $matchsRowset[0];
            if (($braldun->soule_camp_braldun == "a" && $braldun->y_braldun == $match["y_min_soule_terrain"])
                    || ($braldun->soule_camp_braldun == "b" && $braldun->y_braldun == $match["y_max_soule_terrain"])
            ) {

                Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - true");

                $souleEquipeTable = new SouleEquipe();
                $joueurs = $souleEquipeTable->findByIdMatch($match["id_soule_match"], "nb_braldun_plaquage_soule_equipe desc");

                if ($joueurs == null) {
                    Bral_Util_Log::soule()->err("Bral_Util_Soule - calculFinMatch - Erreur Nb Joueurs (" . $match["id_soule_match"] . ") ");
                } else {
                    if ($faireCalculFin === true) {
                        self::calculFinMatchSaison($braldun, $joueurs, $match);
                        self::calculFinMatchGains($braldun->id_braldun, $view, $joueurs, $match, $braldun->soule_camp_braldun);
                        self::calculFinMatchDb($match, $braldun->soule_camp_braldun, $view);
                        self::calculFinMatchJoueursDb($braldun, $joueurs, $match);
                        $braldun->est_soule_braldun = "non";
                        $braldun->soule_camp_braldun = null;
                    } else {
                        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - pas de calcul");
                    }
                    $retourFinMatch = true;
                }
            }
        } else {
            Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - Le joueur (" . $braldun->id_braldun . ") n'a pas le ballon");
        }

        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatch - exit (" . $retourFinMatch . ") -");

        return $retourFinMatch;
    }

    public static function prepareEquipes($matchEnCours, &$view, $niveauSouleTerrain)
    {
        $equipes["equipea"] = array('id_equipe' => 'equipeA', 'nom_equipe' => 'équipe A', "joueurs" => null, "plaquages" => 0, "plaques" => 0, "px" => 0);
        $equipes["equipeb"] = array('id_equipe' => 'equipeB', 'nom_equipe' => 'équipe B', "joueurs" => null, "plaquages" => 0, "plaques" => 0, "px" => 0);

        Zend_Loader::loadClass("SouleEquipe");
        $souleEquipeTable = new SouleEquipe();
        if ($matchEnCours != null) {
            $joueurs = $souleEquipeTable->findByIdMatch($matchEnCours["id_soule_match"]);
            $equipes["equipea"]["nom_equipe"] = $matchEnCours["nom_equipea_soule_match"];
            $equipes["equipeb"]["nom_equipe"] = $matchEnCours["nom_equipeb_soule_match"];

            $equipes["equipea"]["px"] = $matchEnCours["px_equipea_soule_match"];
            $equipes["equipeb"]["px"] = $matchEnCours["px_equipeb_soule_match"];

            $equipes["equipea"]["plaquages"] = 0;
            $equipes["equipea"]["plaques"] = 0;
            $equipes["equipeb"]["plaquages"] = 0;
            $equipes["equipeb"]["plaques"] = 0;

        } else {
            $joueurs = $souleEquipeTable->findNonDebuteByNiveauTerrain($niveauSouleTerrain);
        }

        if ($joueurs != null && count($joueurs) > 0) {
            foreach ($joueurs as $j) {
                if ($j["camp_soule_equipe"] == 'a') {
                    $equipes["equipea"]["joueurs"][] = $j;
                    $equipes["equipea"]["plaquages"] = $equipes["equipea"]["plaquages"] + $j["nb_braldun_plaquage_soule_equipe"];
                    $equipes["equipea"]["plaques"] = $equipes["equipea"]["plaques"] + $j["nb_plaque_soule_equipe"];
                } else {
                    $equipes["equipeb"]["joueurs"][] = $j;
                    $equipes["equipeb"]["plaquages"] = $equipes["equipeb"]["plaquages"] + $j["nb_braldun_plaquage_soule_equipe"];
                    $equipes["equipeb"]["plaques"] = $equipes["equipeb"]["plaques"] + $j["nb_plaque_soule_equipe"];
                }
            }
        }

        $view->equipes = $equipes;
        $view->joueurs = $joueurs;
    }

    private static function calculFinMatchGains($idBraldunFin, $view, $joueurs, $match, $campGagnant)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchGains - enter -");

        $equipeA = array();
        $equipeB = array();

        $niveauTotal = 0;
        $maxPlaquages = 0;
        $nbCasesBallon = 0;
        foreach ($joueurs as $j) {
            if ($j["camp_soule_equipe"] == "a") {
                $equipeA[$j["id_braldun"]]["nb_plaquage"] = $j["nb_braldun_plaquage_soule_equipe"];
                $equipeA[$j["id_braldun"]]["nb_passe"] = $j["nb_passe_soule_equipe"];
                $equipeA[$j["id_braldun"]]["nb_case_ballon"] = $j["nb_case_ballon_soule_equipe"];
                $equipeA[$j["id_braldun"]]["braldun"] = $j;
            } else {
                $equipeB[$j["id_braldun"]]["nb_plaquage"] = $j["nb_braldun_plaquage_soule_equipe"];
                $equipeB[$j["id_braldun"]]["nb_passe"] = $j["nb_passe_soule_equipe"];
                $equipeB[$j["id_braldun"]]["nb_case_ballon"] = $j["nb_case_ballon_soule_equipe"];
                $equipeB[$j["id_braldun"]]["braldun"] = $j;
            }

            if ($maxPlaquages < $j["nb_braldun_plaquage_soule_equipe"]) {
                $maxPlaquages = $j["nb_braldun_plaquage_soule_equipe"];
            }

            if ($nbCasesBallon < $j["nb_case_ballon_soule_equipe"]) {
                $nbCasesBallon = $j["nb_case_ballon_soule_equipe"];
            }

            $niveauTotal = $niveauTotal + $j["niveau_braldun"];
        }

        $typeMineraiTable = new TypeMinerai();
        $minerais = $typeMineraiTable->fetchAll();
        $minerais = $minerais->toArray();

        Zend_Loader::loadClass("Bral_Util_Plantes");
        $plantes = Bral_Util_Plantes::getTabPlantes();

        if ($campGagnant == 'a') {
            self::repartitionGain($match, $idBraldunFin, $view, $niveauTotal, $equipeA, true, $minerais, $plantes, $maxPlaquages, $nbCasesBallon, $equipeB);
            self::repartitionGain($match, $idBraldunFin, $view, $niveauTotal, $equipeB, false, $minerais, $plantes, $maxPlaquages, $nbCasesBallon, $equipeA);
        } else {
            self::repartitionGain($match, $idBraldunFin, $view, $niveauTotal, $equipeA, false, $minerais, $plantes, $maxPlaquages, $nbCasesBallon, $equipeB);
            self::repartitionGain($match, $idBraldunFin, $view, $niveauTotal, $equipeB, true, $minerais, $plantes, $maxPlaquages, $nbCasesBallon, $equipeA);
        }

        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchGains - exit -");
    }

    private static function repartitionGain($match, $idBraldunFin, $view, $niveauTotal, $equipe, $estGagnant, $minerais, $plantes, $maxPlaquages, $nbCasesBallon, $equipeAdverse)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - repartitionGain - enter -");

        if ($estGagnant) {
            $pourcentage = 0.4;
        } else {
            $pourcentage = 0.2;
        }

        $rang = -1;
        $nbPlaquageCourant = -1;
        $nbBraldun = 0;
        foreach ($equipe as $idBraldun => $tab) { // equipe est deja trie par ordre de nb_braldun_plaquage_soule_equipe asc
            $nbBraldun++;
            if ($nbPlaquageCourant == -1) {
                $nbPlaquageCourant = $tab["nb_plaquage"];
                $rang = 1;
            }
            if ($nbPlaquageCourant != $tab["nb_plaquage"]) {
                $nbPlaquageCourant = $tab["nb_plaquage"];
                $rang++;
            }

            $nbGain = ceil($niveauTotal * $pourcentage * self::getCoefRang($rang));
            if ($rang > 10) {
                $nbBraldunRestant = count($equipe) - $nbBraldun;
                $nbGain = ceil($nbGain / $nbBraldunRestant);
            }

            if ($estGagnant && $nbGain < 6) {
                $nbGain = 6;
            } else if ($estGagnant == false && $nbGain < 3) {
                $nbGain = 3;
            }

            self::calculGainBraldun($match, $equipe, $idBraldunFin, $tab["braldun"], $view, $nbGain, $minerais, $plantes, $rang, $estGagnant, $maxPlaquages, $nbCasesBallon, $equipeAdverse);
        }

        Bral_Util_Log::soule()->trace("Bral_Util_Soule - repartitionGain - exit -");
    }

    private static function calculGainBraldun($match, $equipe, $idBraldunFin, $braldun, $view, $nbGain, $minerais, $plantes, $rang, $estGagnant, $maxPlaquages, $nbCasesBallon, $equipeAdverse)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculGainBraldun - enter - idBraldun(" . $braldun["id_braldun"] . ") gain(" . $nbGain . ")");

        $nbMinerai = count($minerais);
        $nbPlante = count($plantes);

        $nbUnitaireGain = $nbGain;

        $tirage1 = Bral_Util_De::get_de_specifique(0, $nbMinerai + $nbPlante - 1);
        $tirage2 = Bral_Util_De::get_de_specifique_hors_liste(0, $nbMinerai + $nbPlante - 1, array($tirage1));
        $tirage3 = Bral_Util_De::get_de_specifique_hors_liste(0, $nbMinerai + $nbPlante - 1, array($tirage1, $tirage2));

        $texte = self::updateDbDataPxPerso($match, $braldun, $equipe);
        $texte .= self::updateDbData($braldun["id_braldun"], $nbUnitaireGain, $tirage1, $nbMinerai, $nbPlante, $minerais, $plantes);
        $texte .= self::updateDbData($braldun["id_braldun"], $nbUnitaireGain, $tirage2, $nbMinerai, $nbPlante, $minerais, $plantes);
        $texte .= self::updateDbData($braldun["id_braldun"], $nbUnitaireGain, $tirage3, $nbMinerai, $nbPlante, $minerais, $plantes);

        $config = Zend_Registry::get('config');
        $idType = $config->game->evenements->type->soule;

        $details = "[b" . $idBraldunFin . "] a marqué";
        if ($idBraldunFin == $braldun["id_braldun"]) {
            $details .= " et ";
        } else {
            $details .= ", [b" . $braldun["id_braldun"] . "] ";
        }
        $details .= " a terminé au rang n°" . $rang;

        if ($estGagnant) {
            $details .= " des gagnants";
        } else {
            $details .= " des perdants";
        }

        if ($idBraldunFin != $braldun["id_braldun"]) {
            $detailsBot = Bral_Util_Lien::remplaceBaliseParNomEtJs("[b" . $idBraldunFin . "]", false);
            $detailsBot .= " a";
        } else {
            $detailsBot = " Vous avez";
        }
        $detailsBot .= " apporté le ballon au bon endroit, le match de soule est terminé." . PHP_EOL . PHP_EOL;
        $detailsBot .= " Vous avez gagné : " . PHP_EOL;
        $detailsBot .= $texte;
        $detailsBot .= " placés directement dans votre coffre à la banque";

        Bral_Util_Evenement::majEvenements($braldun["id_braldun"], $idType, $details, $detailsBot, $braldun["niveau_braldun"], "braldun", true, $view, $match["id_soule_match"]);

        self::calculDistinction($equipe, $braldun, $maxPlaquages, $nbCasesBallon, $estGagnant, $equipeAdverse, $idBraldunFin, $match);

        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculGainBraldun - exit -");
    }

    private static function calculDistinction($equipe, $braldun, $maxPlaquages, $nbCasesBallon, $estGagnant, $equipeAdverse, $idBraldunFin, $match)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculDistinction - enter -");

        Zend_Loader::loadClass("Bral_Util_Distinction");

        if ($equipe[$braldun["id_braldun"]]["nb_plaquage"] > 0) { // plaqueur dans un match de Soule
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($braldun["id_braldun"], $braldun["niveau_braldun"], Bral_Util_Distinction::ID_TYPE_PLAQUEUR, null, null, " (match n°" . $match["id_soule_match"] . ")", false);
        }

        if ($maxPlaquages == $equipe[$braldun["id_braldun"]]["nb_plaquage"]) { // Meilleur plaqueur dans un match de Soule
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($braldun["id_braldun"], $braldun["niveau_braldun"], Bral_Util_Distinction::ID_TYPE_MEILLEUR_PLAQUEUR, null, null, " (match n°" . $match["id_soule_match"] . ")", false);
        }

        if ($nbCasesBallon == $equipe[$braldun["id_braldun"]]["nb_case_ballon"]) { // Plus grande course du match
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($braldun["id_braldun"], $braldun["niveau_braldun"], Bral_Util_Distinction::ID_TYPE_GRANDE_COURSE, null, null, " (match n°" . $match["id_soule_match"] . ")", false);
        }

        if ($equipe[$braldun["id_braldun"]]["nb_passe"] > 0) { // Passeur dans un match de Soule
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($braldun["id_braldun"], $braldun["niveau_braldun"], Bral_Util_Distinction::ID_TYPE_PASSEUR, null, null, " (match n°" . $match["id_soule_match"] . ")", false);
        }

        if ($idBraldunFin == $braldun["id_braldun"]) { // Marqueur dans un match de Soule
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($braldun["id_braldun"], $braldun["niveau_braldun"], Bral_Util_Distinction::ID_TYPE_MARQUEUR, null, null, " (match n°" . $match["id_soule_match"] . ")", false);
        }

        if ($estGagnant) { // Gagner un match de Soule
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($braldun["id_braldun"], $braldun["niveau_braldun"], Bral_Util_Distinction::ID_TYPE_GAGNER_MATCH, null, null, " (match n°" . $match["id_soule_match"] . ")");
        }

        if (count($equipeAdverse) > count($equipe)) { // Gagner un match de Soule en infériorité
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($braldun["id_braldun"], $braldun["niveau_braldun"], Bral_Util_Distinction::ID_TYPE_GAGNER_MATCH_INFERIORITE, null, null, " (match n°" . $match["id_soule_match"] . ")");
        }

        // Champion de soule
        if (Bral_Util_Distinction::possedeDistinctionSoulePourChampion($braldun["id_braldun"]) && !Bral_Util_Distinction::possedeDistinction($braldun["id_braldun"], Bral_Util_Distinction::ID_TYPE_CHAMPION_SOULE, null, null)) {
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($braldun["id_braldun"], $braldun["niveau_braldun"], Bral_Util_Distinction::ID_TYPE_CHAMPION_SOULE);
        }

        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculDistinction - exit -");
    }

    private static function updateDbDataPxPerso($match, $braldun, $equipe)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - updateDbDataPxPerso - enter");

        if ($braldun["soule_camp_braldun"] == "a") {
            $gainPxPerso = floor($match["px_equipea_soule_match"] / count($equipe));
            $nbPxPerso = $braldun["px_perso_braldun"] + $gainPxPerso;
        } else {
            $gainPxPerso = floor($match["px_equipeb_soule_match"] / count($equipe));
            $nbPxPerso = $braldun["px_perso_braldun"] + $gainPxPerso;
        }

        $braldunTable = new Braldun();
        $data = array(
            "px_perso_braldun" => $nbPxPerso,
        );

        $where = "id_braldun = " . $braldun["id_braldun"];
        $braldunTable->update($data, $where);

        $texte = " " . $gainPxPerso . " PX (Perso) ainsi que " . PHP_EOL;

        Bral_Util_Log::soule()->trace("Bral_Util_Soule - updateDbDataPxPerso - exit");
        return $texte;
    }

    private static function updateDbData($idBraldun, $nbUnitaireGain, $tirage, $nbMinerai, $nbPlante, $minerais, $plantes)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - updateDbData - enter $idBraldun, $nbUnitaireGain, $tirage, $nbMinerai, $nbPlante -");

        if ($nbUnitaireGain > 1) {
            $s = "s";
        } else {
            $s = "";
        }

        Zend_Loader::loadClass("Coffre");
        $coffreTable = new Coffre();

        $coffre = $coffreTable->findByIdBraldun($idBraldun);
        if ($coffre == null || count($coffre) != 1) {
            throw new Zend_Eception("Erreur updateDbData idb:" . $idBraldun);
        }

        $idCoffre = $coffre[0]["id_coffre"];

        if ($tirage < $nbMinerai) {
            $coffreMineraiTable = new CoffreMinerai();
            $data = array(
                "id_fk_coffre_coffre_minerai" => $idCoffre,
                "id_fk_type_coffre_minerai" => $minerais[$tirage]["id_type_minerai"],
                "quantite_brut_coffre_minerai" => $nbUnitaireGain,
            );
            $texte = "  " . $nbUnitaireGain . " minerai$s brut$s de " . $minerais[$tirage]["nom_type_minerai"];
            Bral_Util_Log::soule()->trace("Bral_Util_Soule - updateDbData minerai type(" . $minerais[$tirage]["id_type_minerai"] . ") nb(" . $nbUnitaireGain . ")");
            $coffreMineraiTable->insertOrUpdate($data);
        } else {
            $coffrePartieplanteTable = new CoffrePartieplante();
            $data = array(
                "id_fk_coffre_coffre_partieplante" => $idCoffre,
                "id_fk_type_coffre_partieplante" => $plantes[$tirage - $nbMinerai]["id_type_partieplante"],
                "id_fk_type_plante_coffre_partieplante" => $plantes[$tirage - $nbMinerai]["id_type_plante"],
                "quantite_coffre_partieplante" => $nbUnitaireGain,
            );

            $texte = "  " . $nbUnitaireGain . " " . $plantes[$tirage - $nbMinerai]["nom_type_partieplante"] . "$s de " . $plantes[$tirage - $nbMinerai]["nom_type_plante"];
            Bral_Util_Log::soule()->trace("Bral_Util_Soule - updateDbData minerai type(" . $plantes[$tirage - $nbMinerai]["id_type_partieplante"] . ", " . $plantes[$tirage - $nbMinerai]["id_type_plante"] . ") nb(" . $nbUnitaireGain . ")");
            $coffrePartieplanteTable->insertOrUpdate($data);
        }

        Bral_Util_Log::soule()->trace("Bral_Util_Soule - updateDbData - exit");
        return $texte . PHP_EOL;
    }

    private static function getCoefRang($rang)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - getCoefRang - enter -");
        $coef = 0;
        switch ($rang) {
            case 1:
                $coef = 0.3;
                break;
            case 2:
                $coef = 0.15;
                break;
            case 3:
                $coef = 0.1;
                break;
            case 4:
                $coef = 0.08;
                break;
            case 5:
                $coef = 0.07;
                break;
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
                $coef = 0.05;
                break;
            default:
                $coef = 0.05;
                break;
        }
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - getCoefRang - exit (" . $coef . ") -");
        return $coef;
    }

    private static function calculFinMatchDb($match, $campGagnant, $view)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchDb - enter - matchId(" . $match["id_soule_match"] . ")");

        $htmlFin = self::prepareCarte($match, $view);

        $souleMatchTable = new SouleMatch();
        $data = array(
            "date_fin_soule_match" => date("Y-m-d H:i:s"),
            "id_fk_joueur_ballon_soule_match" => null,
            "x_ballon_soule_match" => null,
            "y_ballon_soule_match" => null,
            "camp_gagnant_soule_match" => $campGagnant,
            "html_fin_soule_match" => $htmlFin,
        );
        $where = "id_soule_match = " . (int)$match["id_soule_match"];
        $souleMatchTable->update($data, $where);

        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchDb - exit -");
    }

    private static function prepareCarte($match, $view)
    {
        self::prepareEquipes($match, $view, $match["niveau_soule_terrain"]);
        Zend_Loader::loadClass("SouleTerrain");
        $souleTerrainTable = new SouleTerrain();
        $terrainRowset = $souleTerrainTable->findByIdTerrain($match["id_fk_terrain_soule_match"]);
        $view->terrainCourant = $terrainRowset;
        $view->matchEnCours = $match;

        $idPorteur = $match["id_fk_joueur_ballon_soule_match"];
        $braldunTable = new Braldun();
        $braldun = $braldunTable->findById($idPorteur);
        $porteur = $braldun->toArray();
        $view->porteur = $porteur;

        return $view->render("soule/voir/carte.phtml");
    }

    private static function calculFinMatchJoueursDb($braldun, $joueurs, $match)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchJoueursDb - enter - matchId(" . $match["id_soule_match"] . ")");

        $braldunTable = new Braldun();

        foreach ($joueurs as $j) {

            if ($j["retour_xy_soule_equipe"] == "oui") {
                $x_braldun = $j["x_avant_braldun_soule_equipe"];
                $y_braldun = $j["y_avant_braldun_soule_equipe"];
            } else {
                // Mairie de Krotrasque (555, -125)
                if (Bral_Util_De::get_1d2() == 1) {
                    $xalea = Bral_Util_De::get_1d6() - 1;
                } else {
                    $xalea = -(Bral_Util_De::get_1d6() - 1);
                }

                if (Bral_Util_De::get_1d2() == 1) {
                    $yalea = Bral_Util_De::get_1d6() - 1;
                } else {
                    $yalea = -(Bral_Util_De::get_1d6() - 1);
                }

                $x_braldun = 555 + $xalea;
                $y_braldun = -125 + $yalea;
            }

            $mdate = date("Y-m-d H:i:s");
            $config = Zend_Registry::get('config');
            $date_fin_tour_braldun = Bral_Util_ConvertDate::get_date_remove_time_to_date($mdate, $config->game->tour->inscription->duree_base_cumul);

            $data = array(
                "x_braldun" => $x_braldun,
                "y_braldun" => $y_braldun,
                "z_braldun" => 0,
                "est_en_sortie_soule_braldun" => 'oui',
                "est_soule_braldun" => 'non',
                "est_intangible_braldun" => "oui",
                "est_engage_braldun" => "non",
                "est_engage_next_dla_braldun" => "non",
                "date_fin_tour_braldun" => $date_fin_tour_braldun,
            );

            $where = "id_braldun = " . $j["id_braldun"];
            $braldunTable->update($data, $where);

            if ($braldun->id_braldun == $j["id_braldun"]) {
                $braldun->est_soule_braldun = "non";
                $braldun->x_braldun = $x_braldun;
                $braldun->y_braldun = $y_braldun;
                $braldun->z_braldun = 0;
                $braldun->est_en_sortie_soule_braldun = "oui";
                $braldun->est_intangible_braldun = "oui";
                $braldun->est_engage_braldun = "non";
                $braldun->est_engage_next_dla_braldun = "non";
                $braldun->px_perso_braldun = $j["px_perso_braldun"]; // rafraichissement des px perso
            }
        }

        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchJoueursDb - exit -");
    }

    private static function calculFinMatchSaison($braldun, $joueurs, $match)
    {
        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchSaison - enter - matchId(" . $match["id_soule_match"] . ")");

        // Si le match a débuté avant le 1 septembre : ce n'est pas un match de saison
        if (date("Y-09-01 00:00:00") > $match["date_debut_soule_match"]) {
            Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchSaison - ce n'est pas un match de saison exit -");
            return;
        }

        Zend_Loader::loadClass("SouleMatch");
        $souleMatchTable = new SouleMatch();
        $matchs = $souleMatchTable->findMatchSaisonTermineByIdTerrainAndAnnee($match["id_fk_terrain_soule_match"], date("Y"));

        if ($matchs != null || count($matchs) > 0) {
            Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchSaison - il y a deja eu un match de saison en " . date("Y") . " terrain:" . $match["id_fk_terrain_soule_match"] . "- exit -");
            return;
        }

        Zend_Loader::loadClass("Bral_Util_Distinction");
        Zend_Loader::loadClass("Bral_Helper_Calendrier");

        // c'est ici un match de saison. On attribue les distinctions
        foreach ($joueurs as $j) {
            $annee = Bral_Helper_Calendrier::getAnnee(date('Y'));
            $anneePlusUn = $annee + 1;
            Bral_Util_Distinction::ajouterDistinctionEtEvenement($j["id_braldun"], $j["niveau_braldun"], Bral_Util_Distinction::ID_TYPE_SOULE_JOUEUR_SAISON, null, null, " " . $annee . " / " . $anneePlusUn, false);
        }

        Bral_Util_Log::soule()->trace("Bral_Util_Soule - calculFinMatchSaison - exit -");
    }

    public static function updateCagnotteDb($braldun, $nbPxCommun)
    {
        // pendant un match de soule, les px communs vont dans la cagnotte
        Zend_Loader::loadClass("SouleMatch");
        $souleMatchTable = new SouleMatch();
        $matchs = $souleMatchTable->findByIdMatch($braldun->id_fk_soule_match_braldun);

        // Gain péhiks * 2 pendant un match de soule, pour la cagnotte
        $nbPxCommun = $nbPxCommun * 2;

        if ($matchs == null || count($matchs) > 1) {
            throw new Zend_Exception("Bral_Util_Soule::updateCagnotteDb - Erreur calcul match en cours. idh:" . $braldun->id_braldun);
        } else {
            $match = $matchs[0];

            if ($braldun->soule_camp_braldun == 'a') {
                $data = array(
                    "px_equipea_soule_match" => $match["px_equipea_soule_match"] + $nbPxCommun,
                );
            } else {
                $data = array(
                    "px_equipeb_soule_match" => $match["px_equipeb_soule_match"] + $nbPxCommun,
                );
            }
            $where = "id_soule_match = " . (int)$match["id_soule_match"];
            $souleMatchTable->update($data, $where);
        }
    }

    /**
     * Désinscription d'un Braldûn à un match de Soule.
     * @param $idBraldun identifiant du Hobbbit
     * @return null si pas de match
     */
    public static function calculDesinscription($idBraldun)
    {
        $match = self::desincriptionPrepareTerrain($idBraldun);
        if ($match != null) {
            self::calculDesinscriptionBd($match["id_soule_match"], $idBraldun);
        }
        return $match;
    }

    public static function desincriptionPrepareTerrain($idBraldun)
    {
        Zend_Loader::loadClass('SouleMatch');
        $souleMatchTable = new SouleMatch();
        $matchs = $souleMatchTable->findNonDebuteByIdBraldun($idBraldun);

        $matchRetour = null;

        if ($matchs != null && count($matchs) == 1) { // s'il n'y a pas de match en cours
            $match = $matchs[0];
            // on regarde s'il le quota n'est pas atteint (enfin non en cours ie: > 0)
            if ($match["nb_jours_quota_soule_match"] == 0) {
                $matchRetour = $match;
                //} else {
                //	throw new Zend_Exception(get_class($this)." deinscriptionPossible impossible quota");
            }
        }
        return $matchRetour;
    }

    public static function calculDesinscriptionBd($idMatch, $idBraldun)
    {
        $where = "id_fk_match_soule_equipe = " . (int)$idMatch;
        $where .= " AND id_fk_braldun_soule_equipe = " . (int)$idBraldun;

        Zend_Loader::loadClass('SouleEquipe');
        $souleEquipeTable = new SouleEquipe();
        $souleEquipeTable->delete($where);
    }

    // Appelé par Tour.php
    public static function calculSortieSoule(&$braldun)
    {
        $retour = null;
        if ($braldun->est_en_sortie_soule_braldun == 'oui') {
            $idMatch = $braldun->id_fk_soule_match_braldun;
            $camp = $braldun->soule_camp_braldun;

            $braldun->est_en_sortie_soule_braldun = 'non';
            $braldun->soule_camp_braldun = null;

            $braldun->id_fk_soule_match_braldun = null;

            Zend_Loader::loadClass("SouleMatch");
            $souleMatchTable = new SouleMatch();
            $matchs = $souleMatchTable->findByIdMatch($idMatch);

            if (count($matchs) != 1) {
                throw new Zend_Exception("calculSortieSoule::Erreur Match:" . $idMatch);
            }

            $match = $matchs[0];

            if ($match["camp_gagnant_soule_match"] == $camp) {
                $retour["resultat"] = "victoire";
            } else {
                $retour["resultat"] = "defaite";
            }
            if ($camp = 'a') {
                $retour["equipeBraldun"] = $match["nom_equipea_soule_match"];
                $retour["equipeAdverse"] = $match["nom_equipeb_soule_match"];
            } else {
                $retour["equipeBraldun"] = $match["nom_equipeb_soule_match"];
                $retour["equipeAdverse"] = $match["nom_equipea_soule_match"];
            }
        }

        return $retour;
    }

    public static function deplacerAvecBallon($braldun, $offsetX, $offsetY)
    {

        Zend_Loader::loadClass("SouleMatch");
        $souleMatch = new SouleMatch();
        $matchsRowset = $souleMatch->findByIdBraldunBallon($braldun->id_braldun);

        if ($matchsRowset != null && count($matchsRowset) == 1) {
            //$this->match = $matchsRowset[0];
        } else { // pas de ballon
            return;
        }


        $nbCasesX = $offsetX; // ex : -1, ou -2, ou 2, etc...
        $nbCasesY = $offsetY;

        if ($nbCasesX < 0) {
            $nbCasesX = -$nbCasesX;
        }
        if ($nbCasesY < 0) {
            $nbCasesY = -$nbCasesY;
        }
        if ($nbCasesX < $nbCasesY) {
            $nbCases = $nbCasesY;
        } else {
            $nbCases = $nbCasesX;
        }

        Zend_Loader::loadClass("SouleEquipe");
        $souleEquipeTable = new SouleEquipe();

        $joueur = $souleEquipeTable->findByIdBraldunAndIdMatch($braldun->id_braldun, $braldun->id_fk_soule_match_braldun);
        if ($joueur == null) {
            throw new Zend_Exception("Erreur deplacerAvecBallon idH:" . $braldun->id_braldun . " idM:" . $braldun->id_fk_soule_match_braldun);
        }

        $where = "id_soule_equipe=" . $joueur["id_soule_equipe"];
        $data["nb_case_ballon_soule_equipe"] = $joueur["nb_case_ballon_soule_equipe"] + $nbCases;
        $souleEquipeTable->update($data, $where);
    }
}
