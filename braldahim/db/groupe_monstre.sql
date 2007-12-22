-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:14
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `groupe_monstre`
-- 

CREATE TABLE `groupe_monstre` (
  `id_groupe_monstre` int(11) NOT NULL auto_increment,
  `id_fk_type_groupe_monstre` int(11) NOT NULL,
  `date_creation_groupe_monstre` datetime NOT NULL,
  `id_fk_hobbit_cible_groupe_monstre` int(11) default NULL,
  `nb_membres_max_groupe_monstre` int(11) NOT NULL,
  `nb_membres_restant_groupe_monstre` int(11) NOT NULL,
  `phase_tactique_groupe_monstre` int(11) NOT NULL,
  `id_role_a_groupe_monstre` int(11) default NULL,
  `id_role_b_groupe_monstre` int(11) default NULL,
  `date_fin_tour_groupe_monstre` datetime default NULL COMMENT 'DLA du dernier monstre à jouer dans ce groupe',
  `x_direction_groupe_monstre` int(11) NOT NULL,
  `y_direction_groupe_monstre` int(11) NOT NULL,
  PRIMARY KEY  (`id_groupe_monstre`),
  KEY `id_fk_type_groupe_monstre` (`id_fk_type_groupe_monstre`),
  KEY `id_fk_hobbit_cible_groupe_monstre` (`id_fk_hobbit_cible_groupe_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `groupe_monstre`
-- 
ALTER TABLE `groupe_monstre`
  ADD CONSTRAINT `groupe_monstre_ibfk_2` FOREIGN KEY (`id_fk_hobbit_cible_groupe_monstre`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE SET NULL,
  ADD CONSTRAINT `groupe_monstre_ibfk_1` FOREIGN KEY (`id_fk_type_groupe_monstre`) REFERENCES `type_groupe_monstre` (`id_type_groupe_monstre`);
