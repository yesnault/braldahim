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
-- Structure de la table `recette_equipements`
-- 

CREATE TABLE `recette_equipements` (
  `id_recette_equipement` int(11) NOT NULL auto_increment,
  `id_fk_type_recette_equipement` int(11) NOT NULL,
  `niveau_recette_equipement` int(11) NOT NULL,
  `poids_recette_equipement` float NOT NULL,
  `id_fk_type_qualite_recette_equipement` int(11) NOT NULL,
  `armure_recette_equipement` int(11) NOT NULL,
  `force_recette_equipement` int(11) NOT NULL,
  `agilite_recette_equipement` int(11) NOT NULL,
  `vigueur_recette_equipement` int(11) NOT NULL,
  `sagesse_recette_equipement` int(11) NOT NULL,
  `vue_recette_equipement` int(11) NOT NULL,
  `bm_attaque_recette_equipement` int(11) NOT NULL,
  `bm_degat_recette_equipement` int(11) NOT NULL,
  `bm_defense_recette_equipement` int(11) NOT NULL,
  `id_fk_type_emplacement_recette_equipement` int(11) NOT NULL,
  PRIMARY KEY  (`id_recette_equipement`),
  UNIQUE KEY `id_fk_type_recette_equipement` (`id_fk_type_recette_equipement`,`niveau_recette_equipement`,`id_fk_type_qualite_recette_equipement`),
  KEY `id_fk_type_emplacement_recette_equipement` (`id_fk_type_emplacement_recette_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=862 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `recette_equipements`
-- 
ALTER TABLE `recette_equipements`
  ADD CONSTRAINT `recette_equipements_ibfk_2` FOREIGN KEY (`id_fk_type_emplacement_recette_equipement`) REFERENCES `type_emplacement` (`id_type_emplacement`),
  ADD CONSTRAINT `recette_equipements_ibfk_1` FOREIGN KEY (`id_fk_type_recette_equipement`) REFERENCES `type_equipement` (`id_type_equipement`);
