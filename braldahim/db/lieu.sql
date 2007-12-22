-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:17
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `lieu`
-- 

CREATE TABLE `lieu` (
  `id_lieu` int(11) NOT NULL auto_increment,
  `nom_lieu` varchar(30) NOT NULL,
  `description_lieu` mediumtext NOT NULL,
  `x_lieu` int(11) NOT NULL,
  `y_lieu` int(11) NOT NULL,
  `etat_lieu` int(11) NOT NULL,
  `id_fk_type_lieu` int(11) NOT NULL,
  `id_fk_ville_lieu` int(11) default NULL,
  `date_creation_lieu` datetime NOT NULL,
  PRIMARY KEY  (`id_lieu`),
  UNIQUE KEY `xy_lieu` (`x_lieu`,`y_lieu`),
  KEY `id_fk_type_lieu` (`id_fk_type_lieu`),
  KEY `id_fk_ville_lieu` (`id_fk_ville_lieu`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=74 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `lieu`
-- 
ALTER TABLE `lieu`
  ADD CONSTRAINT `lieu_ibfk_4` FOREIGN KEY (`id_fk_ville_lieu`) REFERENCES `ville` (`id_ville`),
  ADD CONSTRAINT `lieu_ibfk_3` FOREIGN KEY (`id_fk_type_lieu`) REFERENCES `type_lieu` (`id_type_lieu`);
