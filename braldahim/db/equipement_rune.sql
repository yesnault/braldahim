-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 23:04
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `equipement_rune`
-- 

CREATE TABLE `equipement_rune` (
  `id_equipement_rune` int(11) NOT NULL,
  `id_rune_equipement_rune` int(11) NOT NULL,
  `id_fk_type_rune_equipement_rune` int(11) NOT NULL,
  `ordre_equipement_rune` int(11) NOT NULL,
  PRIMARY KEY  (`id_equipement_rune`),
  KEY `id_fk_type_rune_equipement_rune` (`id_fk_type_rune_equipement_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contenu de la table `equipement_rune`
-- 


-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `equipement_rune`
-- 
ALTER TABLE `equipement_rune`
  ADD CONSTRAINT `equipement_rune_ibfk_1` FOREIGN KEY (`id_fk_type_rune_equipement_rune`) REFERENCES `type_rune` (`id_type_rune`);
