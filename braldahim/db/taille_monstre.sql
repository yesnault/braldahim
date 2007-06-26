-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mardi 26 Juin 2007 à 21:26
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
  `nom_taille_m_monstre` varchar(20) NOT NULL COMMENT 'Nom de la taille au masculin',
  `nom_taille_f_monstre` varchar(20) NOT NULL COMMENT 'Nom de la taille au féminin',
  `pourcentage_taille_monstre` int(11) NOT NULL COMMENT 'Pourcentage d''apparition',
  PRIMARY KEY  (`id_taille_monstre`),
  UNIQUE KEY `nom_taille_f_monstre` (`nom_taille_f_monstre`),
  UNIQUE KEY `nom_taille_m_monstre` (`nom_taille_m_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
