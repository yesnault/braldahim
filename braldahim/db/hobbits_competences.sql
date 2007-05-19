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
-- Structure de la table `hobbits_competences`
-- 

DROP TABLE IF EXISTS `hobbits_competences`;
CREATE TABLE `hobbits_competences` (
  `id_hobbit_hcomp` int(11) NOT NULL default '0',
  `id_competence_hcomp` int(11) NOT NULL default '0',
  `pourcentage_hcomp` int(11) NOT NULL default '10',
  `date_gain_tour_hcomp` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_hobbit_hcomp`,`id_competence_hcomp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
