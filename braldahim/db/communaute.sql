-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Dim 30 Mars 2008 à 22:18
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `communaute`
-- 

CREATE TABLE `communaute` (
  `id_communaute` int(11) NOT NULL,
  `nom_communaute` varchar(50) NOT NULL,
  `date_creation_communaute` datetime NOT NULL,
  `id_fk_hobbit_createur_communaute` int(11) NOT NULL,
  `nb_membre_communaute` int(11) NOT NULL,
  `description_communaute` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_communaute`),
  UNIQUE KEY `id_fk_hobbit_createur_communaute` (`id_fk_hobbit_createur_communaute`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contenu de la table `communaute`
-- 


-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `communaute`
-- 
ALTER TABLE `communaute`
  ADD CONSTRAINT `communaute_ibfk_1` FOREIGN KEY (`id_fk_hobbit_createur_communaute`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE;
