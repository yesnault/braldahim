-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Ven 14 Mars 2008 à 23:57
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_piece`
-- 

CREATE TABLE `type_piece` (
  `id_type_piece` int(11) NOT NULL auto_increment,
  `nom_systeme_type_piece` varchar(10) NOT NULL,
  `nom_type_piece` varchar(10) NOT NULL,
  PRIMARY KEY  (`id_type_piece`),
  UNIQUE KEY `nom_systeme_type_piece` (`nom_systeme_type_piece`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
