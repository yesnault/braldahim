<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Carnet_Factory
{
    static function getAction($request, $view)
    {
        Zend_Loader::loadClass("Bral_Carnet_Carnet");
        Zend_Loader::loadClass("Bral_Carnet_Voir");

        $matches = null;
        preg_match('/(.*)_carnet_(.*)/', $request->get("caction"), $matches);
        $action = $matches[1]; // "do" ou "ask"
        $nomSystemeCarnet = $matches[2];
        $construct = null;

        $construct = "Bral_Carnet_" . Bral_Util_String::firstToUpper($nomSystemeCarnet);
        try {
            Zend_Loader::loadClass($construct);
        } catch (Exception $e) {
            throw new Zend_Exception("Bral_Carnet_Factory construct invalide (classe): " . $nomSystemeCarnet);
        }

        $construct = "Bral_Carnet_" . $nomSystemeCarnet;
        // verification que la classe du lieu existe.
        if (($construct != null) && (class_exists($construct))) {
            return new $construct ($nomSystemeCarnet, $request, $view, $action);
        } else {
            throw new Zend_Exception("Bral_Carnet_Factory Action invalide: " . $nomSystemeCarnet);
        }
    }
}