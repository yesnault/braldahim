-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 03 Novembre 2007 à 16:51
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_chasse`
-- 

CREATE TABLE `laban_chasse` (
  `id_hobbit_laban_chasse` int(11) NOT NULL,
  `quantite_viande_laban_chasse` int(11) default NULL,
  `quantite_fourrure_laban_chasse` int(11) default NULL,
  `quantite_peau_laban_chasse` int(11) default NULL,
  PRIMARY KEY  (`id_hobbit_laban_chasse`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
