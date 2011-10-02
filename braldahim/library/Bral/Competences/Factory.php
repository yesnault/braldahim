<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Factory
{

    static function getAction($request, $view)
    {
        Zend_Loader::loadClass("Bral_Competences_Competence");
        Zend_Loader::loadClass("Bral_Echoppes_Echoppe");
        Zend_Loader::loadClass("Bral_Competences_Boutiquer");
        Zend_Loader::loadClass("Bral_Competences_Produire");

        $matches = null;
        preg_match('/(.*)_competence_(.*)/', $request->get("caction"), $matches);
        $action = $matches[1]; // "do" ou "ask"
        $nomSystemeCompetence = $matches[2];
        $construct = null;
        $braldunCompetence = null;

        // On regarde si c'est une competence basique
        $competencesBasiques = Bral_Util_Registre::get('competencesBasiques');
        foreach ($competencesBasiques as $c) {
            if ($c["nom_systeme"] == $nomSystemeCompetence) {
                $construct = "Bral_Competences_" . Bral_Util_String::firstToUpper($nomSystemeCompetence);
                $competence = $c;
                break;
            }
        }

        if ($view->user->activation == false) {
            throw new Zend_Exception("Tour non activé");
        }

        // On regarde si c'est une competence de soule
        if ($construct == null) {
            $competencesSoule = Bral_Util_Registre::get('competencesSoule');
            foreach ($competencesSoule as $c) {
                if ($c["nom_systeme"] == $nomSystemeCompetence) {
                    $construct = "Bral_Competences_" . Bral_Util_String::firstToUpper($nomSystemeCompetence);
                    $competence = $c;
                    break;
                }
            }
        }

        // verification que le joueur a accès à la compétence
        if ($construct == null) {
            Zend_Loader::loadClass("BraldunsCompetences");
            $braldunsCompetencesTables = new BraldunsCompetences();
            $braldunCompetences = $braldunsCompetencesTables->findByIdBraldun($view->user->id_braldun);

            $competences = Bral_Util_Registre::get('competences');

            foreach ($braldunCompetences as $c) {
                if ($c["nom_systeme_competence"] == $nomSystemeCompetence) {
                    $construct = "Bral_Competences_" . Bral_Util_String::firstToUpper($nomSystemeCompetence);
                    $competence = $competences[$c["id_competence"]];
                    $braldunCompetence = $c;
                    break;
                }
            }
        }

        // verification que la classe de la competence existe.
        try {
            Zend_Loader::loadClass($construct);
        } catch (Exception $e) {
            throw new Zend_Exception("Comp&eacute;tence invalide (classe): " . $nomSystemeCompetence);
        }

        if (($construct != null) && (class_exists($construct))) {
            Zend_Loader::loadClass($construct);
            return new $construct ($competence, $braldunCompetence, $request, $view, $action);
        } else {
            throw new Zend_Exception("Comp&eacute;tence invalide: " . $nomSystemeCompetence);
        }
    }
}