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
-- Structure de la table `laban_equipement`
-- 

CREATE TABLE `laban_equipement` (
  `id_laban_equipement` int(11) NOT NULL,
  `id_fk_recette_laban_equipement` int(11) NOT NULL,
  `id_fk_hobbit_laban_equipement` int(11) NOT NULL,
  `nb_runes_laban_equipement` int(11) NOT NULL,
  `id_fk_type_rune_1_laban_equipement` int(11) default NULL,
  `id_fk_type_rune_2_laban_equipement` int(11) default NULL,
  `id_fk_type_rune_3_laban_equipement` int(11) default NULL,
  `id_fk_type_rune_4_laban_equipement` int(11) default NULL,
  `id_fk_type_rune_5_laban_equipement` int(11) default NULL,
  `id_fk_type_rune_6_laban_equipement` int(11) default NULL,
  PRIMARY KEY  (`id_laban_equipement`),
  KEY `id_fk_hobbit_laban_equipement` (`id_fk_hobbit_laban_equipement`),
  KEY `id_fk_recette_laban_equipement` (`id_fk_recette_laban_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `laban_equipement`
-- 
ALTER TABLE `laban_equipement`
  ADD CONSTRAINT `laban_equipement_ibfk_3` FOREIGN KEY (`id_fk_hobbit_laban_equipement`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_equipement_ibfk_2` FOREIGN KEY (`id_fk_recette_laban_equipement`) REFERENCES `recette_equipements` (`id_recette_equipement`);
