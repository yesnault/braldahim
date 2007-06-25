-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Lundi 25 Juin 2007 à 21:59
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `taille_monstre`
-- 

CREATE TABLE `taille_monstre` (
  `id_taille_monstre` int(11) NOT NULL auto_increment,
  `nom_taille_monstre` varchar(20) NOT NULL,
  PRIMARY KEY  (`id_taille_monstre`),
  UNIQUE KEY `nom_taille_monstre` (`nom_taille_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
