-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Samedi 19 Mai 2007 à 00:42
-- Version du serveur: 5.0.33
-- Version de PHP: 5.2.0
-- 
-- Base de données: `braldahim`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `competence`
-- 

CREATE TABLE `competence` (
  `id` int(11) NOT NULL auto_increment,
  `nom_systeme_competence` varchar(255) NOT NULL default '',
  `nom_competence` varchar(255) NOT NULL default '',
  `description_competence` mediumtext NOT NULL,
  `niveau_requis_competence` int(11) NOT NULL default '0',
  `pi_cout_competence` int(11) NOT NULL default '0',
  `px_gain_competence` int(11) NOT NULL default '0',
  `pourcentage_max_competence` int(11) NOT NULL default '90',
  `pa_utilisation_competence` int(11) NOT NULL default '6',
  `type_competence` enum('basic','commun','metier') NOT NULL default 'basic',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `nom_competence` (`nom_competence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- 
-- Contenu de la table `competence`
-- 

INSERT INTO `competence` VALUES (1, 'marcher', 'Marcher', 'Et oui, çà arrive de marcher !', 0, 0, 0, 0, 1, 'commun');
INSERT INTO `competence` VALUES (2, 'decaler_dla', 'Decaler sa DLA', '', 0, 0, 0, 0, 0, 'basic');
INSERT INTO `competence` VALUES (3, 'gardiennage', 'Gardiennage', 'Description Gardiennage', 0, 0, 0, 0, 0, 'basic');

-- --------------------------------------------------------

-- 
-- Structure de la table `environnement`
-- 

CREATE TABLE `environnement` (
  `id` int(11) NOT NULL auto_increment,
  `nom_environnement` varchar(20) NOT NULL,
  `description_environnement` varchar(250) NOT NULL,
  `nom_systeme_environnement` varchar(20) NOT NULL,
  `image_environnement` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- 
-- Contenu de la table `environnement`
-- 

INSERT INTO `environnement` VALUES (1, 'Plaine', 'Description Plaine', 'plaine', '');
INSERT INTO `environnement` VALUES (2, 'Forêt', 'Description Forêt', 'foret', '');
INSERT INTO `environnement` VALUES (3, 'Marais', 'Description marais', 'marais', '');
INSERT INTO `environnement` VALUES (4, 'Montagne', 'Description Montagne', 'montagne', '');
INSERT INTO `environnement` VALUES (5, 'Caverne', 'Description Caverne', 'caverne', '');

-- --------------------------------------------------------

-- 
-- Structure de la table `gardiennage`
-- 

CREATE TABLE `gardiennage` (
  `id` int(11) NOT NULL auto_increment,
  `id_hobbit_gardiennage` int(11) NOT NULL,
  `id_gardien_gardiennage` int(11) NOT NULL,
  `date_debut_gardiennage` date NOT NULL,
  `date_fin_gardiennage` date NOT NULL,
  `nb_jours_gardiennage` int(11) NOT NULL,
  `commentaire_gardiennage` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `hobbit`
-- 

CREATE TABLE `hobbit` (
  `id` int(11) NOT NULL auto_increment,
  `nom_hobbit` varchar(20) NOT NULL,
  `password_hobbit` varchar(50) NOT NULL,
  `email_hobbit` varchar(100) NOT NULL,
  `etat_hobbit` int(11) NOT NULL,
  `sexe_hobbit` enum('feminin','masculin') NOT NULL,
  `x_hobbit` int(11) NOT NULL,
  `y_hobbit` int(1) NOT NULL,
  `date_debut_tour_hobbit` datetime NOT NULL,
  `date_fin_tour_hobbit` datetime NOT NULL,
  `duree_prochain_tour_hobbit` time NOT NULL,
  `duree_base_tour_hobbit` time NOT NULL,
  `duree_courant_tour_hobbit` time NOT NULL,
  `tour_position_hobbit` int(11) NOT NULL,
  `pa_hobbit` int(11) NOT NULL,
  `vue_base_hobbit` int(11) NOT NULL,
  `vue_bm_hobbit` int(11) NOT NULL,
  `force_base_hobbit` int(11) NOT NULL,
  `force_bm_hobbit` int(11) NOT NULL,
  `agilite_base_hobbit` int(11) NOT NULL,
  `agilite_bm_hobbit` int(11) NOT NULL,
  `sagesse_base_hobbit` int(11) NOT NULL,
  `sagesse_bm_hobbit` int(11) NOT NULL,
  `vigueur_base_hobbit` int(11) NOT NULL,
  `vigueur_bm_hobbit` int(11) NOT NULL,
  `regeneration_hobbit` int(11) NOT NULL,
  `px_hobbit` int(11) NOT NULL,
  `balance_faim_hobbit` int(11) NOT NULL,
  `armure_naturelle_hobbit` int(11) NOT NULL,
  `armure_equipement_hobbit` int(11) NOT NULL,
  `poids_transportable_hobbit` int(11) NOT NULL,
  `pv_hobbit` int(11) NOT NULL,
  `est_mort_hobbit` enum('oui','non') NOT NULL default 'non',
  `est_compte_actif_hobbit` enum('oui','non') NOT NULL default 'non',
  `date_creation_hobbit` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Tables des Hobbits' AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `hobbits_competences`
-- 

CREATE TABLE `hobbits_competences` (
  `id_hobbit_hcomp` int(11) NOT NULL default '0',
  `id_competence_hcomp` int(11) NOT NULL default '0',
  `pourcentage_hcomp` int(11) NOT NULL default '10',
  `date_gain_tour_hcomp` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_hobbit_hcomp`,`id_competence_hcomp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `hobbits_metiers`
-- 

CREATE TABLE `hobbits_metiers` (
  `id_hobbit_hmetier` int(11) NOT NULL,
  `id_metier_hmetier` int(11) NOT NULL,
  `est_actif_hmetier` enum('oui','non') NOT NULL,
  `date_apprentissage_hmetier` date NOT NULL,
  PRIMARY KEY  (`id_hobbit_hmetier`,`id_metier_hmetier`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `lieu`
-- 

CREATE TABLE `lieu` (
  `id` int(11) NOT NULL auto_increment,
  `nom_lieu` varchar(20) NOT NULL,
  `description_lieu` varchar(250) NOT NULL,
  `x_lieu` int(11) NOT NULL,
  `y_lieu` int(11) NOT NULL,
  `etat_lieu` int(11) NOT NULL,
  `id_fk_type_lieu` int(11) NOT NULL,
  `date_creation_lieu` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `metier`
-- 

CREATE TABLE `metier` (
  `id` int(11) NOT NULL auto_increment,
  `nom_metier` varchar(20) NOT NULL,
  `nom_systeme_metier` varchar(20) NOT NULL,
  `description_metier` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- 
-- Contenu de la table `metier`
-- 

INSERT INTO `metier` VALUES (1, 'Mineur', 'mineur', 'Description du métier mineur');
INSERT INTO `metier` VALUES (2, 'Chasseur', 'chasseur', 'Description du métier chasseur');
INSERT INTO `metier` VALUES (3, 'Bûcheron', 'bucheron', 'Description du métier Bûcheron');
INSERT INTO `metier` VALUES (4, 'Herboriste', 'herboriste', 'Description du métier Herboriste');
INSERT INTO `metier` VALUES (5, 'Forgeron', 'forgeron', 'Description du métier Forgeron');
INSERT INTO `metier` VALUES (6, 'Apothicaire', 'apothicaire', 'Description du métier Apothicaire');
INSERT INTO `metier` VALUES (7, 'Menuisier', 'menuisier', 'Description du métier menuisier');
INSERT INTO `metier` VALUES (8, 'Cuisiner', 'cuisinier', 'Description du métier Cuisinier');
INSERT INTO `metier` VALUES (9, 'Tanneur', 'tanneur', 'Description du métier Tanneur');
INSERT INTO `metier` VALUES (10, 'Guerrier', 'guerrier', 'Description du métier Guerrier');

-- --------------------------------------------------------

-- 
-- Structure de la table `type_lieu`
-- 

CREATE TABLE `type_lieu` (
  `id` int(11) NOT NULL,
  `nom_type_lieu` varchar(20) NOT NULL,
  `nom_systeme_type_lieu` varchar(20) NOT NULL,
  `description_type_lieu` varchar(250) NOT NULL,
  `niveau_min_type_lieu` int(2) NOT NULL,
  `pa_utilisation_type_lieu` int(1) NOT NULL,
  `est_alterable_type_lieu` enum('oui','non') NOT NULL,
  `est_franchissable_type_lieu` enum('oui','non') NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Contenu de la table `type_lieu`
-- 

INSERT INTO `type_lieu` VALUES (1, 'Ahenne Peheux', 'ahennepeheux', 'Description Ahenne Peheux', 0, 0, 'non', 'oui');

-- --------------------------------------------------------

-- 
-- Structure de la table `zone`
-- 

CREATE TABLE `zone` (
  `id` int(11) NOT NULL auto_increment,
  `id_fk_environnement_zone` int(11) NOT NULL,
  `nom_zone` varchar(100) NOT NULL,
  `description_zone` varchar(100) NOT NULL,
  `image_zone` varchar(100) NOT NULL,
  `x_min_zone` int(11) NOT NULL,
  `y_min_zone` int(11) NOT NULL,
  `x_max_zone` int(11) NOT NULL,
  `y_max_zone` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;
