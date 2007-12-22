-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:15
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `hobbits_metiers`
-- 

CREATE TABLE `hobbits_metiers` (
  `id_fk_hobbit_hmetier` int(11) NOT NULL,
  `id_fk_metier_hmetier` int(11) NOT NULL,
  `est_actif_hmetier` enum('oui','non') NOT NULL,
  `date_apprentissage_hmetier` date NOT NULL,
  PRIMARY KEY  (`id_fk_hobbit_hmetier`,`id_fk_metier_hmetier`),
  KEY `id_fk_metier_hmetier` (`id_fk_metier_hmetier`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `hobbits_metiers`
-- 
ALTER TABLE `hobbits_metiers`
  ADD CONSTRAINT `hobbits_metiers_ibfk_5` FOREIGN KEY (`id_fk_metier_hmetier`) REFERENCES `metier` (`id_metier`),
  ADD CONSTRAINT `hobbits_metiers_ibfk_4` FOREIGN KEY (`id_fk_hobbit_hmetier`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE;
