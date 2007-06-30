-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 30 Juin 2007 à 16:44
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_groupe_monstre`
-- 

CREATE TABLE `type_groupe_monstre` (
  `id_type_groupe_monstre` int(11) NOT NULL auto_increment,
  `nom_groupe_monstre` varchar(20) NOT NULL,
  `nb_membres_min_type_groupe_monstre` int(11) NOT NULL,
  `nb_membres_max_type_groupe_monstre` int(11) NOT NULL,
  `repeuplement_type_groupe_monstre` enum('oui','non') NOT NULL default 'non',
  PRIMARY KEY  (`id_type_groupe_monstre`),
  UNIQUE KEY `nom_groupe_monstre` (`nom_groupe_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;
