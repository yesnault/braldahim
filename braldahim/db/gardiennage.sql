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
-- Structure de la table `gardiennage`
-- 

CREATE TABLE `gardiennage` (
  `id_gardiennage` int(11) NOT NULL auto_increment,
  `id_fk_hobbit_gardiennage` int(11) NOT NULL,
  `id_gardien_gardiennage` int(11) NOT NULL,
  `date_debut_gardiennage` date NOT NULL,
  `date_fin_gardiennage` date NOT NULL,
  `nb_jours_gardiennage` int(11) NOT NULL,
  `commentaire_gardiennage` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_gardiennage`),
  KEY `id_gardien_gardiennage` (`id_gardien_gardiennage`),
  KEY `id_fk_hobbit_gardiennage` (`id_fk_hobbit_gardiennage`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `gardiennage`
-- 
ALTER TABLE `gardiennage`
  ADD CONSTRAINT `gardiennage_ibfk_2` FOREIGN KEY (`id_gardien_gardiennage`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE,
  ADD CONSTRAINT `gardiennage_ibfk_1` FOREIGN KEY (`id_fk_hobbit_gardiennage`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE;
