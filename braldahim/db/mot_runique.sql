-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Ven 14 Mars 2008 à 23:54
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `mot_runique`
-- 

CREATE TABLE `mot_runique` (
  `id_mot_runique` int(11) NOT NULL auto_increment,
  `id_fk_type_piece_mot_runique` int(11) NOT NULL,
  `suffixe_mot_runique` varchar(15) NOT NULL,
  `id_fk_type_rune_1_mot_runique` int(11) NOT NULL,
  `id_fk_type_rune_2_mot_runique` int(11) default NULL,
  `id_fk_type_rune_3_mot_runique` int(11) default NULL,
  `id_fk_type_rune_4_mot_runique` int(11) default NULL,
  `id_fk_type_rune_5_mot_runique` int(11) default NULL,
  `id_fk_type_rune_6_mot_runique` int(11) default NULL,
  `effet_mot_runique` varchar(300) NOT NULL,
  PRIMARY KEY  (`id_mot_runique`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
