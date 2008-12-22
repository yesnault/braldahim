<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Xml_Entry {

  public function set_type($p) {
    $this->type = $p;
  }

  public function get_type() {
    return $this->type;
  }

  public function set_valeur($p) {
    $this->valeur = $p;
  }

  public function get_valeur() {
    return $this->valeur;
  }

  public function set_data($p) {
    $this->data = $p;
  }

  public function get_data() {
    return $this->data;
  }

  public function echo_xml() {
    echo "<type>".$this->type."</type>\n";
    echo "<valeur>".$this->valeur."</valeur>\n";
    echo "<data>";
    echo "<![CDATA[";
   	echo  $this->data;
    echo "]]>";
    echo "</data>\n";
  }

  public function __construct() {
  }
}
