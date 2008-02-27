-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mer 27 Février 2008 à 23:17
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_potion`
-- 

CREATE TABLE `type_potion` (
  `id_type_potion` int(11) NOT NULL auto_increment,
  `nom_type_potion` varchar(20) NOT NULL,
  `caract_type_potion` enum('FOR','AGI','VIG','SAG','PV') NOT NULL,
  `bm_type_potion` enum('bonus','malus') NOT NULL,
  PRIMARY KEY  (`id_type_potion`),
  UNIQUE KEY `nom_type_potion` (`nom_type_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;
