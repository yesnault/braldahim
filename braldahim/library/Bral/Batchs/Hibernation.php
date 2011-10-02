<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_Hibernation extends Bral_Batchs_Batch
{

    public function calculBatchImpl()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_Hibernation - calculBatchImpl - enter -");

        $aujourdhui = date("Y-m-d 0:0:0");

        $braldunTable = new Braldun();

        $where = 'date_fin_hibernation_braldun >= \'' . $aujourdhui . '\'';
        $data = array(
            'est_en_hibernation_braldun' => 'oui',
        );
        $nbEntres = $braldunTable->update($data, $where);

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Hibernation - nbHibernationEntres:" . $nbEntres . " - nbHibernationSortis:" . $nbSortis);

        Bral_Util_Log::batchs()->trace("Bral_Batchs_Hibernation - exit -");
        return "nbHibernationEntres:" . $nbEntres . " nbHibernationSortis:" . $nbSortis;
    }
}