<?php 

class Bral_Xml_Response {

  /* add an new entry in the list */
  public function add_entry($p) {
    $this->list[] = $p;
  }

  public function get_list() {
    return $this->list;
  }

  public function get_xml() {
    $r =  '<?xml version="1.0" encoding="utf-8" ?>';
    $r .= "<root>\n";

    foreach ($this->list as $e) {
      $r .= "<entrie>\n";
      $r .= $e->get_xml();
      $r .= "</entrie>\n";
    }
    $r .= "</root>\n";
    return $r;
  }

  public function __construct() {

  }
}
