<?php

class Bral_Util_ConvertDate {
  
  /* Convertit un datetime mysql vers un timestamp
   * @return timestamp
   */
  function get_epoch_mysql_datetime($date) {
    $break = explode(" ", $date);
    $datebreak = explode("-", $break[0]);
    $time = explode(":", $break[1]);
    $epoch = date("U", mktime($time[0],$time[1],$time[2],$datebreak[1],$datebreak[2],$datebreak[0]));
    return $epoch;
  }
  
  /* Ajoute une heure (H:m:s) a un datetime mysql et
   * retourne le resultat en timestamp
   * @return timestamp
   */
  function get_epoch_add_time_to_date($date, $add_time) {
    $break = explode(" ", $date);
    $datebreak = explode("-", $break[0]);
    $time = explode(":", $break[1]);
    $add_time = explode(":", $add_time);
    $epoch = date("U", mktime($time[0]+$add_time[0],$time[1]+$add_time[1],$time[2]+$add_time[2],
			      $datebreak[1],$datebreak[2],$datebreak[0]));
    
    return $epoch;
  }

  /* Ajoute une heure (H:m:s) a un datetime mysql et 
   * retourne le resultat en date
   * @return date
   */
  function get_date_add_time_to_date($date, $add_time) {
    return date("Y-m-d H:i:s", $this->get_epoch_add_time_to_date($date, $add_time));
  }

  public function __construct() {
    
  }
}
