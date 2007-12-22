-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:12
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

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
  `pourcentage_init_competence` int(11) NOT NULL,
  `pa_utilisation_competence` int(11) NOT NULL default '6',
  `type_competence` enum('basic','commun','metier') NOT NULL default 'basic',
  `id_fk_metier_competence` int(11) default NULL,
  PRIMARY KEY  (`id_competence`),
  UNIQUE KEY `nom_competence` (`nom_competence`),
  KEY `id_fk_metier_competence` (`id_fk_metier_competence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `competence`
-- 
ALTER TABLE `competence`
  ADD CONSTRAINT `competence_ibfk_1` FOREIGN KEY (`id_fk_metier_competence`) REFERENCES `metier` (`id_metier`);
