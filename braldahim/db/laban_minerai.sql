-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mardi 29 Mai 2007 à 21:59
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_minerai`
-- 

CREATE TABLE `laban_minerai` (
  `id_fk_type_laban_minerai` int(11) NOT NULL,
  `id_hobbit_laban_minerai` int(11) NOT NULL,
  `quantite_laban_minerai` int(11) default NULL,
  PRIMARY KEY  (`id_fk_type_laban_minerai`,`id_hobbit_laban_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
