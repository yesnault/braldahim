-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 19 Mai 2007 à 10:39
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `environnement`
-- 

DROP TABLE IF EXISTS `environnement`;
CREATE TABLE `environnement` (
  `id_environnement` int(11) NOT NULL auto_increment,
  `nom_environnement` varchar(20) NOT NULL,
  `description_environnement` varchar(250) NOT NULL,
  `nom_systeme_environnement` varchar(20) NOT NULL,
  `image_environnement` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_environnement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- 
-- Contenu de la table `environnement`
-- 

INSERT INTO `environnement` VALUES (1, 'Plaine', 'Description Plaine', 'plaine', '');
INSERT INTO `environnement` VALUES (2, 'Forêt', 'Description Forêt', 'foret', '');
INSERT INTO `environnement` VALUES (3, 'Marais', 'Description marais', 'marais', '');
INSERT INTO `environnement` VALUES (4, 'Montagne', 'Description Montagne', 'montagne', '');
INSERT INTO `environnement` VALUES (5, 'Caverne', 'Description Caverne', 'caverne', '');
