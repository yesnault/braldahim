-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 08 Mars 2008 à 23:48
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `couple`
-- 

CREATE TABLE `couple` (
  `id_fk_m_hobbit_couple` int(11) NOT NULL,
  `id_fk_f_hobbit_couple` int(11) NOT NULL,
  `date_creation_couple` datetime NOT NULL,
  `nb_enfants_couple` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_m_hobbit_couple`,`id_fk_f_hobbit_couple`),
  UNIQUE KEY `id_fk_f_hobbit_couple` (`id_fk_f_hobbit_couple`),
  UNIQUE KEY `id_fk_m_hobbit_couple` (`id_fk_m_hobbit_couple`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `couple`
-- 
ALTER TABLE `couple`
  ADD CONSTRAINT `couple_ibfk_2` FOREIGN KEY (`id_fk_f_hobbit_couple`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE,
  ADD CONSTRAINT `couple_ibfk_1` FOREIGN KEY (`id_fk_m_hobbit_couple`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE;
