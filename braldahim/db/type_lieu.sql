-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 19 Mai 2007 à 18:35
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_lieu`
-- 

DROP TABLE IF EXISTS `type_lieu`;
CREATE TABLE `type_lieu` (
  `id` int(11) NOT NULL,
  `nom_type_lieu` varchar(20) NOT NULL,
  `nom_systeme_type_lieu` varchar(20) NOT NULL,
  `description_type_lieu` varchar(250) NOT NULL,
  `niveau_min_type_lieu` int(2) NOT NULL,
  `pa_utilisation_type_lieu` int(1) NOT NULL,
  `est_alterable_type_lieu` enum('oui','non') NOT NULL,
  `est_franchissable_type_lieu` enum('oui','non') NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contenu de la table `type_lieu`
-- 


INSERT INTO `type_lieu` VALUES (1, 'Mairie', 'mairie', 'Description Marie', 0, 0, 'non', 'oui');
INSERT INTO `type_lieu` VALUES (2, 'Ahenne Peheux', 'ahennepeheux', 'Description Ahenne Peheux', 0, 0, 'non', 'oui');
INSERT INTO `type_lieu` VALUES (3, 'Essene Cehef', 'essenecehef', 'Description Essene Cehef', 0, 6, 'non', 'oui');
