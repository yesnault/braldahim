-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:21
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_equipement`
-- 

CREATE TABLE `type_equipement` (
  `id_type_equipement` int(11) NOT NULL auto_increment,
  `nom_type_equipement` varchar(50) NOT NULL,
  `description_type_equipement` varchar(300) default NULL,
  `nb_runes_max_type_equipement` int(11) NOT NULL,
  `id_fk_metier_type_equipement` int(11) NOT NULL,
  PRIMARY KEY  (`id_type_equipement`),
  KEY `nom_type_equipement` (`nom_type_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=42 ;
