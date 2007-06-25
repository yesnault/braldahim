-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Lundi 25 Juin 2007 à 22:17
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `plante`
-- 

CREATE TABLE `plante` (
  `id_plante` int(11) NOT NULL auto_increment,
  `id_fk_type_plante` int(11) NOT NULL,
  `x_plante` int(11) NOT NULL,
  `y_plante` int(11) NOT NULL,
  `partie_1_plante` int(11) NOT NULL,
  `partie_2_plante` int(11) default NULL,
  `partie_3_plante` int(11) default NULL,
  `partie_4_plante` int(11) default NULL,
  PRIMARY KEY  (`id_plante`),
  KEY `idx_x_plante_y_plante` (`x_plante`,`y_plante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
