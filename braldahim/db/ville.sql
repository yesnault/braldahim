-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Dimanche 20 Mai 2007 à 22:28
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `ville`
-- 

CREATE TABLE `ville` (
  `id` int(11) NOT NULL auto_increment,
  `nom_ville` varchar(20) NOT NULL,
  `description_ville` varchar(200) NOT NULL,
  `id_fk_region_ville` int(11) NOT NULL,
  `est_capitale_ville` enum('oui','non') NOT NULL,
  `x_min_ville` int(11) NOT NULL,
  `y_min_ville` int(11) NOT NULL,
  `x_max_ville` int(11) NOT NULL,
  `y_max_ville` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
