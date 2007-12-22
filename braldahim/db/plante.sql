-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:18
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `plante`
-- 

CREATE TABLE `plante` (
  `id_plante` int(11) NOT NULL auto_increment,
  `id_fk_type_plante` int(11) NOT NULL,
  `x_plante` int(11) NOT NULL,
  `y_plante` int(11) NOT NULL,
  `partie_1_plante` int(11) NOT NULL,
  `partie_2_plante` int(11) default NULL,
  `partie_3_plante` int(11) default NULL,
  `partie_4_plante` int(11) default NULL,
  PRIMARY KEY  (`id_plante`),
  KEY `idx_x_plante_y_plante` (`x_plante`,`y_plante`),
  KEY `id_fk_type_plante` (`id_fk_type_plante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `plante`
-- 
ALTER TABLE `plante`
  ADD CONSTRAINT `plante_ibfk_1` FOREIGN KEY (`id_fk_type_plante`) REFERENCES `type_plante` (`id_type_plante`);
