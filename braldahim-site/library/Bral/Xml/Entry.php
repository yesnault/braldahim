<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: Entry.php 805 2008-12-22 20:17:56Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-12-22 21:17:56 +0100 (Mon, 22 Dec 2008) $
 * $LastChangedRevision: 805 $
 * $LastChangedBy: yvonnickesnault $
 */
class Bral_Xml_Entry {
	
  private $box = null;
  
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
  	$box = null;
    $this->data = $p;
  }

  public function get_data() {
    return $this->data;
  }
  
  public function set_box($box) {
    $this->box = $box;
  }

  public function echo_xml() {
  	
	    echo "<type>".$this->type."</type>\n";
	    echo "<valeur>".$this->valeur."</valeur>\n";
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

  public function __construct() {
  }
}
