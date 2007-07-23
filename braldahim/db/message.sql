-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Lundi 23 Juillet 2007 à 16:21
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
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
  `contenu_message` varchar(1000) NOT NULL,
  PRIMARY KEY  (`id_message`),
  KEY `date_envoi_message` (`date_envoi_message`),
  KEY `id_fk_hobbit_message` (`id_fk_hobbit_message`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
