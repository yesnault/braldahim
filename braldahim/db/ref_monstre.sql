-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:20
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `ref_monstre`
-- 

CREATE TABLE `ref_monstre` (
  `id_ref_monstre` int(11) NOT NULL auto_increment,
  `id_fk_type_ref_monstre` int(11) NOT NULL,
  `id_fk_taille_ref_monstre` int(11) NOT NULL,
  `niveau_min_ref_monstre` int(11) NOT NULL,
  `niveau_max_ref_monstre` int(11) NOT NULL,
  `pourcentage_vigueur_ref_monstre` int(11) NOT NULL,
  `pourcentage_agilite_ref_monstre` int(11) NOT NULL,
  `pourcentage_sagesse_ref_monstre` int(11) NOT NULL,
  `pourcentage_force_ref_monstre` int(11) NOT NULL,
  `vue_ref_monstre` int(11) NOT NULL,
  PRIMARY KEY  (`id_ref_monstre`),
  UNIQUE KEY `id_fk_type_taille_ref_monstre` (`id_fk_type_ref_monstre`,`id_fk_taille_ref_monstre`),
  KEY `id_fk_taille_ref_monstre` (`id_fk_taille_ref_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `ref_monstre`
-- 
ALTER TABLE `ref_monstre`
  ADD CONSTRAINT `ref_monstre_ibfk_2` FOREIGN KEY (`id_fk_taille_ref_monstre`) REFERENCES `taille_monstre` (`id_taille_monstre`),
  ADD CONSTRAINT `ref_monstre_ibfk_1` FOREIGN KEY (`id_fk_type_ref_monstre`) REFERENCES `type_monstre` (`id_type_monstre`);
