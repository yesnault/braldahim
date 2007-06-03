-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Lundi 28 Mai 2007 à 00:09
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `hobbit`
-- 

DROP TABLE IF EXISTS `hobbit`;
CREATE TABLE `hobbit` (
  `id_hobbit` int(11) NOT NULL auto_increment,
  `nom_hobbit` varchar(20) NOT NULL,
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
  `px_commun_hobbit` int(11) NOT NULL default '0',
  `pi_hobbit` int(11) NOT NULL default '0',
  `balance_faim_hobbit` int(11) NOT NULL,
  `armure_naturelle_hobbit` int(11) NOT NULL,
  `armure_equipement_hobbit` int(11) NOT NULL,
  `poids_transportable_hobbit` int(11) NOT NULL,
  `castars_hobbit` int(11) NOT NULL,
  `pv_max_hobbit` int(11) NOT NULL,
  `pv_restant_hobbit` int(11) NOT NULL,
  `est_mort_hobbit` enum('oui','non') NOT NULL default 'non',
  `est_compte_actif_hobbit` enum('oui','non') NOT NULL default 'non',
  `date_creation_hobbit` datetime NOT NULL,
  PRIMARY KEY  (`id_hobbit`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tables des Hobbits' AUTO_INCREMENT=1 ;
