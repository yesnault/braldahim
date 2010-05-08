-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Sam 19 Septembre 2009 à 11:39
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim_beta`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `ancien_braldun`
-- 

CREATE TABLE `ancien_braldun` (
  `id_ancien_braldun` int(11) NOT NULL auto_increment,
  `id_braldun_ancien_braldun` int(11) NOT NULL,
  `nom_ancien_braldun` varchar(20) NOT NULL,
  `prenom_ancien_braldun` varchar(23) NOT NULL,
  `id_fk_nom_initial_ancien_braldun` int(11) NOT NULL,
  `email_ancien_braldun` varchar(100) NOT NULL,
  `sexe_ancien_braldun` enum('feminin','masculin') NOT NULL,
  `niveau_ancien_braldun` int(11) NOT NULL default '0',
  `nb_ko_ancien_braldun` int(11) NOT NULL default '0',
  `nb_braldun_ko_ancien_braldun` int(11) NOT NULL default '0',
  `nb_plaque_ancien_braldun` int(11) NOT NULL,
  `nb_braldun_plaquage_ancien_braldun` int(11) NOT NULL,
  `nb_monstre_kill_ancien_braldun` int(11) NOT NULL,
  `id_fk_mere_ancien_braldun` int(11) default NULL,
  `id_fk_pere_ancien_braldun` int(11) default NULL,
  `metiers_ancien_braldun` varchar(1000) NOT NULL,
  `titres_ancien_braldun` varchar(1000) NOT NULL,
  `distinctions_ancien_braldun` varchar(1000) default NULL,
  `date_creation_ancien_braldun` datetime NOT NULL,
  PRIMARY KEY  (`id_ancien_braldun`),
  UNIQUE KEY `id_braldun_ancien_braldun_2` (`id_braldun_ancien_braldun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tables des Anciens Bralduns' AUTO_INCREMENT=70 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `batch`
-- 

