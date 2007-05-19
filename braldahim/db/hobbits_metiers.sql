-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 19 Mai 2007 à 10:41
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `hobbits_metiers`
-- 

DROP TABLE IF EXISTS `hobbits_metiers`;
CREATE TABLE `hobbits_metiers` (
  `id_hobbit_hmetier` int(11) NOT NULL,
  `id_metier_hmetier` int(11) NOT NULL,
  `est_actif_hmetier` enum('oui','non') NOT NULL,
  `date_apprentissage_hmetier` date NOT NULL,
  PRIMARY KEY  (`id_hobbit_hmetier`,`id_metier_hmetier`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
