-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:24
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_unite`
-- 

CREATE TABLE `type_unite` (
  `id_type_unite` int(11) NOT NULL auto_increment,
  `nom_systeme_type_unite` varchar(10) NOT NULL,
  `nom_type_unite` varchar(50) NOT NULL,
  PRIMARY KEY  (`id_type_unite`),
  UNIQUE KEY `nom_systeme_type_unite` (`nom_systeme_type_unite`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;