CREATE TABLE `batch` (
  `id_batch` int(11) NOT NULL auto_increment,
  `type_batch` varchar(20) NOT NULL,
  `date_debut_batch` datetime NOT NULL,
  `date_fin_batch` datetime default NULL,
  `etat_batch` varchar(10) NOT NULL,
  `message_batch` mediumtext,
  PRIMARY KEY  (`id_batch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=7232 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `bosquet`
-- 

CREATE TABLE `bosquet` (
  `id_bosquet` int(11) NOT NULL auto_increment,
  `id_fk_type_bosquet_bosquet` int(11) NOT NULL,
  `x_bosquet` int(11) NOT NULL,
  `y_bosquet` int(11) NOT NULL,
  `z_bosquet` int(11) NOT NULL default '0',
  `quantite_restante_bosquet` int(11) NOT NULL,
  `quantite_max_bosquet` int(11) NOT NULL,
  PRIMARY KEY  (`id_bosquet`),
  KEY `id_fk_type_bosquet_bosquet` (`id_fk_type_bosquet_bosquet`),
  KEY `idx_x_bosquet_y_bosquet` (`x_bosquet`,`y_bosquet`,`z_bosquet`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=31995 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `boutique_bois`
-- 

CREATE TABLE `boutique_bois` (
  `id_boutique_bois` int(11) NOT NULL auto_increment,
  `date_achat_boutique_bois` datetime NOT NULL,
  `id_fk_lieu_boutique_bois` int(11) NOT NULL,
  `id_fk_braldun_boutique_bois` int(11) NOT NULL,
  `quantite_rondin_boutique_bois` int(11) NOT NULL,
  `prix_unitaire_boutique_bois` int(11) NOT NULL,
  `id_fk_region_boutique_bois` int(11) NOT NULL,
  `action_boutique_bois` enum('reprise','vente') NOT NULL,
  PRIMARY KEY  (`id_boutique_bois`),
  KEY `id_fk_braldun_boutique_bois` (`id_fk_braldun_boutique_bois`),
  KEY `id_fk_lieu_boutique_bois` (`id_fk_lieu_boutique_bois`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `boutique_minerai`
-- 

CREATE TABLE `boutique_minerai` (
  `id_boutique_minerai` int(11) NOT NULL auto_increment,
  `date_achat_boutique_minerai` datetime NOT NULL,
  `id_fk_type_boutique_minerai` int(11) NOT NULL,
  `id_fk_lieu_boutique_minerai` int(11) NOT NULL,
  `id_fk_braldun_boutique_minerai` int(11) NOT NULL,
  `quantite_brut_boutique_minerai` int(11) NOT NULL default '0',
  `prix_unitaire_boutique_minerai` int(11) NOT NULL,
  `id_fk_region_boutique_minerai` int(11) NOT NULL,
  `action_boutique_minerai` enum('reprise','vente') NOT NULL,
  PRIMARY KEY  (`id_boutique_minerai`),
  KEY `id_fk_lieu_laban_minerai` (`id_fk_lieu_boutique_minerai`),
  KEY `id_fk_braldun_boutique_minerai` (`id_fk_braldun_boutique_minerai`),
  KEY `id_fk_region_boutique_minerai` (`id_fk_region_boutique_minerai`),
  KEY `id_fk_type_boutique_minerai` (`id_fk_type_boutique_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `boutique_partieplante`
-- 

CREATE TABLE `boutique_partieplante` (
  `id_boutique_partieplante` int(11) NOT NULL auto_increment,
  `date_achat_boutique_partieplante` datetime NOT NULL,
  `id_fk_type_boutique_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_boutique_partieplante` int(11) NOT NULL,
  `id_fk_lieu_boutique_partieplante` int(11) NOT NULL,
  `id_fk_braldun_boutique_partieplante` int(11) NOT NULL,
  `quantite_brut_boutique_partieplante` int(11) NOT NULL,
  `prix_unitaire_boutique_partieplante` int(11) NOT NULL,
  `id_fk_region_boutique_partieplante` int(11) NOT NULL,
  `action_boutique_partieplante` enum('reprise','vente') NOT NULL,
  PRIMARY KEY  (`id_boutique_partieplante`),
  KEY `id_fk_type_plante_boutique_partieplante` (`id_fk_type_plante_boutique_partieplante`),
  KEY `id_fk_lieu_boutique_partieplante` (`id_fk_lieu_boutique_partieplante`),
  KEY `id_fk_braldun_boutique_partieplante` (`id_fk_braldun_boutique_partieplante`),
  KEY `id_fk_region_boutique_partieplante` (`id_fk_region_boutique_partieplante`),
  KEY `id_fk_type_boutique_partieplante` (`id_fk_type_boutique_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `boutique_peau`
-- 

CREATE TABLE `boutique_peau` (
  `id_boutique_peau` int(11) NOT NULL auto_increment,
  `date_achat_boutique_peau` datetime NOT NULL,
  `id_fk_lieu_boutique_peau` int(11) NOT NULL,
  `id_fk_braldun_boutique_peau` int(11) NOT NULL,
  `quantite_peau_boutique_peau` int(11) NOT NULL,
  `prix_unitaire_boutique_peau` int(11) NOT NULL,
  `id_fk_region_boutique_peau` int(11) NOT NULL,
  `action_boutique_peau` enum('reprise','vente') NOT NULL,
  PRIMARY KEY  (`id_boutique_peau`),
  KEY `id_fk_braldun_boutique_peau` (`id_fk_braldun_boutique_peau`),
  KEY `id_fk_lieu_boutique_peau` (`id_fk_lieu_boutique_peau`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `boutique_tabac`
-- 

CREATE TABLE `boutique_tabac` (
  `id_boutique_tabac` int(11) NOT NULL auto_increment,
  `date_achat_boutique_tabac` datetime NOT NULL,
  `id_fk_type_boutique_tabac` int(11) NOT NULL,
  `id_fk_lieu_boutique_tabac` int(11) NOT NULL,
  `id_fk_braldun_boutique_tabac` int(11) NOT NULL,
  `quantite_feuille_boutique_tabac` int(11) NOT NULL default '0',
  `prix_unitaire_boutique_tabac` int(11) NOT NULL,
  `id_fk_region_boutique_tabac` int(11) NOT NULL,
  `action_boutique_tabac` enum('reprise','vente') NOT NULL,
  PRIMARY KEY  (`id_boutique_tabac`),
  KEY `id_fk_lieu_laban_tabac` (`id_fk_lieu_boutique_tabac`),
  KEY `id_fk_braldun_boutique_tabac` (`id_fk_braldun_boutique_tabac`),
  KEY `id_fk_region_boutique_tabac` (`id_fk_region_boutique_tabac`),
  KEY `id_fk_type_boutique_tabac` (`id_fk_type_boutique_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `carnet`
-- 

CREATE TABLE `carnet` (
  `id_carnet` int(11) NOT NULL,
  `id_fk_braldun_carnet` int(11) NOT NULL,
  `texte_carnet` mediumtext NOT NULL,
  PRIMARY KEY  (`id_carnet`,`id_fk_braldun_carnet`),
  KEY `id_fk_braldun_carnet` (`id_fk_braldun_carnet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `charrette`
-- 

CREATE TABLE `charrette` (
  `id_charrette` int(11) NOT NULL,
  `id_fk_braldun_charrette` int(11) default NULL,
  `quantite_rondin_charrette` int(11) NOT NULL,
  `x_charrette` int(11) default NULL,
  `y_charrette` int(11) default NULL,
  `z_charrette` int(11) NOT NULL default '0',
  `quantite_viande_charrette` int(11) NOT NULL,
  `quantite_peau_charrette` int(11) NOT NULL,
  `quantite_viande_preparee_charrette` int(11) NOT NULL,
  `quantite_ration_charrette` int(11) NOT NULL,
  `quantite_cuir_charrette` int(11) NOT NULL,
  `quantite_fourrure_charrette` int(11) NOT NULL,
  `quantite_planche_charrette` int(11) NOT NULL,
  `quantite_castar_charrette` int(11) NOT NULL,
  `durabilite_max_charrette` int(11) NOT NULL default '2000',
  `durabilite_actuelle_charrette` int(11) NOT NULL default '2000',
  `poids_transportable_charrette` float NOT NULL default '30',
  `poids_transporte_charrette` float NOT NULL,
  PRIMARY KEY  (`id_charrette`),
  UNIQUE KEY `id_fk_braldun_charrette` (`id_fk_braldun_charrette`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `charrette_aliment`
-- 

CREATE TABLE `charrette_aliment` (
  `id_charrette_aliment` int(11) NOT NULL,
  `id_fk_charrette_aliment` int(11) NOT NULL,
  `id_fk_type_charrette_aliment` int(11) NOT NULL,
  `id_fk_type_qualite_charrette_aliment` int(11) NOT NULL,
  `bbdf_charrette_aliment` int(11) NOT NULL,
  PRIMARY KEY  (`id_charrette_aliment`),
  KEY `id_fk_type_charrette_aliment` (`id_fk_type_charrette_aliment`),
  KEY `id_fk_type_qualite_charrette_aliment` (`id_fk_type_qualite_charrette_aliment`),
  KEY `id_fk_charrette_aliment` (`id_fk_charrette_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `charrette_equipement`
-- 

CREATE TABLE `charrette_equipement` (
  `id_charrette_equipement` int(11) NOT NULL,
  `id_fk_charrette_equipement` int(11) NOT NULL,
  PRIMARY KEY  (`id_charrette_equipement`),
  KEY `id_fk_charrette_equipement` (`id_fk_charrette_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `charrette_materiel`
-- 

CREATE TABLE `charrette_materiel` (
  `id_charrette_materiel` int(11) NOT NULL,
  `id_fk_charrette_materiel` int(11) NOT NULL,
  PRIMARY KEY  (`id_charrette_materiel`),
  KEY `id_fk_charrette_materiel` (`id_fk_charrette_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `charrette_materiel_assemble`
-- 

CREATE TABLE `charrette_materiel_assemble` (
  `id_charrette_materiel_assemble` int(11) NOT NULL,
  `id_materiel_materiel_assemble` int(11) NOT NULL,
  PRIMARY KEY  (`id_charrette_materiel_assemble`,`id_materiel_materiel_assemble`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `charrette_minerai`
-- 

CREATE TABLE `charrette_minerai` (
  `id_fk_type_charrette_minerai` int(11) NOT NULL,
  `id_fk_charrette_minerai` int(11) NOT NULL,
  `quantite_brut_charrette_minerai` int(11) default '0',
  `quantite_lingots_charrette_minerai` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_charrette_minerai`,`id_fk_charrette_minerai`),
  KEY `id_fk_charrette_minerai` (`id_fk_charrette_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `charrette_munition`
-- 

CREATE TABLE `charrette_munition` (
  `id_fk_type_charrette_munition` int(11) NOT NULL,
  `id_fk_charrette_munition` int(11) NOT NULL,
  `quantite_charrette_munition` int(11) default '0',
  PRIMARY KEY  (`id_fk_type_charrette_munition`,`id_fk_charrette_munition`),
  KEY `id_fk_charrette_munition` (`id_fk_charrette_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `charrette_partieplante`
-- 

CREATE TABLE `charrette_partieplante` (
  `id_fk_type_charrette_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_charrette_partieplante` int(11) NOT NULL,
  `id_fk_charrette_partieplante` int(11) NOT NULL,
  `quantite_charrette_partieplante` int(11) NOT NULL,
  `quantite_preparee_charrette_partieplante` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_charrette_partieplante`,`id_fk_type_plante_charrette_partieplante`,`id_fk_charrette_partieplante`),
  KEY `id_fk_type_plante_charrette_partieplante` (`id_fk_type_plante_charrette_partieplante`),
  KEY `id_fk_charrette_partieplante` (`id_fk_charrette_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `charrette_potion`
-- 

CREATE TABLE `charrette_potion` (
  `id_charrette_potion` int(11) NOT NULL,
  `id_fk_charrette_potion` int(11) NOT NULL,
  PRIMARY KEY  (`id_charrette_potion`),
  KEY `id_fk_charrette_potion` (`id_fk_charrette_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `charrette_rune`
-- 

CREATE TABLE `charrette_rune` (
  `id_fk_charrette_rune` int(11) NOT NULL,
  `id_rune_charrette_rune` int(11) NOT NULL,
  PRIMARY KEY  (`id_rune_charrette_rune`),
  KEY `id_fk_charrette_rune` (`id_fk_charrette_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `charrette_tabac`
-- 

CREATE TABLE `charrette_tabac` (
  `id_fk_type_charrette_tabac` int(11) NOT NULL,
  `id_fk_charrette_tabac` int(11) NOT NULL,
  `quantite_feuille_charrette_tabac` int(11) default '0',
  PRIMARY KEY  (`id_fk_type_charrette_tabac`,`id_fk_charrette_tabac`),
  KEY `id_fk_charrette_tabac` (`id_fk_charrette_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `coffre`
-- 

CREATE TABLE `coffre` (
  `id_fk_braldun_coffre` int(11) NOT NULL,
  `quantite_viande_coffre` int(11) NOT NULL default '0',
  `quantite_peau_coffre` int(11) NOT NULL default '0',
  `quantite_viande_preparee_coffre` int(11) NOT NULL default '0',
  `quantite_ration_coffre` int(11) NOT NULL default '0',
  `quantite_cuir_coffre` int(11) NOT NULL default '0',
  `quantite_fourrure_coffre` int(11) NOT NULL default '0',
  `quantite_planche_coffre` int(11) NOT NULL default '0',
  `quantite_castar_coffre` int(11) NOT NULL default '0',
  `quantite_rondin_coffre` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_braldun_coffre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `coffre_aliment`
-- 

CREATE TABLE `coffre_aliment` (
  `id_coffre_aliment` int(11) NOT NULL,
  `id_fk_type_coffre_aliment` int(11) NOT NULL,
  `id_fk_braldun_coffre_aliment` int(11) NOT NULL,
  `id_fk_type_qualite_coffre_aliment` int(11) NOT NULL,
  `bbdf_coffre_aliment` int(11) NOT NULL,
  PRIMARY KEY  (`id_coffre_aliment`),
  KEY `id_fk_type_coffre_aliment` (`id_fk_type_coffre_aliment`),
  KEY `id_fk_braldun_coffre_aliment` (`id_fk_braldun_coffre_aliment`),
  KEY `id_fk_type_qualite_coffre_aliment` (`id_fk_type_qualite_coffre_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `coffre_equipement`
-- 

CREATE TABLE `coffre_equipement` (
  `id_coffre_equipement` int(11) NOT NULL,
  `id_fk_braldun_coffre_equipement` int(11) NOT NULL,
  PRIMARY KEY  (`id_coffre_equipement`),
  KEY `id_fk_braldun_coffre_equipement` (`id_fk_braldun_coffre_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `coffre_materiel`
-- 

CREATE TABLE `coffre_materiel` (
  `id_coffre_materiel` int(11) NOT NULL,
  `id_fk_braldun_coffre_materiel` int(11) NOT NULL,
  PRIMARY KEY  (`id_coffre_materiel`),
  KEY `id_fk_braldun_coffre_materiel` (`id_fk_braldun_coffre_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `coffre_minerai`
-- 

CREATE TABLE `coffre_minerai` (
  `id_fk_type_coffre_minerai` int(11) NOT NULL,
  `id_fk_braldun_coffre_minerai` int(11) NOT NULL,
  `quantite_brut_coffre_minerai` int(11) default '0',
  `quantite_lingots_coffre_minerai` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_coffre_minerai`,`id_fk_braldun_coffre_minerai`),
  KEY `id_fk_braldun_coffre_minerai` (`id_fk_braldun_coffre_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `coffre_munition`
-- 

CREATE TABLE `coffre_munition` (
  `id_fk_type_coffre_munition` int(11) NOT NULL,
  `id_fk_braldun_coffre_munition` int(11) NOT NULL,
  `quantite_coffre_munition` int(11) default '0',
  PRIMARY KEY  (`id_fk_type_coffre_munition`,`id_fk_braldun_coffre_munition`),
  KEY `id_fk_braldun_coffre_munition` (`id_fk_braldun_coffre_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `coffre_partieplante`
-- 

CREATE TABLE `coffre_partieplante` (
  `id_fk_type_coffre_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_coffre_partieplante` int(11) NOT NULL,
  `id_fk_braldun_coffre_partieplante` int(11) NOT NULL,
  `quantite_coffre_partieplante` int(11) NOT NULL,
  `quantite_preparee_coffre_partieplante` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_coffre_partieplante`,`id_fk_type_plante_coffre_partieplante`,`id_fk_braldun_coffre_partieplante`),
  KEY `id_fk_type_plante_coffre_partieplante` (`id_fk_type_plante_coffre_partieplante`),
  KEY `id_fk_braldun_coffre_partieplante` (`id_fk_braldun_coffre_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `coffre_potion`
-- 

CREATE TABLE `coffre_potion` (
  `id_coffre_potion` int(11) NOT NULL,
  `id_fk_braldun_coffre_potion` int(11) NOT NULL,
  PRIMARY KEY  (`id_coffre_potion`),
  KEY `id_fk_braldun_coffre_potion` (`id_fk_braldun_coffre_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `coffre_rune`
-- 

CREATE TABLE `coffre_rune` (
  `id_fk_braldun_coffre_rune` int(11) NOT NULL,
  `id_rune_coffre_rune` int(11) NOT NULL,
  PRIMARY KEY  (`id_rune_coffre_rune`),
  KEY `id_fk_braldun_coffre_rune` (`id_fk_braldun_coffre_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `coffre_tabac`
-- 

CREATE TABLE `coffre_tabac` (
  `id_fk_type_coffre_tabac` int(11) NOT NULL,
  `id_fk_braldun_coffre_tabac` int(11) NOT NULL,
  `quantite_feuille_coffre_tabac` int(11) default '0',
  PRIMARY KEY  (`id_fk_type_coffre_tabac`,`id_fk_braldun_coffre_tabac`),
  KEY `id_fk_braldun_coffre_tabac` (`id_fk_braldun_coffre_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `communaute`
-- 

CREATE TABLE `communaute` (
  `id_communaute` int(11) NOT NULL auto_increment,
  `nom_communaute` varchar(40) NOT NULL,
  `date_creation_communaute` datetime NOT NULL,
  `id_fk_braldun_gestionnaire_communaute` int(11) NOT NULL,
  `description_communaute` mediumtext,
  `site_web_communaute` varchar(255) default NULL,
  PRIMARY KEY  (`id_communaute`),
  UNIQUE KEY `id_fk_braldun_createur_communaute` (`id_fk_braldun_gestionnaire_communaute`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `competence`
-- 

CREATE TABLE `competence` (
  `id_competence` int(11) NOT NULL auto_increment,
  `nom_systeme_competence` varchar(255) NOT NULL default '',
  `nom_competence` varchar(255) NOT NULL default '',
  `description_competence` mediumtext NOT NULL,
  `niveau_requis_competence` int(11) NOT NULL default '0',
  `pi_cout_competence` int(11) NOT NULL default '0',
  `px_gain_competence` int(11) NOT NULL default '0',
  `balance_faim_competence` int(11) NOT NULL,
  `pourcentage_max_competence` int(11) NOT NULL default '90',
  `pourcentage_init_competence` int(11) NOT NULL,
  `pa_utilisation_competence` int(11) NOT NULL default '6',
  `pa_manquee_competence` int(11) NOT NULL default '0',
  `type_competence` enum('basic','commun','metier','soule') NOT NULL default 'basic',
  `id_fk_metier_competence` int(11) default NULL,
  `id_fk_type_tabac_competence` int(11) default NULL,
  `ordre_competence` int(11) NOT NULL,
  PRIMARY KEY  (`id_competence`),
  KEY `id_fk_metier_competence` (`id_fk_metier_competence`),
  KEY `id_fk_type_tabac_competence` (`id_fk_type_tabac_competence`),
  KEY `ordre_competence` (`ordre_competence`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=62 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `couple`
-- 

CREATE TABLE `couple` (
  `id_fk_m_braldun_couple` int(11) NOT NULL,
  `id_fk_f_braldun_couple` int(11) NOT NULL,
  `date_creation_couple` datetime NOT NULL,
  `nb_enfants_couple` int(11) NOT NULL,
  `est_valide_couple` enum('oui','non') NOT NULL default 'oui',
  PRIMARY KEY  (`id_fk_m_braldun_couple`,`id_fk_f_braldun_couple`),
  UNIQUE KEY `id_fk_f_braldun_couple` (`id_fk_f_braldun_couple`),
  UNIQUE KEY `id_fk_m_braldun_couple` (`id_fk_m_braldun_couple`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `creation_bosquets`
-- 

CREATE TABLE `creation_bosquets` (
  `id_fk_type_bosquet_creation_bosquets` int(11) NOT NULL,
  `id_fk_environnement_creation_bosquets` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_bosquet_creation_bosquets`,`id_fk_environnement_creation_bosquets`),
  KEY `id_fk_environnement_creation_filons` (`id_fk_environnement_creation_bosquets`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `creation_minerais`
-- 

CREATE TABLE `creation_minerais` (
  `id_fk_type_minerai_creation_minerais` int(11) NOT NULL,
  `id_fk_environnement_creation_minerais` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_minerai_creation_minerais`,`id_fk_environnement_creation_minerais`),
  KEY `id_fk_environnement_creation_filons` (`id_fk_environnement_creation_minerais`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `creation_monstres`
-- 

CREATE TABLE `creation_monstres` (
  `id_fk_type_monstre_creation_monstres` int(11) NOT NULL,
  `id_fk_environnement_creation_monstres` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_monstre_creation_monstres`,`id_fk_environnement_creation_monstres`),
  KEY `id_fk_environnement_creation_filons` (`id_fk_environnement_creation_monstres`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `creation_plantes`
-- 

CREATE TABLE `creation_plantes` (
  `id_fk_type_plante_creation_plantes` int(11) NOT NULL,
  `id_fk_environnement_creation_plantes` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_plante_creation_plantes`,`id_fk_environnement_creation_plantes`),
  KEY `id_fk_environnement_creation_plantes` (`id_fk_environnement_creation_plantes`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `donjon`
-- 

CREATE TABLE `donjon` (
  `id_donjon` int(11) NOT NULL auto_increment,
  `id_fk_lieu_donjon` int(11) NOT NULL,
  `id_fk_region_donjon` int(11) NOT NULL,
  `id_fk_pnj_donjon` int(11) NOT NULL,
  PRIMARY KEY  (`id_donjon`),
  KEY `id_fk_lieu_donjon` (`id_fk_lieu_donjon`),
  KEY `id_fk_region_donjon` (`id_fk_region_donjon`),
  KEY `id_fk_pnj_donjon` (`id_fk_pnj_donjon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `donjon_equipe`
-- 

CREATE TABLE `donjon_equipe` (
  `id_donjon_equipe` int(11) NOT NULL auto_increment,
  `id_fk_donjon_equipe` int(11) NOT NULL,
  `date_creation_donjon_equipe` datetime NOT NULL,
  `date_limite_inscription_donjon_equipe` datetime NOT NULL,
  `etat_donjon_equipe` enum('inscription','en_cours','termine','annule') NOT NULL default 'inscription',
  `nb_jour_restant_donjon_equipe` int(11) NOT NULL,
  PRIMARY KEY  (`id_donjon_equipe`),
  KEY `id_fk_donjon_equipe` (`id_fk_donjon_equipe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `donjon_braldun`
-- 

CREATE TABLE `donjon_braldun` (
  `id_fk_braldun_donjon_braldun` int(11) NOT NULL,
  `id_fk_equipe_donjon_braldun` int(11) NOT NULL,
  `date_entree_donjon_braldun` datetime default NULL,
  PRIMARY KEY  (`id_fk_braldun_donjon_braldun`,`id_fk_equipe_donjon_braldun`),
  KEY `id_fk_equipe_donjon_braldun` (`id_fk_equipe_donjon_braldun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe`
-- 

CREATE TABLE `echoppe` (
  `id_echoppe` int(11) NOT NULL auto_increment,
  `id_fk_braldun_echoppe` int(11) NOT NULL,
  `x_echoppe` int(11) NOT NULL,
  `y_echoppe` int(11) NOT NULL,
  `nom_echoppe` varchar(30) NOT NULL,
  `date_creation_echoppe` datetime NOT NULL,
  `commentaire_echoppe` mediumtext,
  `id_fk_metier_echoppe` int(11) NOT NULL,
  `quantite_peau_caisse_echoppe` int(11) NOT NULL default '0',
  `quantite_castar_caisse_echoppe` int(11) NOT NULL default '0',
  `quantite_rondin_caisse_echoppe` int(11) NOT NULL default '0',
  `quantite_peau_arriere_echoppe` int(11) NOT NULL default '0',
  `quantite_rondin_arriere_echoppe` int(11) NOT NULL default '0',
  `quantite_cuir_arriere_echoppe` int(11) NOT NULL default '0',
  `quantite_fourrure_arriere_echoppe` int(11) NOT NULL default '0',
  `quantite_planche_arriere_echoppe` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_echoppe`),
  KEY `xy_echoppe` (`x_echoppe`,`y_echoppe`),
  KEY `id_fk_braldun_echoppe` (`id_fk_braldun_echoppe`),
  KEY `id_fk_metier_echoppe` (`id_fk_metier_echoppe`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=44 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_equipement`
-- 

CREATE TABLE `echoppe_equipement` (
  `id_echoppe_equipement` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_equipement` int(11) NOT NULL,
  `type_vente_echoppe_equipement` enum('aucune','publique','braldun') NOT NULL default 'aucune',
  `commentaire_vente_echoppe_equipement` varchar(300) default NULL,
  `id_fk_braldun_vente_echoppe_equipement` int(11) default NULL,
  `unite_1_vente_echoppe_equipement` int(11) NOT NULL default '0',
  `unite_2_vente_echoppe_equipement` int(11) NOT NULL default '0',
  `unite_3_vente_echoppe_equipement` int(11) NOT NULL default '0',
  `prix_1_vente_echoppe_equipement` int(11) NOT NULL default '0',
  `prix_2_vente_echoppe_equipement` int(11) NOT NULL default '0',
  `prix_3_vente_echoppe_equipement` int(11) NOT NULL default '0',
  `date_echoppe_equipement` datetime NOT NULL,
  PRIMARY KEY  (`id_echoppe_equipement`),
  KEY `id_fk_echoppe_echoppe_equipement` (`id_fk_echoppe_echoppe_equipement`),
  KEY `id_braldun_vente_echoppe_equipement` (`id_fk_braldun_vente_echoppe_equipement`),
  KEY `date_echoppe_equipement` (`date_echoppe_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_equipement_minerai`
-- 

CREATE TABLE `echoppe_equipement_minerai` (
  `id_fk_type_echoppe_equipement_minerai` int(11) NOT NULL,
  `id_fk_echoppe_equipement_minerai` int(11) NOT NULL,
  `prix_echoppe_equipement_minerai` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_type_echoppe_equipement_minerai`,`id_fk_echoppe_equipement_minerai`),
  KEY `id_fk_echoppe_equipement_minerai` (`id_fk_echoppe_equipement_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_equipement_partieplante`
-- 

CREATE TABLE `echoppe_equipement_partieplante` (
  `id_fk_type_echoppe_equipement_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_echoppe_equipement_partieplante` int(11) NOT NULL,
  `id_fk_echoppe_equipement_partieplante` int(11) NOT NULL,
  `prix_echoppe_equipement_partieplante` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_type_echoppe_equipement_partieplante`,`id_fk_type_plante_echoppe_equipement_partieplante`,`id_fk_echoppe_equipement_partieplante`),
  KEY `id_fk_type_plante_echoppe_equipement_partieplante` (`id_fk_type_plante_echoppe_equipement_partieplante`),
  KEY `id_fk_echoppe_equipement_partieplante` (`id_fk_echoppe_equipement_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_materiel`
-- 

CREATE TABLE `echoppe_materiel` (
  `id_echoppe_materiel` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_materiel` int(11) NOT NULL,
  `type_vente_echoppe_materiel` enum('aucune','publique','braldun') NOT NULL default 'aucune',
  `commentaire_vente_echoppe_materiel` varchar(300) default NULL,
  `id_fk_braldun_vente_echoppe_materiel` int(11) default NULL,
  `unite_1_vente_echoppe_materiel` int(11) NOT NULL default '0',
  `unite_2_vente_echoppe_materiel` int(11) NOT NULL default '0',
  `unite_3_vente_echoppe_materiel` int(11) NOT NULL default '0',
  `prix_1_vente_echoppe_materiel` int(11) NOT NULL default '0',
  `prix_2_vente_echoppe_materiel` int(11) NOT NULL default '0',
  `prix_3_vente_echoppe_materiel` int(11) NOT NULL default '0',
  `date_echoppe_materiel` datetime NOT NULL,
  PRIMARY KEY  (`id_echoppe_materiel`),
  KEY `id_fk_echoppe_echoppe_materiel` (`id_fk_echoppe_echoppe_materiel`),
  KEY `id_braldun_vente_echoppe_materiel` (`id_fk_braldun_vente_echoppe_materiel`),
  KEY `date_echoppe_materiel` (`date_echoppe_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_materiel_minerai`
-- 

CREATE TABLE `echoppe_materiel_minerai` (
  `id_fk_type_echoppe_materiel_minerai` int(11) NOT NULL,
  `id_fk_echoppe_materiel_minerai` int(11) NOT NULL,
  `prix_echoppe_materiel_minerai` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_type_echoppe_materiel_minerai`,`id_fk_echoppe_materiel_minerai`),
  KEY `id_fk_echoppe_materiel_minerai` (`id_fk_echoppe_materiel_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_materiel_partieplante`
-- 

CREATE TABLE `echoppe_materiel_partieplante` (
  `id_fk_type_echoppe_materiel_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_echoppe_materiel_partieplante` int(11) NOT NULL,
  `id_fk_echoppe_materiel_partieplante` int(11) NOT NULL,
  `prix_echoppe_materiel_partieplante` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_type_echoppe_materiel_partieplante`,`id_fk_type_plante_echoppe_materiel_partieplante`,`id_fk_echoppe_materiel_partieplante`),
  KEY `id_fk_type_plante_echoppe_materiel_partieplante` (`id_fk_type_plante_echoppe_materiel_partieplante`),
  KEY `id_fk_echoppe_materiel_partieplante` (`id_fk_echoppe_materiel_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_minerai`
-- 

CREATE TABLE `echoppe_minerai` (
  `id_fk_type_echoppe_minerai` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_minerai` int(11) NOT NULL,
  `quantite_brut_caisse_echoppe_minerai` int(11) NOT NULL default '0',
  `quantite_brut_arriere_echoppe_minerai` int(11) NOT NULL default '0',
  `quantite_lingots_echoppe_minerai` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_type_echoppe_minerai`,`id_fk_echoppe_echoppe_minerai`),
  KEY `id_fk_echoppe_echoppe_minerai` (`id_fk_echoppe_echoppe_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_partieplante`
-- 

CREATE TABLE `echoppe_partieplante` (
  `id_fk_type_echoppe_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_echoppe_partieplante` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_partieplante` int(11) NOT NULL,
  `quantite_caisse_echoppe_partieplante` int(11) NOT NULL default '0',
  `quantite_arriere_echoppe_partieplante` int(11) NOT NULL default '0',
  `quantite_preparee_echoppe_partieplante` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_type_echoppe_partieplante`,`id_fk_type_plante_echoppe_partieplante`,`id_fk_echoppe_echoppe_partieplante`),
  KEY `id_fk_type_plante_echoppe_partieplante` (`id_fk_type_plante_echoppe_partieplante`),
  KEY `id_fk_echoppe_echoppe_partieplante` (`id_fk_echoppe_echoppe_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_potion`
-- 

CREATE TABLE `echoppe_potion` (
  `id_echoppe_potion` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_potion` int(11) NOT NULL,
  `type_vente_echoppe_potion` enum('aucune','publique','braldun') NOT NULL default 'aucune',
  `commentaire_vente_echoppe_potion` varchar(300) default NULL,
  `id_fk_braldun_vente_echoppe_potion` int(11) default NULL,
  `unite_1_vente_echoppe_potion` int(11) NOT NULL default '0',
  `unite_2_vente_echoppe_potion` int(11) NOT NULL default '0',
  `unite_3_vente_echoppe_potion` int(11) NOT NULL default '0',
  `prix_1_vente_echoppe_potion` int(11) NOT NULL default '0',
  `prix_2_vente_echoppe_potion` int(11) NOT NULL default '0',
  `prix_3_vente_echoppe_potion` int(11) NOT NULL default '0',
  `date_echoppe_potion` datetime NOT NULL,
  PRIMARY KEY  (`id_echoppe_potion`),
  KEY `id_fk_echoppe_potion` (`id_fk_echoppe_echoppe_potion`),
  KEY `id_fk_braldun_vente_echoppe_potion` (`id_fk_braldun_vente_echoppe_potion`),
  KEY `date_echoppe_potion` (`date_echoppe_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_potion_minerai`
-- 

CREATE TABLE `echoppe_potion_minerai` (
  `id_fk_type_echoppe_potion_minerai` int(11) NOT NULL,
  `id_fk_echoppe_potion_minerai` int(11) NOT NULL,
  `prix_echoppe_potion_minerai` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_type_echoppe_potion_minerai`,`id_fk_echoppe_potion_minerai`),
  KEY `id_fk_echoppe_potion_minerai` (`id_fk_echoppe_potion_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `echoppe_potion_partieplante`
-- 

CREATE TABLE `echoppe_potion_partieplante` (
  `id_fk_type_echoppe_potion_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_echoppe_potion_partieplante` int(11) NOT NULL,
  `id_fk_echoppe_potion_partieplante` int(11) NOT NULL,
  `prix_echoppe_potion_partieplante` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_type_echoppe_potion_partieplante`,`id_fk_type_plante_echoppe_potion_partieplante`,`id_fk_echoppe_potion_partieplante`),
  KEY `id_fk_type_plante_echoppe_potion_partieplante` (`id_fk_type_plante_echoppe_potion_partieplante`),
  KEY `id_fk_echoppe_potion_partieplante` (`id_fk_echoppe_potion_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `effet_mot_f`
-- 

CREATE TABLE `effet_mot_f` (
  `id_fk_braldun_effet_mot_f` int(11) NOT NULL,
  `id_fk_type_monstre_effet_mot_f` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_braldun_effet_mot_f`,`id_fk_type_monstre_effet_mot_f`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `effet_potion_braldun`
-- 

CREATE TABLE `effet_potion_braldun` (
  `id_effet_potion_braldun` int(11) NOT NULL auto_increment,
  `id_fk_braldun_cible_effet_potion_braldun` int(11) NOT NULL,
  `id_fk_braldun_lanceur_effet_potion_braldun` int(11) NOT NULL,
  `nb_tour_restant_effet_potion_braldun` int(11) NOT NULL,
  `bm_effet_potion_braldun` int(11) NOT NULL,
  PRIMARY KEY  (`id_effet_potion_braldun`),
  KEY `id_fk_braldun_cible_effet_potion_braldun` (`id_fk_braldun_cible_effet_potion_braldun`),
  KEY `id_fk_braldun_lanceur_effet_potion_braldun` (`id_fk_braldun_lanceur_effet_potion_braldun`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `effet_potion_monstre`
-- 

CREATE TABLE `effet_potion_monstre` (
  `id_effet_potion_monstre` int(11) NOT NULL auto_increment,
  `id_fk_monstre_cible_effet_potion_monstre` int(11) NOT NULL,
  `id_fk_braldun_lanceur_effet_potion_monstre` int(11) NOT NULL,
  `nb_tour_restant_effet_potion_monstre` int(11) NOT NULL,
  `bm_effet_potion_monstre` int(11) NOT NULL,
  PRIMARY KEY  (`id_effet_potion_monstre`),
  KEY `id_fk_monstre_cible_effet_potion_monstre` (`id_fk_monstre_cible_effet_potion_monstre`),
  KEY `id_fk_braldun_lanceur_effet_potion_monstre` (`id_fk_braldun_lanceur_effet_potion_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `element`
-- 

CREATE TABLE `element` (
  `x_element` int(11) NOT NULL,
  `y_element` int(11) NOT NULL,
  `quantite_viande_element` int(11) NOT NULL default '0',
  `quantite_peau_element` int(11) NOT NULL default '0',
  `quantite_viande_preparee_element` int(11) NOT NULL default '0',
  `quantite_ration_element` int(11) NOT NULL default '0',
  `quantite_cuir_element` int(11) NOT NULL default '0',
  `quantite_fourrure_element` int(11) NOT NULL default '0',
  `quantite_planche_element` int(11) NOT NULL default '0',
  `quantite_castar_element` int(11) NOT NULL default '0',
  `quantite_rondin_element` int(11) NOT NULL default '0',
  PRIMARY KEY  (`x_element`,`y_element`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `element_aliment`
-- 

CREATE TABLE `element_aliment` (
  `id_element_aliment` int(11) NOT NULL,
  `x_element_aliment` int(11) NOT NULL,
  `y_element_aliment` int(11) NOT NULL,
  `id_fk_type_element_aliment` int(11) NOT NULL,
  `id_fk_type_qualite_element_aliment` int(11) NOT NULL,
  `bbdf_element_aliment` int(11) NOT NULL,
  `date_fin_element_aliment` datetime NOT NULL,
  PRIMARY KEY  (`id_element_aliment`),
  KEY `id_fk_type_element_aliment` (`id_fk_type_element_aliment`),
  KEY `id_fk_type_qualite_element_aliment` (`id_fk_type_qualite_element_aliment`),
  KEY `x_element_aliment` (`x_element_aliment`,`y_element_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `element_equipement`
-- 

CREATE TABLE `element_equipement` (
  `id_element_equipement` int(11) NOT NULL,
  `x_element_equipement` int(11) NOT NULL,
  `y_element_equipement` int(11) NOT NULL,
  `date_fin_element_equipement` datetime NOT NULL,
  PRIMARY KEY  (`id_element_equipement`),
  KEY `x_element_equipement` (`x_element_equipement`,`y_element_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `element_materiel`
-- 

CREATE TABLE `element_materiel` (
  `id_element_materiel` int(11) NOT NULL,
  `x_element_materiel` int(11) NOT NULL,
  `y_element_materiel` int(11) NOT NULL,
  `date_fin_element_materiel` datetime NOT NULL,
  PRIMARY KEY  (`id_element_materiel`),
  KEY `x_element_materiel` (`x_element_materiel`,`y_element_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `element_minerai`
-- 

CREATE TABLE `element_minerai` (
  `x_element_minerai` int(11) NOT NULL,
  `y_element_minerai` int(11) NOT NULL,
  `id_fk_type_element_minerai` int(11) NOT NULL,
  `quantite_brut_element_minerai` int(11) default '0',
  `quantite_lingots_element_minerai` int(11) NOT NULL,
  `date_fin_element_minerai` datetime NOT NULL,
  PRIMARY KEY  (`id_fk_type_element_minerai`,`x_element_minerai`,`y_element_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `element_munition`
-- 

CREATE TABLE `element_munition` (
  `x_element_munition` int(11) NOT NULL,
  `y_element_munition` int(11) NOT NULL,
  `id_fk_type_element_munition` int(11) NOT NULL,
  `quantite_element_munition` int(11) NOT NULL,
  `date_fin_element_munition` datetime NOT NULL,
  PRIMARY KEY  (`x_element_munition`,`y_element_munition`,`id_fk_type_element_munition`),
  KEY `id_fk_type_element_munition` (`id_fk_type_element_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `element_partieplante`
-- 

CREATE TABLE `element_partieplante` (
  `id_fk_type_element_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_element_partieplante` int(11) NOT NULL,
  `x_element_partieplante` int(11) NOT NULL,
  `y_element_partieplante` int(11) NOT NULL,
  `quantite_element_partieplante` int(11) NOT NULL,
  `quantite_preparee_element_partieplante` int(11) NOT NULL,
  `date_fin_element_partieplante` datetime NOT NULL,
  PRIMARY KEY  (`id_fk_type_element_partieplante`,`id_fk_type_plante_element_partieplante`,`x_element_partieplante`,`y_element_partieplante`),
  KEY `id_fk_type_plante_element_partieplante` (`id_fk_type_plante_element_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `element_potion`
-- 

CREATE TABLE `element_potion` (
  `id_element_potion` int(11) NOT NULL,
  `x_element_potion` int(11) NOT NULL,
  `y_element_potion` int(11) NOT NULL,
  `date_fin_element_potion` datetime NOT NULL,
  PRIMARY KEY  (`id_element_potion`),
  KEY `x_element_potion` (`x_element_potion`,`y_element_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `element_rune`
-- 

CREATE TABLE `element_rune` (
  `x_element_rune` int(11) NOT NULL,
  `y_element_rune` int(11) NOT NULL,
  `id_rune_element_rune` int(11) NOT NULL,
  `date_fin_element_rune` datetime NOT NULL,
  PRIMARY KEY  (`id_rune_element_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `element_tabac`
-- 

CREATE TABLE `element_tabac` (
  `id_fk_type_element_tabac` int(11) NOT NULL,
  `x_element_tabac` int(11) NOT NULL,
  `y_element_tabac` int(11) NOT NULL,
  `quantite_feuille_element_tabac` int(11) default '0',
  `date_fin_element_tabac` datetime NOT NULL,
  PRIMARY KEY  (`id_fk_type_element_tabac`,`x_element_tabac`,`y_element_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `environnement`
-- 

CREATE TABLE `environnement` (
  `id_environnement` int(11) NOT NULL auto_increment,
  `nom_environnement` varchar(20) NOT NULL,
  `description_environnement` varchar(250) NOT NULL,
  `nom_systeme_environnement` varchar(20) NOT NULL,
  `image_environnement` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_environnement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `equipement`
-- 

CREATE TABLE `equipement` (
  `id_equipement` int(11) NOT NULL,
  `id_fk_recette_equipement` int(11) NOT NULL,
  `nb_runes_equipement` int(11) NOT NULL,
  `id_fk_mot_runique_equipement` int(11) default NULL,
  `id_fk_region_equipement` int(11) NOT NULL,
  `etat_initial_equipement` int(11) NOT NULL,
  `etat_courant_equipement` int(11) NOT NULL,
  `vernis_template_equipement` varchar(20) character set utf8 default NULL,
  PRIMARY KEY  (`id_equipement`),
  KEY `id_fk_mot_runique_equipement` (`id_fk_mot_runique_equipement`),
  KEY `id_fk_region_equipement` (`id_fk_region_equipement`),
  KEY `id_fk_recette_equipement` (`id_fk_recette_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `equipement_bonus`
-- 

CREATE TABLE `equipement_bonus` (
  `id_equipement_bonus` int(11) NOT NULL,
  `armure_equipement_bonus` int(11) NOT NULL,
  `agilite_equipement_bonus` int(11) NOT NULL,
  `force_equipement_bonus` int(11) NOT NULL,
  `sagesse_equipement_bonus` int(11) NOT NULL,
  `vigueur_equipement_bonus` int(11) NOT NULL,
  `vernis_bm_vue_equipement_bonus` int(11) default NULL,
  `vernis_bm_armure_equipement_bonus` int(11) default NULL,
  `vernis_bm_poids_equipement_bonus` float default NULL,
  `vernis_bm_agilite_equipement_bonus` int(11) default NULL,
  `vernis_bm_force_equipement_bonus` int(11) default NULL,
  `vernis_bm_sagesse_equipement_bonus` int(11) default NULL,
  `vernis_bm_vigueur_equipement_bonus` int(11) default NULL,
  `vernis_bm_attaque_equipement_bonus` int(11) default NULL,
  `vernis_bm_degat_equipement_bonus` int(11) default NULL,
  `vernis_bm_defense_equipement_bonus` int(11) default NULL,
  PRIMARY KEY  (`id_equipement_bonus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `equipement_rune`
-- 

CREATE TABLE `equipement_rune` (
  `id_equipement_rune` int(11) NOT NULL,
  `id_rune_equipement_rune` int(11) NOT NULL,
  `ordre_equipement_rune` int(11) NOT NULL,
  PRIMARY KEY  (`id_equipement_rune`,`id_rune_equipement_rune`),
  KEY `id_rune_equipement_rune` (`id_rune_equipement_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `etape`
-- 

CREATE TABLE `etape` (
  `id_etape` int(11) NOT NULL auto_increment,
  `id_fk_quete_etape` int(11) NOT NULL,
  `id_fk_type_etape` int(11) NOT NULL,
  `id_fk_braldun_etape` int(11) NOT NULL COMMENT 'Dénormalisation',
  `libelle_etape` varchar(300) NOT NULL,
  `date_debut_etape` datetime default NULL,
  `date_fin_etape` datetime default NULL,
  `est_terminee_etape` enum('oui','non') NOT NULL default 'non',
  `param_1_etape` int(11) default NULL,
  `param_2_etape` int(11) default NULL,
  `param_3_etape` int(11) default NULL,
  `param_4_etape` int(11) default NULL,
  `param_5_etape` int(11) default NULL,
  `objectif_etape` int(11) NOT NULL default '0',
  `ordre_etape` int(11) NOT NULL,
  PRIMARY KEY  (`id_etape`),
  KEY `id_fk_quete_etape` (`id_fk_quete_etape`),
  KEY `id_fk_type_etape` (`id_fk_type_etape`),
  KEY `id_fk_braldun_etape` (`id_fk_braldun_etape`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=159 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `evenement`
-- 

CREATE TABLE `evenement` (
  `id_evenement` int(11) NOT NULL auto_increment,
  `id_fk_braldun_evenement` int(11) default NULL,
  `id_fk_monstre_evenement` int(11) default NULL,
  `date_evenement` datetime NOT NULL,
  `id_fk_type_evenement` int(11) NOT NULL,
  `details_evenement` varchar(1000) NOT NULL,
  `details_bot_evenement` mediumtext,
  `niveau_evenement` int(11) NOT NULL COMMENT 'Nivau du Braldun ou du monstre lors de l''événément',
  `id_fk_soule_match_evenement` int(11) default NULL,
  PRIMARY KEY  (`id_evenement`),
  KEY `idx_id_braldun_evenement` (`id_fk_braldun_evenement`),
  KEY `idx_id_monstre_evenement` (`id_fk_monstre_evenement`),
  KEY `date_evenement` (`date_evenement`),
  KEY `id_fk_soule_match_evenement` (`id_fk_soule_match_evenement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=10022 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `filon`
-- 

CREATE TABLE `filon` (
  `id_filon` int(11) NOT NULL auto_increment,
  `id_fk_type_minerai_filon` int(11) NOT NULL,
  `x_filon` int(11) NOT NULL,
  `y_filon` int(11) NOT NULL,
  `quantite_restante_filon` int(11) NOT NULL,
  `quantite_max_filon` int(11) NOT NULL,
  PRIMARY KEY  (`id_filon`),
  KEY `idx_x_filon_y_filon` (`x_filon`,`y_filon`),
  KEY `id_fk_type_minerai_filon` (`id_fk_type_minerai_filon`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=232012 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `gardiennage`
-- 

CREATE TABLE `gardiennage` (
  `id_gardiennage` int(11) NOT NULL auto_increment,
  `id_fk_braldun_gardiennage` int(11) NOT NULL,
  `id_fk_gardien_gardiennage` int(11) NOT NULL,
  `date_debut_gardiennage` date NOT NULL,
  `date_fin_gardiennage` date NOT NULL,
  `nb_jours_gardiennage` int(11) NOT NULL,
  `commentaire_gardiennage` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_gardiennage`),
  KEY `id_gardien_gardiennage` (`id_fk_gardien_gardiennage`),
  KEY `id_fk_braldun_gardiennage` (`id_fk_braldun_gardiennage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `groupe_monstre`
-- 

CREATE TABLE `groupe_monstre` (
  `id_groupe_monstre` int(11) NOT NULL auto_increment,
  `id_fk_type_groupe_monstre` int(11) NOT NULL,
  `date_creation_groupe_monstre` datetime NOT NULL,
  `id_fk_braldun_cible_groupe_monstre` int(11) default NULL,
  `nb_membres_max_groupe_monstre` int(11) NOT NULL,
  `nb_membres_restant_groupe_monstre` int(11) NOT NULL,
  `phase_tactique_groupe_monstre` int(11) NOT NULL,
  `date_phase_tactique_groupe_monstre` datetime NOT NULL,
  `id_role_a_groupe_monstre` int(11) default NULL,
  `id_role_b_groupe_monstre` int(11) default NULL,
  `date_fin_tour_groupe_monstre` datetime default NULL COMMENT 'DLA du dernier monstre à jouer dans ce groupe',
  `x_direction_groupe_monstre` int(11) NOT NULL,
  `y_direction_groupe_monstre` int(11) NOT NULL,
  `date_a_jouer_groupe_monstre` datetime default NULL,
  PRIMARY KEY  (`id_groupe_monstre`),
  KEY `id_fk_type_groupe_monstre` (`id_fk_type_groupe_monstre`),
  KEY `id_fk_braldun_cible_groupe_monstre` (`id_fk_braldun_cible_groupe_monstre`),
  KEY `date_fin_tour_groupe_monstre` (`date_fin_tour_groupe_monstre`),
  KEY `date_a_jouer_groupe_monstre` (`date_a_jouer_groupe_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=415 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `historique_equipement`
-- 

CREATE TABLE `historique_equipement` (
  `id_historique_equipement` int(11) NOT NULL auto_increment,
  `id_fk_historique_equipement` int(11) NOT NULL,
  `date_historique_equipement` datetime NOT NULL,
  `details_historique_equipement` varchar(200) NOT NULL,
  `id_fk_type_historique_equipement` int(11) NOT NULL,
  PRIMARY KEY  (`id_historique_equipement`),
  KEY `id_fk_historique_equipement` (`id_fk_historique_equipement`),
  KEY `id_fk_type_historique_equipement` (`id_fk_type_historique_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `historique_materiel`
-- 

CREATE TABLE `historique_materiel` (
  `id_historique_materiel` int(11) NOT NULL auto_increment,
  `id_fk_historique_materiel` int(11) NOT NULL,
  `id_fk_type_historique_materiel` int(11) NOT NULL,
  `date_historique_materiel` datetime NOT NULL,
  `details_historique_materiel` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_historique_materiel`),
  KEY `id_fk_historique_materiel` (`id_fk_historique_materiel`),
  KEY `id_fk_type_historique_materiel` (`id_fk_type_historique_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `historique_potion`
-- 

CREATE TABLE `historique_potion` (
  `id_historique_potion` int(11) NOT NULL auto_increment,
  `id_fk_historique_potion` int(11) NOT NULL,
  `id_fk_type_historique_potion` int(11) NOT NULL,
  `date_historique_potion` datetime NOT NULL,
  `details_historique_potion` varchar(300) character set utf8 NOT NULL,
  PRIMARY KEY  (`id_historique_potion`),
  KEY `id_fk_historique_potion` (`id_fk_historique_potion`),
  KEY `id_fk_type_historique_potion` (`id_fk_type_historique_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=36 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `historique_rune`
-- 

CREATE TABLE `historique_rune` (
  `id_historique_rune` int(11) NOT NULL auto_increment,
  `id_fk_historique_rune` int(11) NOT NULL,
  `id_fk_type_historique_rune` int(11) NOT NULL,
  `date_historique_rune` datetime NOT NULL,
  `details_historique_rune` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_historique_rune`),
  KEY `id_fk_historique_rune` (`id_fk_type_historique_rune`),
  KEY `id_fk_historique_rune_2` (`id_fk_historique_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `braldun`
-- 

CREATE TABLE `braldun` (
  `id_braldun` int(11) NOT NULL auto_increment,
  `id_fk_jos_users_braldun` int(11) default NULL COMMENT 'identifiant vers User Joomla : jos_users.id',
  `sysgroupe_braldun` varchar(10) default NULL,
  `nom_braldun` varchar(20) NOT NULL,
  `prenom_braldun` varchar(23) NOT NULL,
  `id_fk_nom_initial_braldun` int(11) NOT NULL,
  `password_braldun` varchar(50) NOT NULL,
  `email_braldun` varchar(100) NOT NULL,
  `etat_braldun` int(11) NOT NULL,
  `sexe_braldun` enum('feminin','masculin') NOT NULL,
  `x_braldun` int(11) NOT NULL,
  `y_braldun` int(1) NOT NULL,
  `date_debut_tour_braldun` datetime NOT NULL,
  `date_fin_tour_braldun` datetime NOT NULL,
  `date_fin_latence_braldun` datetime NOT NULL,
  `date_debut_cumul_braldun` datetime NOT NULL,
  `duree_prochain_tour_braldun` time NOT NULL,
  `duree_courant_tour_braldun` time NOT NULL,
  `tour_position_braldun` int(11) NOT NULL,
  `pa_braldun` int(11) NOT NULL,
  `vue_bm_braldun` int(11) NOT NULL,
  `vue_malus_braldun` int(11) NOT NULL,
  `force_base_braldun` int(11) NOT NULL,
  `force_bm_braldun` int(11) NOT NULL,
  `force_bbdf_braldun` int(11) NOT NULL default '0',
  `agilite_base_braldun` int(11) NOT NULL,
  `agilite_bm_braldun` int(11) NOT NULL,
  `agilite_bbdf_braldun` int(11) NOT NULL default '0',
  `agilite_malus_braldun` int(11) NOT NULL,
  `sagesse_base_braldun` int(11) NOT NULL,
  `sagesse_bm_braldun` int(11) NOT NULL,
  `sagesse_bbdf_braldun` int(11) NOT NULL default '0',
  `vigueur_base_braldun` int(11) NOT NULL,
  `vigueur_bm_braldun` int(11) NOT NULL,
  `vigueur_bbdf_braldun` int(11) NOT NULL default '0',
  `regeneration_braldun` int(11) NOT NULL,
  `regeneration_malus_braldun` int(11) NOT NULL,
  `px_perso_braldun` int(11) NOT NULL default '0',
  `px_commun_braldun` int(11) NOT NULL,
  `pi_cumul_braldun` int(11) NOT NULL default '0',
  `pi_braldun` int(11) NOT NULL default '0',
  `niveau_braldun` int(11) NOT NULL default '0',
  `balance_faim_braldun` int(11) NOT NULL,
  `armure_naturelle_braldun` int(11) NOT NULL,
  `armure_equipement_braldun` int(11) NOT NULL,
  `bm_attaque_braldun` int(11) NOT NULL,
  `bm_defense_braldun` int(11) NOT NULL,
  `bm_degat_braldun` int(11) NOT NULL,
  `poids_transportable_braldun` float NOT NULL default '0',
  `poids_transporte_braldun` float NOT NULL default '0',
  `castars_braldun` int(11) NOT NULL,
  `pv_max_braldun` int(11) NOT NULL COMMENT 'calculé à l''activation du tour',
  `pv_restant_braldun` int(11) NOT NULL,
  `pv_max_bm_braldun` int(11) NOT NULL,
  `est_ko_braldun` enum('oui','non') NOT NULL default 'non',
  `nb_ko_braldun` int(11) NOT NULL default '0',
  `nb_braldun_ko_braldun` int(11) NOT NULL default '0',
  `nb_plaque_braldun` int(11) NOT NULL,
  `nb_braldun_plaquage_braldun` int(11) NOT NULL,
  `nb_monstre_kill_braldun` int(11) NOT NULL,
  `est_compte_actif_braldun` enum('oui','non') NOT NULL default 'non',
  `est_compte_desactive_braldun` enum('oui','non') NOT NULL default 'non',
  `est_en_hibernation_braldun` enum('oui','non') NOT NULL default 'non',
  `date_fin_hibernation_braldun` datetime NOT NULL,
  `date_creation_braldun` datetime NOT NULL,
  `id_fk_mere_braldun` int(11) default NULL,
  `id_fk_pere_braldun` int(11) default NULL,
  `description_braldun` mediumtext NOT NULL,
  `id_fk_communaute_braldun` int(11) default NULL,
  `id_fk_rang_communaute_braldun` int(11) default NULL,
  `date_entree_communaute_braldun` datetime default NULL,
  `url_blason_braldun` varchar(200) default 'http://',
  `url_avatar_braldun` varchar(200) default 'http://',
  `envoi_mail_message_braldun` enum('oui','non') NOT NULL default 'oui',
  `envoi_mail_evenement_braldun` enum('oui','non') NOT NULL default 'non',
  `titre_courant_braldun` varchar(15) default NULL,
  `est_intangible_braldun` enum('oui','non') NOT NULL default 'non',
  `est_engage_braldun` enum('oui','non') NOT NULL default 'non',
  `est_engage_next_dla_braldun` enum('oui','non') NOT NULL default 'non',
  `est_charte_validee_braldun` enum('oui','non') NOT NULL default 'non',
  `id_fk_region_creation_braldun` int(11) NOT NULL,
  `est_soule_braldun` enum('oui','non') NOT NULL default 'non',
  `soule_camp_braldun` enum('a','b') default NULL,
  `id_fk_soule_match_braldun` int(11) default NULL,
  `est_quete_braldun` enum('oui','non') NOT NULL default 'non',
  `est_pnj_braldun` enum('oui','non') NOT NULL default 'non',
  PRIMARY KEY  (`id_braldun`),
  UNIQUE KEY `email_braldun` (`email_braldun`),
  KEY `idx_x_braldun_y_braldun` (`x_braldun`,`y_braldun`),
  KEY `id_fk_communaute_braldun` (`id_fk_communaute_braldun`),
  KEY `id_fk_rang_communaute_braldun` (`id_fk_rang_communaute_braldun`),
  KEY `id_fk_jos_users_braldun` (`id_fk_jos_users_braldun`),
  KEY `est_en_hibernation_braldun` (`est_en_hibernation_braldun`),
  KEY `id_fk_mere_braldun` (`id_fk_mere_braldun`),
  KEY `id_fk_pere_braldun` (`id_fk_pere_braldun`),
  KEY `id_fk_region_creation_braldun` (`id_fk_region_creation_braldun`),
  KEY `est_pnj_braldun` (`est_pnj_braldun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tables des Bralduns' AUTO_INCREMENT=118 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `bralduns_cdm`
-- 

CREATE TABLE `bralduns_cdm` (
  `id_fk_braldun_hcdm` int(11) NOT NULL,
  `id_fk_monstre_hcdm` int(11) NOT NULL,
  `id_fk_type_monstre_hcdm` int(11) NOT NULL,
  `id_fk_taille_monstre_hcdm` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_braldun_hcdm`,`id_fk_monstre_hcdm`,`id_fk_taille_monstre_hcdm`),
  KEY `id_fk_type_monstre_hcdm` (`id_fk_type_monstre_hcdm`),
  KEY `id_fk_taille_monstre_hcdm` (`id_fk_taille_monstre_hcdm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `bralduns_competences`
-- 

CREATE TABLE `bralduns_competences` (
  `id_fk_braldun_hcomp` int(11) NOT NULL default '0',
  `id_fk_competence_hcomp` int(11) NOT NULL default '0',
  `pourcentage_hcomp` int(11) NOT NULL default '10',
  `date_debut_tour_hcomp` datetime NOT NULL default '0000-00-00 00:00:00',
  `nb_action_tour_hcomp` int(11) NOT NULL default '0',
  `nb_gain_tour_hcomp` int(11) NOT NULL default '0',
  `nb_tour_restant_bonus_tabac_hcomp` int(11) NOT NULL default '0',
  `nb_tour_restant_malus_tabac_hcomp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_braldun_hcomp`,`id_fk_competence_hcomp`),
  KEY `id_fk_competence_hcomp` (`id_fk_competence_hcomp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `bralduns_distinction`
-- 

CREATE TABLE `bralduns_distinction` (
  `id_hdistinction` int(11) NOT NULL auto_increment,
  `id_fk_braldun_hdistinction` int(11) NOT NULL,
  `id_fk_type_distinction_hdistinction` int(11) NOT NULL,
  `texte_hdistinction` varchar(100) NOT NULL,
  `url_hdistinction` varchar(200) default NULL,
  `date_hdistinction` date NOT NULL,
  PRIMARY KEY  (`id_hdistinction`),
  KEY `id_fk_braldun_hdistinction` (`id_fk_braldun_hdistinction`),
  KEY `id_fk_type_distinction_hdistinction` (`id_fk_type_distinction_hdistinction`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `bralduns_equipement`
-- 

CREATE TABLE `bralduns_equipement` (
  `id_equipement_hequipement` int(11) NOT NULL,
  `id_fk_braldun_hequipement` int(11) NOT NULL,
  PRIMARY KEY  (`id_equipement_hequipement`),
  KEY `id_fk_braldun_hequipement` (`id_fk_braldun_hequipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `bralduns_metiers`
-- 

CREATE TABLE `bralduns_metiers` (
  `id_fk_braldun_hmetier` int(11) NOT NULL,
  `id_fk_metier_hmetier` int(11) NOT NULL,
  `est_actif_hmetier` enum('oui','non') NOT NULL,
  `date_apprentissage_hmetier` date NOT NULL,
  PRIMARY KEY  (`id_fk_braldun_hmetier`,`id_fk_metier_hmetier`),
  KEY `id_fk_metier_hmetier` (`id_fk_metier_hmetier`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `bralduns_titres`
-- 

CREATE TABLE `bralduns_titres` (
  `id_fk_braldun_htitre` int(11) NOT NULL,
  `id_fk_type_htitre` int(11) NOT NULL,
  `niveau_acquis_htitre` int(11) NOT NULL,
  `date_acquis_htitre` date NOT NULL,
  PRIMARY KEY  (`id_fk_braldun_htitre`,`id_fk_type_htitre`,`niveau_acquis_htitre`),
  KEY `id_fk_type_htitre` (`id_fk_type_htitre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `ids_aliment`
-- 

CREATE TABLE `ids_aliment` (
  `id_ids_aliment` int(11) NOT NULL auto_increment,
  `date_creation_ids_aliment` datetime NOT NULL,
  PRIMARY KEY  (`id_ids_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1073 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `ids_equipement`
-- 

CREATE TABLE `ids_equipement` (
  `id_ids_equipement` int(11) NOT NULL auto_increment,
  `date_creation_ids_equipement` datetime NOT NULL,
  PRIMARY KEY  (`id_ids_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=80 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `ids_materiel`
-- 

CREATE TABLE `ids_materiel` (
  `id_ids_materiel` int(11) NOT NULL auto_increment,
  `date_creation_ids_materiel` datetime NOT NULL,
  PRIMARY KEY  (`id_ids_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `ids_potion`
-- 

CREATE TABLE `ids_potion` (
  `id_ids_potion` int(11) NOT NULL auto_increment,
  `date_creation_ids_potion` datetime NOT NULL,
  PRIMARY KEY  (`id_ids_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=377 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `ids_rune`
-- 

CREATE TABLE `ids_rune` (
  `id_ids_rune` int(11) NOT NULL auto_increment,
  `date_creation_ids_rune` datetime NOT NULL,
  PRIMARY KEY  (`id_ids_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1008 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `info_jeu`
-- 

CREATE TABLE `info_jeu` (
  `id_info_jeu` int(11) NOT NULL auto_increment,
  `date_info_jeu` datetime NOT NULL,
  `titre_info_jeu` varchar(50) default NULL,
  `type_info_jeu` enum('annonce','histoire') NOT NULL default 'annonce',
  `text_info_jeu` text NOT NULL,
  `est_sur_accueil_info_jeu` enum('oui','non') NOT NULL default 'oui',
  `lien_info_jeu` varchar(200) NOT NULL,
  `lien_wiki_info_jeu` varchar(200) default NULL,
  PRIMARY KEY  (`id_info_jeu`),
  KEY `est_sur_accueil_info_jeu` (`est_sur_accueil_info_jeu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=89 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `jos_uddeim`
-- 

CREATE TABLE `jos_uddeim` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fromid` int(11) NOT NULL default '0',
  `toid` int(11) NOT NULL default '0',
  `toids` varchar(250) NOT NULL,
  `message` text NOT NULL,
  `datum` int(11) default NULL,
  `toread` int(1) NOT NULL default '0',
  `totrash` int(1) NOT NULL default '0',
  `totrashdate` int(11) default NULL,
  `totrashoutbox` int(1) NOT NULL default '0',
  `totrashdateoutbox` int(11) default NULL,
  `expires` int(11) default NULL,
  `disablereply` int(1) NOT NULL default '0',
  `systemmessage` varchar(60) default NULL,
  `archived` int(1) NOT NULL default '0',
  `cryptmode` int(1) NOT NULL default '0',
  `crypthash` varchar(32) default NULL,
  `publicname` text,
  `publicemail` text,
  PRIMARY KEY  (`id`),
  KEY `toid_toread` (`toid`,`toread`),
  KEY `datum` (`datum`),
  KEY `totrashdate` (`totrashdate`),
  KEY `totrashdateoutbox` (`totrashdateoutbox`),
  KEY `toread_totrash_datum` (`toread`,`totrash`,`datum`),
  KEY `totrash_totrashdate` (`totrash`,`totrashdate`),
  KEY `fromid` (`fromid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1974 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `jos_uddeim_userlists`
-- 

CREATE TABLE `jos_uddeim_userlists` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `name` varchar(40) NOT NULL default '',
  `description` text NOT NULL,
  `userids` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `laban`
-- 

CREATE TABLE `laban` (
  `id_fk_braldun_laban` int(11) NOT NULL,
  `quantite_viande_laban` int(11) NOT NULL default '0',
  `quantite_peau_laban` int(11) NOT NULL default '0',
  `quantite_viande_preparee_laban` int(11) NOT NULL default '0',
  `quantite_ration_laban` int(11) NOT NULL default '0',
  `quantite_cuir_laban` int(11) NOT NULL default '0',
  `quantite_fourrure_laban` int(11) NOT NULL default '0',
  `quantite_planche_laban` int(11) NOT NULL default '0',
  `quantite_castar_laban` int(11) NOT NULL default '0',
  `quantite_rondin_laban` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_braldun_laban`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_aliment`
-- 

CREATE TABLE `laban_aliment` (
  `id_laban_aliment` int(11) NOT NULL,
  `id_fk_type_laban_aliment` int(11) NOT NULL,
  `id_fk_braldun_laban_aliment` int(11) NOT NULL,
  `id_fk_type_qualite_laban_aliment` int(11) NOT NULL,
  `bbdf_laban_aliment` int(11) NOT NULL,
  PRIMARY KEY  (`id_laban_aliment`),
  KEY `id_fk_type_laban_aliment` (`id_fk_type_laban_aliment`),
  KEY `id_fk_braldun_laban_aliment` (`id_fk_braldun_laban_aliment`),
  KEY `id_fk_type_qualite_laban_aliment` (`id_fk_type_qualite_laban_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_equipement`
-- 

CREATE TABLE `laban_equipement` (
  `id_laban_equipement` int(11) NOT NULL,
  `id_fk_braldun_laban_equipement` int(11) NOT NULL,
  PRIMARY KEY  (`id_laban_equipement`),
  KEY `id_fk_braldun_laban_equipement` (`id_fk_braldun_laban_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_materiel`
-- 

CREATE TABLE `laban_materiel` (
  `id_laban_materiel` int(11) NOT NULL,
  `id_fk_braldun_laban_materiel` int(11) NOT NULL,
  PRIMARY KEY  (`id_laban_materiel`),
  KEY `laban_materiel_ibfk_2` (`id_fk_braldun_laban_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_minerai`
-- 

CREATE TABLE `laban_minerai` (
  `id_fk_type_laban_minerai` int(11) NOT NULL,
  `id_fk_braldun_laban_minerai` int(11) NOT NULL,
  `quantite_brut_laban_minerai` int(11) default '0',
  `quantite_lingots_laban_minerai` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_laban_minerai`,`id_fk_braldun_laban_minerai`),
  KEY `id_fk_braldun_laban_minerai` (`id_fk_braldun_laban_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_munition`
-- 

CREATE TABLE `laban_munition` (
  `id_fk_type_laban_munition` int(11) NOT NULL,
  `id_fk_braldun_laban_munition` int(11) NOT NULL,
  `quantite_laban_munition` int(11) default '0',
  PRIMARY KEY  (`id_fk_type_laban_munition`,`id_fk_braldun_laban_munition`),
  KEY `id_fk_braldun_laban_munition` (`id_fk_braldun_laban_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_partieplante`
-- 

CREATE TABLE `laban_partieplante` (
  `id_fk_type_laban_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_laban_partieplante` int(11) NOT NULL,
  `id_fk_braldun_laban_partieplante` int(11) NOT NULL,
  `quantite_laban_partieplante` int(11) NOT NULL,
  `quantite_preparee_laban_partieplante` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_laban_partieplante`,`id_fk_type_plante_laban_partieplante`,`id_fk_braldun_laban_partieplante`),
  KEY `id_fk_type_plante_laban_partieplante` (`id_fk_type_plante_laban_partieplante`),
  KEY `id_fk_braldun_laban_partieplante` (`id_fk_braldun_laban_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_potion`
-- 

CREATE TABLE `laban_potion` (
  `id_laban_potion` int(11) NOT NULL,
  `id_fk_braldun_laban_potion` int(11) NOT NULL,
  PRIMARY KEY  (`id_laban_potion`),
  KEY `id_fk_braldun_laban_potion` (`id_fk_braldun_laban_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_rune`
-- 

CREATE TABLE `laban_rune` (
  `id_fk_braldun_laban_rune` int(11) NOT NULL,
  `id_rune_laban_rune` int(11) NOT NULL,
  PRIMARY KEY  (`id_rune_laban_rune`),
  KEY `id_fk_braldun_laban_rune` (`id_fk_braldun_laban_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `laban_tabac`
-- 

CREATE TABLE `laban_tabac` (
  `id_fk_type_laban_tabac` int(11) NOT NULL,
  `id_fk_braldun_laban_tabac` int(11) NOT NULL,
  `quantite_feuille_laban_tabac` int(11) default '0',
  PRIMARY KEY  (`id_fk_type_laban_tabac`,`id_fk_braldun_laban_tabac`),
  KEY `id_fk_braldun_laban_tabac` (`id_fk_braldun_laban_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `lieu`
-- 

CREATE TABLE `lieu` (
  `id_lieu` int(11) NOT NULL auto_increment,
  `nom_lieu` varchar(50) NOT NULL,
  `description_lieu` mediumtext NOT NULL,
  `x_lieu` int(11) NOT NULL,
  `y_lieu` int(11) NOT NULL,
  `etat_lieu` int(11) NOT NULL,
  `id_fk_type_lieu` int(11) NOT NULL,
  `id_fk_ville_lieu` int(11) default NULL,
  `date_creation_lieu` datetime NOT NULL,
  `est_soule_lieu` enum('oui','non') NOT NULL default 'non',
  PRIMARY KEY  (`id_lieu`),
  UNIQUE KEY `xy_lieu` (`x_lieu`,`y_lieu`),
  KEY `id_fk_type_lieu` (`id_fk_type_lieu`),
  KEY `id_fk_ville_lieu` (`id_fk_ville_lieu`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=304 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `materiel`
-- 

CREATE TABLE `materiel` (
  `id_materiel` int(11) NOT NULL,
  `id_fk_type_materiel` int(11) NOT NULL,
  PRIMARY KEY  (`id_materiel`),
  KEY `id_fk_recette_materiel` (`id_fk_type_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `metier`
-- 

CREATE TABLE `metier` (
  `id_metier` int(11) NOT NULL auto_increment,
  `nom_masculin_metier` varchar(20) NOT NULL,
  `nom_feminin_metier` varchar(20) NOT NULL,
  `nom_systeme_metier` varchar(20) NOT NULL,
  `description_metier` mediumtext NOT NULL,
  `construction_charrette_metier` enum('oui','non') NOT NULL,
  `construction_echoppe_metier` enum('oui','non') NOT NULL default 'non',
  PRIMARY KEY  (`id_metier`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `monstre`
-- 

CREATE TABLE `monstre` (
  `id_monstre` int(11) NOT NULL auto_increment,
  `id_fk_type_monstre` int(11) NOT NULL,
  `id_fk_taille_monstre` int(11) NOT NULL,
  `id_fk_groupe_monstre` int(11) default NULL,
  `x_monstre` int(11) NOT NULL,
  `y_monstre` int(11) NOT NULL,
  `x_direction_monstre` int(11) NOT NULL,
  `y_direction_monstre` int(11) NOT NULL,
  `x_min_monstre` int(11) default NULL,
  `y_min_monstre` int(11) default NULL,
  `x_max_monstre` int(11) default NULL,
  `y_max_monstre` int(11) default NULL,
  `id_fk_braldun_cible_monstre` int(11) default NULL,
  `pv_restant_monstre` int(11) NOT NULL,
  `pv_max_monstre` int(11) NOT NULL,
  `pa_monstre` int(11) NOT NULL,
  `niveau_monstre` int(11) NOT NULL,
  `vue_monstre` int(11) NOT NULL,
  `vue_malus_monstre` int(11) NOT NULL,
  `force_base_monstre` int(11) NOT NULL,
  `force_bm_monstre` int(11) NOT NULL,
  `agilite_base_monstre` int(11) NOT NULL,
  `agilite_bm_monstre` int(11) NOT NULL,
  `agilite_malus_monstre` int(11) NOT NULL,
  `sagesse_base_monstre` int(11) NOT NULL,
  `sagesse_bm_monstre` int(11) NOT NULL,
  `vigueur_base_monstre` int(11) NOT NULL,
  `vigueur_bm_monstre` int(11) NOT NULL,
  `bm_attaque_monstre` int(11) NOT NULL default '0',
  `bm_defense_monstre` int(11) NOT NULL default '0',
  `bm_degat_monstre` int(11) NOT NULL default '0',
  `regeneration_monstre` int(11) NOT NULL,
  `regeneration_malus_monstre` int(11) NOT NULL,
  `armure_naturelle_monstre` int(11) NOT NULL,
  `date_fin_tour_monstre` datetime NOT NULL,
  `duree_prochain_tour_monstre` time NOT NULL,
  `duree_base_tour_monstre` time NOT NULL,
  `nb_kill_monstre` int(11) NOT NULL,
  `date_creation_monstre` datetime NOT NULL,
  `est_mort_monstre` enum('oui','non') NOT NULL default 'non',
  `date_a_jouer_monstre` datetime default NULL,
  `date_fin_cadavre_monstre` datetime default NULL,
  `est_depiaute_cadavre` enum('oui','non') NOT NULL default 'non',
  `date_suppression_monstre` datetime default NULL COMMENT 'Utilisé pour la disparition des gibiers',
  PRIMARY KEY  (`id_monstre`),
  KEY `id_fk_groupe_monstre` (`id_fk_groupe_monstre`),
  KEY `idx_x_monstre_y_monstre` (`x_monstre`,`y_monstre`),
  KEY `id_fk_type_monstre` (`id_fk_type_monstre`),
  KEY `id_fk_taille_monstre` (`id_fk_taille_monstre`),
  KEY `id_fk_braldun_cible_monstre` (`id_fk_braldun_cible_monstre`),
  KEY `date_a_jouer_monstre` (`date_a_jouer_monstre`),
  KEY `date_suppression_monstre` (`date_suppression_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5290 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `mot_runique`
-- 

CREATE TABLE `mot_runique` (
  `id_mot_runique` int(11) NOT NULL auto_increment,
  `nom_systeme_mot_runique` varchar(6) NOT NULL,
  `id_fk_type_piece_mot_runique` int(11) NOT NULL,
  `suffixe_mot_runique` varchar(15) NOT NULL,
  `coef_lune_changement_mot_runique` int(11) NOT NULL default '50',
  `date_generation_mot_runique` datetime NOT NULL,
  `nb_total_rune_mot_runique` int(2) NOT NULL,
  `nb_rune_niveau_a_mot_runique` int(11) NOT NULL default '0',
  `nb_rune_niveau_b_mot_runique` int(11) NOT NULL default '0',
  `nb_rune_niveau_c_mot_runique` int(11) NOT NULL default '0',
  `nb_rune_niveau_d_mot_runique` int(11) NOT NULL default '0',
  `id_fk_type_rune_1_mot_runique` int(11) NOT NULL,
  `id_fk_type_rune_2_mot_runique` int(11) default NULL,
  `id_fk_type_rune_3_mot_runique` int(11) default NULL,
  `id_fk_type_rune_4_mot_runique` int(11) default NULL,
  `id_fk_type_rune_5_mot_runique` int(11) default NULL,
  `id_fk_type_rune_6_mot_runique` int(11) default NULL,
  `effet_mot_runique` varchar(300) NOT NULL,
  PRIMARY KEY  (`id_mot_runique`),
  UNIQUE KEY `nom_systeme_mot_runique` (`nom_systeme_mot_runique`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `nom`
-- 

CREATE TABLE `nom` (
  `id_nom` int(11) NOT NULL auto_increment,
  `nom` varchar(20) NOT NULL,
  PRIMARY KEY  (`id_nom`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=63 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `palissade`
-- 

CREATE TABLE `palissade` (
  `id_palissade` int(11) NOT NULL auto_increment,
  `x_palissade` int(11) NOT NULL,
  `y_palissade` int(11) NOT NULL,
  `agilite_palissade` int(11) NOT NULL,
  `armure_naturelle_palissade` int(11) NOT NULL,
  `pv_max_palissade` int(11) NOT NULL,
  `pv_restant_palissade` int(11) NOT NULL,
  `date_creation_palissade` datetime NOT NULL,
  `date_fin_palissade` datetime NOT NULL,
  `est_destructible_palissade` enum('oui','non') NOT NULL default 'oui',
  PRIMARY KEY  (`id_palissade`),
  UNIQUE KEY `xy_palissade` (`x_palissade`,`y_palissade`),
  KEY `date_fin_palissade` (`date_fin_palissade`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1683 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `petit_equipement`
-- 

CREATE TABLE `petit_equipement` (
  `id_petit_equipement` int(11) NOT NULL auto_increment,
  `nom_petit_equipement` varchar(50) NOT NULL,
  `id_fk_metier_petit_equipement` int(11) NOT NULL,
  PRIMARY KEY  (`id_petit_equipement`),
  UNIQUE KEY `nom_petit_equipement` (`nom_petit_equipement`),
  KEY `id_fk_metier_petit_equipement` (`id_fk_metier_petit_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `plante`
-- 

CREATE TABLE `plante` (
  `id_plante` int(11) NOT NULL auto_increment,
  `id_fk_type_plante` int(11) NOT NULL,
  `x_plante` int(11) NOT NULL,
  `y_plante` int(11) NOT NULL,
  `partie_1_plante` int(11) NOT NULL,
  `partie_2_plante` int(11) default NULL,
  `partie_3_plante` int(11) default NULL,
  `partie_4_plante` int(11) default NULL,
  PRIMARY KEY  (`id_plante`),
  KEY `idx_x_plante_y_plante` (`x_plante`,`y_plante`),
  KEY `id_fk_type_plante` (`id_fk_type_plante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=600769 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `potion`
-- 

CREATE TABLE `potion` (
  `id_potion` int(11) NOT NULL,
  `id_fk_type_potion` int(11) NOT NULL,
  `id_fk_type_qualite_potion` int(11) NOT NULL,
  `niveau_potion` int(11) NOT NULL,
  `date_utilisation_potion` datetime default NULL,
  PRIMARY KEY  (`id_potion`),
  KEY `id_fk_type_potion` (`id_fk_type_potion`),
  KEY `id_fk_type_qualite_potion` (`id_fk_type_qualite_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `prenom_interdit`
-- 

CREATE TABLE `prenom_interdit` (
  `id_prenom_interdit` int(11) NOT NULL auto_increment,
  `texte_prenom_interdit` varchar(30) NOT NULL,
  PRIMARY KEY  (`id_prenom_interdit`),
  UNIQUE KEY `texte_prenom_interdit` (`texte_prenom_interdit`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `quete`
-- 

CREATE TABLE `quete` (
  `id_quete` int(11) NOT NULL auto_increment,
  `id_fk_lieu_quete` int(11) NOT NULL,
  `id_fk_braldun_quete` int(11) NOT NULL,
  `date_creation_quete` datetime NOT NULL,
  `date_fin_quete` datetime default NULL,
  `gain_quete` text,
  `est_initiatique_quete` enum('oui','non') NOT NULL default 'non',
  PRIMARY KEY  (`id_quete`),
  UNIQUE KEY `id_fk_lieu_quete_2` (`id_fk_lieu_quete`,`id_fk_braldun_quete`),
  KEY `id_fk_braldun_quete` (`id_fk_braldun_quete`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `rang_communaute`
-- 

CREATE TABLE `rang_communaute` (
  `id_rang_communaute` int(11) NOT NULL auto_increment,
  `id_fk_communaute_rang_communaute` int(11) NOT NULL,
  `ordre_rang_communaute` int(11) NOT NULL,
  `nom_rang_communaute` varchar(40) NOT NULL,
  `description_rang_communaute` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_rang_communaute`),
  KEY `id_fk_communaute_rang_communaute` (`id_fk_communaute_rang_communaute`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `recette_cout`
-- 

CREATE TABLE `recette_cout` (
  `id_fk_type_equipement_recette_cout` int(11) NOT NULL,
  `niveau_recette_cout` int(11) NOT NULL,
  `cuir_recette_cout` int(11) NOT NULL,
  `fourrure_recette_cout` int(11) NOT NULL,
  `planche_recette_cout` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_equipement_recette_cout`,`niveau_recette_cout`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `recette_cout_minerai`
-- 

CREATE TABLE `recette_cout_minerai` (
  `id_fk_type_equipement_recette_cout_minerai` int(11) NOT NULL COMMENT 'Identifiant sur la table recette_equipement',
  `id_fk_type_recette_cout_minerai` int(11) NOT NULL COMMENT 'Identifiant sur la table type_minerai',
  `niveau_recette_cout_minerai` int(11) NOT NULL,
  `quantite_recette_cout_minerai` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_equipement_recette_cout_minerai`,`id_fk_type_recette_cout_minerai`,`niveau_recette_cout_minerai`),
  KEY `id_fk_type_recette_cout_minerai` (`id_fk_type_recette_cout_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `recette_equipements`
-- 

CREATE TABLE `recette_equipements` (
  `id_recette_equipement` int(11) NOT NULL auto_increment,
  `id_fk_type_recette_equipement` int(11) NOT NULL,
  `niveau_recette_equipement` int(11) NOT NULL,
  `poids_recette_equipement` float NOT NULL,
  `id_fk_type_qualite_recette_equipement` int(11) NOT NULL,
  `armure_recette_equipement` int(11) NOT NULL,
  `force_recette_equipement` int(11) NOT NULL,
  `agilite_recette_equipement` int(11) NOT NULL,
  `vigueur_recette_equipement` int(11) NOT NULL,
  `sagesse_recette_equipement` int(11) NOT NULL,
  `vue_recette_equipement` int(11) NOT NULL,
  `bm_attaque_recette_equipement` int(11) NOT NULL,
  `bm_degat_recette_equipement` int(11) NOT NULL,
  `bm_defense_recette_equipement` int(11) NOT NULL,
  `id_fk_type_emplacement_recette_equipement` int(11) NOT NULL,
  `etat_initial_recette_equipement` int(11) NOT NULL default '2000',
  PRIMARY KEY  (`id_recette_equipement`),
  UNIQUE KEY `id_fk_type_recette_equipement` (`id_fk_type_recette_equipement`,`niveau_recette_equipement`,`id_fk_type_qualite_recette_equipement`),
  KEY `id_fk_type_emplacement_recette_equipement` (`id_fk_type_emplacement_recette_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=864 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `recette_materiel_cout`
-- 

CREATE TABLE `recette_materiel_cout` (
  `id_fk_type_materiel_recette_materiel_cout` int(11) NOT NULL,
  `cuir_recette_materiel_cout` int(11) NOT NULL,
  `fourrure_recette_materiel_cout` int(11) NOT NULL,
  `planche_recette_materiel_cout` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_materiel_recette_materiel_cout`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `recette_materiel_cout_minerai`
-- 

CREATE TABLE `recette_materiel_cout_minerai` (
  `id_fk_type_materiel_recette_materiel_cout_minerai` int(11) NOT NULL COMMENT 'Identifiant sur la table recette_materiel',
  `id_fk_type_recette_materiel_cout_minerai` int(11) NOT NULL COMMENT 'Identifiant sur la table type_minerai',
  `quantite_lingot_recette_materiel_cout_minerai` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_materiel_recette_materiel_cout_minerai`,`id_fk_type_recette_materiel_cout_minerai`),
  KEY `id_fk_type_recette_materiel_cout_minerai` (`id_fk_type_recette_materiel_cout_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `recette_materiel_cout_plante`
-- 

CREATE TABLE `recette_materiel_cout_plante` (
  `id_fk_type_materiel_recette_materiel_cout_plante` int(11) NOT NULL,
  `id_fk_type_plante_recette_materiel_cout_plante` int(11) NOT NULL,
  `id_fk_type_partieplante_recette_materiel_cout_plante` int(11) NOT NULL,
  `quantite_recette_materiel_cout_plante` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_materiel_recette_materiel_cout_plante`,`id_fk_type_plante_recette_materiel_cout_plante`,`id_fk_type_partieplante_recette_materiel_cout_plante`),
  KEY `id_fk_type_plante_recette_materiel_cout_plante` (`id_fk_type_plante_recette_materiel_cout_plante`),
  KEY `id_fk_type_partieplante_recette_materiel_cout_plante` (`id_fk_type_partieplante_recette_materiel_cout_plante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `recette_potions`
-- 

CREATE TABLE `recette_potions` (
  `id_fk_type_potion_recette_potion` int(11) NOT NULL,
  `id_fk_type_plante_recette_potion` int(11) NOT NULL,
  `id_fk_type_partieplante_recette_potion` int(11) NOT NULL,
  `coef_recette_potion` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_potion_recette_potion`,`id_fk_type_plante_recette_potion`,`id_fk_type_partieplante_recette_potion`),
  KEY `id_fk_type_plante_recette_potion` (`id_fk_type_plante_recette_potion`),
  KEY `id_fk_type_partieplante_recette_potion` (`id_fk_type_partieplante_recette_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `recette_vernis`
-- 

CREATE TABLE `recette_vernis` (
  `id_fk_type_potion_recette_vernis` int(11) NOT NULL,
  `id_fk_type_partieplante_recette_vernis` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_type_potion_recette_vernis`,`id_fk_type_partieplante_recette_vernis`),
  KEY `id_fk_type_partieplante_recette_vernis` (`id_fk_type_partieplante_recette_vernis`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `ref_monstre`
-- 

CREATE TABLE `ref_monstre` (
  `id_ref_monstre` int(11) NOT NULL auto_increment,
  `id_fk_type_ref_monstre` int(11) NOT NULL,
  `id_fk_taille_ref_monstre` int(11) NOT NULL,
  `niveau_min_ref_monstre` int(11) NOT NULL,
  `niveau_max_ref_monstre` int(11) NOT NULL,
  `pourcentage_vigueur_ref_monstre` int(11) NOT NULL,
  `pourcentage_agilite_ref_monstre` int(11) NOT NULL,
  `pourcentage_sagesse_ref_monstre` int(11) NOT NULL,
  `pourcentage_force_ref_monstre` int(11) NOT NULL,
  `vue_ref_monstre` int(11) NOT NULL,
  `max_alea_pourcentage_armure_naturelle_ref_monstre` int(11) NOT NULL,
  `min_alea_pourcentage_armure_naturelle_ref_monstre` int(11) NOT NULL,
  `coef_pi_ref_monstre` float NOT NULL default '1.5',
  PRIMARY KEY  (`id_ref_monstre`),
  UNIQUE KEY `id_fk_type_taille_ref_monstre` (`id_fk_type_ref_monstre`,`id_fk_taille_ref_monstre`),
  KEY `id_fk_taille_ref_monstre` (`id_fk_taille_ref_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `region`
-- 

CREATE TABLE `region` (
  `id_region` int(11) NOT NULL auto_increment,
  `nom_region` varchar(20) NOT NULL,
  `nom_systeme_region` varchar(20) NOT NULL,
  `description_region` mediumtext NOT NULL,
  `x_min_region` int(11) NOT NULL,
  `x_max_region` int(11) NOT NULL,
  `y_min_region` int(11) NOT NULL,
  `y_max_region` int(11) NOT NULL,
  `est_pvp_region` enum('oui','non') NOT NULL default 'non',
  `id_fk_distinction_quete_region` int(11) NOT NULL,
  PRIMARY KEY  (`id_region`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `route`
-- 

CREATE TABLE `route` (
  `id_route` int(11) NOT NULL auto_increment,
  `x_route` int(11) NOT NULL,
  `y_route` int(11) NOT NULL,
  `id_fk_braldun_route` int(11) NOT NULL,
  `date_creation_route` datetime NOT NULL,
  `date_fin_route` datetime NOT NULL,
  `id_fk_type_qualite_route` int(11) default NULL,
  PRIMARY KEY  (`id_route`),
  UNIQUE KEY `x_route` (`x_route`,`y_route`),
  KEY `id_fk_braldun_route` (`id_fk_braldun_route`),
  KEY `date_fin_route` (`date_fin_route`),
  KEY `id_fk_type_qualite_route` (`id_fk_type_qualite_route`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=129 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `rune`
-- 

CREATE TABLE `rune` (
  `id_rune` int(11) NOT NULL,
  `id_fk_type_rune` int(11) NOT NULL,
  `est_identifiee_rune` enum('oui','non') NOT NULL default 'non',
  PRIMARY KEY  (`id_rune`),
  KEY `id_fk_type_rune` (`id_fk_type_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `session`
-- 

CREATE TABLE `session` (
  `id_fk_braldun_session` int(11) NOT NULL,
  `id_php_session` varchar(40) NOT NULL,
  `ip_session` varchar(50) NOT NULL,
  `date_derniere_action_session` datetime NOT NULL,
  PRIMARY KEY  (`id_fk_braldun_session`),
  UNIQUE KEY `id_php_session` (`id_php_session`),
  KEY `date_derniere_action_session` (`date_derniere_action_session`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `soule_equipe`
-- 

CREATE TABLE `soule_equipe` (
  `id_soule_equipe` int(11) NOT NULL auto_increment,
  `id_fk_match_soule_equipe` int(11) NOT NULL,
  `date_entree_soule_equipe` datetime NOT NULL,
  `id_fk_braldun_soule_equipe` int(11) NOT NULL,
  `camp_soule_equipe` enum('a','b') NOT NULL default 'a',
  `x_avant_braldun_soule_equipe` int(11) default NULL,
  `y_avant_braldun_soule_equipe` int(11) default NULL,
  `retour_xy_soule_equipe` enum('oui','non') NOT NULL default 'oui',
  `nb_braldun_plaquage_soule_equipe` int(11) NOT NULL,
  `nb_plaque_soule_equipe` int(11) NOT NULL,
  PRIMARY KEY  (`id_soule_equipe`),
  KEY `id_fk_braldun_soule_equipe` (`id_fk_braldun_soule_equipe`),
  KEY `id_fk_match_soule_equipe` (`id_fk_match_soule_equipe`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `soule_match`
-- 

CREATE TABLE `soule_match` (
  `id_soule_match` int(11) NOT NULL auto_increment,
  `id_fk_terrain_soule_match` int(11) NOT NULL,
  `date_debut_soule_match` datetime default NULL,
  `date_fin_soule_match` datetime default NULL,
  `nom_equipea_soule_match` varchar(100) default NULL,
  `nom_equipeb_soule_match` varchar(100) default NULL,
  `x_ballon_soule_match` int(11) default NULL,
  `y_ballon_soule_match` int(11) default NULL,
  `id_fk_joueur_ballon_soule_match` int(11) default NULL,
  `nb_jours_quota_soule_match` int(11) NOT NULL,
  `camp_gagnant_soule_match` enum('a','b') default NULL,
  `px_equipea_soule_match` int(11) NOT NULL,
  `px_equipeb_soule_match` int(11) NOT NULL,
  PRIMARY KEY  (`id_soule_match`),
  KEY `id_fk_terrain_soule_match` (`id_fk_terrain_soule_match`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `soule_nom_equipe`
-- 

CREATE TABLE `soule_nom_equipe` (
  `id_soule_nom_equipe` int(11) NOT NULL auto_increment,
  `nom_soule_nom_equipe` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_soule_nom_equipe`),
  UNIQUE KEY `nom_soule_nom_equipe` (`nom_soule_nom_equipe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `soule_terrain`
-- 

CREATE TABLE `soule_terrain` (
  `id_soule_terrain` int(11) NOT NULL auto_increment,
  `nom_systeme_soule_terrain` varchar(20) NOT NULL,
  `nom_soule_terrain` varchar(20) NOT NULL,
  `info_soule_terrain` varchar(40) NOT NULL,
  `niveau_soule_terrain` int(11) NOT NULL,
  `x_min_soule_terrain` int(11) NOT NULL,
  `x_max_soule_terrain` int(11) NOT NULL,
  `y_min_soule_terrain` int(11) NOT NULL,
  `y_max_soule_terrain` int(11) NOT NULL,
  PRIMARY KEY  (`id_soule_terrain`),
  UNIQUE KEY `nom_systeme_soule_terrain` (`nom_systeme_soule_terrain`),
  UNIQUE KEY `niveau_soule_terrain` (`niveau_soule_terrain`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `stats_experience`
-- 

CREATE TABLE `stats_experience` (
  `id_stats_experience` int(11) NOT NULL auto_increment,
  `id_fk_braldun_stats_experience` int(11) NOT NULL,
  `mois_stats_experience` date NOT NULL,
  `nb_px_perso_gagnes_stats_experience` int(11) NOT NULL,
  `nb_px_commun_gagnes_stats_experience` int(11) NOT NULL,
  `niveau_braldun_stats_experience` int(11) NOT NULL,
  PRIMARY KEY  (`id_stats_experience`),
  UNIQUE KEY `id_braldun_stats_experience` (`id_fk_braldun_stats_experience`,`mois_stats_experience`,`niveau_braldun_stats_experience`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=441 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `stats_fabricants`
-- 

CREATE TABLE `stats_fabricants` (
  `id_stats_fabricants` int(11) NOT NULL auto_increment,
  `id_fk_braldun_stats_fabricants` int(11) NOT NULL,
  `niveau_braldun_stats_fabricants` int(11) NOT NULL,
  `somme_niveau_piece_stats_fabricants` int(11) NOT NULL,
  `mois_stats_fabricants` date NOT NULL,
  `nb_piece_stats_fabricants` int(11) NOT NULL,
  `id_fk_metier_stats_fabricants` int(11) NOT NULL,
  PRIMARY KEY  (`id_stats_fabricants`),
  UNIQUE KEY `id_fk_braldun_stats_fabricants` (`id_fk_braldun_stats_fabricants`,`niveau_braldun_stats_fabricants`,`mois_stats_fabricants`,`id_fk_metier_stats_fabricants`),
  KEY `id_fk_metier_stats_fabricants` (`id_fk_metier_stats_fabricants`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=81 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `stats_mots_runiques`
-- 

CREATE TABLE `stats_mots_runiques` (
  `id_stats_mots_runiques` int(11) NOT NULL auto_increment,
  `id_fk_mot_runique_stats_mots_runiques` int(11) NOT NULL,
  `mois_stats_mots_runiques` date NOT NULL,
  `id_fk_type_piece_stats_mots_runiques` int(11) NOT NULL,
  `niveau_piece_stats_mots_runiques` int(11) NOT NULL,
  `nb_piece_stats_mots_runiques` int(11) NOT NULL,
  PRIMARY KEY  (`id_stats_mots_runiques`),
  UNIQUE KEY `id_fk_mot_runique_stats_mots_runiques` (`id_fk_mot_runique_stats_mots_runiques`,`mois_stats_mots_runiques`,`id_fk_type_piece_stats_mots_runiques`,`niveau_piece_stats_mots_runiques`),
  KEY `id_fk_type_piece_stats_mots_runiques` (`id_fk_type_piece_stats_mots_runiques`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `stats_recolteurs`
-- 

CREATE TABLE `stats_recolteurs` (
  `id_stats_recolteurs` int(11) NOT NULL auto_increment,
  `id_fk_braldun_stats_recolteurs` int(11) NOT NULL,
  `mois_stats_recolteurs` date NOT NULL,
  `niveau_braldun_stats_recolteurs` int(11) NOT NULL,
  `nb_minerai_stats_recolteurs` int(11) NOT NULL,
  `nb_partieplante_stats_recolteurs` int(11) NOT NULL,
  `nb_peau_stats_recolteurs` int(11) NOT NULL,
  `nb_viande_stats_recolteurs` int(11) NOT NULL,
  `nb_bois_stats_recolteurs` int(11) NOT NULL,
  PRIMARY KEY  (`id_stats_recolteurs`),
  UNIQUE KEY `id_fk_braldun_stats_recolteurs` (`id_fk_braldun_stats_recolteurs`,`mois_stats_recolteurs`,`niveau_braldun_stats_recolteurs`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=99 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `stats_routes`
-- 

CREATE TABLE `stats_routes` (
  `id_stats_routes` int(11) NOT NULL auto_increment,
  `id_fk_braldun_stats_routes` int(11) NOT NULL,
  `niveau_braldun_stats_routes` int(11) NOT NULL,
  `mois_stats_routes` date NOT NULL,
  `nb_stats_routes` int(11) NOT NULL,
  `id_fk_metier_stats_routes` int(11) NOT NULL,
  PRIMARY KEY  (`id_stats_routes`),
  UNIQUE KEY `id_fk_braldun_stats_routes` (`id_fk_braldun_stats_routes`,`niveau_braldun_stats_routes`,`mois_stats_routes`,`id_fk_metier_stats_routes`),
  KEY `id_fk_metier_stats_routes` (`id_fk_metier_stats_routes`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `stats_runes`
-- 

CREATE TABLE `stats_runes` (
  `id_stats_runes` int(11) NOT NULL auto_increment,
  `mois_stats_runes` date NOT NULL,
  `id_fk_type_rune_stats_runes` int(11) NOT NULL,
  `nb_rune_stats_runes` int(11) NOT NULL,
  PRIMARY KEY  (`id_stats_runes`),
  UNIQUE KEY `mois_stats_runes` (`mois_stats_runes`,`id_fk_type_rune_stats_runes`),
  KEY `id_fk_type_rune_stats_runes` (`id_fk_type_rune_stats_runes`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `stock_tabac`
-- 

CREATE TABLE `stock_tabac` (
  `id_stock_tabac` int(11) NOT NULL auto_increment,
  `date_stock_tabac` date NOT NULL,
  `id_fk_type_stock_tabac` int(11) NOT NULL,
  `id_fk_region_stock_tabac` int(11) NOT NULL,
  `nb_feuille_initial_stock_tabac` int(11) NOT NULL default '0',
  `nb_feuille_restant_stock_tabac` int(11) NOT NULL,
  `prix_unitaire_vente_stock_tabac` int(11) NOT NULL,
  `prix_unitaire_reprise_stock_tabac` int(11) NOT NULL,
  PRIMARY KEY  (`id_stock_tabac`),
  UNIQUE KEY `unique` (`date_stock_tabac`,`id_fk_type_stock_tabac`,`id_fk_region_stock_tabac`),
  KEY `stock_tabac_ibfk_3` (`id_fk_type_stock_tabac`),
  KEY `stock_tabac_ibfk_4` (`id_fk_region_stock_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1150 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `taille_monstre`
-- 

CREATE TABLE `taille_monstre` (
  `id_taille_monstre` int(11) NOT NULL auto_increment,
  `nom_taille_m_monstre` varchar(20) NOT NULL COMMENT 'Nom de la taille au masculin',
  `nom_taille_f_monstre` varchar(20) NOT NULL COMMENT 'Nom de la taille au féminin',
  `pourcentage_taille_monstre` int(11) NOT NULL COMMENT 'Pourcentage d''apparition',
  `nb_cdm_taille_monstre` int(11) NOT NULL,
  PRIMARY KEY  (`id_taille_monstre`),
  UNIQUE KEY `nom_taille_f_monstre` (`nom_taille_f_monstre`),
  UNIQUE KEY `nom_taille_m_monstre` (`nom_taille_m_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `testeur`
-- 

CREATE TABLE `testeur` (
  `id_testeur` int(11) NOT NULL auto_increment,
  `email_testeur` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_testeur`),
  UNIQUE KEY `email_testeur` (`email_testeur`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_aliment`
-- 

CREATE TABLE `type_aliment` (
  `id_type_aliment` int(11) NOT NULL auto_increment,
  `nom_type_aliment` varchar(50) NOT NULL,
  `nom_systeme_type_aliment` varchar(10) NOT NULL,
  `bbdf_base_type_aliment` int(11) NOT NULL,
  PRIMARY KEY  (`id_type_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_bosquet`
-- 

CREATE TABLE `type_bosquet` (
  `id_type_bosquet` int(11) NOT NULL auto_increment,
  `nom_type_bosquet` varchar(20) NOT NULL,
  `nom_systeme_type_bosquet` varchar(10) NOT NULL,
  `description_type_bosquet` varchar(200) NOT NULL,
  `nb_creation_type_bosquet` int(11) NOT NULL,
  PRIMARY KEY  (`id_type_bosquet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_distinction`
-- 

CREATE TABLE `type_distinction` (
  `id_type_distinction` int(11) NOT NULL auto_increment,
  `nom_systeme_type_distinction` varchar(20) NOT NULL,
  `nom_type_distinction` varchar(40) NOT NULL,
  PRIMARY KEY  (`id_type_distinction`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_emplacement`
-- 

CREATE TABLE `type_emplacement` (
  `id_type_emplacement` int(11) NOT NULL auto_increment,
  `nom_systeme_type_emplacement` varchar(20) NOT NULL,
  `nom_type_emplacement` varchar(20) NOT NULL,
  `ordre_emplacement` int(11) NOT NULL,
  `est_equipable_type_emplacement` enum('oui','non') NOT NULL default 'oui',
  PRIMARY KEY  (`id_type_emplacement`),
  KEY `nom_systeme_type_emplacement` (`nom_systeme_type_emplacement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_equipement`
-- 

CREATE TABLE `type_equipement` (
  `id_type_equipement` int(11) NOT NULL auto_increment,
  `nom_type_equipement` varchar(50) character set utf8 NOT NULL,
  `region_1_nom_type_equipement` varchar(50) character set utf8 NOT NULL,
  `region_2_nom_type_equipement` varchar(50) character set utf8 NOT NULL,
  `region_3_nom_type_equipement` varchar(50) character set utf8 NOT NULL,
  `region_4_nom_type_equipement` varchar(50) character set utf8 NOT NULL,
  `region_5_nom_type_equipement` varchar(50) character set utf8 NOT NULL,
  `id_fk_type_munition_type_equipement` int(11) default NULL,
  `description_type_equipement` varchar(300) character set utf8 default NULL,
  `nb_runes_max_type_equipement` int(11) NOT NULL,
  `id_fk_metier_type_equipement` int(11) NOT NULL,
  `id_fk_type_piece_type_equipement` int(11) NOT NULL,
  `nb_munition_type_equipement` int(11) NOT NULL default '0',
  `genre_type_equipement` enum('masculin','feminin') NOT NULL default 'masculin',
  `id_fk_type_ingredient_base_type_equipement` int(11) NOT NULL,
  PRIMARY KEY  (`id_type_equipement`),
  KEY `nom_type_equipement` (`nom_type_equipement`),
  KEY `id_fk_type_piece_type_equipement` (`id_fk_type_piece_type_equipement`),
  KEY `id_fk_type_munition_type_equipement` (`id_fk_type_munition_type_equipement`),
  KEY `id_fk_type_ingredient_base_type_equipement` (`id_fk_type_ingredient_base_type_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=44 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_etape`
-- 

CREATE TABLE `type_etape` (
  `id_type_etape` int(11) NOT NULL auto_increment,
  `nom_systeme_type_etape` varchar(30) NOT NULL,
  `nom_type_etape` varchar(30) NOT NULL,
  `est_metier_type_etape` enum('oui','non') NOT NULL default 'non',
  `est_initiatique_type_etape` enum('oui','non') NOT NULL default 'non',
  PRIMARY KEY  (`id_type_etape`),
  UNIQUE KEY `nom_systeme_type_etape` (`nom_systeme_type_etape`),
  KEY `est_metier_type_etape` (`est_metier_type_etape`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_etape_metier`
-- 

CREATE TABLE `type_etape_metier` (
  `id_fk_etape_type_etape_metier` int(11) NOT NULL,
  `id_fk_metier_type_etape_metier` int(11) NOT NULL,
  PRIMARY KEY  (`id_fk_etape_type_etape_metier`,`id_fk_metier_type_etape_metier`),
  KEY `id_fk_metier_type_etape_metier` (`id_fk_metier_type_etape_metier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_evenement`
-- 

CREATE TABLE `type_evenement` (
  `id_type_evenement` int(11) NOT NULL auto_increment,
  `nom_type_evenement` varchar(20) NOT NULL,
  PRIMARY KEY  (`id_type_evenement`),
  UNIQUE KEY `nom_type_evenement` (`nom_type_evenement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_groupe_monstre`
-- 

CREATE TABLE `type_groupe_monstre` (
  `id_type_groupe_monstre` int(11) NOT NULL auto_increment,
  `nom_groupe_monstre` varchar(20) NOT NULL,
  `nb_membres_min_type_groupe_monstre` int(11) NOT NULL,
  `nb_membres_max_type_groupe_monstre` int(11) NOT NULL,
  `repeuplement_type_groupe_monstre` enum('oui','non') NOT NULL default 'non',
  PRIMARY KEY  (`id_type_groupe_monstre`),
  UNIQUE KEY `nom_groupe_monstre` (`nom_groupe_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_historique_equipement`
-- 

CREATE TABLE `type_historique_equipement` (
  `id_type_historique_equipement` int(11) NOT NULL auto_increment,
  `nom_type_historique_equipement` varchar(20) NOT NULL,
  PRIMARY KEY  (`id_type_historique_equipement`),
  UNIQUE KEY `nom_type_historique_equipement` (`nom_type_historique_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_historique_materiel`
-- 

CREATE TABLE `type_historique_materiel` (
  `id_type_historique_materiel` int(11) NOT NULL auto_increment,
  `nom_type_historique_materiel` varchar(20) NOT NULL,
  PRIMARY KEY  (`id_type_historique_materiel`),
  UNIQUE KEY `nom_type_historique_materiel` (`nom_type_historique_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_historique_potion`
-- 

CREATE TABLE `type_historique_potion` (
  `id_type_historique_potion` int(11) NOT NULL auto_increment,
  `nom_type_historique_potion` varchar(20) NOT NULL,
  PRIMARY KEY  (`id_type_historique_potion`),
  UNIQUE KEY `nom_type_historique_potion` (`nom_type_historique_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_historique_rune`
-- 

CREATE TABLE `type_historique_rune` (
  `id_type_historique_rune` int(11) NOT NULL auto_increment,
  `nom_type_historique_rune` varchar(20) NOT NULL,
  PRIMARY KEY  (`id_type_historique_rune`),
  UNIQUE KEY `nom_type_historique_rune` (`nom_type_historique_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_ingredient`
-- 

CREATE TABLE `type_ingredient` (
  `id_type_ingredient` int(11) NOT NULL auto_increment,
  `nom_systeme_type_ingredient` varchar(10) NOT NULL,
  `nom_type_ingredient` varchar(10) NOT NULL,
  `id_fk_type_minerai_ingredient` int(11) default NULL,
  PRIMARY KEY  (`id_type_ingredient`),
  UNIQUE KEY `nom_systeme_type_ingredient` (`nom_systeme_type_ingredient`,`nom_type_ingredient`),
  KEY `id_fk_type_minerai_ingredient` (`id_fk_type_minerai_ingredient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_lieu`
-- 

CREATE TABLE `type_lieu` (
  `id_type_lieu` int(11) NOT NULL auto_increment,
  `nom_type_lieu` varchar(20) NOT NULL,
  `nom_systeme_type_lieu` varchar(20) NOT NULL,
  `description_type_lieu` mediumtext NOT NULL,
  `niveau_min_type_lieu` int(2) NOT NULL,
  `pa_utilisation_type_lieu` int(1) NOT NULL,
  `est_alterable_type_lieu` enum('oui','non') NOT NULL,
  `est_franchissable_type_lieu` enum('oui','non') NOT NULL,
  PRIMARY KEY  (`id_type_lieu`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_materiel`
-- 

CREATE TABLE `type_materiel` (
  `id_type_materiel` int(11) NOT NULL auto_increment,
  `nom_type_materiel` varchar(50) NOT NULL,
  `nom_systeme_type_materiel` varchar(20) NOT NULL,
  `description_type_materiel` varchar(300) default NULL,
  `id_fk_metier_type_materiel` int(11) NOT NULL,
  `durabilite_type_materiel` int(11) NOT NULL,
  `usure_type_materiel` int(11) NOT NULL,
  `capacite_type_materiel` int(11) NOT NULL,
  `poids_type_materiel` float NOT NULL default '0',
  `force_base_min_type_materiel` int(11) NOT NULL default '0',
  `agilite_base_min_type_materiel` int(11) NOT NULL default '0',
  `sagesse_base_min_type_materiel` int(11) NOT NULL default '0',
  `vigueur_base_min_type_materiel` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_type_materiel`),
  UNIQUE KEY `nom_type_materiel_2` (`nom_type_materiel`),
  UNIQUE KEY `nom_systeme_type_materiel` (`nom_systeme_type_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_materiel_assemble`
-- 

CREATE TABLE `type_materiel_assemble` (
  `id_base_type_materiel_assemble` int(11) NOT NULL,
  `id_supplement_type_materiel_assemble` int(11) NOT NULL,
  PRIMARY KEY  (`id_base_type_materiel_assemble`,`id_supplement_type_materiel_assemble`),
  KEY `id_supplement_type_materiel_assemble` (`id_supplement_type_materiel_assemble`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_message`
-- 

CREATE TABLE `type_message` (
  `id_type_message` int(11) NOT NULL auto_increment,
  `nom_systeme_type_message` varchar(20) NOT NULL,
  `nom_type_message` varchar(30) NOT NULL,
  PRIMARY KEY  (`id_type_message`),
  UNIQUE KEY `nom_systeme_type_message` (`nom_systeme_type_message`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_minerai`
-- 

CREATE TABLE `type_minerai` (
  `id_type_minerai` int(11) NOT NULL auto_increment,
  `nom_type_minerai` varchar(20) character set latin1 NOT NULL,
  `nom_systeme_type_minerai` varchar(10) character set latin1 NOT NULL,
  `description_type_minerai` varchar(200) character set latin1 NOT NULL,
  `nb_creation_type_minerai` int(11) NOT NULL,
  PRIMARY KEY  (`id_type_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_monstre`
-- 

CREATE TABLE `type_monstre` (
  `id_type_monstre` int(11) NOT NULL auto_increment,
  `nom_type_monstre` varchar(30) NOT NULL,
  `genre_type_monstre` enum('feminin','masculin') NOT NULL COMMENT 'Genre du monstre : masculin ou féminin',
  `id_fk_type_groupe_monstre` int(11) NOT NULL,
  `nb_creation_type_monstre` int(11) NOT NULL,
  PRIMARY KEY  (`id_type_monstre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_munition`
-- 

CREATE TABLE `type_munition` (
  `id_type_munition` int(11) NOT NULL auto_increment,
  `nom_systeme_type_munition` varchar(15) NOT NULL,
  `nom_type_munition` varchar(15) NOT NULL,
  `nom_pluriel_type_munition` varchar(15) NOT NULL,
  PRIMARY KEY  (`id_type_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_partieplante`
-- 

CREATE TABLE `type_partieplante` (
  `id_type_partieplante` int(11) NOT NULL auto_increment,
  `nom_type_partieplante` varchar(20) NOT NULL,
  `nom_systeme_type_partieplante` varchar(10) NOT NULL,
  `description_type_partieplante` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_type_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_piece`
-- 

CREATE TABLE `type_piece` (
  `id_type_piece` int(11) NOT NULL auto_increment,
  `nom_systeme_type_piece` varchar(10) NOT NULL,
  `nom_type_piece` varchar(20) NOT NULL,
  PRIMARY KEY  (`id_type_piece`),
  UNIQUE KEY `nom_systeme_type_piece` (`nom_systeme_type_piece`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_plante`
-- 

CREATE TABLE `type_plante` (
  `id_type_plante` int(11) NOT NULL auto_increment,
  `nom_type_plante` varchar(20) NOT NULL,
  `nom_systeme_type_plante` varchar(200) NOT NULL,
  `prefix_type_plante` varchar(3) NOT NULL,
  `categorie_type_plante` enum('Arbre','Buisson','Fleur') NOT NULL,
  `id_fk_environnement_type_plante` int(11) NOT NULL,
  `id_fk_partieplante1_type_plante` int(11) NOT NULL,
  `id_fk_partieplante2_type_plante` int(11) default NULL,
  `id_fk_partieplante3_type_plante` int(11) default NULL,
  `id_fk_partieplante4_type_plante` int(11) default NULL,
  `nb_creation_type_plante` int(11) NOT NULL,
  PRIMARY KEY  (`id_type_plante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_potion`
-- 

CREATE TABLE `type_potion` (
  `id_type_potion` int(11) NOT NULL auto_increment,
  `nom_type_potion` varchar(20) NOT NULL,
  `caract_type_potion` enum('FOR','AGI','VIG','SAG','PV','VUE','ARM','POIDS','ATT','DEG','DEF') default NULL,
  `bm_type_potion` enum('bonus','malus') default NULL,
  `type_potion` enum('potion','vernis_reparateur','vernis_enchanteur') NOT NULL,
  `id_fk_type_ingredient_type_potion` int(11) default NULL COMMENT 'type base à rénover',
  `template_m_type_potion` varchar(20) default NULL,
  `template_f_type_potion` varchar(20) default NULL,
  `caract2_type_potion` enum('FOR','AGI','VIG','SAG','PV','VUE','ARM','POIDS','ATT','DEG','DEF') default NULL,
  `bm2_type_potion` enum('bonus','malus') default NULL,
  PRIMARY KEY  (`id_type_potion`),
  UNIQUE KEY `nom_type_potion` (`nom_type_potion`),
  KEY `id_fk_type_ingredient_type_potion` (`id_fk_type_ingredient_type_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_qualite`
-- 

CREATE TABLE `type_qualite` (
  `id_type_qualite` int(11) NOT NULL auto_increment,
  `nom_systeme_type_qualite` varchar(10) NOT NULL,
  `nom_type_qualite` varchar(10) NOT NULL,
  `nom_aliment_type_qualite` varchar(10) NOT NULL,
  PRIMARY KEY  (`id_type_qualite`),
  KEY `nom_systeme_type_qualite` (`nom_systeme_type_qualite`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_rang_communaute`
-- 

CREATE TABLE `type_rang_communaute` (
  `id_type_rang_communaute` int(11) NOT NULL auto_increment,
  `nom_type_rang_communaute` varchar(10) NOT NULL,
  PRIMARY KEY  (`id_type_rang_communaute`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_rune`
-- 

CREATE TABLE `type_rune` (
  `id_type_rune` int(11) NOT NULL auto_increment,
  `nom_type_rune` varchar(2) NOT NULL,
  `effet_type_rune` varchar(200) NOT NULL,
  `sagesse_type_rune` int(11) NOT NULL,
  `type_type_rune` enum('caracteristique','metier') NOT NULL,
  `niveau_type_rune` enum('a','b','c','d') NOT NULL,
  `image_type_rune` varchar(250) default NULL,
  PRIMARY KEY  (`id_type_rune`),
  UNIQUE KEY `nom_type_rune` (`nom_type_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_tabac`
-- 

CREATE TABLE `type_tabac` (
  `id_type_tabac` int(11) NOT NULL auto_increment,
  `nom_type_tabac` varchar(20) NOT NULL,
  `nom_court_type_tabac` varchar(15) character set utf8 NOT NULL,
  `nom_systeme_type_tabac` varchar(10) NOT NULL,
  `description_type_tabac` varchar(200) NOT NULL,
  PRIMARY KEY  (`id_type_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_titre`
-- 

CREATE TABLE `type_titre` (
  `id_type_titre` int(11) NOT NULL auto_increment,
  `nom_masculin_type_titre` varchar(15) NOT NULL,
  `nom_feminin_type_titre` varchar(15) NOT NULL,
  `nom_systeme_type_titre` varchar(8) NOT NULL,
  `description_type_titre` varchar(10) NOT NULL,
  PRIMARY KEY  (`id_type_titre`),
  UNIQUE KEY `nom_systeme_type_titre` (`nom_systeme_type_titre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `type_unite`
-- 

CREATE TABLE `type_unite` (
  `id_type_unite` int(11) NOT NULL auto_increment,
  `nom_systeme_type_unite` varchar(10) NOT NULL,
  `nom_type_unite` varchar(10) NOT NULL,
  `nom_pluriel_type_unite` varchar(10) NOT NULL,
  PRIMARY KEY  (`id_type_unite`),
  UNIQUE KEY `nom_systeme_type_unite` (`nom_systeme_type_unite`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente`
-- 

CREATE TABLE `vente` (
  `id_vente` int(11) NOT NULL auto_increment,
  `id_fk_braldun_vente` int(11) NOT NULL,
  `date_debut_vente` datetime NOT NULL,
  `date_fin_vente` datetime NOT NULL,
  `commentaire_vente` varchar(100) default NULL,
  `unite_1_vente` int(11) NOT NULL default '0',
  `unite_2_vente` int(11) NOT NULL default '0',
  `unite_3_vente` int(11) NOT NULL default '0',
  `prix_1_vente` int(11) NOT NULL default '0',
  `prix_2_vente` int(11) NOT NULL default '0',
  `prix_3_vente` int(11) NOT NULL default '0',
  `type_vente` enum('aliment','element','equipement','materiel','minerai','munition','partieplante','potion','rune','tabac') default NULL,
  PRIMARY KEY  (`id_vente`),
  KEY `id_fk_braldun_vente` (`id_fk_braldun_vente`),
  KEY `date_fin_vente` (`date_fin_vente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_aliment`
-- 

CREATE TABLE `vente_aliment` (
  `id_vente_aliment` int(11) NOT NULL,
  `id_fk_vente_aliment` int(11) NOT NULL,
  `id_fk_type_vente_aliment` int(11) NOT NULL,
  `id_fk_type_qualite_vente_aliment` int(11) NOT NULL,
  `bbdf_vente_aliment` int(11) NOT NULL,
  PRIMARY KEY  (`id_vente_aliment`),
  KEY `id_fk_type_vente_aliment` (`id_fk_type_vente_aliment`),
  KEY `id_fk_type_qualite_vente_aliment` (`id_fk_type_qualite_vente_aliment`),
  KEY `id_fk_vente_aliment` (`id_fk_vente_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_element`
-- 

CREATE TABLE `vente_element` (
  `id_vente_element` int(11) NOT NULL auto_increment,
  `id_fk_vente_element` int(11) NOT NULL,
  `type_vente_element` enum('viande_fraiche','peau','viande_preparee','cuir','fourrure','planche','castar','rondin') NOT NULL,
  `quantite_vente_element` int(11) NOT NULL,
  PRIMARY KEY  (`id_vente_element`),
  KEY `id_fk_vente_element` (`id_fk_vente_element`,`type_vente_element`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_equipement`
-- 

CREATE TABLE `vente_equipement` (
  `id_vente_equipement` int(11) NOT NULL,
  `id_fk_vente_equipement` int(11) NOT NULL,
  PRIMARY KEY  (`id_vente_equipement`),
  KEY `id_fk_vente_equipement` (`id_fk_vente_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_materiel`
-- 

CREATE TABLE `vente_materiel` (
  `id_vente_materiel` int(11) NOT NULL,
  `id_fk_vente_materiel` int(11) NOT NULL,
  PRIMARY KEY  (`id_vente_materiel`),
  KEY `vente_materiel_ibfk_2` (`id_fk_vente_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_minerai`
-- 

CREATE TABLE `vente_minerai` (
  `id_vente_minerai` int(11) NOT NULL auto_increment,
  `id_fk_type_vente_minerai` int(11) NOT NULL,
  `id_fk_vente_minerai` int(11) NOT NULL,
  `type_vente_minerai` enum('brut','lingot') character set utf8 NOT NULL,
  `quantite_vente_minerai` int(11) NOT NULL,
  PRIMARY KEY  (`id_vente_minerai`),
  KEY `id_fk_vente_minerai` (`id_fk_vente_minerai`),
  KEY `vente_minerai_ibfk_2` (`id_fk_type_vente_minerai`),
  KEY `type_vente_minerai` (`type_vente_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_munition`
-- 

CREATE TABLE `vente_munition` (
  `id_vente_munition` int(11) NOT NULL auto_increment,
  `id_fk_vente_munition` int(11) NOT NULL,
  `id_fk_type_vente_munition` int(11) NOT NULL,
  `quantite_vente_munition` int(11) NOT NULL,
  PRIMARY KEY  (`id_vente_munition`),
  KEY `id_fk_vente_munition` (`id_fk_vente_munition`),
  KEY `vente_munition_ibfk_2` (`id_fk_type_vente_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_partieplante`
-- 

CREATE TABLE `vente_partieplante` (
  `id_vente_partieplante` int(11) NOT NULL auto_increment,
  `id_fk_vente_partieplante` int(11) NOT NULL,
  `id_fk_type_vente_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_vente_partieplante` int(11) NOT NULL,
  `type_vente_partieplante` enum('brute','preparee') character set utf8 NOT NULL,
  `quantite_vente_partieplante` int(11) NOT NULL,
  PRIMARY KEY  (`id_vente_partieplante`),
  KEY `id_fk_type_plante_vente_partieplante` (`id_fk_type_plante_vente_partieplante`),
  KEY `id_fk_vente_partieplante` (`id_fk_vente_partieplante`),
  KEY `vente_partieplante_ibfk_1` (`id_fk_type_vente_partieplante`),
  KEY `type_vente_partieplante` (`type_vente_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_potion`
-- 

CREATE TABLE `vente_potion` (
  `id_vente_potion` int(11) NOT NULL,
  `id_fk_vente_potion` int(11) NOT NULL,
  PRIMARY KEY  (`id_vente_potion`),
  KEY `id_fk_vente_potion` (`id_fk_vente_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_prix_minerai`
-- 

CREATE TABLE `vente_prix_minerai` (
  `id_fk_type_vente_prix_minerai` int(11) NOT NULL,
  `id_fk_vente_prix_minerai` int(11) NOT NULL,
  `type_prix_minerai` enum('brut','lingot') NOT NULL,
  `prix_vente_prix_minerai` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_type_vente_prix_minerai`,`id_fk_vente_prix_minerai`,`type_prix_minerai`),
  KEY `id_fk_vente_prix_minerai` (`id_fk_vente_prix_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_prix_partieplante`
-- 

CREATE TABLE `vente_prix_partieplante` (
  `id_fk_type_vente_prix_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_vente_prix_partieplante` int(11) NOT NULL,
  `id_fk_vente_prix_partieplante` int(11) NOT NULL,
  `type_prix_partieplante` enum('brute','preparee') NOT NULL,
  `prix_vente_prix_partieplante` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id_fk_type_vente_prix_partieplante`,`id_fk_type_plante_vente_prix_partieplante`,`id_fk_vente_prix_partieplante`,`type_prix_partieplante`),
  KEY `id_fk_type_plante_vente_prix_partieplante` (`id_fk_type_plante_vente_prix_partieplante`),
  KEY `id_fk_vente_prix_partieplante` (`id_fk_vente_prix_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_rune`
-- 

CREATE TABLE `vente_rune` (
  `id_rune_vente_rune` int(11) NOT NULL,
  `id_fk_vente_rune` int(11) NOT NULL,
  PRIMARY KEY  (`id_rune_vente_rune`),
  KEY `id_fk_vente_rune` (`id_fk_vente_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `vente_tabac`
-- 

CREATE TABLE `vente_tabac` (
  `id_vente_tabac` int(11) NOT NULL auto_increment,
  `id_fk_vente_tabac` int(11) NOT NULL,
  `id_fk_type_vente_tabac` int(11) NOT NULL,
  `quantite_feuille_vente_tabac` int(11) default '0',
  PRIMARY KEY  (`id_vente_tabac`),
  KEY `id_fk_vente_tabac` (`id_fk_vente_tabac`),
  KEY `vente_tabac_ibfk_2` (`id_fk_type_vente_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `ville`
-- 

CREATE TABLE `ville` (
  `id_ville` int(11) NOT NULL auto_increment,
  `nom_ville` varchar(20) NOT NULL,
  `description_ville` varchar(200) NOT NULL,
  `nom_systeme_ville` varchar(20) NOT NULL,
  `id_fk_region_ville` int(11) NOT NULL,
  `est_capitale_ville` enum('oui','non') NOT NULL,
  `x_min_ville` int(11) NOT NULL,
  `y_min_ville` int(11) NOT NULL,
  `x_max_ville` int(11) NOT NULL,
  `y_max_ville` int(11) NOT NULL,
  PRIMARY KEY  (`id_ville`),
  KEY `id_fk_region_ville` (`id_fk_region_ville`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `zone`
-- 

CREATE TABLE `zone` (
  `id_zone` int(11) NOT NULL auto_increment,
  `id_fk_environnement_zone` int(11) NOT NULL,
  `nom_zone` varchar(100) NOT NULL,
  `description_zone` varchar(100) NOT NULL,
  `image_zone` varchar(100) NOT NULL,
  `x_min_zone` int(11) NOT NULL,
  `x_max_zone` int(11) NOT NULL,
  `y_min_zone` int(11) NOT NULL,
  `y_max_zone` int(11) NOT NULL,
  `est_soule_zone` enum('oui','non') NOT NULL default 'non',
  PRIMARY KEY  (`id_zone`),
  KEY `id_fk_environnement_zone` (`id_fk_environnement_zone`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ;

-- 
-- Contraintes pour les tables exportées
-- 

-- 
-- Contraintes pour la table `bosquet`
-- 
ALTER TABLE `bosquet`
  ADD CONSTRAINT `bosquet_ibfk_1` FOREIGN KEY (`id_fk_type_bosquet_bosquet`) REFERENCES `type_bosquet` (`id_type_bosquet`);

-- 
-- Contraintes pour la table `boutique_bois`
-- 
ALTER TABLE `boutique_bois`
  ADD CONSTRAINT `boutique_bois_ibfk_1` FOREIGN KEY (`id_fk_lieu_boutique_bois`) REFERENCES `lieu` (`id_lieu`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_bois_ibfk_2` FOREIGN KEY (`id_fk_braldun_boutique_bois`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `boutique_minerai`
-- 
ALTER TABLE `boutique_minerai`
  ADD CONSTRAINT `boutique_minerai_ibfk_6` FOREIGN KEY (`id_fk_type_boutique_minerai`) REFERENCES `type_minerai` (`id_type_minerai`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_minerai_ibfk_7` FOREIGN KEY (`id_fk_lieu_boutique_minerai`) REFERENCES `lieu` (`id_lieu`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_minerai_ibfk_8` FOREIGN KEY (`id_fk_braldun_boutique_minerai`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_minerai_ibfk_9` FOREIGN KEY (`id_fk_region_boutique_minerai`) REFERENCES `region` (`id_region`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `boutique_partieplante`
-- 
ALTER TABLE `boutique_partieplante`
  ADD CONSTRAINT `boutique_partieplante_ibfk_5` FOREIGN KEY (`id_fk_type_boutique_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_partieplante_ibfk_6` FOREIGN KEY (`id_fk_type_plante_boutique_partieplante`) REFERENCES `type_plante` (`id_type_plante`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_partieplante_ibfk_7` FOREIGN KEY (`id_fk_lieu_boutique_partieplante`) REFERENCES `lieu` (`id_lieu`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_partieplante_ibfk_8` FOREIGN KEY (`id_fk_braldun_boutique_partieplante`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_partieplante_ibfk_9` FOREIGN KEY (`id_fk_region_boutique_partieplante`) REFERENCES `region` (`id_region`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `boutique_peau`
-- 
ALTER TABLE `boutique_peau`
  ADD CONSTRAINT `boutique_peau_ibfk_1` FOREIGN KEY (`id_fk_lieu_boutique_peau`) REFERENCES `lieu` (`id_lieu`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_peau_ibfk_2` FOREIGN KEY (`id_fk_braldun_boutique_peau`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `boutique_tabac`
-- 
ALTER TABLE `boutique_tabac`
  ADD CONSTRAINT `boutique_tabac_ibfk_6` FOREIGN KEY (`id_fk_type_boutique_tabac`) REFERENCES `type_tabac` (`id_type_tabac`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_tabac_ibfk_7` FOREIGN KEY (`id_fk_lieu_boutique_tabac`) REFERENCES `lieu` (`id_lieu`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_tabac_ibfk_8` FOREIGN KEY (`id_fk_braldun_boutique_tabac`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `boutique_tabac_ibfk_9` FOREIGN KEY (`id_fk_region_boutique_tabac`) REFERENCES `region` (`id_region`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `charrette`
-- 
ALTER TABLE `charrette`
  ADD CONSTRAINT `charrette_ibfk_2` FOREIGN KEY (`id_charrette`) REFERENCES `ids_materiel` (`id_ids_materiel`),
  ADD CONSTRAINT `charrette_ibfk_3` FOREIGN KEY (`id_fk_braldun_charrette`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `charrette_aliment`
-- 
ALTER TABLE `charrette_aliment`
  ADD CONSTRAINT `charrette_aliment_ibfk_10` FOREIGN KEY (`id_fk_type_qualite_charrette_aliment`) REFERENCES `type_qualite` (`id_type_qualite`),
  ADD CONSTRAINT `charrette_aliment_ibfk_8` FOREIGN KEY (`id_fk_charrette_aliment`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE,
  ADD CONSTRAINT `charrette_aliment_ibfk_9` FOREIGN KEY (`id_fk_type_charrette_aliment`) REFERENCES `type_aliment` (`id_type_aliment`);

-- 
-- Contraintes pour la table `charrette_equipement`
-- 
ALTER TABLE `charrette_equipement`
  ADD CONSTRAINT `charrette_equipement_ibfk_16` FOREIGN KEY (`id_charrette_equipement`) REFERENCES `equipement` (`id_equipement`),
  ADD CONSTRAINT `charrette_equipement_ibfk_17` FOREIGN KEY (`id_fk_charrette_equipement`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `charrette_materiel`
-- 
ALTER TABLE `charrette_materiel`
  ADD CONSTRAINT `charrette_materiel_ibfk_4` FOREIGN KEY (`id_fk_charrette_materiel`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `charrette_materiel_assemble`
-- 
ALTER TABLE `charrette_materiel_assemble`
  ADD CONSTRAINT `charrette_materiel_assemble_ibfk_6` FOREIGN KEY (`id_charrette_materiel_assemble`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `charrette_minerai`
-- 
ALTER TABLE `charrette_minerai`
  ADD CONSTRAINT `charrette_minerai_ibfk_1` FOREIGN KEY (`id_fk_type_charrette_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `charrette_minerai_ibfk_2` FOREIGN KEY (`id_fk_charrette_minerai`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `charrette_munition`
-- 
ALTER TABLE `charrette_munition`
  ADD CONSTRAINT `charrette_munition_ibfk_1` FOREIGN KEY (`id_fk_type_charrette_munition`) REFERENCES `type_munition` (`id_type_munition`),
  ADD CONSTRAINT `charrette_munition_ibfk_2` FOREIGN KEY (`id_fk_charrette_munition`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `charrette_partieplante`
-- 
ALTER TABLE `charrette_partieplante`
  ADD CONSTRAINT `charrette_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_charrette_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `charrette_partieplante_ibfk_2` FOREIGN KEY (`id_fk_type_plante_charrette_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `charrette_partieplante_ibfk_3` FOREIGN KEY (`id_fk_charrette_partieplante`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `charrette_potion`
-- 
ALTER TABLE `charrette_potion`
  ADD CONSTRAINT `charrette_potion_ibfk_6` FOREIGN KEY (`id_fk_charrette_potion`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `charrette_rune`
-- 
ALTER TABLE `charrette_rune`
  ADD CONSTRAINT `charrette_rune_ibfk_4` FOREIGN KEY (`id_fk_charrette_rune`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `charrette_tabac`
-- 
ALTER TABLE `charrette_tabac`
  ADD CONSTRAINT `charrette_tabac_ibfk_1` FOREIGN KEY (`id_fk_type_charrette_tabac`) REFERENCES `type_tabac` (`id_type_tabac`),
  ADD CONSTRAINT `charrette_tabac_ibfk_2` FOREIGN KEY (`id_fk_charrette_tabac`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `coffre`
-- 
ALTER TABLE `coffre`
  ADD CONSTRAINT `coffre_ibfk_1` FOREIGN KEY (`id_fk_braldun_coffre`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `coffre_aliment`
-- 
ALTER TABLE `coffre_aliment`
  ADD CONSTRAINT `coffre_aliment_ibfk_3` FOREIGN KEY (`id_fk_type_coffre_aliment`) REFERENCES `type_aliment` (`id_type_aliment`),
  ADD CONSTRAINT `coffre_aliment_ibfk_4` FOREIGN KEY (`id_fk_braldun_coffre_aliment`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `coffre_aliment_ibfk_5` FOREIGN KEY (`id_fk_type_qualite_coffre_aliment`) REFERENCES `type_qualite` (`id_type_qualite`);

-- 
-- Contraintes pour la table `coffre_equipement`
-- 
ALTER TABLE `coffre_equipement`
  ADD CONSTRAINT `coffre_equipement_ibfk_23` FOREIGN KEY (`id_coffre_equipement`) REFERENCES `equipement` (`id_equipement`),
  ADD CONSTRAINT `coffre_equipement_ibfk_24` FOREIGN KEY (`id_fk_braldun_coffre_equipement`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `coffre_materiel`
-- 
ALTER TABLE `coffre_materiel`
  ADD CONSTRAINT `coffre_materiel_ibfk_2` FOREIGN KEY (`id_fk_braldun_coffre_materiel`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `coffre_minerai`
-- 
ALTER TABLE `coffre_minerai`
  ADD CONSTRAINT `coffre_minerai_ibfk_2` FOREIGN KEY (`id_fk_type_coffre_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `coffre_minerai_ibfk_3` FOREIGN KEY (`id_fk_braldun_coffre_minerai`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `coffre_munition`
-- 
ALTER TABLE `coffre_munition`
  ADD CONSTRAINT `coffre_munition_ibfk_2` FOREIGN KEY (`id_fk_type_coffre_munition`) REFERENCES `type_munition` (`id_type_munition`),
  ADD CONSTRAINT `coffre_munition_ibfk_3` FOREIGN KEY (`id_fk_braldun_coffre_munition`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `coffre_partieplante`
-- 
ALTER TABLE `coffre_partieplante`
  ADD CONSTRAINT `coffre_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_coffre_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `coffre_partieplante_ibfk_2` FOREIGN KEY (`id_fk_type_plante_coffre_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `coffre_partieplante_ibfk_3` FOREIGN KEY (`id_fk_braldun_coffre_partieplante`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `coffre_potion`
-- 
ALTER TABLE `coffre_potion`
  ADD CONSTRAINT `coffre_potion_ibfk_6` FOREIGN KEY (`id_fk_braldun_coffre_potion`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `coffre_rune`
-- 
ALTER TABLE `coffre_rune`
  ADD CONSTRAINT `coffre_rune_ibfk_3` FOREIGN KEY (`id_fk_braldun_coffre_rune`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `coffre_tabac`
-- 
ALTER TABLE `coffre_tabac`
  ADD CONSTRAINT `coffre_tabac_ibfk_2` FOREIGN KEY (`id_fk_type_coffre_tabac`) REFERENCES `type_tabac` (`id_type_tabac`),
  ADD CONSTRAINT `coffre_tabac_ibfk_3` FOREIGN KEY (`id_fk_braldun_coffre_tabac`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `communaute`
-- 
ALTER TABLE `communaute`
  ADD CONSTRAINT `communaute_ibfk_1` FOREIGN KEY (`id_fk_braldun_gestionnaire_communaute`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `competence`
-- 
ALTER TABLE `competence`
  ADD CONSTRAINT `competence_ibfk_1` FOREIGN KEY (`id_fk_metier_competence`) REFERENCES `metier` (`id_metier`),
  ADD CONSTRAINT `competence_ibfk_2` FOREIGN KEY (`id_fk_type_tabac_competence`) REFERENCES `type_tabac` (`id_type_tabac`) ON DELETE SET NULL;

-- 
-- Contraintes pour la table `creation_bosquets`
-- 
ALTER TABLE `creation_bosquets`
  ADD CONSTRAINT `creation_bosquets_ibfk_1` FOREIGN KEY (`id_fk_type_bosquet_creation_bosquets`) REFERENCES `type_bosquet` (`id_type_bosquet`) ON DELETE CASCADE,
  ADD CONSTRAINT `creation_bosquets_ibfk_2` FOREIGN KEY (`id_fk_environnement_creation_bosquets`) REFERENCES `environnement` (`id_environnement`) ON UPDATE CASCADE;

-- 
-- Contraintes pour la table `creation_minerais`
-- 
ALTER TABLE `creation_minerais`
  ADD CONSTRAINT `creation_minerais_ibfk_1` FOREIGN KEY (`id_fk_type_minerai_creation_minerais`) REFERENCES `type_minerai` (`id_type_minerai`) ON DELETE CASCADE,
  ADD CONSTRAINT `creation_minerais_ibfk_2` FOREIGN KEY (`id_fk_environnement_creation_minerais`) REFERENCES `environnement` (`id_environnement`) ON UPDATE CASCADE;

-- 
-- Contraintes pour la table `creation_monstres`
-- 
ALTER TABLE `creation_monstres`
  ADD CONSTRAINT `creation_monstres_ibfk_1` FOREIGN KEY (`id_fk_type_monstre_creation_monstres`) REFERENCES `type_monstre` (`id_type_monstre`) ON DELETE CASCADE,
  ADD CONSTRAINT `creation_monstres_ibfk_2` FOREIGN KEY (`id_fk_environnement_creation_monstres`) REFERENCES `environnement` (`id_environnement`) ON UPDATE CASCADE;

-- 
-- Contraintes pour la table `creation_plantes`
-- 
ALTER TABLE `creation_plantes`
  ADD CONSTRAINT `creation_plantes_ibfk_1` FOREIGN KEY (`id_fk_type_plante_creation_plantes`) REFERENCES `type_plante` (`id_type_plante`) ON DELETE CASCADE,
  ADD CONSTRAINT `creation_plantes_ibfk_2` FOREIGN KEY (`id_fk_environnement_creation_plantes`) REFERENCES `environnement` (`id_environnement`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `donjon`
-- 
ALTER TABLE `donjon`
  ADD CONSTRAINT `donjon_ibfk_1` FOREIGN KEY (`id_fk_lieu_donjon`) REFERENCES `lieu` (`id_lieu`),
  ADD CONSTRAINT `donjon_ibfk_2` FOREIGN KEY (`id_fk_region_donjon`) REFERENCES `region` (`id_region`),
  ADD CONSTRAINT `donjon_ibfk_3` FOREIGN KEY (`id_fk_pnj_donjon`) REFERENCES `braldun` (`id_braldun`);

-- 
-- Contraintes pour la table `donjon_equipe`
-- 
ALTER TABLE `donjon_equipe`
  ADD CONSTRAINT `donjon_equipe_ibfk_1` FOREIGN KEY (`id_fk_donjon_equipe`) REFERENCES `donjon` (`id_donjon`);

-- 
-- Contraintes pour la table `donjon_braldun`
-- 
ALTER TABLE `donjon_braldun`
  ADD CONSTRAINT `donjon_braldun_ibfk_1` FOREIGN KEY (`id_fk_braldun_donjon_braldun`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `donjon_braldun_ibfk_2` FOREIGN KEY (`id_fk_equipe_donjon_braldun`) REFERENCES `donjon_equipe` (`id_donjon_equipe`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `echoppe`
-- 
ALTER TABLE `echoppe`
  ADD CONSTRAINT `echoppe_ibfk_1` FOREIGN KEY (`id_fk_braldun_echoppe`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_ibfk_2` FOREIGN KEY (`id_fk_metier_echoppe`) REFERENCES `metier` (`id_metier`);

-- 
-- Contraintes pour la table `echoppe_equipement`
-- 
ALTER TABLE `echoppe_equipement`
  ADD CONSTRAINT `echoppe_equipement_ibfk_22` FOREIGN KEY (`id_echoppe_equipement`) REFERENCES `equipement` (`id_equipement`),
  ADD CONSTRAINT `echoppe_equipement_ibfk_23` FOREIGN KEY (`id_fk_echoppe_echoppe_equipement`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_equipement_ibfk_24` FOREIGN KEY (`id_fk_braldun_vente_echoppe_equipement`) REFERENCES `braldun` (`id_braldun`) ON DELETE SET NULL;

-- 
-- Contraintes pour la table `echoppe_equipement_minerai`
-- 
ALTER TABLE `echoppe_equipement_minerai`
  ADD CONSTRAINT `echoppe_equipement_minerai_ibfk_3` FOREIGN KEY (`id_fk_type_echoppe_equipement_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `echoppe_equipement_minerai_ibfk_4` FOREIGN KEY (`id_fk_echoppe_equipement_minerai`) REFERENCES `echoppe_equipement` (`id_echoppe_equipement`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `echoppe_equipement_partieplante`
-- 
ALTER TABLE `echoppe_equipement_partieplante`
  ADD CONSTRAINT `echoppe_equipement_partieplante_ibfk_7` FOREIGN KEY (`id_fk_type_echoppe_equipement_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `echoppe_equipement_partieplante_ibfk_8` FOREIGN KEY (`id_fk_type_plante_echoppe_equipement_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `echoppe_equipement_partieplante_ibfk_9` FOREIGN KEY (`id_fk_echoppe_equipement_partieplante`) REFERENCES `echoppe_equipement` (`id_echoppe_equipement`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `echoppe_materiel`
-- 
ALTER TABLE `echoppe_materiel`
  ADD CONSTRAINT `echoppe_materiel_ibfk_12` FOREIGN KEY (`id_fk_echoppe_echoppe_materiel`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_materiel_ibfk_13` FOREIGN KEY (`id_fk_braldun_vente_echoppe_materiel`) REFERENCES `braldun` (`id_braldun`) ON DELETE SET NULL;

-- 
-- Contraintes pour la table `echoppe_materiel_minerai`
-- 
ALTER TABLE `echoppe_materiel_minerai`
  ADD CONSTRAINT `echoppe_materiel_minerai_ibfk_3` FOREIGN KEY (`id_fk_type_echoppe_materiel_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `echoppe_materiel_minerai_ibfk_4` FOREIGN KEY (`id_fk_echoppe_materiel_minerai`) REFERENCES `echoppe_materiel` (`id_echoppe_materiel`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `echoppe_materiel_partieplante`
-- 
ALTER TABLE `echoppe_materiel_partieplante`
  ADD CONSTRAINT `echoppe_materiel_partieplante_ibfk_7` FOREIGN KEY (`id_fk_type_echoppe_materiel_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `echoppe_materiel_partieplante_ibfk_8` FOREIGN KEY (`id_fk_type_plante_echoppe_materiel_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `echoppe_materiel_partieplante_ibfk_9` FOREIGN KEY (`id_fk_echoppe_materiel_partieplante`) REFERENCES `echoppe_materiel` (`id_echoppe_materiel`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `echoppe_minerai`
-- 
ALTER TABLE `echoppe_minerai`
  ADD CONSTRAINT `echoppe_minerai_ibfk_1` FOREIGN KEY (`id_fk_type_echoppe_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `echoppe_minerai_ibfk_2` FOREIGN KEY (`id_fk_echoppe_echoppe_minerai`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `echoppe_partieplante`
-- 
ALTER TABLE `echoppe_partieplante`
  ADD CONSTRAINT `echoppe_partieplante_ibfk_5` FOREIGN KEY (`id_fk_type_echoppe_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `echoppe_partieplante_ibfk_6` FOREIGN KEY (`id_fk_type_plante_echoppe_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `echoppe_partieplante_ibfk_7` FOREIGN KEY (`id_fk_echoppe_echoppe_partieplante`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `echoppe_potion`
-- 
ALTER TABLE `echoppe_potion`
  ADD CONSTRAINT `echoppe_potion_ibfk_32` FOREIGN KEY (`id_fk_echoppe_echoppe_potion`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_potion_ibfk_33` FOREIGN KEY (`id_fk_braldun_vente_echoppe_potion`) REFERENCES `braldun` (`id_braldun`) ON DELETE SET NULL;

-- 
-- Contraintes pour la table `echoppe_potion_minerai`
-- 
ALTER TABLE `echoppe_potion_minerai`
  ADD CONSTRAINT `echoppe_potion_minerai_ibfk_3` FOREIGN KEY (`id_fk_type_echoppe_potion_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `echoppe_potion_minerai_ibfk_4` FOREIGN KEY (`id_fk_echoppe_potion_minerai`) REFERENCES `echoppe_potion` (`id_echoppe_potion`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `echoppe_potion_partieplante`
-- 
ALTER TABLE `echoppe_potion_partieplante`
  ADD CONSTRAINT `echoppe_potion_partieplante_ibfk_7` FOREIGN KEY (`id_fk_type_echoppe_potion_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `echoppe_potion_partieplante_ibfk_8` FOREIGN KEY (`id_fk_type_plante_echoppe_potion_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `echoppe_potion_partieplante_ibfk_9` FOREIGN KEY (`id_fk_echoppe_potion_partieplante`) REFERENCES `echoppe_potion` (`id_echoppe_potion`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `effet_mot_f`
-- 
ALTER TABLE `effet_mot_f`
  ADD CONSTRAINT `effet_mot_f_ibfk_1` FOREIGN KEY (`id_fk_braldun_effet_mot_f`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `effet_potion_braldun`
-- 
ALTER TABLE `effet_potion_braldun`
  ADD CONSTRAINT `effet_potion_braldun_ibfk_8` FOREIGN KEY (`id_fk_braldun_cible_effet_potion_braldun`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `effet_potion_braldun_ibfk_9` FOREIGN KEY (`id_fk_braldun_lanceur_effet_potion_braldun`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `effet_potion_monstre`
-- 
ALTER TABLE `effet_potion_monstre`
  ADD CONSTRAINT `effet_potion_monstre_ibfk_15` FOREIGN KEY (`id_fk_monstre_cible_effet_potion_monstre`) REFERENCES `monstre` (`id_monstre`) ON DELETE CASCADE,
  ADD CONSTRAINT `effet_potion_monstre_ibfk_16` FOREIGN KEY (`id_fk_braldun_lanceur_effet_potion_monstre`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `element_aliment`
-- 
ALTER TABLE `element_aliment`
  ADD CONSTRAINT `element_aliment_ibfk_13` FOREIGN KEY (`id_fk_type_element_aliment`) REFERENCES `type_aliment` (`id_type_aliment`);

-- 
-- Contraintes pour la table `element_equipement`
-- 
ALTER TABLE `element_equipement`
  ADD CONSTRAINT `element_equipement_ibfk_6` FOREIGN KEY (`id_element_equipement`) REFERENCES `equipement` (`id_equipement`);

-- 
-- Contraintes pour la table `element_minerai`
-- 
ALTER TABLE `element_minerai`
  ADD CONSTRAINT `element_minerai_ibfk_2` FOREIGN KEY (`id_fk_type_element_minerai`) REFERENCES `type_minerai` (`id_type_minerai`);

-- 
-- Contraintes pour la table `element_munition`
-- 
ALTER TABLE `element_munition`
  ADD CONSTRAINT `element_munition_ibfk_1` FOREIGN KEY (`id_fk_type_element_munition`) REFERENCES `type_munition` (`id_type_munition`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `element_partieplante`
-- 
ALTER TABLE `element_partieplante`
  ADD CONSTRAINT `element_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_element_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `element_partieplante_ibfk_2` FOREIGN KEY (`id_fk_type_plante_element_partieplante`) REFERENCES `type_plante` (`id_type_plante`);

-- 
-- Contraintes pour la table `element_tabac`
-- 
ALTER TABLE `element_tabac`
  ADD CONSTRAINT `element_tabac_ibfk_1` FOREIGN KEY (`id_fk_type_element_tabac`) REFERENCES `type_minerai` (`id_type_minerai`);

-- 
-- Contraintes pour la table `equipement`
-- 
ALTER TABLE `equipement`
  ADD CONSTRAINT `equipement_ibfk_1` FOREIGN KEY (`id_fk_recette_equipement`) REFERENCES `recette_equipements` (`id_recette_equipement`),
  ADD CONSTRAINT `equipement_ibfk_2` FOREIGN KEY (`id_fk_mot_runique_equipement`) REFERENCES `mot_runique` (`id_mot_runique`),
  ADD CONSTRAINT `equipement_ibfk_3` FOREIGN KEY (`id_fk_region_equipement`) REFERENCES `region` (`id_region`);

-- 
-- Contraintes pour la table `equipement_bonus`
-- 
ALTER TABLE `equipement_bonus`
  ADD CONSTRAINT `equipement_bonus_ibfk_1` FOREIGN KEY (`id_equipement_bonus`) REFERENCES `equipement` (`id_equipement`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `equipement_rune`
-- 
ALTER TABLE `equipement_rune`
  ADD CONSTRAINT `equipement_rune_ibfk_1` FOREIGN KEY (`id_equipement_rune`) REFERENCES `equipement` (`id_equipement`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipement_rune_ibfk_2` FOREIGN KEY (`id_rune_equipement_rune`) REFERENCES `rune` (`id_rune`);

-- 
-- Contraintes pour la table `etape`
-- 
ALTER TABLE `etape`
  ADD CONSTRAINT `etape_ibfk_3` FOREIGN KEY (`id_fk_quete_etape`) REFERENCES `quete` (`id_quete`) ON DELETE CASCADE,
  ADD CONSTRAINT `etape_ibfk_4` FOREIGN KEY (`id_fk_type_etape`) REFERENCES `type_etape` (`id_type_etape`),
  ADD CONSTRAINT `etape_ibfk_5` FOREIGN KEY (`id_fk_braldun_etape`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `evenement`
-- 
ALTER TABLE `evenement`
  ADD CONSTRAINT `evenement_ibfk_5` FOREIGN KEY (`id_fk_braldun_evenement`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `evenement_ibfk_6` FOREIGN KEY (`id_fk_soule_match_evenement`) REFERENCES `soule_match` (`id_soule_match`) ON DELETE CASCADE,
  ADD CONSTRAINT `evenement_ibfk_7` FOREIGN KEY (`id_fk_monstre_evenement`) REFERENCES `monstre` (`id_monstre`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `filon`
-- 
ALTER TABLE `filon`
  ADD CONSTRAINT `filon_ibfk_1` FOREIGN KEY (`id_fk_type_minerai_filon`) REFERENCES `type_minerai` (`id_type_minerai`);

-- 
-- Contraintes pour la table `gardiennage`
-- 
ALTER TABLE `gardiennage`
  ADD CONSTRAINT `gardiennage_ibfk_1` FOREIGN KEY (`id_fk_braldun_gardiennage`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `gardiennage_ibfk_2` FOREIGN KEY (`id_fk_gardien_gardiennage`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `groupe_monstre`
-- 
ALTER TABLE `groupe_monstre`
  ADD CONSTRAINT `groupe_monstre_ibfk_1` FOREIGN KEY (`id_fk_type_groupe_monstre`) REFERENCES `type_groupe_monstre` (`id_type_groupe_monstre`),
  ADD CONSTRAINT `groupe_monstre_ibfk_2` FOREIGN KEY (`id_fk_braldun_cible_groupe_monstre`) REFERENCES `braldun` (`id_braldun`) ON DELETE SET NULL;

-- 
-- Contraintes pour la table `historique_equipement`
-- 
ALTER TABLE `historique_equipement`
  ADD CONSTRAINT `historique_equipement_ibfk_1` FOREIGN KEY (`id_fk_historique_equipement`) REFERENCES `ids_equipement` (`id_ids_equipement`),
  ADD CONSTRAINT `historique_equipement_ibfk_2` FOREIGN KEY (`id_fk_type_historique_equipement`) REFERENCES `type_historique_equipement` (`id_type_historique_equipement`);

-- 
-- Contraintes pour la table `historique_materiel`
-- 
ALTER TABLE `historique_materiel`
  ADD CONSTRAINT `historique_materiel_ibfk_3` FOREIGN KEY (`id_fk_historique_materiel`) REFERENCES `materiel` (`id_materiel`) ON DELETE CASCADE,
  ADD CONSTRAINT `historique_materiel_ibfk_4` FOREIGN KEY (`id_fk_type_historique_materiel`) REFERENCES `type_historique_materiel` (`id_type_historique_materiel`);

-- 
-- Contraintes pour la table `historique_potion`
-- 
ALTER TABLE `historique_potion`
  ADD CONSTRAINT `historique_potion_ibfk_1` FOREIGN KEY (`id_fk_historique_potion`) REFERENCES `ids_potion` (`id_ids_potion`),
  ADD CONSTRAINT `historique_potion_ibfk_2` FOREIGN KEY (`id_fk_type_historique_potion`) REFERENCES `type_historique_potion` (`id_type_historique_potion`);

-- 
-- Contraintes pour la table `historique_rune`
-- 
ALTER TABLE `historique_rune`
  ADD CONSTRAINT `historique_rune_ibfk_4` FOREIGN KEY (`id_fk_historique_rune`) REFERENCES `rune` (`id_rune`) ON DELETE CASCADE,
  ADD CONSTRAINT `historique_rune_ibfk_5` FOREIGN KEY (`id_fk_type_historique_rune`) REFERENCES `type_historique_rune` (`id_type_historique_rune`);

-- 
-- Contraintes pour la table `braldun`
-- 
ALTER TABLE `braldun`
  ADD CONSTRAINT `braldun_ibfk_10` FOREIGN KEY (`id_fk_region_creation_braldun`) REFERENCES `region` (`id_region`),
  ADD CONSTRAINT `braldun_ibfk_8` FOREIGN KEY (`id_fk_communaute_braldun`) REFERENCES `communaute` (`id_communaute`) ON DELETE SET NULL,
  ADD CONSTRAINT `braldun_ibfk_9` FOREIGN KEY (`id_fk_rang_communaute_braldun`) REFERENCES `rang_communaute` (`id_rang_communaute`) ON DELETE SET NULL;

-- 
-- Contraintes pour la table `bralduns_cdm`
-- 
ALTER TABLE `bralduns_cdm`
  ADD CONSTRAINT `bralduns_cdm_ibfk_3` FOREIGN KEY (`id_fk_braldun_hcdm`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_cdm_ibfk_4` FOREIGN KEY (`id_fk_type_monstre_hcdm`) REFERENCES `type_monstre` (`id_type_monstre`),
  ADD CONSTRAINT `bralduns_cdm_ibfk_5` FOREIGN KEY (`id_fk_taille_monstre_hcdm`) REFERENCES `taille_monstre` (`id_taille_monstre`);

-- 
-- Contraintes pour la table `bralduns_competences`
-- 
ALTER TABLE `bralduns_competences`
  ADD CONSTRAINT `bralduns_competences_ibfk_3` FOREIGN KEY (`id_fk_braldun_hcomp`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_competences_ibfk_4` FOREIGN KEY (`id_fk_competence_hcomp`) REFERENCES `competence` (`id_competence`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `bralduns_distinction`
-- 
ALTER TABLE `bralduns_distinction`
  ADD CONSTRAINT `bralduns_distinction_ibfk_2` FOREIGN KEY (`id_fk_braldun_hdistinction`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_distinction_ibfk_3` FOREIGN KEY (`id_fk_type_distinction_hdistinction`) REFERENCES `type_distinction` (`id_type_distinction`);

-- 
-- Contraintes pour la table `bralduns_equipement`
-- 
ALTER TABLE `bralduns_equipement`
  ADD CONSTRAINT `bralduns_equipement_ibfk_17` FOREIGN KEY (`id_equipement_hequipement`) REFERENCES `equipement` (`id_equipement`),
  ADD CONSTRAINT `bralduns_equipement_ibfk_18` FOREIGN KEY (`id_fk_braldun_hequipement`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `bralduns_metiers`
-- 
ALTER TABLE `bralduns_metiers`
  ADD CONSTRAINT `bralduns_metiers_ibfk_4` FOREIGN KEY (`id_fk_braldun_hmetier`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_metiers_ibfk_5` FOREIGN KEY (`id_fk_metier_hmetier`) REFERENCES `metier` (`id_metier`);

-- 
-- Contraintes pour la table `bralduns_titres`
-- 
ALTER TABLE `bralduns_titres`
  ADD CONSTRAINT `bralduns_titres_ibfk_1` FOREIGN KEY (`id_fk_braldun_htitre`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_titres_ibfk_2` FOREIGN KEY (`id_fk_type_htitre`) REFERENCES `type_titre` (`id_type_titre`);

-- 
-- Contraintes pour la table `jos_uddeim`
-- 
ALTER TABLE `jos_uddeim`
  ADD CONSTRAINT `jos_uddeim_ibfk_1` FOREIGN KEY (`fromid`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `jos_uddeim_ibfk_2` FOREIGN KEY (`toid`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `jos_uddeim_userlists`
-- 
ALTER TABLE `jos_uddeim_userlists`
  ADD CONSTRAINT `jos_uddeim_userlists_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `laban`
-- 
ALTER TABLE `laban`
  ADD CONSTRAINT `laban_ibfk_1` FOREIGN KEY (`id_fk_braldun_laban`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `laban_aliment`
-- 
ALTER TABLE `laban_aliment`
  ADD CONSTRAINT `laban_aliment_ibfk_3` FOREIGN KEY (`id_fk_type_laban_aliment`) REFERENCES `type_aliment` (`id_type_aliment`),
  ADD CONSTRAINT `laban_aliment_ibfk_4` FOREIGN KEY (`id_fk_braldun_laban_aliment`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_aliment_ibfk_5` FOREIGN KEY (`id_fk_type_qualite_laban_aliment`) REFERENCES `type_qualite` (`id_type_qualite`);

-- 
-- Contraintes pour la table `laban_equipement`
-- 
ALTER TABLE `laban_equipement`
  ADD CONSTRAINT `laban_equipement_ibfk_11` FOREIGN KEY (`id_laban_equipement`) REFERENCES `equipement` (`id_equipement`),
  ADD CONSTRAINT `laban_equipement_ibfk_12` FOREIGN KEY (`id_fk_braldun_laban_equipement`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `laban_materiel`
-- 
ALTER TABLE `laban_materiel`
  ADD CONSTRAINT `laban_materiel_ibfk_2` FOREIGN KEY (`id_fk_braldun_laban_materiel`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `laban_minerai`
-- 
ALTER TABLE `laban_minerai`
  ADD CONSTRAINT `laban_minerai_ibfk_2` FOREIGN KEY (`id_fk_type_laban_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `laban_minerai_ibfk_3` FOREIGN KEY (`id_fk_braldun_laban_minerai`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `laban_munition`
-- 
ALTER TABLE `laban_munition`
  ADD CONSTRAINT `laban_munition_ibfk_2` FOREIGN KEY (`id_fk_type_laban_munition`) REFERENCES `type_munition` (`id_type_munition`),
  ADD CONSTRAINT `laban_munition_ibfk_3` FOREIGN KEY (`id_fk_braldun_laban_munition`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `laban_partieplante`
-- 
ALTER TABLE `laban_partieplante`
  ADD CONSTRAINT `laban_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_laban_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `laban_partieplante_ibfk_2` FOREIGN KEY (`id_fk_type_plante_laban_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `laban_partieplante_ibfk_3` FOREIGN KEY (`id_fk_braldun_laban_partieplante`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `laban_potion`
-- 
ALTER TABLE `laban_potion`
  ADD CONSTRAINT `laban_potion_ibfk_6` FOREIGN KEY (`id_fk_braldun_laban_potion`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `laban_rune`
-- 
ALTER TABLE `laban_rune`
  ADD CONSTRAINT `laban_rune_ibfk_3` FOREIGN KEY (`id_fk_braldun_laban_rune`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `laban_tabac`
-- 
ALTER TABLE `laban_tabac`
  ADD CONSTRAINT `laban_tabac_ibfk_2` FOREIGN KEY (`id_fk_type_laban_tabac`) REFERENCES `type_tabac` (`id_type_tabac`),
  ADD CONSTRAINT `laban_tabac_ibfk_3` FOREIGN KEY (`id_fk_braldun_laban_tabac`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `lieu`
-- 
ALTER TABLE `lieu`
  ADD CONSTRAINT `lieu_ibfk_3` FOREIGN KEY (`id_fk_type_lieu`) REFERENCES `type_lieu` (`id_type_lieu`),
  ADD CONSTRAINT `lieu_ibfk_4` FOREIGN KEY (`id_fk_ville_lieu`) REFERENCES `ville` (`id_ville`);

-- 
-- Contraintes pour la table `materiel`
-- 
ALTER TABLE `materiel`
  ADD CONSTRAINT `materiel_ibfk_1` FOREIGN KEY (`id_fk_type_materiel`) REFERENCES `type_materiel` (`id_type_materiel`);

-- 
-- Contraintes pour la table `monstre`
-- 
ALTER TABLE `monstre`
  ADD CONSTRAINT `monstre_ibfk_10` FOREIGN KEY (`id_fk_groupe_monstre`) REFERENCES `groupe_monstre` (`id_groupe_monstre`),
  ADD CONSTRAINT `monstre_ibfk_11` FOREIGN KEY (`id_fk_braldun_cible_monstre`) REFERENCES `braldun` (`id_braldun`) ON DELETE SET NULL,
  ADD CONSTRAINT `monstre_ibfk_8` FOREIGN KEY (`id_fk_type_monstre`) REFERENCES `type_monstre` (`id_type_monstre`),
  ADD CONSTRAINT `monstre_ibfk_9` FOREIGN KEY (`id_fk_taille_monstre`) REFERENCES `taille_monstre` (`id_taille_monstre`);

-- 
-- Contraintes pour la table `petit_equipement`
-- 
ALTER TABLE `petit_equipement`
  ADD CONSTRAINT `petit_equipement_ibfk_1` FOREIGN KEY (`id_fk_metier_petit_equipement`) REFERENCES `metier` (`id_metier`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `plante`
-- 
ALTER TABLE `plante`
  ADD CONSTRAINT `plante_ibfk_1` FOREIGN KEY (`id_fk_type_plante`) REFERENCES `type_plante` (`id_type_plante`);

-- 
-- Contraintes pour la table `potion`
-- 
ALTER TABLE `potion`
  ADD CONSTRAINT `potion_ibfk_3` FOREIGN KEY (`id_potion`) REFERENCES `ids_potion` (`id_ids_potion`),
  ADD CONSTRAINT `potion_ibfk_4` FOREIGN KEY (`id_fk_type_potion`) REFERENCES `type_potion` (`id_type_potion`),
  ADD CONSTRAINT `potion_ibfk_5` FOREIGN KEY (`id_fk_type_qualite_potion`) REFERENCES `type_qualite` (`id_type_qualite`);

-- 
-- Contraintes pour la table `quete`
-- 
ALTER TABLE `quete`
  ADD CONSTRAINT `quete_ibfk_1` FOREIGN KEY (`id_fk_lieu_quete`) REFERENCES `lieu` (`id_lieu`),
  ADD CONSTRAINT `quete_ibfk_2` FOREIGN KEY (`id_fk_braldun_quete`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `rang_communaute`
-- 
ALTER TABLE `rang_communaute`
  ADD CONSTRAINT `rang_communaute_ibfk_1` FOREIGN KEY (`id_fk_communaute_rang_communaute`) REFERENCES `communaute` (`id_communaute`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `recette_cout`
-- 
ALTER TABLE `recette_cout`
  ADD CONSTRAINT `recette_cout_ibfk_1` FOREIGN KEY (`id_fk_type_equipement_recette_cout`) REFERENCES `type_equipement` (`id_type_equipement`);

-- 
-- Contraintes pour la table `recette_cout_minerai`
-- 
ALTER TABLE `recette_cout_minerai`
  ADD CONSTRAINT `recette_cout_minerai_ibfk_1` FOREIGN KEY (`id_fk_type_equipement_recette_cout_minerai`) REFERENCES `type_equipement` (`id_type_equipement`),
  ADD CONSTRAINT `recette_cout_minerai_ibfk_2` FOREIGN KEY (`id_fk_type_recette_cout_minerai`) REFERENCES `type_minerai` (`id_type_minerai`);

-- 
-- Contraintes pour la table `recette_equipements`
-- 
ALTER TABLE `recette_equipements`
  ADD CONSTRAINT `recette_equipements_ibfk_1` FOREIGN KEY (`id_fk_type_recette_equipement`) REFERENCES `type_equipement` (`id_type_equipement`),
  ADD CONSTRAINT `recette_equipements_ibfk_2` FOREIGN KEY (`id_fk_type_emplacement_recette_equipement`) REFERENCES `type_emplacement` (`id_type_emplacement`);

-- 
-- Contraintes pour la table `recette_materiel_cout`
-- 
ALTER TABLE `recette_materiel_cout`
  ADD CONSTRAINT `recette_materiel_cout_ibfk_1` FOREIGN KEY (`id_fk_type_materiel_recette_materiel_cout`) REFERENCES `type_materiel` (`id_type_materiel`);

-- 
-- Contraintes pour la table `recette_materiel_cout_minerai`
-- 
ALTER TABLE `recette_materiel_cout_minerai`
  ADD CONSTRAINT `recette_materiel_cout_minerai_ibfk_1` FOREIGN KEY (`id_fk_type_materiel_recette_materiel_cout_minerai`) REFERENCES `type_materiel` (`id_type_materiel`),
  ADD CONSTRAINT `recette_materiel_cout_minerai_ibfk_2` FOREIGN KEY (`id_fk_type_recette_materiel_cout_minerai`) REFERENCES `type_minerai` (`id_type_minerai`);

-- 
-- Contraintes pour la table `recette_materiel_cout_plante`
-- 
ALTER TABLE `recette_materiel_cout_plante`
  ADD CONSTRAINT `recette_materiel_cout_plantes_ibfk_6` FOREIGN KEY (`id_fk_type_materiel_recette_materiel_cout_plante`) REFERENCES `type_materiel` (`id_type_materiel`),
  ADD CONSTRAINT `recette_materiel_cout_plantes_ibfk_7` FOREIGN KEY (`id_fk_type_plante_recette_materiel_cout_plante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `recette_materiel_cout_plantes_ibfk_8` FOREIGN KEY (`id_fk_type_partieplante_recette_materiel_cout_plante`) REFERENCES `type_partieplante` (`id_type_partieplante`);

-- 
-- Contraintes pour la table `recette_potions`
-- 
ALTER TABLE `recette_potions`
  ADD CONSTRAINT `recette_potions_ibfk_6` FOREIGN KEY (`id_fk_type_potion_recette_potion`) REFERENCES `type_potion` (`id_type_potion`),
  ADD CONSTRAINT `recette_potions_ibfk_7` FOREIGN KEY (`id_fk_type_plante_recette_potion`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `recette_potions_ibfk_8` FOREIGN KEY (`id_fk_type_partieplante_recette_potion`) REFERENCES `type_partieplante` (`id_type_partieplante`);

-- 
-- Contraintes pour la table `recette_vernis`
-- 
ALTER TABLE `recette_vernis`
  ADD CONSTRAINT `recette_vernis_ibfk_2` FOREIGN KEY (`id_fk_type_potion_recette_vernis`) REFERENCES `type_potion` (`id_type_potion`),
  ADD CONSTRAINT `recette_vernis_ibfk_3` FOREIGN KEY (`id_fk_type_partieplante_recette_vernis`) REFERENCES `type_partieplante` (`id_type_partieplante`);

-- 
-- Contraintes pour la table `ref_monstre`
-- 
ALTER TABLE `ref_monstre`
  ADD CONSTRAINT `ref_monstre_ibfk_1` FOREIGN KEY (`id_fk_type_ref_monstre`) REFERENCES `type_monstre` (`id_type_monstre`),
  ADD CONSTRAINT `ref_monstre_ibfk_2` FOREIGN KEY (`id_fk_taille_ref_monstre`) REFERENCES `taille_monstre` (`id_taille_monstre`);

-- 
-- Contraintes pour la table `route`
-- 
ALTER TABLE `route`
  ADD CONSTRAINT `route_ibfk_1` FOREIGN KEY (`id_fk_braldun_route`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `route_ibfk_2` FOREIGN KEY (`id_fk_type_qualite_route`) REFERENCES `type_qualite` (`id_type_qualite`);

-- 
-- Contraintes pour la table `rune`
-- 
ALTER TABLE `rune`
  ADD CONSTRAINT `rune_ibfk_1` FOREIGN KEY (`id_fk_type_rune`) REFERENCES `type_rune` (`id_type_rune`);

-- 
-- Contraintes pour la table `session`
-- 
ALTER TABLE `session`
  ADD CONSTRAINT `session_ibfk_1` FOREIGN KEY (`id_fk_braldun_session`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `soule_equipe`
-- 
ALTER TABLE `soule_equipe`
  ADD CONSTRAINT `soule_equipe_ibfk_2` FOREIGN KEY (`id_fk_match_soule_equipe`) REFERENCES `soule_match` (`id_soule_match`) ON DELETE CASCADE,
  ADD CONSTRAINT `soule_equipe_ibfk_3` FOREIGN KEY (`id_fk_braldun_soule_equipe`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `soule_match`
-- 
ALTER TABLE `soule_match`
  ADD CONSTRAINT `soule_match_ibfk_1` FOREIGN KEY (`id_fk_terrain_soule_match`) REFERENCES `soule_terrain` (`id_soule_terrain`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `stats_experience`
-- 
ALTER TABLE `stats_experience`
  ADD CONSTRAINT `stats_experience_ibfk_1` FOREIGN KEY (`id_fk_braldun_stats_experience`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `stats_fabricants`
-- 
ALTER TABLE `stats_fabricants`
  ADD CONSTRAINT `stats_fabricants_ibfk_1` FOREIGN KEY (`id_fk_braldun_stats_fabricants`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `stats_fabricants_ibfk_2` FOREIGN KEY (`id_fk_metier_stats_fabricants`) REFERENCES `metier` (`id_metier`);

-- 
-- Contraintes pour la table `stats_mots_runiques`
-- 
ALTER TABLE `stats_mots_runiques`
  ADD CONSTRAINT `stats_mots_runiques_ibfk_1` FOREIGN KEY (`id_fk_mot_runique_stats_mots_runiques`) REFERENCES `mot_runique` (`id_mot_runique`) ON DELETE CASCADE,
  ADD CONSTRAINT `stats_mots_runiques_ibfk_2` FOREIGN KEY (`id_fk_type_piece_stats_mots_runiques`) REFERENCES `type_piece` (`id_type_piece`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `stats_recolteurs`
-- 
ALTER TABLE `stats_recolteurs`
  ADD CONSTRAINT `stats_recolteurs_ibfk_1` FOREIGN KEY (`id_fk_braldun_stats_recolteurs`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `stats_routes`
-- 
ALTER TABLE `stats_routes`
  ADD CONSTRAINT `stats_routes_ibfk_1` FOREIGN KEY (`id_fk_braldun_stats_routes`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `stats_routes_ibfk_2` FOREIGN KEY (`id_fk_metier_stats_routes`) REFERENCES `metier` (`id_metier`);

-- 
-- Contraintes pour la table `stats_runes`
-- 
ALTER TABLE `stats_runes`
  ADD CONSTRAINT `stats_runes_ibfk_1` FOREIGN KEY (`id_fkÌ_type_rune_stats_runes`) REFERENCES `type_rune` (`id_type_rune`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `stock_tabac`
-- 
ALTER TABLE `stock_tabac`
  ADD CONSTRAINT `stock_tabac_ibfk_3` FOREIGN KEY (`id_fk_type_stock_tabac`) REFERENCES `type_tabac` (`id_type_tabac`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_tabac_ibfk_4` FOREIGN KEY (`id_fk_region_stock_tabac`) REFERENCES `region` (`id_region`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `type_equipement`
-- 
ALTER TABLE `type_equipement`
  ADD CONSTRAINT `type_equipement_ibfk_4` FOREIGN KEY (`id_fk_type_munition_type_equipement`) REFERENCES `type_munition` (`id_type_munition`),
  ADD CONSTRAINT `type_equipement_ibfk_5` FOREIGN KEY (`id_fk_type_piece_type_equipement`) REFERENCES `type_piece` (`id_type_piece`),
  ADD CONSTRAINT `type_equipement_ibfk_6` FOREIGN KEY (`id_fk_type_ingredient_base_type_equipement`) REFERENCES `type_ingredient` (`id_type_ingredient`);

-- 
-- Contraintes pour la table `type_etape_metier`
-- 
ALTER TABLE `type_etape_metier`
  ADD CONSTRAINT `type_etape_metier_ibfk_1` FOREIGN KEY (`id_fk_etape_type_etape_metier`) REFERENCES `type_etape` (`id_type_etape`) ON DELETE CASCADE,
  ADD CONSTRAINT `type_etape_metier_ibfk_2` FOREIGN KEY (`id_fk_metier_type_etape_metier`) REFERENCES `metier` (`id_metier`);

-- 
-- Contraintes pour la table `type_ingredient`
-- 
ALTER TABLE `type_ingredient`
  ADD CONSTRAINT `type_ingredient_ibfk_1` FOREIGN KEY (`id_fk_type_minerai_ingredient`) REFERENCES `type_minerai` (`id_type_minerai`);

-- 
-- Contraintes pour la table `type_materiel_assemble`
-- 
ALTER TABLE `type_materiel_assemble`
  ADD CONSTRAINT `type_materiel_assemble_ibfk_1` FOREIGN KEY (`id_base_type_materiel_assemble`) REFERENCES `type_materiel` (`id_type_materiel`),
  ADD CONSTRAINT `type_materiel_assemble_ibfk_2` FOREIGN KEY (`id_supplement_type_materiel_assemble`) REFERENCES `type_materiel` (`id_type_materiel`);

-- 
-- Contraintes pour la table `type_potion`
-- 
ALTER TABLE `type_potion`
  ADD CONSTRAINT `type_potion_ibfk_1` FOREIGN KEY (`id_fk_type_ingredient_type_potion`) REFERENCES `type_ingredient` (`id_type_ingredient`);

-- 
-- Contraintes pour la table `vente`
-- 
ALTER TABLE `vente`
  ADD CONSTRAINT `vente_ibfk_1` FOREIGN KEY (`id_fk_braldun_vente`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `vente_aliment`
-- 
ALTER TABLE `vente_aliment`
  ADD CONSTRAINT `vente_aliment_ibfk_14` FOREIGN KEY (`id_fk_vente_aliment`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE,
  ADD CONSTRAINT `vente_aliment_ibfk_15` FOREIGN KEY (`id_fk_type_vente_aliment`) REFERENCES `type_aliment` (`id_type_aliment`);

-- 
-- Contraintes pour la table `vente_element`
-- 
ALTER TABLE `vente_element`
  ADD CONSTRAINT `vente_element_ibfk_1` FOREIGN KEY (`id_fk_vente_element`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `vente_equipement`
-- 
ALTER TABLE `vente_equipement`
  ADD CONSTRAINT `vente_equipement_ibfk_11` FOREIGN KEY (`id_vente_equipement`) REFERENCES `equipement` (`id_equipement`),
  ADD CONSTRAINT `vente_equipement_ibfk_12` FOREIGN KEY (`id_fk_vente_equipement`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `vente_materiel`
-- 
ALTER TABLE `vente_materiel`
  ADD CONSTRAINT `vente_materiel_ibfk_2` FOREIGN KEY (`id_fk_vente_materiel`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `vente_minerai`
-- 
ALTER TABLE `vente_minerai`
  ADD CONSTRAINT `vente_minerai_ibfk_2` FOREIGN KEY (`id_fk_type_vente_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `vente_minerai_ibfk_3` FOREIGN KEY (`id_fk_vente_minerai`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `vente_munition`
-- 
ALTER TABLE `vente_munition`
  ADD CONSTRAINT `vente_munition_ibfk_2` FOREIGN KEY (`id_fk_type_vente_munition`) REFERENCES `type_munition` (`id_type_munition`),
  ADD CONSTRAINT `vente_munition_ibfk_3` FOREIGN KEY (`id_fk_vente_munition`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `vente_partieplante`
-- 
ALTER TABLE `vente_partieplante`
  ADD CONSTRAINT `vente_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_vente_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `vente_partieplante_ibfk_2` FOREIGN KEY (`id_fk_type_plante_vente_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `vente_partieplante_ibfk_3` FOREIGN KEY (`id_fk_vente_partieplante`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `vente_potion`
-- 
ALTER TABLE `vente_potion`
  ADD CONSTRAINT `vente_potion_ibfk_6` FOREIGN KEY (`id_fk_vente_potion`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `vente_prix_minerai`
-- 
ALTER TABLE `vente_prix_minerai`
  ADD CONSTRAINT `vente_prix_minerai_ibfk_3` FOREIGN KEY (`id_fk_type_vente_prix_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `vente_prix_minerai_ibfk_4` FOREIGN KEY (`id_fk_vente_prix_minerai`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `vente_prix_partieplante`
-- 
ALTER TABLE `vente_prix_partieplante`
  ADD CONSTRAINT `vente_prix_partieplante_ibfk_7` FOREIGN KEY (`id_fk_type_vente_prix_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `vente_prix_partieplante_ibfk_8` FOREIGN KEY (`id_fk_type_plante_vente_prix_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `vente_prix_partieplante_ibfk_9` FOREIGN KEY (`id_fk_vente_prix_partieplante`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `vente_rune`
-- 
ALTER TABLE `vente_rune`
  ADD CONSTRAINT `vente_rune_ibfk_3` FOREIGN KEY (`id_fk_vente_rune`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `vente_tabac`
-- 
ALTER TABLE `vente_tabac`
  ADD CONSTRAINT `vente_tabac_ibfk_2` FOREIGN KEY (`id_fk_type_vente_tabac`) REFERENCES `type_tabac` (`id_type_tabac`),
  ADD CONSTRAINT `vente_tabac_ibfk_3` FOREIGN KEY (`id_fk_vente_tabac`) REFERENCES `vente` (`id_vente`) ON DELETE CASCADE;

-- 
-- Contraintes pour la table `ville`
-- 
ALTER TABLE `ville`
  ADD CONSTRAINT `ville_ibfk_1` FOREIGN KEY (`id_fk_region_ville`) REFERENCES `region` (`id_region`);

-- 
-- Contraintes pour la table `zone`
-- 
ALTER TABLE `zone`
  ADD CONSTRAINT `zone_ibfk_1` FOREIGN KEY (`id_fk_environnement_zone`) REFERENCES `environnement` (`id_environnement`);
