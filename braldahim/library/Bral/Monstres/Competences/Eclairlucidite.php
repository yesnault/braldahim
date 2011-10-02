<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Monstres_Competences_Eclairlucidite extends Bral_Monstres_Competences_Attaque
{

    public function calculJetAttaque()
    {
    }

    public function calculDegat($estCritique)
    {
    }

    public function actionSpecifique()
    {
        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - actionSpecifique - enter");

        Zend_Loader::loadClass("Bral_Util_Effets");

        $bonus = floor($this->monstre["niveau_monstre"] / 2) - 3 + Bral_Util_De::get_1d6();
        if ($bonus < 0) {
            $bonus = 1;
        }
        $nbTours = Bral_Util_De::get_1d3();
        Bral_Util_Effets::ajouteEtAppliqueEffetMonstre($this->monstre, Bral_Util_Effets::CARACT_SAGESSE, Bral_Util_Effets::TYPE_BONUS, $nbTours, $bonus);
        $this->majEvenement($bonus, $nbTours);

        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - actionSpecifique - exit");
        return null;
    }

    private function majEvenement($bonus, $nbTours)
    {
        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - enter");
        $idTypeEvenement = self::$config->game->evenements->type->attaquer;
        $details = "[m" . $this->monstre["id_monstre"] . "]";

        $tab[] = " se secoue la tête et semble retrouver ses idées.";
        $tab[] = " est traversé par un éclair de lucidité.";
        $tab[] = " se replace correctement et analyse la situation.";
        $tab[] = " cherche un Braldûn à charger.";

        $details .= $tab[Bral_Util_De::get_de_specifique(0, count($tab) - 1)];
        Bral_Util_Evenement::majEvenementsFromVieMonstre(null, $this->monstre["id_monstre"], $idTypeEvenement, $details, "", $this->monstre["niveau_monstre"], $this->view);
        Bral_Util_Log::viemonstres()->trace(get_class($this) . "  - majEvenement - exit");
    }
}