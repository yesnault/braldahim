-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:16
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_minerai`
-- 

CREATE TABLE `laban_minerai` (
  `id_fk_type_laban_minerai` int(11) NOT NULL,
  `id_fk_hobbit_laban_minerai` int(11) NOT NULL,
  `quantite_brut_laban_minerai` int(11) default NULL,
  `quantite_lingots_laban_minerai` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_laban_minerai`,`id_fk_hobbit_laban_minerai`),
  KEY `id_fk_hobbit_laban_minerai` (`id_fk_hobbit_laban_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `laban_minerai`
-- 
ALTER TABLE `laban_minerai`
  ADD CONSTRAINT `laban_minerai_ibfk_3` FOREIGN KEY (`id_fk_hobbit_laban_minerai`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_minerai_ibfk_2` FOREIGN KEY (`id_fk_type_laban_minerai`) REFERENCES `type_minerai` (`id_type_minerai`);
