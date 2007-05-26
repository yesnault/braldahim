-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 26 Mai 2007 à 17:57
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_plante`
-- 

CREATE TABLE `laban_plante` (
  `id_laban_plante` int(11) NOT NULL auto_increment,
  `id_fk_type_laban_plante` int(11) NOT NULL,
  `id_hobbit_laban_plante` int(11) NOT NULL,
  `partie_1_laban_plante` int(11) NOT NULL,
  `partie_2_laban_plante` int(11) default NULL,
  `partie_3_laban_plante` int(11) default NULL,
  `partie_4_laban_plante` int(11) default NULL,
  PRIMARY KEY  (`id_laban_plante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
