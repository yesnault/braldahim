-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Ven 07 Mars 2008 à 21:32
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `nom`
-- 

CREATE TABLE `nom` (
  `id_nom` int(11) NOT NULL auto_increment,
  `nom` varchar(30) NOT NULL,
  PRIMARY KEY  (`id_nom`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=61 ;
