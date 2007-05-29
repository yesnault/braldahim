-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mardi 29 Mai 2007 à 21:43
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_minerai`
-- 

DROP TABLE IF EXISTS `type_minerai`;
CREATE TABLE `type_minerai` (
  `id_type_minerai` int(11) NOT NULL auto_increment,
  `nom_type_minerai` varchar(20) NOT NULL,
  `nom_systeme_type_minerai` varchar(10) NOT NULL,
  `description_type_minerai` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_type_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;
