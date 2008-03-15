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
-- Structure de la table `equipement_rune`
-- 

CREATE TABLE `equipement_rune` (
  `id_equipement_rune` int(11) NOT NULL,
  `id_rune_equipement_rune` int(11) NOT NULL,
  `id_fk_type_rune_equipement_rune` int(11) NOT NULL,
  `ordre_equipement_rune` int(11) NOT NULL,
  PRIMARY KEY  (`id_equipement_rune`,`id_rune_equipement_rune`),
  KEY `id_fk_type_rune_equipement_rune` (`id_fk_type_rune_equipement_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
