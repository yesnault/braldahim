-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:17
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `monstre`
-- 

CREATE TABLE `monstre` (
  `id_monstre` int(11) NOT NULL auto_increment,
  `id_fk_type_monstre` int(11) NOT NULL,
  `id_fk_taille_monstre` int(11) NOT NULL,
  `id_fk_groupe_monstre` int(11) default NULL,
  `x_monstre` int(11) NOT NULL,
  `y_monstre` int(11) NOT NULL,
  `id_fk_hobbit_cible_monstre` int(11) default NULL,
  `pv_restant_monstre` int(11) NOT NULL,
  `pa_monstre` int(11) NOT NULL,
  `niveau_monstre` int(11) NOT NULL,
  `vue_monstre` int(11) NOT NULL,
  `force_base_monstre` int(11) NOT NULL,
  `force_bm_monstre` int(11) NOT NULL,
  `agilite_base_monstre` int(11) NOT NULL,
  `agilite_bm_monstre` int(11) NOT NULL,
  `sagesse_base_monstre` int(11) NOT NULL,
  `sagesse_bm_monstre` int(11) NOT NULL,
  `vigueur_base_monstre` int(11) NOT NULL,
  `vigueur_bm_monstre` int(11) NOT NULL,
  `regeneration_monstre` int(11) NOT NULL,
  `armure_naturelle_monstre` int(11) NOT NULL,
  `date_fin_tour_monstre` datetime NOT NULL,
  `duree_prochain_tour_monstre` time NOT NULL,
  `duree_base_tour_monstre` time NOT NULL,
  `nb_kill_monstre` int(11) NOT NULL,
  `date_creation_monstre` datetime NOT NULL,
  `est_mort_monstre` enum('oui','non') NOT NULL default 'non',
  PRIMARY KEY  (`id_monstre`),
  KEY `id_fk_groupe_monstre` (`id_fk_groupe_monstre`),
  KEY `idx_x_monstre_y_monstre` (`x_monstre`,`y_monstre`),
  KEY `id_fk_type_monstre` (`id_fk_type_monstre`),
  KEY `id_fk_taille_monstre` (`id_fk_taille_monstre`),
  KEY `id_fk_hobbit_cible_monstre` (`id_fk_hobbit_cible_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `monstre`
-- 
ALTER TABLE `monstre`
  ADD CONSTRAINT `monstre_ibfk_11` FOREIGN KEY (`id_fk_hobbit_cible_monstre`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE SET NULL,
  ADD CONSTRAINT `monstre_ibfk_10` FOREIGN KEY (`id_fk_groupe_monstre`) REFERENCES `groupe_monstre` (`id_groupe_monstre`),
  ADD CONSTRAINT `monstre_ibfk_8` FOREIGN KEY (`id_fk_type_monstre`) REFERENCES `type_monstre` (`id_type_monstre`),
  ADD CONSTRAINT `monstre_ibfk_9` FOREIGN KEY (`id_fk_taille_monstre`) REFERENCES `taille_monstre` (`id_taille_monstre`);
