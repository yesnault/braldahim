-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- G�n�r� le : Dim 06 Avril 2008 � 00:25
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de donn�es: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `hobbit`
-- 

CREATE TABLE `hobbit` (
  `id_hobbit` int(11) NOT NULL auto_increment,
  `sysgroupe_hobbit` varchar(10) default NULL,
  `nom_hobbit` varchar(20) NOT NULL,
  `prenom_hobbit` varchar(23) NOT NULL,
  `id_fk_nom_initial_hobbit` int(11) NOT NULL,
  `password_hobbit` varchar(50) NOT NULL,
  `email_hobbit` varchar(100) NOT NULL,
  `etat_hobbit` int(11) NOT NULL,
  `sexe_hobbit` enum('feminin','masculin') NOT NULL,
  `x_hobbit` int(11) NOT NULL,
  `y_hobbit` int(1) NOT NULL,
  `date_debut_tour_hobbit` datetime NOT NULL,
  `date_fin_tour_hobbit` datetime NOT NULL,
  `duree_prochain_tour_hobbit` time NOT NULL,
  `duree_base_tour_hobbit` time NOT NULL,
  `duree_courant_tour_hobbit` time NOT NULL,
  `tour_position_hobbit` int(11) NOT NULL,
  `pa_hobbit` int(11) NOT NULL,
  `vue_bm_hobbit` int(11) NOT NULL,
  `vue_malus_hobbit` int(11) NOT NULL,
  `force_base_hobbit` int(11) NOT NULL,
  `force_bm_hobbit` int(11) NOT NULL,
  `agilite_base_hobbit` int(11) NOT NULL,
  `agilite_bm_hobbit` int(11) NOT NULL,
  `agilite_malus_hobbit` int(11) NOT NULL,
  `sagesse_base_hobbit` int(11) NOT NULL,
  `sagesse_bm_hobbit` int(11) NOT NULL,
  `vigueur_base_hobbit` int(11) NOT NULL,
  `vigueur_bm_hobbit` int(11) NOT NULL,
  `regeneration_hobbit` int(11) NOT NULL,
  `regeneration_malus_hobbit` int(11) NOT NULL,
  `px_perso_hobbit` int(11) NOT NULL default '0',
  `px_commun_hobbit` int(11) NOT NULL,
  `pi_cumul_hobbit` int(11) NOT NULL default '0',
  `pi_hobbit` int(11) NOT NULL default '0',
  `niveau_hobbit` int(11) NOT NULL default '0',
  `balance_faim_hobbit` int(11) NOT NULL,
  `armure_naturelle_hobbit` int(11) NOT NULL,
  `armure_equipement_hobbit` int(11) NOT NULL,
  `bm_attaque_hobbit` int(11) NOT NULL,
  `bm_defense_hobbit` int(11) NOT NULL,
  `bm_degat_hobbit` int(11) NOT NULL,
  `poids_transportable_hobbit` int(11) NOT NULL,
  `castars_hobbit` int(11) NOT NULL,
  `pv_max_hobbit` int(11) NOT NULL COMMENT 'calcul� � l''activation du tour',
  `pv_restant_hobbit` int(11) NOT NULL,
  `pv_max_bm_hobbit` int(11) NOT NULL,
  `est_mort_hobbit` enum('oui','non') NOT NULL default 'non',
  `nb_mort_hobbit` int(11) NOT NULL default '0',
  `nb_hobbit_kill_hobbit` int(11) NOT NULL default '0',
  `nb_monstre_kill_hobbit` int(11) NOT NULL,
  `est_compte_actif_hobbit` enum('oui','non') NOT NULL default 'non',
  `date_creation_hobbit` datetime NOT NULL,
  `id_fk_mere_hobbit` int(11) default NULL,
  `id_fk_pere_hobbit` int(11) default NULL,
  `description_hobbit` mediumblob NOT NULL,
  `id_fk_communaute_hobbit` int(11) default NULL,
  `id_fk_rang_communaute_hobbit` int(11) default NULL,
  `date_entree_communaute_hobbit` datetime default NULL,
  PRIMARY KEY  (`id_hobbit`),
  UNIQUE KEY `email_hobbit` (`email_hobbit`),
  KEY `idx_x_hobbit_y_hobbit` (`x_hobbit`,`y_hobbit`),
  KEY `id_fk_communaute_hobbit` (`id_fk_communaute_hobbit`),
  KEY `id_fk_rang_communaute_hobbit` (`id_fk_rang_communaute_hobbit`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tables des Hobbits' AUTO_INCREMENT=0 ;

-- 
-- Contraintes pour les tables export�es
-- 

-- 
-- Contraintes pour la table `hobbit`
-- 
ALTER TABLE `hobbit`
  ADD CONSTRAINT `hobbit_ibfk_2` FOREIGN KEY (`id_fk_rang_communaute_hobbit`) REFERENCES `rang_communaute` (`id_fk_type_rang_communaute`) ON DELETE SET NULL,
  ADD CONSTRAINT `hobbit_ibfk_1` FOREIGN KEY (`id_fk_communaute_hobbit`) REFERENCES `communaute` (`id_communaute`) ON DELETE SET NULL;
