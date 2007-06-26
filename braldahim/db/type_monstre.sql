-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mardi 26 Juin 2007 à 22:22
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_monstre`
-- 

CREATE TABLE `type_monstre` (
  `id_type_monstre` int(11) NOT NULL auto_increment,
  `nom_type_monstre` varchar(30) NOT NULL,
  `genre_type_monstre` enum('feminin','masculin') NOT NULL COMMENT 'Genre du monstre : masculin ou féminin',
  PRIMARY KEY  (`id_type_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
