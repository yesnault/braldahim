<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Donjon
{

    public static function messageSignature(&$message, $donjonCourant, $avecMessageInutile = true)
    {
        $message .= $donjonCourant["prenom_braldun"] . " " . $donjonCourant["nom_braldun"] . ", ";
        if ($donjonCourant["sexe_braldun"] == "masculin") {
            $message .= "garde";
        } else {
            $message .= "gardienne";
        }
        $message .= " du donjon de la " . $donjonCourant["nom_region"] . PHP_EOL;

        if ($avecMessageInutile) {
            $message .= "Inutile de répondre à ce message.";
        }
    }

    public static function controleInscriptionEquipe($donjon, $view)
    {
        Zend_Loader::loadClass('DonjonEquipe');

        $donjonEquipeTable = new DonjonEquipe();
        $donjonEquipe = $donjonEquipeTable->findNonTermineeByIdDonjon($donjon["id_donjon"]);

        if (count($donjonEquipe) > 1) {
            throw new Exception('Equipe de donjon en cours > 1 idDonjon:' . $donjon["id_donjon"]);
        }

        if (count($donjonEquipe) == 1) {
            $equipeCourante = $donjonEquipe[0];
            if ($equipeCourante["etat_donjon_equipe"] == "inscription" && $equipeCourante["date_limite_inscription_donjon_equipe"] <= date("Y-m-d H:i:s")) {
                self::annuleInscriptionEquipe($donjon, $equipeCourante, $view);
            }
        }
    }

    private static function annuleInscriptionEquipe($donjon, $equipe, $view)
    {
        $donjonEquipeTable = new DonjonEquipe();

        $data = array(
            'etat_donjon_equipe' => 'annule',
        );

        $where = 'id_donjon_equipe=' . $equipe['id_donjon_equipe'];
        $donjonEquipeTable->update($data, $where);

        Zend_Loader::loadClass("DonjonBraldun");
        $donjonBraldunTable = new DonjonBraldun();
        $donjonBraldun = $donjonBraldunTable->findByIdEquipe($equipe["id_donjon_equipe"]);

        foreach ($donjonBraldun as $h) {
            self::envoieMessageAnnulationBraldun($donjon, $h["id_braldun"], $view);
        }
    }

    private static function envoieMessageAnnulationBraldun($donjon, $idBraldun, $view)
    {
        $message = "[Poste de Garde]" . PHP_EOL . PHP_EOL;

        $message .= "Ahhhh ! " . PHP_EOL;
        $message .= "Vos coéquipiers n'ont pas validé à temps et la porte du Donjon n'a donc pas été ouverte." . PHP_EOL . PHP_EOL;
        $message .= " Votre inscription au Donjon est annulée." . PHP_EOL . PHP_EOL;

        Bral_Util_Donjon::messageSignature($message, $donjon);
        Bral_Util_Messagerie::envoiMessageAutomatique($donjon["id_fk_pnj_donjon"], $idBraldun, $message, $view);
    }

    public static function controleFin($donjon, $view)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - controleFin - enter -");
        Zend_Loader::loadClass('DonjonEquipe');

        $donjonEquipeTable = new DonjonEquipe();
        $donjonEquipe = $donjonEquipeTable->findNonTermineeByIdDonjon($donjon["id_donjon"]);

        if (count($donjonEquipe) > 1) {
            throw new Exception('Equipe de donjon en cours > 1 idDonjon:' . $donjon["id_donjon"]);
        }

        if (count($donjonEquipe) == 1) {
            $equipeCourante = $donjonEquipe[0];

            if ($equipeCourante["etat_donjon_equipe"] == "en_cours" && $equipeCourante["date_mort_monstre_donjon_equipe"] != null && $equipeCourante["date_mort_monstre_donjon_equipe"] <= Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), -3)) {
                Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - controleFin - finalise Reussi :" . $equipeCourante["id_donjon_equipe"]);
                self::finaliseDonjonReussi($donjon, $equipeCourante, $view);
            } elseif ($equipeCourante["etat_donjon_equipe"] == "en_cours" && $equipeCourante["date_mort_monstre_donjon_equipe"] == null && $equipeCourante["date_fin_donjon_equipe"] != null && $equipeCourante["date_fin_donjon_equipe"] <= date("Y-m-d H:i:s")) {
                Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - controleFin - finalise Echec :" . $equipeCourante["id_donjon_equipe"]);
                self::finaliseDonjonEchec($donjon, $equipeCourante, $view);
            } else {
                Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - controleFin - pas de donjon a traiter -");
            }
        }
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - controleFin - exit -");
    }

    private static function finaliseDonjonEchec($donjon, $equipe, $view)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseDonjonEchec - enter -");

        Zend_Loader::loadClass("Bral_Util_Lien");
        Zend_Loader::loadClass("Bral_Util_Evenement");

        Zend_Loader::loadClass("Region");
        $regionTable = new Region();
        $region = $regionTable->findById($donjon["id_fk_region_donjon"]);
        $nomComte = $region["nom_region"];

        Zend_Loader::loadClass("DonjonBraldun");
        $donjonBraldunTable = new DonjonBraldun();
        $donjonBraldun = $donjonBraldunTable->findByIdEquipe($equipe["id_donjon_equipe"]);

        $listeBralduns = "";
        foreach ($donjonBraldun as $h) {
            $listeBralduns .= $h["prenom_braldun"] . " " . $h["nom_braldun"] . " (" . $h["id_braldun"] . "), ";
            self::envoieMessageEchecBraldun($donjon, $equipe, $h, $view);
            self::finaliseBraldunEchec($h);
        }

        self::envoieMessageEchecBralduns($donjon, $equipe, $nomComte, $listeBralduns, $view);
        self::creationEmissaires($donjon, $view);

        // et l'on termine le donjon
        $donjonEquipeTable = new DonjonEquipe();
        $data = array(
            'etat_donjon_equipe' => 'termine',
        );
        $where = 'id_donjon_equipe=' . $equipe['id_donjon_equipe'];
        $donjonEquipeTable->update($data, $where);

        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseDonjonEchec - exit -");
    }

    private static function finaliseBraldunEchec($braldun)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseBraldunEchec - enter h:" . $braldun["id_braldun"]);
        $braldun["nb_ko_braldun"] = $braldun["nb_ko_braldun"] + 1;

        $data = array(
            'pv_restant_braldun' => 0,
            'est_ko_braldun' => 'oui',
            'nb_ko_braldun' => $braldun["nb_ko_braldun"],
            'date_fin_tour_braldun' => date("Y-m-d H:i:s"),
            'est_donjon_braldun' => 'non',
        );
        $where = "id_braldun = " . $braldun["id_braldun"];
        $braldunTable = new Braldun();
        $braldunTable->update($data, $where);
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseBraldunEchec - exit -");
    }

    private static function envoieMessageEchecBraldun($donjon, $equipe, $braldun, $view)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecBraldun - enter h:" . $braldun["id_braldun"]);
        $detailsBot = "[Poste de Garde]" . PHP_EOL . PHP_EOL;

        $detailsBot .= "Ahhhh ! " . PHP_EOL;
        $detailsBot .= "Vous n'avez pas réussi à venir à bout de [m" . $equipe["id_fk_monstre_donjon_equipe"] . "] en 2 lunes. " . PHP_EOL . PHP_EOL;
        $detailsBot .= "Vous êtes KO et renvoyé à l'hôpital le plus proche." . PHP_EOL . PHP_EOL;

        Bral_Util_Donjon::messageSignature($detailsBot, $donjon);
        $detailsBot = Bral_Util_Lien::remplaceBaliseParNomEtJs($detailsBot, false);

        $details = "[b" . $braldun["id_braldun"] . "] a échoué au Donjon, face à [m" . $equipe["id_fk_monstre_donjon_equipe"] . "]";

        $config = Zend_Registry::get('config');
        $idTypeEvenementCible = $config->game->evenements->type->ko;
        Bral_Util_Evenement::majEvenements($braldun["id_braldun"], $idTypeEvenementCible, $details, $detailsBot, $braldun["niveau_braldun"], "braldun", true, $view);

        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecBraldun - exit -");
    }

    private static function envoieMessageEchecBralduns($donjon, $equipe, $nomComte, $listeBralduns, $view)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecBralduns - enter -");
        $message = "[Poste de Garde]" . PHP_EOL . PHP_EOL;

        $message .= "Bonjour. " . PHP_EOL;
        $message .= "Tristre nouvelle aujourd'hui : ils ont échoué ! Oui, " . PHP_EOL;
        $message .= $listeBralduns . PHP_EOL;

        $message .= " n'ont pas réussi à vaincre [m" . $equipe["id_fk_monstre_donjon_equipe"] . "]." . PHP_EOL . PHP_EOL;
        $message .= "Il est très en colère et a envoyé un émissaire dans ";
        $message .= "chaque ville de la " . $nomComte . " !" . PHP_EOL . PHP_EOL;
        $message .= "Aux armes ! Défendons nos villes !" . PHP_EOL . PHP_EOL;

        $message = Bral_Util_Lien::remplaceBaliseParNomEtJs($message, false);

        Bral_Util_Donjon::messageSignature($message, $donjon);
        $braldunTable = new Braldun();
        $bralduns = $braldunTable->findAllJoueurs();
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecBralduns - nbJoueurs:" . count($bralduns));
        foreach ($bralduns as $h) {
            Bral_Util_Messagerie::envoiMessageAutomatique($donjon["id_fk_pnj_donjon"], $h["id_braldun"], $message, $view);
        }
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageEchecBralduns - exit -");
    }

    private static function creationEmissaires($donjon, $view)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - creationEmissaires - enter -");
        Bral_Util_Donjon::creationNids($donjon, "echec");
        Zend_Loader::loadClass("Bral_Batchs_Factory");
        Bral_Batchs_Factory::calculBatch("CreationMonstres", $view, $donjon["id_donjon"]);
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - creationEmissaires - exit -");
    }

    public static function creationNids($donjon, $type)
    {
        Zend_Loader::loadClass("Nid");
        $nidTable = new Nid();
        $where = "id_fk_donjon_nid = " . $donjon["id_donjon"];
        $nidTable->delete($where);

        Zend_Loader::loadClass("DonjonNid");
        $donjonNidTable = new DonjonNid();
        if ($type == "creation") {
            $nids = $donjonNidTable->findByIdDonjonCreation($donjon["id_donjon"]);
        } else {
            $nids = $donjonNidTable->findByIdDonjonEchec($donjon["id_donjon"]);
        }

        foreach ($nids as $n) {
            $nbMonstres = Bral_Util_De::get_de_specifique($n["nb_membres_min_type_groupe_monstre"], $n["nb_membres_max_type_groupe_monstre"]);
            $data["x_nid"] = $n["x_donjon_nid"];
            $data["y_nid"] = $n["y_donjon_nid"];
            $data["z_nid"] = $n["z_donjon_nid"];
            $data["nb_monstres_total_nid"] = $nbMonstres;
            $data["nb_monstres_restants_nid"] = $nbMonstres;

            $data["id_fk_zone_nid"] = $n["id_fk_zone_nid_donjon_nid"];
            $data["id_fk_type_monstre_nid"] = $n["id_fk_type_monstre_donjon_nid"];

            $data["id_fk_donjon_nid"] = $n["id_fk_donjon_nid"];
            $data["date_creation_nid"] = date("Y-m-d H:i:s");

            $data["date_generation_nid"] = Bral_Util_ConvertDate::get_date_add_day_to_date(date("Y-m-d H:i:s"), abs($n["z_donjon_nid"]) - 7);

            $nidTable->insert($data);
        }
    }

    public static function dropGainsEtUpdateDonjon($idDonjon, $monstre, $niveauBraldun, $effetMotD, $view)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - dropGainsEtUpdateDonjon - enter -");

        Zend_Loader::loadClass("DonjonEquipe");
        $donjonEquipeTable = new DonjonEquipe();
        $donjonEquipe = $donjonEquipeTable->findNonTermineeByIdDonjon($idDonjon);

        if (count($donjonEquipe) != 1) {
            throw new Exception('Equipe de donjon en cours != 1 idDonjon:' . $idDonjon);
        } else {
            $donjonEquipe = $donjonEquipe[0];
        }

        self::dropGains($idDonjon, $donjonEquipe, $monstre, $niveauBraldun, $effetMotD);

        $data["date_mort_monstre_donjon_equipe"] = date("Y-m-d H:i:s");
        $where = "id_donjon_equipe = " . $donjonEquipe["id_donjon_equipe"];
        $donjonEquipeTable->update($data, $where);

        Zend_Loader::loadClass("DonjonBraldun");
        $donjonBraldunTable = new DonjonBraldun();
        $donjonBraldun = $donjonBraldunTable->findByIdEquipe($donjonEquipe["id_donjon_equipe"]);

        Zend_Loader::loadClass('Donjon');
        $donjonTable = new Donjon();
        $donjon = $donjonTable->findByIdDonjon($idDonjon);
        $donjon = $donjon[0];

        foreach ($donjonBraldun as $h) {
            self::envoieMessageDelaiSortie($donjon, $h["id_braldun"], $view);
        }

        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - dropGainsEtUpdateDonjon - exit -");
        return true;
    }

    private static function envoieMessageDelaiSortie($donjon, $idBraldun, $view)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageDelaiSortie - enter -");
        $message = "[Poste de Garde]" . PHP_EOL . PHP_EOL;

        $message .= "Bravo ! " . PHP_EOL;
        $message .= "Vous avez réussi à vaincre le boss." . PHP_EOL . PHP_EOL;
        $message .= " Vous avez maintenant 3 jours pour ramasser les gains tombés à";
        $message .= " terre et vous serez ensuite automatiquement renvoyé à la capitale de la Comté." . PHP_EOL . PHP_EOL;

        Bral_Util_Donjon::messageSignature($message, $donjon);
        Bral_Util_Messagerie::envoiMessageAutomatique($donjon["id_fk_pnj_donjon"], $idBraldun, $message, $view);
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageDelaiSortie - exit -");
    }

    public static function dropGains($idDonjon, $donjonEquipe, $monstre, $niveauBraldun, $effetMotD)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - dropGains - enter -");

        Zend_Loader::loadClass("IdsEquipement");
        Zend_Loader::loadClass("Equipement");
        Zend_Loader::loadClass("ElementEquipement");
        Zend_Loader::loadClass("Bral_Util_Equipement");

        $nbBraldun = 9;
        for ($i = 1; $i < $nbBraldun; $i++) {
            // 1 rune par braldun, 1 rune a déjà été droppé dans mortMonstreDb
            Bral_Util_Rune::dropRune($monstre["x_monstre"], $monstre["y_monstre"], $monstre["z_monstre"], $monstre["niveau_monstre"], $niveauBraldun, $monstre["id_fk_type_groupe_monstre"], $effetMotD, $monstre["id_monstre"]);
        }

        Zend_Loader::loadClass("RecetteEquipement");
        $recetteEquipementTable = new RecetteEquipement();

        $equipementsRowset = $recetteEquipementTable->findByIdDonjon($idDonjon);
        if (count($equipementsRowset) != 1) {
            throw new Exception('dropGains Set de donjon en cours != 1 idDonjon:' . $idDonjon);
        } else {
            $equipement = $equipementsRowset[0];
        }

        for ($i = 1; $i <= $nbBraldun; $i++) {
            self::dropSet($equipement, $donjonEquipe, $monstre);
        }
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - dropGains - exit -");
    }

    public static function dropSet($equipement, $donjonEquipe, $monstre)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - dropSet - enter -");

        $idsEquipementTable = new IdsEquipement();
        $idEquipement = $idsEquipementTable->prepareNext();

        $equipementTable = new Equipement();
        $data = array(
            'id_equipement' => $idEquipement,
            'id_fk_recette_equipement' => $equipement["id_recette_equipement"],
            'nb_runes_equipement' => 0,
            'id_fk_region_equipement' => $donjonEquipe["id_fk_region_donjon"],
            'etat_initial_equipement' => $equipement["etat_initial_recette_equipement"],
            'etat_courant_equipement' => $equipement["etat_initial_recette_equipement"],
            'poids_equipement' => $equipement["poids_recette_equipement"],
            'armure_equipement' => $equipement["armure_recette_equipement"],
            'force_equipement' => $equipement["force_recette_equipement"],
            'agilite_equipement' => $equipement["agilite_recette_equipement"],
            'vigueur_equipement' => $equipement["vigueur_recette_equipement"],
            'sagesse_equipement' => $equipement["sagesse_recette_equipement"],
            'attaque_equipement' => $equipement["bm_attaque_recette_equipement"],
            'degat_equipement' => $equipement["bm_degat_recette_equipement"],
            'defense_equipement' => $equipement["bm_defense_recette_equipement"],
        );
        $equipementTable->insert($data);

        $dateCreation = date("Y-m-d H:i:s");
        $nbJours = Bral_Util_De::get_2d10();
        $dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

        $elementEquipementTable = new ElementEquipement();
        $data = array(
            "id_element_equipement" => $idEquipement,
            "x_element_equipement" => $monstre["x_monstre"],
            "y_element_equipement" => $monstre["y_monstre"],
            "z_element_equipement" => $monstre["z_monstre"],
            "date_fin_element_equipement" => $dateFin,
        );
        $elementEquipementTable->insert($data);

        Bral_Util_Equipement::insertEquipementBonus($idEquipement, -1, -1);

        $details = "[m" . $monstre["id_monstre"] . "] a lâché la pièce d'équipement n°" . $idEquipement;
        Bral_Util_Equipement::insertHistorique(Bral_Util_Equipement::HISTORIQUE_CREATION_ID, $idEquipement, $details);

        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - dropSet - exit -");
    }

    public static function finaliseDonjonReussi($donjon, $equipe, $view)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseDonjonReussi - enter -");

        Zend_Loader::loadClass("Bral_Util_Lien");
        Zend_Loader::loadClass("Bral_Util_Evenement");

        Zend_Loader::loadClass("Region");
        $regionTable = new Region();
        $region = $regionTable->findById($donjon["id_fk_region_donjon"]);
        $nomComte = $region["nom_region"];

        Zend_Loader::loadClass("DonjonBraldun");
        $donjonBraldunTable = new DonjonBraldun();
        $donjonBraldun = $donjonBraldunTable->findByIdEquipe($equipe["id_donjon_equipe"]);

        $listeBralduns = "";
        foreach ($donjonBraldun as $h) {
            $listeBralduns .= $h["prenom_braldun"] . " " . $h["nom_braldun"] . "(" . $h["id_braldun"] . "), ";
            self::envoieMessageReussiBraldun($donjon, $equipe, $nomComte, $h, $view);
            self::finaliseBraldunReussi($donjon, $h, $nomComte);
        }

        self::envoieMessageReussiBralduns($donjon, $equipe, $nomComte, $listeBralduns, $view);

        $donjonEquipeTable = new DonjonEquipe();
        $data = array(
            'etat_donjon_equipe' => 'termine',
        );
        $where = 'id_donjon_equipe=' . $equipe['id_donjon_equipe'];
        $donjonEquipeTable->update($data, $where);

        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseDonjonReussi - exit -");
    }

    private static function finaliseBraldunReussi($donjon, $braldun, $nomComte)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseBraldunReussi - enter h:" . $braldun["id_braldun"]);

        Zend_Loader::loadClass("TypeLieu");
        $lieuTable = new Lieu();
        $lieuxRowset = $lieuTable->findByTypeAndRegion(TypeLieu::ID_TYPE_MAIRIE, $donjon["id_fk_region_donjon"], "non", "oui");
        $lieu = $lieuxRowset[0];

        $data = array(
            'est_donjon_braldun' => 'non',
            'x_braldun' => $lieu["x_lieu"],
            'y_braldun' => $lieu["y_lieu"],
            'z_braldun' => $lieu["z_lieu"],
        );
        $where = "id_braldun = " . $braldun["id_braldun"];
        $braldunTable = new Braldun();
        $braldunTable->update($data, $where);

        Zend_Loader::loadClass("Bral_Util_Distinction");
        $texte = "Écumeur du donjon de la " . $nomComte;
        Bral_Util_Log::quete()->trace("Braldun " . $braldun["id_braldun"] . " - Bral_Util_Donjon::finaliseBraldunReussi - Ajout d'une distinction : " . $texte);
        Bral_Util_Distinction::ajouterDistinction($braldun["id_braldun"], $donjon["id_fk_distinction_donjon"], $texte);

        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - finaliseBraldunReussi - exit -");
    }

    private static function envoieMessageReussiBraldun($donjon, $equipe, $nomComte, $braldun, $view)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageReussiBraldun - enter h:" . $braldun["id_braldun"]);
        $detailsBot = "[Poste de Garde]" . PHP_EOL . PHP_EOL;

        $detailsBot .= "Féliciations ! " . PHP_EOL;
        $detailsBot .= "Vous avez réussi à venir à bout de [m" . $equipe["id_fk_monstre_donjon_equipe"] . "] en 2 lunes. " . PHP_EOL . PHP_EOL;
        $detailsBot .= "Vous êtes victorieux envoyé à la mairie de la capital de la " . $nomComte . "." . PHP_EOL . PHP_EOL;

        Bral_Util_Donjon::messageSignature($detailsBot, $donjon);
        $detailsBot = Bral_Util_Lien::remplaceBaliseParNomEtJs($detailsBot, false);

        $details = "[b" . $braldun["id_braldun"] . "] sort victorieux du Donjon face à [m" . $equipe["id_fk_monstre_donjon_equipe"] . "]";

        $config = Zend_Registry::get('config');
        $idTypeEvenementCible = $config->game->evenements->type->special;
        Bral_Util_Evenement::majEvenements($braldun["id_braldun"], $idTypeEvenementCible, $details, $detailsBot, $braldun["niveau_braldun"], "braldun", true, $view);

        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageReussiBraldun - exit -");
    }

    private static function envoieMessageReussiBralduns($donjon, $equipe, $nomComte, $listeBralduns, $view)
    {
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageReussiBralduns - enter -");

        $message = "[Poste de Garde]" . PHP_EOL . PHP_EOL;

        $message .= "Bonjour. " . PHP_EOL;
        $message .= "Ils l'ont fait ! Qui sont ces héros qui sont allés mettre une bonne rouste à [m" . $equipe["id_fk_monstre_donjon_equipe"] . "] ?" . PHP_EOL;
        $message .= "Les voilà : " . $listeBralduns . PHP_EOL;

        $message .= " revenant chargés de trésors et de gloire ! " . PHP_EOL . PHP_EOL;
        $message .= "Sortez les tonneaux, la bière va couler à flot ce soir dans notre belle " . $nomComte . "." . PHP_EOL . PHP_EOL;

        $message = Bral_Util_Lien::remplaceBaliseParNomEtJs($message, false);

        Bral_Util_Donjon::messageSignature($message, $donjon);
        $braldunTable = new Braldun();
        $bralduns = $braldunTable->findAllJoueurs();
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageReussiBralduns - nbJoueurs:" . count($bralduns));
        foreach ($bralduns as $h) {
            Bral_Util_Messagerie::envoiMessageAutomatique($donjon["id_fk_pnj_donjon"], $h["id_braldun"], $message, $view);
        }
        Bral_Util_Log::batchs()->trace("Bral_Util_Donjon - envoieMessageReussiBralduns - exit -");
    }
}
