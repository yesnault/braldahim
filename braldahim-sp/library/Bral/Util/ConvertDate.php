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
class Bral_Util_ConvertDate
{

	private function __construct()
	{
	}

	/* Convertit un date mysql vers une date php
	 * @return date
	 */
	public static function get_date_mysql_datetime($pattern, $pdate)
	{
		$mdate = explode("-", $pdate);
		return date($pattern, mktime(0, 0, 0, $mdate[1], $mdate[2], $mdate[0]));
	}

	/* Convertit un datetime mysql vers une date php
	 * @return date
	 */
	public static function get_datetime_mysql_datetime($pattern, $pdatetime)
	{
		$break = explode(" ", $pdatetime);
		$datebreak = explode("-", $break[0]);
		$time = explode(":", $break[1]);
		return date($pattern, mktime($time[0], $time[1], $time[2], $datebreak[1], $datebreak[2], $datebreak[0]));
	}

	/* Convertit un datetime mysql vers un timestamp
	 * @return timestamp
	 */
	public static function get_epoch_mysql_datetime($date)
	{
		$break = explode(" ", $date);
		$datebreak = explode("-", $break[0]);
		$time = explode(":", $break[1]);
		$epoch = date("U", mktime($time[0], $time[1], $time[2], $datebreak[1], $datebreak[2], $datebreak[0]));
		return $epoch;
	}

	/* Ajoute une heure (H:m:s) a un datetime mysql et
	 * retourne le resultat en timestamp
	 * @return timestamp
	 */
	public static function get_epoch_add_time_to_date($date, $add_time)
	{
		$break = explode(" ", $date);
		$datebreak = explode("-", $break[0]);
		$time = explode(":", $break[1]);
		$add_time = explode(":", $add_time);
		$epoch = date("U", mktime($time[0] + $add_time[0], $time[1] + $add_time[1], $time[2] + $add_time[2],
				$datebreak[1], $datebreak[2], $datebreak[0]));

		return $epoch;
	}

	/* Soustrait une heure (H:m:s) a un datetime mysql et
	 * retourne le resultat en timestamp
	 * @return timestamp
	 */
	public static function get_epoch_remove_time_to_date($date, $rem_time)
	{
		$break = explode(" ", $date);
		$datebreak = explode("-", $break[0]);
		$time = explode(":", $break[1]);
		$rem_time = explode(":", $rem_time);
		$epoch = date("U", mktime($time[0] - $rem_time[0], $time[1] - $rem_time[1], $time[2] - $rem_time[2],
				$datebreak[1], $datebreak[2], $datebreak[0]));

		return $epoch;
	}

	/* Ajoute une date (Y-m-d H:m:s) a un datetime mysql et
	 * retourne le resultat en timestamp
	 * @return timestamp
	 */
	public static function get_epoch_add_date_to_date($date, $add_date)
	{
		$break = explode(" ", $date);
		$datebreak = explode("-", $break[0]);
		$time = explode(":", $break[1]);

		$add_break = explode(" ", $add_date);
		$add_datebreak = explode("-", $add_break[0]);
		$add_time = explode(":", $add_break[1]);

		$epoch = date("U", mktime($time[0] + $add_time[0], $time[1] + $add_time[1], $time[2] + $add_time[2],
				$datebreak[1] + $add_datebreak[1], $datebreak[2] + $add_datebreak[2], $datebreak[0] + $add_datebreak[0]));

		return $epoch;
	}

	/* Ajoute un/des jours a un datetime mysql et
	 * retourne le resultat en timestamp
	 * @return timestamp
	 */
	public static function get_epoch_add_day_to_date($date, $add_day)
	{
		$break = explode(" ", $date);
		$datebreak = explode("-", $break[0]);
		$time = explode(":", $break[1]);

		$epoch = date("U", mktime($time[0], $time[1], $time[2],
				$datebreak[1], $datebreak[2] + $add_day, $datebreak[0]));

		return $epoch;
	}

	/* Ajoute une heure (H:m:s) a un datetime mysql et
	 * retourne le resultat en date
	 * @return date
	 */
	public static function get_date_add_time_to_date($date, $add_time)
	{
		return date("Y-m-d H:i:s", self::get_epoch_add_time_to_date($date, $add_time));
	}

