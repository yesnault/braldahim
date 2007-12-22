-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:18
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `recette_cout`
-- 

CREATE TABLE `recette_cout` (
  `id_fk_type_equipement_recette_cout` int(11) NOT NULL,
  `niveau_recette_cout` int(11) NOT NULL,
  `cuir_recette_cout` int(11) NOT NULL,
  `fourrure_recette_cout` int(11) NOT NULL,
  `planche_recette_cout` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_equipement_recette_cout`,`niveau_recette_cout`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `recette_cout`
-- 
ALTER TABLE `recette_cout`
  ADD CONSTRAINT `recette_cout_ibfk_1` FOREIGN KEY (`id_fk_type_equipement_recette_cout`) REFERENCES `type_equipement` (`id_type_equipement`);
