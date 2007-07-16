-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Lundi 16 Juillet 2007 à 21:57
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
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
  `id_cible_groupe_monstre` int(11) default NULL,
  `nb_membres_max_groupe_monstre` int(11) NOT NULL,
  `nb_membres_restant_groupe_monstre` int(11) NOT NULL,
  `phase_tactique_groupe_monstre` int(11) NOT NULL,
  `id_role_a_groupe_monstre` int(11) default NULL,
  `id_role_b_groupe_monstre` int(11) default NULL,
  `date_fin_tour_groupe_monstre` datetime default NULL COMMENT 'DLA du dernier monstre à jouer dans ce groupe',
  `x_direction_groupe_monstre` int(11) NOT NULL,
  `y_direction_groupe_monstre` int(11) NOT NULL,
  PRIMARY KEY  (`id_groupe_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
