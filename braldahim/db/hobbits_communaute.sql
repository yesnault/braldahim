-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Lun 31 Mars 2008 à 00:33
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `hobbits_communaute`
-- 

CREATE TABLE `hobbits_communaute` (
  `id_fk_communaute_communaute` int(11) NOT NULL,
  `id_fk_hobbit_communaute` int(11) NOT NULL,
  `id_fk_rang_communaute_hobbit_communaute` int(11) NOT NULL,
  `date_entree_hobbit_communaute` datetime NOT NULL,
  `commentaire_hobbit_communaute` varchar(200) default NULL,
  PRIMARY KEY  (`id_fk_communaute_communaute`,`id_fk_hobbit_communaute`),
  UNIQUE KEY `id_fk_hobbit_communaute` (`id_fk_hobbit_communaute`),
  KEY `id_fk_rang_communaute_hobbit_communaute` (`id_fk_rang_communaute_hobbit_communaute`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contenu de la table `hobbits_communaute`
-- 


-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `hobbits_communaute`
-- 
ALTER TABLE `hobbits_communaute`
  ADD CONSTRAINT `hobbits_communaute_ibfk_11` FOREIGN KEY (`id_fk_communaute_communaute`) REFERENCES `communaute` (`id_communaute`) ON DELETE CASCADE,
  ADD CONSTRAINT `hobbits_communaute_ibfk_12` FOREIGN KEY (`id_fk_hobbit_communaute`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE,
  ADD CONSTRAINT `hobbits_communaute_ibfk_13` FOREIGN KEY (`id_fk_rang_communaute_hobbit_communaute`) REFERENCES `rang_communaute` (`id_fk_type_rang_communaute`) ON DELETE CASCADE;
