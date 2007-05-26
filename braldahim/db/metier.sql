-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 19 Mai 2007 à 10:42
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `metier`
-- 

DROP TABLE IF EXISTS `metier`;
CREATE TABLE `metier` (
  `id_metier` int(11) NOT NULL auto_increment,
  `nom_metier` varchar(20) NOT NULL,
  `nom_systeme_metier` varchar(20) NOT NULL,
  `description_metier` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_metier`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- 
-- Contenu de la table `metier`
-- 

INSERT INTO `metier` VALUES (1, 'Mineur', 'mineur', 'Description du métier mineur');
INSERT INTO `metier` VALUES (2, 'Chasseur', 'chasseur', 'Description du métier chasseur');
INSERT INTO `metier` VALUES (3, 'Bûcheron', 'bucheron', 'Description du métier Bûcheron');
INSERT INTO `metier` VALUES (4, 'Herboriste', 'herboriste', 'Description du métier Herboriste');
INSERT INTO `metier` VALUES (5, 'Forgeron', 'forgeron', 'Description du métier Forgeron');
INSERT INTO `metier` VALUES (6, 'Apothicaire', 'apothicaire', 'Description du métier Apothicaire');
INSERT INTO `metier` VALUES (7, 'Menuisier', 'menuisier', 'Description du métier menuisier');
INSERT INTO `metier` VALUES (8, 'Cuisiner', 'cuisinier', 'Description du métier Cuisinier');
INSERT INTO `metier` VALUES (9, 'Tanneur', 'tanneur', 'Description du métier Tanneur');
INSERT INTO `metier` VALUES (10, 'Guerrier', 'guerrier', 'Description du métier Guerrier');
