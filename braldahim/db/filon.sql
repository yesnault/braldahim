-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:14
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `filon`
-- 

CREATE TABLE `filon` (
  `id_filon` int(11) NOT NULL auto_increment,
  `id_fk_type_minerai_filon` int(11) NOT NULL,
  `x_filon` int(11) NOT NULL,
  `y_filon` int(11) NOT NULL,
  `quantite_restante_filon` int(11) NOT NULL,
  `quantite_max_filon` int(11) NOT NULL,
  PRIMARY KEY  (`id_filon`),
  KEY `idx_x_filon_y_filon` (`x_filon`,`y_filon`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `filon`
-- 
ALTER TABLE `filon`
  ADD CONSTRAINT `filon_ibfk_1` FOREIGN KEY (`x_filon`) REFERENCES `type_minerai` (`id_type_minerai`);
