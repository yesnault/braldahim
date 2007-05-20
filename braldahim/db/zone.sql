-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 19 Mai 2007 à 10:43
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `zone`
-- 
-- Création: Dimanche 29 Avril 2007 à 22:59
-- 

DROP TABLE IF EXISTS `zone`;
CREATE TABLE `zone` (
  `id` int(11) NOT NULL auto_increment,
  `id_fk_environnement_zone` int(11) NOT NULL,
  `nom_zone` varchar(100) NOT NULL,
  `description_zone` varchar(100) NOT NULL,
  `image_zone` varchar(100) NOT NULL,
  `x_min_zone` int(11) NOT NULL,
  `y_min_zone` int(11) NOT NULL,
  `x_max_zone` int(11) NOT NULL,
  `y_max_zone` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
