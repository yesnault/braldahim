-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Dim 30 Mars 2008 à 22:19
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_rang_communaute`
-- 

CREATE TABLE `type_rang_communaute` (
  `id_type_rang_communaute` int(11) NOT NULL auto_increment,
  `nom_type_rang_communaute` varchar(10) NOT NULL,
  PRIMARY KEY  (`id_type_rang_communaute`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Contenu de la table `type_rang_communaute`
-- 

