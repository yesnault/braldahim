-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:23
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_plante`
-- 

CREATE TABLE `type_plante` (
  `id_type_plante` int(11) NOT NULL auto_increment,
  `nom_type_plante` varchar(20) NOT NULL,
  `nom_systeme_type_plante` varchar(200) NOT NULL,
  `categorie_type_plante` enum('Arbre','Buisson','Fleur') NOT NULL,
  `id_fk_environnement_type_plante` int(11) NOT NULL,
  `id_fk_partieplante1_type_plante` int(11) NOT NULL,
  `id_fk_partieplante2_type_plante` int(11) default NULL,
  `id_fk_partieplante3_type_plante` int(11) default NULL,
  `id_fk_partieplante4_type_plante` int(11) default NULL,
  PRIMARY KEY  (`id_type_plante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;
