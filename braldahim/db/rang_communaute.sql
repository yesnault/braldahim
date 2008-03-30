-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Dim 30 Mars 2008 à 22:20
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `rang_communaute`
-- 

CREATE TABLE `rang_communaute` (
  `id_fk_type_rang_communaute` int(11) NOT NULL,
  `id_fk_communaute_rang_communaute` int(11) NOT NULL,
  `nom_rang_communaute` varchar(20) NOT NULL,
  `description_rang_communaute` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_fk_type_rang_communaute`,`id_fk_communaute_rang_communaute`),
  KEY `id_fk_communaute_rang_communaute` (`id_fk_communaute_rang_communaute`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contenu de la table `rang_communaute`
-- 


-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `rang_communaute`
-- 
ALTER TABLE `rang_communaute`
  ADD CONSTRAINT `rang_communaute_ibfk_2` FOREIGN KEY (`id_fk_communaute_rang_communaute`) REFERENCES `communaute` (`id_communaute`) ON DELETE CASCADE,
  ADD CONSTRAINT `rang_communaute_ibfk_1` FOREIGN KEY (`id_fk_type_rang_communaute`) REFERENCES `type_rang_communaute` (`id_type_rang_communaute`);
