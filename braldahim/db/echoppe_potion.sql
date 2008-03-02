-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Dim 02 Mars 2008 à 13:21
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_potion`
-- 

CREATE TABLE `echoppe_potion` (
  `id_echoppe_potion` int(11) NOT NULL auto_increment,
  `id_fk_echoppe_echoppe_potion` int(11) NOT NULL,
  `id_fk_type_qualite_echoppe_potion` int(11) NOT NULL,
  `niveau_echoppe_potion` int(11) NOT NULL,
  `id_fk_type_potion_echoppe_potion` int(11) NOT NULL,
  `type_vente_echoppe_potion` enum('aucune','publique','hobbit') NOT NULL default 'aucune',
  `commentaire_vente_echoppe_potion` varchar(300) default NULL,
  `id_fk_hobbit_vente_echoppe_potion` int(11) default NULL,
  `unite_1_vente_echoppe_potion` int(11) NOT NULL default '0',
  `unite_2_vente_echoppe_potion` int(11) NOT NULL default '0',
  `unite_3_vente_echoppe_potion` int(11) NOT NULL default '0',
  `prix_1_vente_echoppe_potion` int(11) NOT NULL default '0',
  `prix_2_vente_echoppe_potion` int(11) NOT NULL default '0',
  `prix_3_vente_echoppe_potion` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_echoppe_potion`),
  KEY `id_fk_echoppe_potion` (`id_fk_echoppe_echoppe_potion`),
  KEY `id_fk_qualite_echoppe_potion` (`id_fk_type_qualite_echoppe_potion`),
  KEY `id_fk_type_potion_echoppe_potion` (`id_fk_type_potion_echoppe_potion`),
  KEY `id_fk_hobbit_vente_echoppe_potion` (`id_fk_hobbit_vente_echoppe_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Contenu de la table `echoppe_potion`
-- 


-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `echoppe_potion`
-- 
ALTER TABLE `echoppe_potion`
  ADD CONSTRAINT `echoppe_potion_ibfk_24` FOREIGN KEY (`id_fk_hobbit_vente_echoppe_potion`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE SET NULL,
  ADD CONSTRAINT `echoppe_potion_ibfk_21` FOREIGN KEY (`id_fk_echoppe_echoppe_potion`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_potion_ibfk_22` FOREIGN KEY (`id_fk_type_qualite_echoppe_potion`) REFERENCES `type_qualite` (`id_type_qualite`),
  ADD CONSTRAINT `echoppe_potion_ibfk_23` FOREIGN KEY (`id_fk_type_potion_echoppe_potion`) REFERENCES `type_potion` (`id_type_potion`);
