<?php

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

  public function get_xml() {
    $r = "<type>".$this->type."</type>\n";
    $r .= "<valeur>".$this->valeur."</valeur>\n";
    $r .= "<data>";
    $r .= "<![CDATA[";
    $r .=  $this->data;
    $r .= "]]>";
    $r .= "</data>\n";
    return $r;
  }

  public function __construct() {

  }
}
