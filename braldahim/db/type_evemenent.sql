-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Dimanche 24 Juin 2007 à 23:22
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_evenement`
-- 

CREATE TABLE `type_evenement` (
  `id_type_evenement` int(11) NOT NULL auto_increment,
  `nom_type_evenement` varchar(20) NOT NULL,
  PRIMARY KEY  (`id_type_evenement`),
  UNIQUE KEY `nom_type_evenement` (`nom_type_evenement`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- 
-- Contenu de la table `type_evenement`
-- 

INSERT INTO `type_evenement` VALUES (1, 'Naissance');
INSERT INTO `type_evenement` VALUES (2, 'Mort');
INSERT INTO `type_evenement` VALUES (3, 'Déplacement');
INSERT INTO `type_evenement` VALUES (4, 'Compétence');
INSERT INTO `type_evenement` VALUES (5, 'Kill');
INSERT INTO `type_evenement` VALUES (6, 'Don');
INSERT INTO `type_evenement` VALUES (7, 'Service');
INSERT INTO `type_evenement` VALUES (8, 'Ramasser');
INSERT INTO `type_evenement` VALUES (9, 'Attaquer');
