<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Soule extends Bral_Batchs_Batch
{

    public function calculBatchImpl()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculBatchImpl - enter -");

        Zend_Loader::loadClass("Bral_Util_Evenement");
        Zend_Loader::loadClass("SouleEquipe");
        Zend_Loader::loadClass("SouleNomEquipe");
        Zend_Loader::loadClass("SouleMatch");
        Zend_Loader::loadClass("SouleNomEquipe");

        $retour = $this->calculCreationMatchs();

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculBatchImpl - exit -");
        return $retour;
    }

    private function calculCreationMatchs()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMatchs - enter -");
        Zend_Loader::loadClass("Bral_Util_Tracemail");

        $retour = "";

        $souleMatch = new SouleMatch();
        $matchs = $souleMatch->findNonDebutes();

        $souleEquipe = new SouleEquipe();

        $nbJoueursEquipeMin = floor($this->config->game->soule->max->joueurs / 2);
        $nbJoueursEquipeMinQuota = floor($this->config->game->soule->min->joueurs / 2);

        if ($matchs != null) {
            foreach ($matchs as $m) { // pour tous les matchs non débutés
                Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMatchs - Traitement du match(" . $m["id_soule_match"] . ") ");
                $equipes = $souleEquipe->countInscritsNonDebuteByIdMatch($m["id_soule_match"]);
                if ($equipes != null && count($equipes) == 2
                        && (($equipes[0]["nombre"] >= $nbJoueursEquipeMin && $equipes[1]["nombre"] >= $nbJoueursEquipeMin)
                                || ($m["nb_jours_quota_soule_match"] >= 2))
                ) {

                    Bral_Util_Tracemail::traite("Lancement du match de soule n°" . $m["id_soule_match"], $this->view, "Lancement du match de soule n°" . $m["id_soule_match"]);
                    $retour .= $this->calculCreationMath($m);
                } elseif ($equipes != null && count($equipes) == 2
                        && $equipes[0]["nombre"] >= $nbJoueursEquipeMinQuota && $equipes[1]["nombre"] >= $nbJoueursEquipeMinQuota
                ) {
                    $retour .= $this->updateJoursQuotaMinMatch($m);
                } else {
                    if (count($equipes) == 2) {
                        $retour .= " match(" . $m["id_soule_match"] . ") e1:" . $equipes[0]["nombre"] . " e2:" . $equipes[1]["nombre"];
                        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - match(" . $m["id_soule_match"] . ") equipe 1:" . $equipes[0]["nombre"] . " equipe 2:" . $equipes[1]["nombre"]);
                    } elseif (count($equipes) == 1) {
                        $retour .= " match(" . $m["id_soule_match"] . ") e1:" . $equipes[0]["nombre"] . " e2:0";
                        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - match(" . $m["id_soule_match"] . ") equipe 1:" . $equipes[0]["nombre"]);
                    } else {
                        Bral_Util_Log::batchs()->err("Bral_Batchs_Soule - pas d'equipe avec un match (" . $m["id_soule_match"] . ") initialise");
                        $retour .= " Erreur match(" . $m["id_soule_match"] . ")";
                    }
                }
            }
        } else {
            Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - pas de match non debute -");
        }

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMatchs - exit -");
        return $retour;
    }

    private function updateJoursQuotaMinMatch($match)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - updateJoursQuotaMinMatch - enter -");
        $retour = " updateQuota match(" . $match["id_soule_match"] . ")";

        $quota = $match["nb_jours_quota_soule_match"] + 1;
        $souleMatchTable = new SouleMatch();
        $data = array(
            "nb_jours_quota_soule_match" => $quota,
        );
        $where = "id_soule_match = " . (int)$match["id_soule_match"];
        $souleMatchTable->update($data, $where);

        Bral_Util_Tracemail::traite("Match n°" . $match["id_soule_match"] . " Quota = " . $quota . " (après maj)", $this->view, "Match n°" . $match["id_soule_match"] . " Maj Quota");

        if ($quota == 1) {
            $this->envoyerMessageAvantDebut($match);
        }
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - updateJoursQuotaMinMatch - exit -");
        return $retour;
    }

    private function envoyerMessageAvantDebut($match)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - envoyerMessageAvantDebut - enter -");

        $souleEquipe = new SouleEquipe();

        // on récupère tous les joueurs
        $joueurs = $souleEquipe->findByIdMatch($match["id_soule_match"]);

        if ($joueurs != null && count($joueurs) > 0) {
            foreach ($joueurs as $j) {

                $detailsBot = "Oyez !" . PHP_EOL . PHP_EOL . "Le match approche !";

                $detailsBot .= PHP_EOL . PHP_EOL . "Dans exactement deux jours, vous serez transporté sur le " . $match["nom_soule_terrain"] . PHP_EOL . PHP_EOL;
                $detailsBot .= "Des palissades indestructibles vous empêcheront d'avancer sur le terrain. Elle seront";
                $detailsBot .= " enlevées 48h après votre arrivée sur le terrain (vers 8h du matin). Vous aurez donc 48h pour vous organiser avec ";
                $detailsBot .= "votre équipe avant le début du match.";

                $message = $detailsBot . PHP_EOL . PHP_EOL . " Pierre Albalablabla" . PHP_EOL . "Au plaisir de vous voir bientôt sur le terrain.";

                Bral_Util_Messagerie::envoiMessageAutomatique($this->config->game->pnj->pierre->id_braldun, $j["id_braldun"], $message, $this->view);
            }
        }

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - envoyerMessageAvantDebut - fin -");
    }

    private function calculCreationMath($match)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMath - enter -");
        $retour = " creation match(" . $match["id_soule_match"] . ")";

        $souleEquipe = new SouleEquipe();

        $this->calculNomEquipe($match);

        // on récupère tous les joueurs
        $joueurs = $souleEquipe->findByIdMatch($match["id_soule_match"]);

        if ($joueurs != null && count($joueurs) > 0) {
            foreach ($joueurs as $j) {
                $retour .= $this->calculCreationJoueur($j, $match);
            }
        } else {
            throw new Zend_Exception("Bral_Batchs_Soule - calculCreationMath nb.joueurs invaides match(" . $match["id_soule_match"] . ")");
        }

        $this->updateMatchDb($match);

        $this->deleteRouteAndPalissadeSurTerrain($match);

        $this->creationPalissadeSurTerrain($match);

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationMath - exit -");
        return $retour;
    }

    private function creationPalissadeSurTerrain($match)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - creationPalissadeSurTerrain - enter -");

        Zend_Loader::loadClass("Palissade");
        $palissadeTable = new Palissade();

        $date_creation = date("Y-m-d H:i:s");
        $nb_jours = 2; // les pali
        $date_fin = Bral_Util_ConvertDate::get_date_add_day_to_date($date_creation, $nb_jours);

        for ($x = $match["x_min_soule_terrain"] + 1; $x < $match["x_max_soule_terrain"]; $x++) {
            $y = $match["y_max_soule_terrain"] - 1; // en haut du terrain
            $palissadeTable->insert($this->prepareDataPalissade($x, $y, $date_creation, $date_fin));
            $y = $match["y_min_soule_terrain"] + 1; // en bas du terrain
            $palissadeTable->insert($this->prepareDataPalissade($x, $y, $date_creation, $date_fin));
        }

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - creationPalissadeSurTerrain - exit -");
    }

    private function prepareDataPalissade($x, $y, $date_creation, $date_fin)
    {
        return array(
            "x_palissade" => $x,
            "y_palissade" => $y,
            "z_palissade" => 0,
            "agilite_palissade" => 0,
            "armure_naturelle_palissade" => 100,
            "pv_restant_palissade" => 100,
            "pv_max_palissade" => 100,
            "date_creation_palissade" => $date_creation,
            "date_fin_palissade" => $date_fin,
            "est_destructible_palissade" => "non",
            "est_portail_palissade" => "non",
            "code_1_palissade" => 0,
            "code_2_palissade" => 0,
            "code_3_palissade" => 0,
            "code_4_palissade" => 0,
        );
    }

    private function deleteRouteAndPalissadeSurTerrain($match)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - deleteRouteSurTerrain - enter -");

        Zend_Loader::loadClass("Route");
        $routeTable = new Route();
        $where = "x_route >= " . $match["x_min_soule_terrain"];
        $where .= " AND x_route <= " . $match["x_max_soule_terrain"];
        $where .= " AND y_route >= " . $match["y_min_soule_terrain"];
        $where .= " AND y_route <= " . $match["y_max_soule_terrain"];
        $routeTable->delete($where);

        Zend_Loader::loadClass("Palissade");
        $palissadeTable = new Palissade();
        $where = "x_palissade >= " . $match["x_min_soule_terrain"];
        $where .= " AND x_palissade <= " . $match["x_max_soule_terrain"];
        $where .= " AND y_palissade >= " . $match["y_min_soule_terrain"];
        $where .= " AND y_palissade <= " . $match["y_max_soule_terrain"];
        $palissadeTable->delete($where);

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - deleteRouteSurTerrain - exit -");
    }

    private function updateMatchDb($match)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - updateMatchDb - enter -");
        $souleMatchTable = new SouleMatch();
        $data = array(
            "date_debut_soule_match" => date("Y-m-d H:i:s"),
            "nom_equipea_soule_match" => $match["nom_equipea_soule_match"],
            "nom_equipeb_soule_match" => $match["nom_equipeb_soule_match"],
        );
        $where = "id_soule_match = " . (int)$match["id_soule_match"];
        $souleMatchTable->update($data, $where);
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - updateMatchDb - exit -");
    }

    private function calculNomEquipe(&$match)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculNomEquipe - enter -");
        $souleNomEquipe = new SouleNomEquipe();
        $nomRowset = $souleNomEquipe->fetchAll();

        foreach ($nomRowset as $n) {
            $noms[] = $n["nom_soule_nom_equipe"];
        }
        srand((float)microtime() * 1000000);
        shuffle($noms);

        $nomA = array_pop($noms);
        $nomB = array_pop($noms);

        $match["nom_equipea_soule_match"] = $nomA;
        $match["nom_equipeb_soule_match"] = $nomB;

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculNomEquipe - nomA:" . $match["nom_equipea_soule_match"]);
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculNomEquipe - nomB:" . $match["nom_equipeb_soule_match"]);

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculNomEquipe - exit -");
    }

    private function calculCreationJoueur($joueur, $match)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationJoueur - enter -");
        $retour = "";

        $braldunTable = new Braldun();

        $ecartX = $match["x_max_soule_terrain"] - $match["x_min_soule_terrain"];

        if ($ecartX <= 2) {
            throw new Zend_Exception("Bral_Batchs_Soule - calculCreationMath ecartX invalide(" . $ecartX . ") match(" . $match["id_soule_match"] . ") xmax" . $match["x_max_soule_terrain"] . " xmin:" . $match["x_min_soule_terrain"]);
        }

        // On place tous les joueurs au milieu de leur en-but
        $x = $match["x_min_soule_terrain"] + intval($ecartX / 2);

        if ($joueur["camp_soule_equipe"] == "a") {
            $y = $match["y_max_soule_terrain"]; // en haut du terrain
        } else { // b, en bas
            $y = $match["y_min_soule_terrain"]; // en bas du terrain
        }

        $minutesProchain = Bral_Util_ConvertDate::getMinuteFromHeure($joueur["duree_prochain_tour_braldun"]);
        $minutesProchain = intval($minutesProchain / 2);

        $duree_prochain_tour = Bral_Util_ConvertDate::getHeureFromMinute($minutesProchain);

        $data = array(
            "x_braldun" => $x,
            "y_braldun" => $y,
            "est_soule_braldun" => "oui",
            "date_fin_tour_braldun" => date("Y-m-d H:i:s"),
            "duree_prochain_tour_braldun" => $duree_prochain_tour,
            "soule_camp_braldun" => $joueur["camp_soule_equipe"], // dénormalisation
            "id_fk_soule_match_braldun" => $match["id_soule_match"], // dénormalisation
        );
        $where = "id_braldun = " . (int)$joueur["id_braldun"];
        $braldunTable->update($data, $where);

        $souleEquipe = new SouleEquipe();
        $data = array(
            "x_avant_braldun_soule_equipe" => $joueur["x_braldun"],
            "y_avant_braldun_soule_equipe" => $joueur["y_braldun"],
        );
        $where = "id_fk_braldun_soule_equipe = " . (int)$joueur["id_braldun"];
        $souleEquipe->update($data, $where);

        $idType = $this->config->game->evenements->type->soule;
        $details = "La roulotte a pris [b" . $joueur["id_braldun"] . "] pour aller jouer un match sur le " . $match["nom_soule_terrain"];
        $detailsBot = "Vous êtes arrivés sur le " . $match["nom_soule_terrain"] . " en " . $x . "," . $y . PHP_EOL . "Le nom de votre équipe est : ";
        if ($joueur["camp_soule_equipe"] == "a") {
            $detailsBot .= $match["nom_equipea_soule_match"];
        } else {
            $detailsBot .= $match["nom_equipeb_soule_match"];
        }

        $detailsBot .= PHP_EOL . PHP_EOL . "Attention, la durée de votre tour est divisée par deux pendant le match.";

        Bral_Util_Evenement::majEvenements($joueur["id_braldun"], $idType, $details, $detailsBot, $joueur["niveau_braldun"], "braldun", true, $this->view);

        $detailsBot = PHP_EOL . " Vous avez maintenant exactement 48h pour vous organiser avec le reste de votre équipe, ";
        $detailsBot .= PHP_EOL . "les palissades seront ensuite enlevées.";

        $message = $detailsBot . PHP_EOL . PHP_EOL . " Pierre Albalablabla" . PHP_EOL . "Au plaisir de vous voir bientôt sur le terrain.";

        Bral_Util_Messagerie::envoiMessageAutomatique($this->config->game->pnj->pierre->id_braldun, (int)$joueur["id_braldun"], $message, $this->view);


        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationJoueur - joueur(" . $joueur["id_braldun"] . ") x:" . $x . " y:" . $y);

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Soule - calculCreationJoueur - exit -");
        return $retour;
    }
}