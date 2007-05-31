-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mardi 29 Mai 2007 à 23:56
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_partieplante`
-- 

CREATE TABLE `laban_partieplante` (
  `id_fk_type_laban_partieplante` int(11) NOT NULL,
  `id_hobbit_laban_partieplante` int(11) NOT NULL,
  `quantite_laban_partieplante` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_laban_partieplante`,`id_hobbit_laban_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
