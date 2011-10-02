<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Butin
{

    function __construct()
    {
    }

    public static function nouveau($idBraldun, $x, $y, $z)
    {
        Zend_Loader::loadClass("Bral_Util_Attaque");
        $estRegionPvp = Bral_Util_Attaque::estRegionPvp($x, $y);

        if ($estRegionPvp) {
            return null; // pas de butin en region pvp
        }

        Zend_Loader::loadClass("Butin");
        $butinTable = new Butin();
        $data["id_fk_braldun_butin"] = $idBraldun;
        $data["date_butin"] = date("Y-m-d H:i:s");
        $data["x_butin"] = $x;
        $data["y_butin"] = $y;
        $data["z_butin"] = $z;
        $idButin = $butinTable->insert($data);
        return $idButin;
    }
}