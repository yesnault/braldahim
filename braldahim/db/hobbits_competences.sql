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
-- Structure de la table `hobbits_competences`
-- 

CREATE TABLE `hobbits_competences` (
  `id_fk_hobbit_hcomp` int(11) NOT NULL default '0',
  `id_fk_competence_hcomp` int(11) NOT NULL default '0',
  `pourcentage_hcomp` int(11) NOT NULL default '10',
  `date_gain_tour_hcomp` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_fk_hobbit_hcomp`,`id_fk_competence_hcomp`),
  KEY `id_fk_competence_hcomp` (`id_fk_competence_hcomp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `hobbits_competences`
-- 
ALTER TABLE `hobbits_competences`
  ADD CONSTRAINT `hobbits_competences_ibfk_2` FOREIGN KEY (`id_fk_competence_hcomp`) REFERENCES `competence` (`id_competence`),
  ADD CONSTRAINT `hobbits_competences_ibfk_1` FOREIGN KEY (`id_fk_hobbit_hcomp`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE;