	/* Soustrait une heure (H:m:s) a un datetime mysql et
	 * retourne le resultat en date
	 * @return date
	 */
	public static function get_date_remove_time_to_date($date, $rem_time)
	{
		return date("Y-m-d H:i:s", self::get_epoch_remove_time_to_date($date, $rem_time));
	}

	/* Ajoute une date (Y-m-d H:m:s) a un datetime mysql et
	 * retourne le resultat en date
	 * @return date
	 */
	public static function get_date_add_date_to_date($date, $add_time)
	{
		return date("Y-m-d H:i:s", self::get_epoch_add_date_to_date($date, $add_time));
	}

	/* Ajoute un/des jours a un datetime mysql et
	 * retourne le resultat en date & time "Y-m-d H:i:s"
	 * @return date
	 */
	public static function get_date_add_day_to_date($date, $add_day)
	{
		return date("Y-m-d H:i:s", self::get_epoch_add_day_to_date($date, $add_day));
	}

	public static function get_divise_time_to_time($time, $div)
	{
		$time = explode(":", $time);

		$h = $time[0] * 3600;
		$m = $time[1] * 60;
		$s = $time[2] * 1;
		$n = $h + $m + $s;
		$n2 = $n / $div;
		$h2 = intval($n2 / 3600);
		$restant = $n2 - ($h2 * 3600);
		$m2 = intval($restant / 60);
		$restant = $restant - ($m2 * 60);
		$s2 = intval($restant);
		$r = $h2 . ":" . $m2 . ":" . $s2;
		return $r;
	}

	public static function get_time_from_minutes($minutes)
	{
		$h = floor($minutes / 60);
		$m = ($minutes - $h * 60);
		$s = 0;
		$r = $h . ":" . $m . ":" . $s;
		return $r;
	}

	/* Soustrait une heure (H:m:s) a un time mysql et
	 * retourne le resultat en time
	 * @return timestamp
	 */
	public static function get_time_remove_time_to_time($time, $add_time)
	{
		$time = explode(":", $time);
		$add_time = explode(":", $add_time);

		$h = $time[0] * 3600;
		$m = $time[1] * 60;
		$s = $time[2] * 1;
		$stime = $h + $m + $s;

		$h = $add_time[0] * 3600;
		$m = $add_time[1] * 60;
		$s = $add_time[2] * 1;
		$sadd_time = $h + $m + $s;

		$seconds = $stime - $sadd_time;

		$h2 = intval($seconds / 3600);
		$restant = $seconds - ($h2 * 3600);
		$m2 = intval($restant / 60);
		$restant = $restant - ($m2 * 60);
		$s2 = intval($restant);
		$r = $h2 . ":" . $m2 . ":" . $s2;

		return $r;
	}

	/* Ajoute une heure (H:m:s) a un time mysql et
	 * retourne le resultat en time
	 * @return timestamp
	 */
	public static function get_time_add_time_to_time($time, $add_time)
	{
		$time = explode(":", $time);
		$add_time = explode(":", $add_time);

		$h = $time[0] * 3600;
		$m = $time[1] * 60;
		$s = $time[2] * 1;
		$stime = $h + $m + $s;

		$h = $add_time[0] * 3600;
		$m = $add_time[1] * 60;
		$s = $add_time[2] * 1;
		$sadd_time = $h + $m + $s;

		$seconds = $stime + $sadd_time;

		$h2 = intval($seconds / 3600);
		$restant = $seconds - ($h2 * 3600);
		$m2 = intval($restant / 60);
		$restant = $restant - ($m2 * 60);
		$s2 = intval($restant);
		$r = $h2 . ":" . $m2 . ":" . $s2;

		return $r;
	}

	/*
	 * Retourne le temps en minutes d'une heure donnée (H:m:s)
	 */
	public static function getMinuteFromHeure($time)
	{
		$time = explode(":", $time);

		$h = $time[0] * 60;
		$m = $time[1] * 1;
		//$s = $time[2] * 1;
		$minutes = $h + $m;

		return $minutes;
	}

	/*
	 * Converti un temps un minute en heure H:m:s .
	 */
	public static function getHeureFromMinute($minutes)
	{
		$heures = floor($minutes / 60);
		$min = $minutes - $heures * 60;

		if ($heures == 0) {
			$heures = "00";
		}

		if ($min == 0) {
			$min = "00";
		}

		if ($min != "00" && $min != 0 && floor($min / 10) == 0) {
			$min = "0" . $min;
		}

		return $heures . ":" . $min . ":00";
	}

}
