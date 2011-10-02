<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Xml_Entry
{

    private $box = null;

    public function set_type($p)
    {
        $this->type = $p;
    }

    public function get_type()
    {
        return $this->type;
    }

    public function set_valeur($p)
    {
        $this->valeur = $p;
    }

    public function get_valeur()
    {
        return $this->valeur;
    }

    public function set_data($p)
    {
        $box = null;
        $this->data = $p;
    }

    public function get_data()
    {
        return $this->data;
    }

    public function set_box($box)
    {
        $this->box = $box;
    }

    public function echo_xml()
    {
        echo "<type>" . $this->type . "</type>\n";
        echo "<valeur>" . $this->valeur . "</valeur>\n";
        echo "<data>";
        echo "<![CDATA[";
        if ($this->box == null) {
            echo $this->data;
        } else {
            echo $this->box->render();
        }
        echo "]]>";
        echo "</data>\n";
    }

    public function __construct()
    {
    }
}
