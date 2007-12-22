-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:16
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_partieplante`
-- 

CREATE TABLE `laban_partieplante` (
  `id_fk_type_laban_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_laban_partieplante` int(11) NOT NULL,
  `id_fk_hobbit_laban_partieplante` int(11) NOT NULL,
  `quantite_laban_partieplante` int(11) NOT NULL,
  `quantite_preparee_laban_partieplante` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_laban_partieplante`,`id_fk_type_plante_laban_partieplante`,`id_fk_hobbit_laban_partieplante`),
  KEY `id_fk_type_plante_laban_partieplante` (`id_fk_type_plante_laban_partieplante`),
  KEY `id_fk_hobbit_laban_partieplante` (`id_fk_hobbit_laban_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `laban_partieplante`
-- 
ALTER TABLE `laban_partieplante`
  ADD CONSTRAINT `laban_partieplante_ibfk_3` FOREIGN KEY (`id_fk_hobbit_laban_partieplante`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_laban_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `laban_partieplante_ibfk_2` FOREIGN KEY (`id_fk_type_plante_laban_partieplante`) REFERENCES `type_plante` (`id_type_plante`);
