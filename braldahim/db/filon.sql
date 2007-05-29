-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mardi 29 Mai 2007 à 20:13
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `filon`
-- 

DROP TABLE IF EXISTS `filon`;
CREATE TABLE `filon` (
  `id_filon` int(11) NOT NULL auto_increment,
  `id_fk_type_filon` int(11) NOT NULL,
  `x_filon` int(11) NOT NULL,
  `y_filon` int(11) NOT NULL,
  `quantite_restante_filon` INT NOT NULL ,
  `quantite_max_filon` INT NOT NULL ,
  PRIMARY KEY  (`id_filon`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
