-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 26 Mai 2007 à 15:19
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `competence`
-- 

CREATE TABLE `competence` (
  `id_competence` int(11) NOT NULL auto_increment,
  `nom_systeme_competence` varchar(255) NOT NULL default '',
  `nom_competence` varchar(255) NOT NULL default '',
  `description_competence` mediumtext NOT NULL,
  `niveau_requis_competence` int(11) NOT NULL default '0',
  `pi_cout_competence` int(11) NOT NULL default '0',
  `px_gain_competence` int(11) NOT NULL default '0',
  `balance_faim_competence` int(11) NOT NULL,
  `pourcentage_max_competence` int(11) NOT NULL default '90',
  `pa_utilisation_competence` int(11) NOT NULL default '6',
  `type_competence` enum('basic','commun','metier') NOT NULL default 'basic',
  `id_fk_metier_competence` int(11) default NULL,
  PRIMARY KEY  (`id_competence`),
  UNIQUE KEY `nom_competence` (`nom_competence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- 
-- Contenu de la table `competence`
-- 

INSERT INTO `competence` VALUES (1, 'marcher', 'Marcher', 'Et oui, çà arrive de marcher !', 0, 0, 0, -1, 0, 1, 'commun', NULL);
INSERT INTO `competence` VALUES (2, 'decalerdla', 'Decaler sa DLA', '', 0, 0, 0, 0, 0, 0, 'basic', NULL);
INSERT INTO `competence` VALUES (3, 'gardiennage', 'Gardiennage', 'Description Gardiennage', 0, 0, 0, 0, 0, 0, 'basic', NULL);
INSERT INTO `competence` VALUES (4, 'rechercheplante', 'Recherche de Plantes', 'description recherche de plantes', 0, 0, 1, -2, 90, 2, 'metier', 4);
INSERT INTO `competence` VALUES (5, 'cueillette', 'Cueillette', 'description cueillette', 0, 0, 2, -4, 90, 4, 'metier', 4);
