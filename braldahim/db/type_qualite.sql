-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:23
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_qualite`
-- 

CREATE TABLE `type_qualite` (
  `id_type_qualite` int(11) NOT NULL auto_increment,
  `nom_systeme_type_qualite` varchar(10) NOT NULL,
  `nom_type_qualite` varchar(10) NOT NULL,
  PRIMARY KEY  (`id_type_qualite`),
  KEY `nom_systeme_type_qualite` (`nom_systeme_type_qualite`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;
