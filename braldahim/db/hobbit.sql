-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 08 Mars 2008 à 23:50
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `hobbit`
-- 

CREATE TABLE `hobbit` (
  `id_hobbit` int(11) NOT NULL auto_increment,
  `sysgroupe_hobbit` varchar(10) default NULL,
  `nom_hobbit` varchar(20) NOT NULL,
  `prenom_hobbit` varchar(22) NOT NULL,
  `id_fk_nom_initial_hobbit` int(11) NOT NULL,
  `password_hobbit` varchar(50) NOT NULL,
  `email_hobbit` varchar(100) NOT NULL,
  `etat_hobbit` int(11) NOT NULL,
  `sexe_hobbit` enum('feminin','masculin') NOT NULL,
  `x_hobbit` int(11) NOT NULL,
  `y_hobbit` int(1) NOT NULL,
  `date_debut_tour_hobbit` datetime NOT NULL,
  `date_fin_tour_hobbit` datetime NOT NULL,
  `duree_prochain_tour_hobbit` time NOT NULL,
  `duree_base_tour_hobbit` time NOT NULL,
  `duree_courant_tour_hobbit` time NOT NULL,
  `tour_position_hobbit` int(11) NOT NULL,
  `pa_hobbit` int(11) NOT NULL,
  `vue_bm_hobbit` int(11) NOT NULL,
  `force_base_hobbit` int(11) NOT NULL,
  `force_bm_hobbit` int(11) NOT NULL,
  `agilite_base_hobbit` int(11) NOT NULL,
  `agilite_bm_hobbit` int(11) NOT NULL,
  `sagesse_base_hobbit` int(11) NOT NULL,
  `sagesse_bm_hobbit` int(11) NOT NULL,
  `vigueur_base_hobbit` int(11) NOT NULL,
  `vigueur_bm_hobbit` int(11) NOT NULL,
  `regeneration_hobbit` int(11) NOT NULL,
  `px_perso_hobbit` int(11) NOT NULL default '0',
  `px_commun_hobbit` int(11) NOT NULL,
  `px_base_niveau_hobbit` int(11) NOT NULL default '0',
  `pi_hobbit` int(11) NOT NULL default '0',
  `niveau_hobbit` int(11) NOT NULL default '0',
  `balance_faim_hobbit` int(11) NOT NULL,
  `armure_naturelle_hobbit` int(11) NOT NULL,
  `armure_equipement_hobbit` int(11) NOT NULL,
  `poids_transportable_hobbit` int(11) NOT NULL,
  `castars_hobbit` int(11) NOT NULL,
  `pv_restant_hobbit` int(11) NOT NULL,
  `est_mort_hobbit` enum('oui','non') NOT NULL default 'non',
  `nb_mort_hobbit` int(11) NOT NULL default '0',
  `nb_kill_hobbit` int(11) NOT NULL default '0',
  `est_compte_actif_hobbit` enum('oui','non') NOT NULL default 'non',
  `date_creation_hobbit` datetime NOT NULL,
  `id_fk_mere_hobbit` int(11) default NULL,
  `id_fk_pere_hobbit` int(11) default NULL,
  PRIMARY KEY  (`id_hobbit`),
  UNIQUE KEY `email_hobbit` (`email_hobbit`),
  KEY `idx_x_hobbit_y_hobbit` (`x_hobbit`,`y_hobbit`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tables des Hobbits' AUTO_INCREMENT=31 ;
