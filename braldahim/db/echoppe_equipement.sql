-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Dim 02 Mars 2008 à 13:22
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_equipement`
-- 

CREATE TABLE `echoppe_equipement` (
  `id_echoppe_equipement` int(11) NOT NULL auto_increment,
  `id_fk_echoppe_echoppe_equipement` int(11) NOT NULL,
  `id_fk_recette_echoppe_equipement` int(11) NOT NULL,
  `nb_runes_echoppe_equipement` int(11) NOT NULL,
  `type_vente_echoppe_equipement` enum('aucune','publique','hobbit') NOT NULL default 'aucune',
  `commentaire_vente_echoppe_equipement` varchar(300) default NULL,
  `id_fk_hobbit_vente_echoppe_equipement` int(11) default NULL,
  `unite_1_vente_echoppe_equipement` int(11) NOT NULL default '0',
  `unite_2_vente_echoppe_equipement` int(11) NOT NULL default '0',
  `unite_3_vente_echoppe_equipement` int(11) NOT NULL default '0',
  `prix_1_vente_echoppe_equipement` int(11) NOT NULL default '0',
  `prix_2_vente_echoppe_equipement` int(11) NOT NULL default '0',
  `prix_3_vente_echoppe_equipement` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_echoppe_equipement`),
  KEY `id_fk_echoppe_echoppe_equipement` (`id_fk_echoppe_echoppe_equipement`),
  KEY `id_fk_recette_echoppe_equipement` (`id_fk_recette_echoppe_equipement`),
  KEY `id_hobbit_vente_echoppe_equipement` (`id_fk_hobbit_vente_echoppe_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- 
-- Contenu de la table `echoppe_equipement`
-- 

INSERT INTO `echoppe_equipement` VALUES (1, 1, 43, 0, 'aucune', NULL, NULL, 0, 0, 0, 0, 0, 0);
INSERT INTO `echoppe_equipement` VALUES (2, 1, 613, 0, 'aucune', NULL, NULL, 0, 0, 0, 0, 0, 0);
INSERT INTO `echoppe_equipement` VALUES (3, 1, 613, 0, 'aucune', NULL, NULL, 0, 0, 0, 0, 0, 0);
INSERT INTO `echoppe_equipement` VALUES (4, 1, 613, 0, 'publique', NULL, NULL, 1, 0, 0, 99, 0, 0);
INSERT INTO `echoppe_equipement` VALUES (5, 1, 613, 0, 'aucune', NULL, NULL, 0, 0, 0, 0, 0, 0);
INSERT INTO `echoppe_equipement` VALUES (6, 1, 613, 4, 'aucune', NULL, NULL, 0, 0, 0, 0, 0, 0);

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `echoppe_equipement`
-- 
ALTER TABLE `echoppe_equipement`
  ADD CONSTRAINT `echoppe_equipement_ibfk_8` FOREIGN KEY (`id_fk_hobbit_vente_echoppe_equipement`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE SET NULL,
  ADD CONSTRAINT `echoppe_equipement_ibfk_6` FOREIGN KEY (`id_fk_echoppe_echoppe_equipement`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_equipement_ibfk_7` FOREIGN KEY (`id_fk_recette_echoppe_equipement`) REFERENCES `recette_equipements` (`id_recette_equipement`);
