<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Evenement
{

    const ATTAQUE_REUSSIE = 'attaque_reussie';
    const ATTAQUE_ESQUIVEE = 'attaque_esquivee';
    const RIPOSTE = 'riposte';

    /*
      * Mise a jour des Evenements du braldun / du monstre.
      */
    public static function majEvenements($idConcerne, $idTypeEvenement, $details, $detailsBot, $niveau, $type = "braldun", $estAEnvoyer = false, $view = null, $idMatchSoule = null, $actionEvenement = null, $tour = null)
    {
        Zend_Loader::loadClass('Evenement');
        Zend_Loader::loadClass("Bral_Util_Lien");

        $evenementTable = new Evenement();

        $detailsTransforme = Bral_Util_Lien::remplaceBaliseParNomEtJs($details);

        if ($type == "braldun") {
            $data = array(
                'id_fk_braldun_evenement' => $idConcerne,
                'date_evenement' => date("Y-m-d H:i:s"),
                'id_fk_type_evenement' => $idTypeEvenement,
                'details_evenement' => $detailsTransforme,
                'details_bot_evenement' => $detailsBot,
                'niveau_evenement' => $niveau,
                'id_fk_soule_match_evenement' => $idMatchSoule,
                'action_evenement' => $actionEvenement,
                'tour_braldun_evenement' => $tour,
            );
        } else {
            $data = array(
                'id_fk_monstre_evenement' => $idConcerne,
                'date_evenement' => date("Y-m-d H:i:s"),
                'id_fk_type_evenement' => $idTypeEvenement,
                'details_evenement' => $detailsTransforme,
                'niveau_evenement' => $niveau,
                'action_evenement' => $actionEvenement,
                'tour_monstre_evenement' => $tour,
            );
        }
        $evenementTable->insert($data);

        if ($type == "braldun" && $estAEnvoyer == true && $view != null) {
            self::envoiMail($idConcerne, Bral_Util_Lien::remplaceBaliseParNomEtJs($details, false), $detailsBot, $view);
        }
    }

    public static function majEvenementsFromVieMonstre($idBraldunConcerne, $idMonstreConcerne, $idTypeEvenement, $details, $detailsBot, $niveau, $view, $numTourBraldun = null, $numTourMonstre = null, $actionEvenement = null)
    {
        Zend_Loader::loadClass('Evenement');
        Zend_loader::loadClass("Bral_Util_Lien");
        $evenementTable = new Evenement();

        $detailsTransforme = Bral_Util_Lien::remplaceBaliseParNomEtJs($details);

        $data = array(
            'id_fk_braldun_evenement' => $idBraldunConcerne,
            'id_fk_monstre_evenement' => $idMonstreConcerne,
            'date_evenement' => date("Y-m-d H:i:s"),
            'id_fk_type_evenement' => $idTypeEvenement,
            'details_evenement' => $detailsTransforme,
            'details_bot_evenement' => $detailsBot,
            'niveau_evenement' => $niveau,
            'tour_braldun_evenement' => $numTourBraldun,
            'tour_monstre_evenement' => $numTourMonstre,
            'action_evenement' => $actionEvenement,
        );
        $evenementTable->insert($data);

        if ($idBraldunConcerne != null) {
            self::envoiMail($idBraldunConcerne, Bral_Util_Lien::remplaceBaliseParNomEtJs($details, false), $detailsBot, $view);
        }
    }

    private static function envoiMail($idBraldunConcerne, $titre, $detailsBot, $view)
    {
        Zend_Loader::loadClass('Bral_Util_Mail');
        $braldunTable = new Braldun();
        $braldunRowset = $braldunTable->findById($idBraldunConcerne);

        if ($braldunRowset != null) {
            $braldun = $braldunRowset->toArray();
            $c = Zend_Registry::get('config');
            if ($braldun["envoi_mail_evenement_braldun"] == "oui") {
                Bral_Util_Mail::envoiMailAutomatique($braldun, $c->mail->evenement->titre . " : " . $titre, $detailsBot, $view);
            }
        } else {
            throw new Zend_Exception('Bral_Util_Evenement::envoiMail id Braldun inconnu:' . $idBraldunConcerne);
        }
    }
}
