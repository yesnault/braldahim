-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Ven 14 Mars 2008 à 23:56
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_equipement`
-- 

CREATE TABLE `laban_equipement` (
  `id_laban_equipement` int(11) NOT NULL,
  `id_fk_recette_laban_equipement` int(11) NOT NULL,
  `id_fk_hobbit_laban_equipement` int(11) NOT NULL,
  `nb_runes_laban_equipement` int(11) NOT NULL,
  `id_fk_mot_runique_laban_equipement` int(11) default NULL,
  PRIMARY KEY  (`id_laban_equipement`),
  KEY `id_fk_hobbit_laban_equipement` (`id_fk_hobbit_laban_equipement`),
  KEY `id_fk_recette_laban_equipement` (`id_fk_recette_laban_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
