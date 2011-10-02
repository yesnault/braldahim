<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Creusetunnel extends Bral_Monstres_Competences_Deplacement
{

    public function actionSpecifique()
    {
        Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Creusetunnel - enter - (idm:" . $this->monstre["id_monstre"] . ")");

        // Si cible en cours, pas de creuseTunnel
        if ($this->monstre["id_fk_braldun_cible_monstre"] != null) {
            Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Creusetunnel - (idm:" . $this->monstre["id_monstre"] . ") cible en cours. Passage sur deplacement normal");

            $typeMonstreMCompetence = new TypeMonstreMCompetence();
            $competences = $typeMonstreMCompetence->findDeplacementByIdMCompetence(MCompetence::ID_DEPLACEMENT_SOLITAIRE);

            if (count($competences) != 1) {
                throw new Zend_Exception(get_class($this) . "Erreur, parametrage MCompetence::ID_DEPLACEMENT_SOLITAIRE");
            }
            $actionDeplacement = Bral_Monstres_Competences_Factory::getAction($competences[0], $this->monstre, null, $this->view);
            $actionDeplacement->action();
            Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Creusetunnel - exit (idm:" . $this->monstre["id_monstre"] . ")");
            return;
        }

        $rayon_max = 20;
        Zend_Loader::loadClass('Filon');
        $filonTable = new Filon();
        $filonRow = $filonTable->findLePlusProche($this->monstre["x_monstre"], $this->monstre["y_monstre"], $this->monstre["z_monstre"], $rayon_max);

        if ($filonRow != null && count($filonRow) > 0) {
            if ($filonRow["x_filon"] == $this->monstre["x_monstre"] &&
                    $filonRow["y_filon"] == $this->monstre["y_monstre"]
            ) {
                Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Creusetunnel - deja sur le filon - (idm:" . $this->monstre["id_monstre"] . ")");
            } else {
                Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Creusetunnel - deplacement vers le filon - (idm:" . $this->monstre["id_monstre"] . ") xFilon:" . $filonRow["x_filon"] . " yFilon:" . $filonRow["y_filon"] . " zFilon:" . $filonRow["z_filon"]);

                if ($filonRow["x_filon"] < $this->monstre["x_monstre"]) {
                    $this->monstre["x_direction_monstre"] = $this->monstre["x_direction_monstre"] - 1;
                } elseif ($filonRow["x_filon"] > $this->monstre["x_monstre"]) {
                    $this->monstre["x_direction_monstre"] = $this->monstre["x_direction_monstre"] + 1;
                }

                if ($filonRow["y_filon"] < $this->monstre["y_monstre"]) {
                    $this->monstre["y_direction_monstre"] = $this->monstre["y_direction_monstre"] - 1;
                } elseif ($filonRow["y_filon"] > $this->monstre["y_monstre"]) {
                    $this->monstre["y_direction_monstre"] = $this->monstre["y_direction_monstre"] + 1;
                }

                Zend_Loader::loadClass("Tunnel");
                $tunnelTable = new Tunnel();
                $tunnels = $tunnelTable->findByCase($this->monstre["y_direction_monstre"], $this->monstre["y_direction_monstre"], $this->monstre["z_monstre"]);

                if ($tunnels == null && count($tunnels) == 0) {
                    // S'il y a une mine non creusée, il faut le creuser
                    Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Creusetunnel - tunnel a creuser - (idm:" . $this->monstre["id_monstre"] . ")");

                    $data = array(
                        "x_tunnel" => $this->monstre["x_direction_monstre"],
                        "y_tunnel" => $this->monstre["y_direction_monstre"],
                        "z_tunnel" => $this->monstre["z_monstre"],
                        "date_tunnel" => date("Y-m-d H:00:00"),
                        "est_eboulable_tunnel" => 'oui',
                    );

                    $tunnelTable = new Tunnel();
                    $tunnelTable->insert($data);
                    $this->majEvenement();
                } else {
                    Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Creusetunnel - pas de tunnel a creuser - (idm:" . $this->monstre["id_monstre"] . ")");
                }

                $vieMonstre = Bral_Monstres_VieMonstre::getInstance();
                $vieMonstre->setMonstre($this->monstre);
                $vieMonstre->deplacementMonstre($this->monstre["x_direction_monstre"], $this->monstre["y_direction_monstre"]);
            }
        } else {
            Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Creusetunnel - aucun filon trouve - (idm:" . $this->monstre["id_monstre"] . ")");
        }

        Bral_Util_Log::viemonstres()->trace(get_class($this) . " - Creusetunnel - exit - (idm:" . $this->monstre["id_monstre"] . ")");
    }

    private function majEvenement()
    {
        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - enter - (idm:" . $this->monstre["id_monstre"] . ")");
        $idTypeEvenement = self::$config->game->evenements->type->competence;
        $details = "[m" . $this->monstre["id_monstre"] . "] a creusé un tunnel";
        $detailsBot = $details;
        Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $idTypeEvenement, $details, $detailsBot, $this->monstre["niveau_monstre"], $this->view);
        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - exit - (idm:" . $this->monstre["id_monstre"] . ")");
    }

}