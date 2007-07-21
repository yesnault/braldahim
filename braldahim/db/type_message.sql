-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 21 Juillet 2007 à 12:57
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_message`
-- 

CREATE TABLE `type_message` (
  `id_type_message` int(11) NOT NULL auto_increment,
  `nom_systeme_type_message` varchar(20) NOT NULL,
  `nom_type_message` varchar(30) NOT NULL,
  PRIMARY KEY  (`id_type_message`),
  UNIQUE KEY `nom_systeme_type_message` (`nom_systeme_type_message`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- 
-- Contenu de la table `type_message`
-- 

INSERT INTO `type_message` VALUES (1, 'reception', 'Boite de réception');
INSERT INTO `type_message` VALUES (2, 'envoye', 'Message envoyé');
INSERT INTO `type_message` VALUES (3, 'brouillon', 'Brouillon');
INSERT INTO `type_message` VALUES (4, 'supprime', 'Message supprimé');
INSERT INTO `type_message` VALUES (5, 'archive', 'Message archivé');
