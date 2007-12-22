-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:14
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `evenement`
-- 

CREATE TABLE `evenement` (
  `id_evenement` int(11) NOT NULL auto_increment,
  `id_fk_hobbit_evenement` int(11) default NULL,
  `id_fk_monstre_evenement` int(11) default NULL,
  `date_evenement` datetime NOT NULL,
  `id_fk_type_evenement` int(11) NOT NULL,
  `details_evenement` varchar(1000) NOT NULL,
  PRIMARY KEY  (`id_evenement`),
  KEY `idx_id_hobbit_evenement` (`id_fk_hobbit_evenement`),
  KEY `idx_id_monstre_evenement` (`id_fk_monstre_evenement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `evenement`
-- 
ALTER TABLE `evenement`
  ADD CONSTRAINT `evenement_ibfk_2` FOREIGN KEY (`id_fk_monstre_evenement`) REFERENCES `monstre` (`id_monstre`) ON DELETE CASCADE,
  ADD CONSTRAINT `evenement_ibfk_1` FOREIGN KEY (`id_fk_hobbit_evenement`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE;
