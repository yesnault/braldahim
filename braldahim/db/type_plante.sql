-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mercredi 23 Mai 2007 à 20:49
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `type_plante`
-- 

CREATE TABLE `type_plante` (
  `id` int(11) NOT NULL auto_increment,
  `nom_type_plante` varchar(20) NOT NULL,
  `nom_systeme_type_plante` varchar(200) NOT NULL,
  `categorie_type_plante` enum('Arbre','Buisson','Fleur') NOT NULL,
  `id_fk_environnement_type_plante` int(11) NOT NULL,
  `nom_partie_1_type_plante` varchar(10) NOT NULL,
  `nom_partie_2_type_plante` varchar(10) NOT NULL,
  `nom_partie_3_type_plante` varchar(10) NOT NULL,
  `nom_partie_4_type_plante` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;
