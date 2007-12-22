-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:19
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `recette_cout_minerai`
-- 

CREATE TABLE `recette_cout_minerai` (
  `id_fk_type_equipement_recette_cout_minerai` int(11) NOT NULL COMMENT 'Identifiant sur la table recette_equipement',
  `id_fk_type_recette_cout_minerai` int(11) NOT NULL COMMENT 'Identifiant sur la table type_minerai',
  `niveau_recette_cout_minerai` int(11) NOT NULL,
  `quantite_recette_cout_minerai` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_equipement_recette_cout_minerai`,`id_fk_type_recette_cout_minerai`,`niveau_recette_cout_minerai`),
  KEY `id_fk_type_recette_cout_minerai` (`id_fk_type_recette_cout_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `recette_cout_minerai`
-- 
ALTER TABLE `recette_cout_minerai`
  ADD CONSTRAINT `recette_cout_minerai_ibfk_2` FOREIGN KEY (`id_fk_type_recette_cout_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `recette_cout_minerai_ibfk_1` FOREIGN KEY (`id_fk_type_equipement_recette_cout_minerai`) REFERENCES `type_equipement` (`id_type_equipement`);
