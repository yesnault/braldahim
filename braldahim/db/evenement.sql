-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Lundi 16 Juillet 2007 à 21:57
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `evenement`
-- 

CREATE TABLE `evenement` (
  `id_evenement` int(11) NOT NULL auto_increment,
  `id_hobbit_evenement` int(11) default NULL,
  `id_monstre_evenement` int(11) default NULL,
  `date_evenement` datetime NOT NULL,
  `id_fk_type_evenement` int(11) NOT NULL,
  `details_evenement` varchar(1000) NOT NULL,
  PRIMARY KEY  (`id_evenement`),
  KEY `idx_id_hobbit_evenement` (`id_hobbit_evenement`),
  KEY `idx_id_monstre_evenement` (`id_monstre_evenement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
