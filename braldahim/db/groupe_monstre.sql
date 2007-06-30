-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 30 Juin 2007 à 16:46
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `groupe_monstre`
-- 

CREATE TABLE `groupe_monstre` (
  `id_groupe_monstre` int(11) NOT NULL auto_increment,
  `id_fk_type_groupe_monstre` int(11) NOT NULL,
  `date_creation_groupe_monstre` datetime NOT NULL,
  `id_cible_groupe_monstre` int(11) default NULL,
  `nb_membres_max_groupe_monstre` int(11) NOT NULL,
  `nb_membres_restant_groupe_monstre` int(11) NOT NULL,
  `phase_tactique_groupe_monstre` int(11) NOT NULL,
  `id_role_a_groupe_monstre` int(11) default NULL,
  `id_role_b_groupe_monstre` int(11) default NULL,
  PRIMARY KEY  (`id_groupe_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
