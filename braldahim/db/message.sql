-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 22 Décembre 2007 à 20:17
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `message`
-- 

CREATE TABLE `message` (
  `id_message` int(11) NOT NULL auto_increment,
  `id_fk_hobbit_message` int(11) NOT NULL,
  `id_fk_type_message` int(11) NOT NULL,
  `date_envoi_message` datetime NOT NULL,
  `date_lecture_message` datetime default NULL,
  `expediteur_message` int(11) NOT NULL,
  `destinataires_message` varchar(1000) NOT NULL,
  `copies_message` varchar(1000) default NULL,
  `titre_message` varchar(80) NOT NULL,
  `contenu_message` text NOT NULL,
  PRIMARY KEY  (`id_message`),
  KEY `date_envoi_message` (`date_envoi_message`),
  KEY `id_fk_hobbit_message` (`id_fk_hobbit_message`),
  KEY `id_fk_type_message` (`id_fk_type_message`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `message`
-- 
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`id_fk_type_message`) REFERENCES `type_message` (`id_type_message`),
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`id_fk_hobbit_message`) REFERENCES `hobbit` (`id_hobbit`) ON DELETE CASCADE;
