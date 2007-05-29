-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mardi 29 Mai 2007 à 20:15
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_filon`
-- 

CREATE TABLE `type_filon` (
  `id_type_filon` int(11) NOT NULL auto_increment,
  `nom_type_filon` varchar(20) NOT NULL,
  `nom_systeme_type_filon` varchar(10) NOT NULL,
  `description_type_filon` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_type_filon`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
