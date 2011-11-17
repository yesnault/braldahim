-- phpMyAdmin SQL Dump
-- version 3.4.0
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le : Jeu 17 Novembre 2011 à 21:37
-- Version du serveur: 5.1.56
-- Version de PHP: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `braldahim_jeu`
--

-- --------------------------------------------------------

--
-- Structure de la table `abus`
--

CREATE TABLE IF NOT EXISTS `abus` (
  `id_abus` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_abus` int(11) NOT NULL,
  `date_abus` datetime NOT NULL,
  `texte_abus` text NOT NULL,
  `est_regle_abus` enum('oui','non') NOT NULL DEFAULT 'non',
  PRIMARY KEY (`id_abus`),
  KEY `id_fk_braldun_abus` (`id_fk_braldun_abus`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `aliment`
--

CREATE TABLE IF NOT EXISTS `aliment` (
  `id_aliment` int(11) NOT NULL,
  `id_fk_type_aliment` int(11) NOT NULL,
  `id_fk_type_qualite_aliment` int(11) NOT NULL,
  `bbdf_aliment` int(11) NOT NULL,
  `id_fk_effet_braldun_aliment` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_aliment`),
  KEY `id_fk_type_aliment` (`id_fk_type_aliment`),
  KEY `id_fk_type_qualite_aliment` (`id_fk_type_qualite_aliment`),
  KEY `id_fk_effet_braldun_aliment` (`id_fk_effet_braldun_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `ancien_braldun`
--

CREATE TABLE IF NOT EXISTS `ancien_braldun` (
  `id_ancien_braldun` int(11) NOT NULL AUTO_INCREMENT,
  `id_braldun_ancien_braldun` int(11) NOT NULL,
  `nom_ancien_braldun` varchar(20) NOT NULL,
  `prenom_ancien_braldun` varchar(23) NOT NULL,
  `id_fk_nom_initial_ancien_braldun` int(11) NOT NULL,
  `email_ancien_braldun` varchar(100) NOT NULL,
  `sexe_ancien_braldun` enum('feminin','masculin') NOT NULL,
  `niveau_ancien_braldun` int(11) NOT NULL DEFAULT '0',
  `nb_ko_ancien_braldun` int(11) NOT NULL DEFAULT '0',
  `nb_braldun_ko_ancien_braldun` int(11) NOT NULL DEFAULT '0',
  `nb_plaque_ancien_braldun` int(11) NOT NULL,
  `nb_braldun_plaquage_ancien_braldun` int(11) NOT NULL,
  `nb_monstre_kill_ancien_braldun` int(11) NOT NULL,
  `id_fk_mere_ancien_braldun` int(11) DEFAULT NULL,
  `id_fk_pere_ancien_braldun` int(11) DEFAULT NULL,
  `metiers_ancien_braldun` varchar(1000) NOT NULL,
  `titres_ancien_braldun` varchar(1000) NOT NULL,
  `distinctions_ancien_braldun` varchar(1000) DEFAULT NULL,
  `date_creation_ancien_braldun` datetime NOT NULL,
  PRIMARY KEY (`id_ancien_braldun`),
  UNIQUE KEY `id_braldun_ancien_braldun_2` (`id_braldun_ancien_braldun`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Tables des Anciens Braldûns' AUTO_INCREMENT=572 ;

-- --------------------------------------------------------

--
-- Structure de la table `batch`
--

CREATE TABLE IF NOT EXISTS `batch` (
  `id_batch` int(11) NOT NULL AUTO_INCREMENT,
  `type_batch` varchar(20) NOT NULL,
  `date_debut_batch` datetime NOT NULL,
  `date_fin_batch` datetime DEFAULT NULL,
  `etat_batch` varchar(10) NOT NULL,
  `message_batch` mediumtext,
  PRIMARY KEY (`id_batch`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=195719 ;

-- --------------------------------------------------------

--
-- Structure de la table `blabla`
--

CREATE TABLE IF NOT EXISTS `blabla` (
  `id_blabla` int(11) NOT NULL AUTO_INCREMENT,
  `x_blabla` mediumint(9) NOT NULL,
  `y_blabla` mediumint(9) NOT NULL,
  `z_blabla` mediumint(9) NOT NULL,
  `id_fk_braldun_blabla` int(11) NOT NULL,
  `date_blabla` datetime NOT NULL,
  `message_blabla` varchar(1000) NOT NULL,
  `est_censure_blabla` enum('oui','non') NOT NULL DEFAULT 'non',
  PRIMARY KEY (`id_blabla`),
  KEY `id_fk_braldun_blabla` (`id_fk_braldun_blabla`),
  KEY `x_blabla` (`x_blabla`,`y_blabla`,`z_blabla`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4064 ;

-- --------------------------------------------------------

--
-- Structure de la table `bosquet`
--

CREATE TABLE IF NOT EXISTS `bosquet` (
  `id_bosquet` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_type_bosquet_bosquet` int(11) NOT NULL,
  `x_bosquet` int(11) NOT NULL,
  `y_bosquet` int(11) NOT NULL,
  `z_bosquet` int(11) NOT NULL DEFAULT '0',
  `quantite_restante_bosquet` int(11) NOT NULL,
  `quantite_max_bosquet` int(11) NOT NULL,
  `numero_bosquet` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_bosquet`),
  KEY `id_fk_type_bosquet_bosquet` (`id_fk_type_bosquet_bosquet`),
  KEY `idx_x_bosquet_y_bosquet` (`x_bosquet`,`y_bosquet`,`z_bosquet`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=186955 ;

-- --------------------------------------------------------

--
-- Structure de la table `bougrie`
--

CREATE TABLE IF NOT EXISTS `bougrie` (
  `id_bougrie` int(11) NOT NULL AUTO_INCREMENT,
  `texte_bougrie` mediumtext NOT NULL,
  `regle_bougrie` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`id_bougrie`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

--
-- Structure de la table `boutique_tabac`
--

CREATE TABLE IF NOT EXISTS `boutique_tabac` (
  `id_boutique_tabac` int(11) NOT NULL AUTO_INCREMENT,
  `date_achat_boutique_tabac` datetime NOT NULL,
  `id_fk_type_boutique_tabac` int(11) NOT NULL,
  `id_fk_lieu_boutique_tabac` int(11) NOT NULL,
  `id_fk_braldun_boutique_tabac` int(11) NOT NULL,
  `quantite_feuille_boutique_tabac` int(11) NOT NULL DEFAULT '0',
  `prix_unitaire_boutique_tabac` int(11) NOT NULL,
  `id_fk_region_boutique_tabac` int(11) NOT NULL,
  `action_boutique_tabac` enum('reprise','vente') NOT NULL,
  PRIMARY KEY (`id_boutique_tabac`),
  KEY `id_fk_lieu_laban_tabac` (`id_fk_lieu_boutique_tabac`),
  KEY `id_fk_braldun_boutique_tabac` (`id_fk_braldun_boutique_tabac`),
  KEY `id_fk_region_boutique_tabac` (`id_fk_region_boutique_tabac`),
  KEY `id_fk_type_boutique_tabac` (`id_fk_type_boutique_tabac`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=305 ;

-- --------------------------------------------------------

--
-- Structure de la table `braldun`
--

CREATE TABLE IF NOT EXISTS `braldun` (
  `id_braldun` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_jos_users_braldun` int(11) DEFAULT NULL COMMENT 'identifiant vers User Joomla : jos_users.id',
  `sysgroupe_braldun` varchar(10) DEFAULT NULL,
  `nom_braldun` varchar(20) NOT NULL,
  `prenom_braldun` varchar(23) NOT NULL,
  `id_fk_nom_initial_braldun` int(11) NOT NULL,
  `password_salt_braldun` varchar(50) DEFAULT NULL,
  `password_hash_braldun` varchar(64) DEFAULT NULL,
  `email_braldun` varchar(100) NOT NULL,
  `etat_braldun` int(11) NOT NULL,
  `sexe_braldun` enum('feminin','masculin') NOT NULL,
  `x_braldun` int(11) NOT NULL,
  `y_braldun` int(1) NOT NULL,
  `z_braldun` int(11) NOT NULL DEFAULT '0',
  `date_debut_tour_braldun` datetime NOT NULL,
  `date_fin_tour_braldun` datetime NOT NULL,
  `date_fin_latence_braldun` datetime NOT NULL,
  `date_debut_cumul_braldun` datetime NOT NULL,
  `duree_prochain_tour_braldun` time NOT NULL,
  `duree_courant_tour_braldun` time NOT NULL,
  `duree_bm_tour_braldun` smallint(6) NOT NULL DEFAULT '0',
  `tour_position_braldun` int(11) NOT NULL,
  `pa_braldun` int(11) NOT NULL,
  `vue_bm_braldun` int(11) NOT NULL,
  `vue_malus_braldun` int(11) NOT NULL,
  `force_base_braldun` int(11) NOT NULL,
  `force_bm_braldun` int(11) NOT NULL,
  `force_bbdf_braldun` int(11) NOT NULL DEFAULT '0',
  `agilite_base_braldun` int(11) NOT NULL,
  `agilite_bm_braldun` int(11) NOT NULL,
  `agilite_bbdf_braldun` int(11) NOT NULL DEFAULT '0',
  `agilite_malus_braldun` int(11) NOT NULL,
  `sagesse_base_braldun` int(11) NOT NULL,
  `sagesse_bm_braldun` int(11) NOT NULL,
  `sagesse_bbdf_braldun` int(11) NOT NULL DEFAULT '0',
  `vigueur_base_braldun` int(11) NOT NULL,
  `vigueur_bm_braldun` int(11) NOT NULL,
  `vigueur_bbdf_braldun` int(11) NOT NULL DEFAULT '0',
  `regeneration_braldun` int(11) NOT NULL,
  `regeneration_bm_braldun` int(11) NOT NULL,
  `px_perso_braldun` int(11) NOT NULL DEFAULT '0',
  `px_commun_braldun` int(11) NOT NULL,
  `pi_cumul_braldun` int(11) NOT NULL DEFAULT '0',
  `pi_braldun` int(11) NOT NULL DEFAULT '0',
  `pi_academie_braldun` int(11) NOT NULL DEFAULT '0',
  `niveau_braldun` int(11) NOT NULL DEFAULT '0',
  `balance_faim_braldun` int(11) NOT NULL,
  `armure_naturelle_braldun` int(11) NOT NULL,
  `armure_equipement_braldun` int(11) NOT NULL,
  `armure_bm_braldun` int(11) NOT NULL DEFAULT '0',
  `bm_attaque_braldun` int(11) NOT NULL,
  `bm_defense_braldun` int(11) NOT NULL,
  `bm_degat_braldun` int(11) NOT NULL,
  `bm_marcher_braldun` int(11) NOT NULL DEFAULT '0',
  `poids_transportable_braldun` float NOT NULL DEFAULT '0',
  `poids_transporte_braldun` float NOT NULL DEFAULT '0',
  `castars_braldun` int(11) NOT NULL,
  `pv_max_braldun` int(11) NOT NULL COMMENT 'calculé à l''activation du tour',
  `pv_restant_braldun` int(11) NOT NULL,
  `pv_max_bm_braldun` int(11) NOT NULL,
  `est_ko_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `nb_ko_braldun` int(11) NOT NULL DEFAULT '0',
  `nb_braldun_ko_braldun` int(11) NOT NULL DEFAULT '0',
  `nb_plaque_braldun` int(11) NOT NULL,
  `nb_braldun_plaquage_braldun` int(11) NOT NULL,
  `nb_monstre_kill_braldun` int(11) NOT NULL,
  `est_compte_actif_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_compte_desactive_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_en_hibernation_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `date_fin_hibernation_braldun` datetime NOT NULL,
  `date_creation_braldun` datetime NOT NULL,
  `id_fk_mere_braldun` int(11) DEFAULT NULL,
  `id_fk_pere_braldun` int(11) DEFAULT NULL,
  `description_braldun` mediumtext NOT NULL,
  `id_fk_communaute_braldun` int(11) DEFAULT NULL,
  `id_fk_rang_communaute_braldun` int(11) DEFAULT NULL,
  `date_entree_communaute_braldun` datetime DEFAULT NULL,
  `url_blason_braldun` varchar(200) DEFAULT 'http://',
  `url_avatar_braldun` varchar(200) DEFAULT 'http://',
  `envoi_mail_message_braldun` enum('oui','non') NOT NULL DEFAULT 'oui',
  `envoi_mail_evenement_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `envoi_mail_soule_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `titre_courant_braldun` varchar(15) DEFAULT NULL,
  `est_intangible_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_intangible_prochaine_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_engage_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_engage_next_dla_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_charte_validee_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_sondage_valide_braldun` enum('oui','non') NOT NULL DEFAULT 'oui',
  `id_fk_region_creation_braldun` int(11) NOT NULL,
  `est_soule_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `soule_camp_braldun` enum('a','b') DEFAULT NULL,
  `id_fk_soule_match_braldun` int(11) DEFAULT NULL,
  `est_en_sortie_soule_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_quete_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_donjon_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_pnj_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `beta_conserver_nom_braldun` enum('non','oui') NOT NULL DEFAULT 'non',
  `nb_dla_jouees_braldun` int(11) NOT NULL DEFAULT '0',
  `points_distinctions_braldun` int(11) NOT NULL DEFAULT '0',
  `points_gredin_braldun` int(11) NOT NULL DEFAULT '0',
  `points_redresseur_braldun` int(11) NOT NULL DEFAULT '0',
  `nb_ko_redresseurs_suite_braldun` int(11) NOT NULL DEFAULT '0',
  `nb_ko_gredins_suite_braldun` int(11) NOT NULL DEFAULT '0',
  `nb_ko_neutre_braldun` int(11) NOT NULL DEFAULT '0',
  `nb_ko_redresseur_braldun` int(11) NOT NULL DEFAULT '0',
  `nb_ko_gredin_braldun` int(11) NOT NULL DEFAULT '0',
  `position_messagerie_braldun` enum('d','b') NOT NULL DEFAULT 'd',
  `nb_blabla_braldun` mediumint(9) NOT NULL DEFAULT '0',
  `nb_tour_blabla_braldun` mediumint(9) NOT NULL DEFAULT '0',
  `est_partage_communaute_butin_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  `id_fk_lieu_resurrection_braldun` int(11) DEFAULT NULL,
  `est_testeur_vue_braldun` enum('oui','non') NOT NULL DEFAULT 'non',
  PRIMARY KEY (`id_braldun`),
  UNIQUE KEY `email_braldun` (`email_braldun`),
  KEY `id_fk_communaute_braldun` (`id_fk_communaute_braldun`),
  KEY `id_fk_rang_communaute_braldun` (`id_fk_rang_communaute_braldun`),
  KEY `id_fk_jos_users_braldun` (`id_fk_jos_users_braldun`),
  KEY `est_en_hibernation_braldun` (`est_en_hibernation_braldun`),
  KEY `id_fk_mere_braldun` (`id_fk_mere_braldun`),
  KEY `id_fk_pere_braldun` (`id_fk_pere_braldun`),
  KEY `id_fk_region_creation_braldun` (`id_fk_region_creation_braldun`),
  KEY `est_pnj_braldun` (`est_pnj_braldun`),
  KEY `x_braldun` (`x_braldun`,`y_braldun`,`z_braldun`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Tables des Braldûns' AUTO_INCREMENT=813 ;

-- --------------------------------------------------------

--
-- Structure de la table `bralduns_cdm`
--

CREATE TABLE IF NOT EXISTS `bralduns_cdm` (
  `id_fk_braldun_hcdm` int(11) NOT NULL,
  `id_fk_monstre_hcdm` int(11) NOT NULL,
  `id_fk_type_monstre_hcdm` int(11) NOT NULL,
  `id_fk_taille_monstre_hcdm` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_braldun_hcdm`,`id_fk_monstre_hcdm`,`id_fk_taille_monstre_hcdm`),
  KEY `id_fk_type_monstre_hcdm` (`id_fk_type_monstre_hcdm`),
  KEY `id_fk_taille_monstre_hcdm` (`id_fk_taille_monstre_hcdm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `bralduns_competences`
--

CREATE TABLE IF NOT EXISTS `bralduns_competences` (
  `id_fk_braldun_hcomp` int(11) NOT NULL DEFAULT '0',
  `id_fk_competence_hcomp` int(11) NOT NULL DEFAULT '0',
  `pourcentage_hcomp` int(11) NOT NULL DEFAULT '10',
  `date_debut_tour_hcomp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `nb_action_tour_hcomp` int(11) NOT NULL DEFAULT '0',
  `nb_gain_tour_hcomp` int(11) NOT NULL DEFAULT '0',
  `nb_tour_restant_bonus_tabac_hcomp` int(11) NOT NULL DEFAULT '0',
  `nb_tour_restant_malus_tabac_hcomp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fk_braldun_hcomp`,`id_fk_competence_hcomp`),
  KEY `id_fk_competence_hcomp` (`id_fk_competence_hcomp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `bralduns_competences_favorites`
--

CREATE TABLE IF NOT EXISTS `bralduns_competences_favorites` (
  `id_fk_braldun_hcompf` int(11) NOT NULL DEFAULT '0',
  `id_fk_competence_hcompf` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fk_braldun_hcompf`,`id_fk_competence_hcompf`),
  KEY `id_fk_competence_hcompf` (`id_fk_competence_hcompf`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `bralduns_distinction`
--

CREATE TABLE IF NOT EXISTS `bralduns_distinction` (
  `id_hdistinction` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_hdistinction` int(11) NOT NULL,
  `id_fk_type_distinction_hdistinction` int(11) NOT NULL,
  `texte_hdistinction` varchar(100) NOT NULL,
  `url_hdistinction` varchar(200) DEFAULT NULL,
  `date_hdistinction` date NOT NULL,
  PRIMARY KEY (`id_hdistinction`),
  KEY `id_fk_braldun_hdistinction` (`id_fk_braldun_hdistinction`),
  KEY `id_fk_type_distinction_hdistinction` (`id_fk_type_distinction_hdistinction`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=862 ;

-- --------------------------------------------------------

--
-- Structure de la table `bralduns_equipement`
--

CREATE TABLE IF NOT EXISTS `bralduns_equipement` (
  `id_equipement_hequipement` int(11) NOT NULL,
  `id_fk_braldun_hequipement` int(11) NOT NULL,
  PRIMARY KEY (`id_equipement_hequipement`),
  KEY `id_fk_braldun_hequipement` (`id_fk_braldun_hequipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `bralduns_metiers`
--

CREATE TABLE IF NOT EXISTS `bralduns_metiers` (
  `id_fk_braldun_hmetier` int(11) NOT NULL,
  `id_fk_metier_hmetier` int(11) NOT NULL,
  `est_actif_hmetier` enum('oui','non') NOT NULL,
  `date_apprentissage_hmetier` date NOT NULL,
  PRIMARY KEY (`id_fk_braldun_hmetier`,`id_fk_metier_hmetier`),
  KEY `id_fk_metier_hmetier` (`id_fk_metier_hmetier`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `bralduns_roles`
--

CREATE TABLE IF NOT EXISTS `bralduns_roles` (
  `id_fk_braldun_hroles` int(11) NOT NULL,
  `id_fk_role_hroles` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_braldun_hroles`,`id_fk_role_hroles`),
  KEY `id_fk_role_hroles` (`id_fk_role_hroles`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `bralduns_titres`
--

CREATE TABLE IF NOT EXISTS `bralduns_titres` (
  `id_fk_braldun_htitre` int(11) NOT NULL,
  `id_fk_type_htitre` int(11) NOT NULL,
  `niveau_acquis_htitre` int(11) NOT NULL,
  `date_acquis_htitre` date NOT NULL,
  PRIMARY KEY (`id_fk_braldun_htitre`,`id_fk_type_htitre`,`niveau_acquis_htitre`),
  KEY `id_fk_type_htitre` (`id_fk_type_htitre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `buisson`
--

CREATE TABLE IF NOT EXISTS `buisson` (
  `id_buisson` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_type_buisson_buisson` int(11) NOT NULL,
  `x_buisson` int(11) NOT NULL,
  `y_buisson` int(11) NOT NULL,
  `z_buisson` int(11) NOT NULL DEFAULT '0',
  `quantite_restante_buisson` int(11) NOT NULL,
  `quantite_max_buisson` int(11) NOT NULL,
  PRIMARY KEY (`id_buisson`),
  UNIQUE KEY `idx_x_buisson_y_buisson` (`x_buisson`,`y_buisson`,`z_buisson`),
  KEY `id_fk_type_buisson_buisson` (`id_fk_type_buisson_buisson`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=119930 ;

-- --------------------------------------------------------

--
-- Structure de la table `butin`
--

CREATE TABLE IF NOT EXISTS `butin` (
  `id_butin` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_butin` int(11) NOT NULL,
  `date_butin` datetime NOT NULL,
  `x_butin` int(11) NOT NULL,
  `y_butin` int(11) NOT NULL,
  `z_butin` int(11) NOT NULL,
  PRIMARY KEY (`id_butin`),
  KEY `id_fk_braldun_butin` (`id_fk_braldun_butin`),
  KEY `x_butin` (`x_butin`,`y_butin`,`z_butin`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4792 ;

-- --------------------------------------------------------

--
-- Structure de la table `butin_partage`
--

CREATE TABLE IF NOT EXISTS `butin_partage` (
  `id_fk_braldun_butin_partage` int(11) NOT NULL,
  `id_fk_autorise_butin_partage` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_braldun_butin_partage`,`id_fk_autorise_butin_partage`),
  KEY `id_fk_autorise_butin_partage` (`id_fk_autorise_butin_partage`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `carnet`
--

CREATE TABLE IF NOT EXISTS `carnet` (
  `id_carnet` int(11) NOT NULL,
  `id_fk_braldun_carnet` int(11) NOT NULL,
  `texte_carnet` mediumtext NOT NULL,
  PRIMARY KEY (`id_carnet`,`id_fk_braldun_carnet`),
  KEY `id_fk_braldun_carnet` (`id_fk_braldun_carnet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `champ`
--

CREATE TABLE IF NOT EXISTS `champ` (
  `id_champ` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_champ` int(11) NOT NULL,
  `x_champ` int(11) NOT NULL,
  `y_champ` int(11) NOT NULL,
  `z_champ` int(11) NOT NULL DEFAULT '0',
  `nom_champ` varchar(30) NOT NULL,
  `date_creation_champ` datetime NOT NULL,
  `commentaire_champ` mediumtext,
  `phase_champ` enum('jachere','seme','a_recolter') NOT NULL DEFAULT 'jachere',
  `date_seme_champ` datetime DEFAULT NULL,
  `date_fin_seme_champ` datetime DEFAULT NULL,
  `date_fin_recolte_champ` datetime DEFAULT NULL,
  `date_utilisation_champ` datetime NOT NULL,
  `id_fk_type_graine_champ` int(11) DEFAULT NULL,
  `quantite_champ` int(11) NOT NULL DEFAULT '500',
  `deja_recolte_champ` enum('oui','non') NOT NULL DEFAULT 'non',
  PRIMARY KEY (`id_champ`),
  KEY `id_fk_braldun_champ` (`id_fk_braldun_champ`),
  KEY `x_champ` (`x_champ`,`y_champ`,`z_champ`),
  KEY `id_fk_type_graine_champ` (`id_fk_type_graine_champ`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=123 ;

-- --------------------------------------------------------

--
-- Structure de la table `champ_taupe`
--

CREATE TABLE IF NOT EXISTS `champ_taupe` (
  `id_champ_taupe` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_champ_taupe` int(11) NOT NULL,
  `x_champ_taupe` int(11) NOT NULL,
  `y_champ_taupe` int(11) NOT NULL,
  `taille_champ_taupe` int(11) NOT NULL,
  `numero_champ_taupe` int(11) NOT NULL,
  `etat_champ_taupe` enum('vivant','detruit','entretenu') NOT NULL DEFAULT 'vivant',
  `date_entretien_champ_taupe` datetime DEFAULT NULL,
  PRIMARY KEY (`id_champ_taupe`),
  UNIQUE KEY `id_fk_champ_taupe_2` (`id_fk_champ_taupe`,`x_champ_taupe`,`y_champ_taupe`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13547 ;

-- --------------------------------------------------------

--
-- Structure de la table `charrette`
--

CREATE TABLE IF NOT EXISTS `charrette` (
  `id_charrette` int(11) NOT NULL,
  `id_fk_braldun_charrette` int(11) DEFAULT NULL,
  `quantite_rondin_charrette` int(11) NOT NULL,
  `x_charrette` int(11) DEFAULT NULL,
  `y_charrette` int(11) DEFAULT NULL,
  `z_charrette` int(11) DEFAULT NULL,
  `quantite_peau_charrette` int(11) NOT NULL,
  `quantite_cuir_charrette` int(11) NOT NULL,
  `quantite_fourrure_charrette` int(11) NOT NULL,
  `quantite_planche_charrette` int(11) NOT NULL,
  `quantite_castar_charrette` int(11) NOT NULL,
  `durabilite_max_charrette` int(11) NOT NULL DEFAULT '2000',
  `durabilite_actuelle_charrette` int(11) NOT NULL DEFAULT '2000',
  `poids_transporte_charrette` float NOT NULL,
  `poids_transportable_charrette` float NOT NULL DEFAULT '20',
  `est_partage_communaute_charrette` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_partage_bralduns_charrette` enum('oui','non') NOT NULL DEFAULT 'non',
  `sabot_1_charrette` int(11) NOT NULL DEFAULT '0',
  `sabot_2_charrette` int(11) NOT NULL DEFAULT '0',
  `sabot_3_charrette` int(11) NOT NULL DEFAULT '0',
  `sabot_4_charrette` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_charrette`),
  UNIQUE KEY `id_fk_braldun_charrette` (`id_fk_braldun_charrette`),
  KEY `x_charrette` (`x_charrette`,`y_charrette`,`z_charrette`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_aliment`
--

CREATE TABLE IF NOT EXISTS `charrette_aliment` (
  `id_charrette_aliment` int(11) NOT NULL,
  `id_fk_charrette_aliment` int(11) NOT NULL,
  PRIMARY KEY (`id_charrette_aliment`),
  KEY `id_fk_charrette_aliment` (`id_fk_charrette_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_equipement`
--

CREATE TABLE IF NOT EXISTS `charrette_equipement` (
  `id_charrette_equipement` int(11) NOT NULL,
  `id_fk_charrette_equipement` int(11) NOT NULL,
  PRIMARY KEY (`id_charrette_equipement`),
  KEY `id_fk_charrette_equipement` (`id_fk_charrette_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_graine`
--

CREATE TABLE IF NOT EXISTS `charrette_graine` (
  `id_fk_type_charrette_graine` int(11) NOT NULL,
  `id_fk_charrette_graine` int(11) NOT NULL,
  `quantite_charrette_graine` int(11) DEFAULT '0',
  PRIMARY KEY (`id_fk_type_charrette_graine`,`id_fk_charrette_graine`),
  KEY `id_fk_charrette_graine` (`id_fk_charrette_graine`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_ingredient`
--

CREATE TABLE IF NOT EXISTS `charrette_ingredient` (
  `id_fk_type_charrette_ingredient` int(11) NOT NULL,
  `id_fk_charrette_ingredient` int(11) NOT NULL,
  `quantite_charrette_ingredient` int(11) DEFAULT '0',
  PRIMARY KEY (`id_fk_type_charrette_ingredient`,`id_fk_charrette_ingredient`),
  KEY `id_fk_charrette_ingredient` (`id_fk_charrette_ingredient`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_materiel`
--

CREATE TABLE IF NOT EXISTS `charrette_materiel` (
  `id_charrette_materiel` int(11) NOT NULL,
  `id_fk_charrette_materiel` int(11) NOT NULL,
  PRIMARY KEY (`id_charrette_materiel`),
  KEY `id_fk_charrette_materiel` (`id_fk_charrette_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_materiel_assemble`
--

CREATE TABLE IF NOT EXISTS `charrette_materiel_assemble` (
  `id_charrette_materiel_assemble` int(11) NOT NULL,
  `id_materiel_materiel_assemble` int(11) NOT NULL,
  PRIMARY KEY (`id_charrette_materiel_assemble`,`id_materiel_materiel_assemble`),
  KEY `id_materiel_materiel_assemble` (`id_materiel_materiel_assemble`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_minerai`
--

CREATE TABLE IF NOT EXISTS `charrette_minerai` (
  `id_fk_type_charrette_minerai` int(11) NOT NULL,
  `id_fk_charrette_minerai` int(11) NOT NULL,
  `quantite_brut_charrette_minerai` int(11) DEFAULT '0',
  `quantite_lingots_charrette_minerai` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_charrette_minerai`,`id_fk_charrette_minerai`),
  KEY `id_fk_charrette_minerai` (`id_fk_charrette_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_munition`
--

CREATE TABLE IF NOT EXISTS `charrette_munition` (
  `id_fk_type_charrette_munition` int(11) NOT NULL,
  `id_fk_charrette_munition` int(11) NOT NULL,
  `quantite_charrette_munition` int(11) DEFAULT '0',
  PRIMARY KEY (`id_fk_type_charrette_munition`,`id_fk_charrette_munition`),
  KEY `id_fk_charrette_munition` (`id_fk_charrette_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_partage`
--

CREATE TABLE IF NOT EXISTS `charrette_partage` (
  `id_fk_charrette_partage` int(11) NOT NULL,
  `id_fk_braldun_charrette_partage` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_charrette_partage`,`id_fk_braldun_charrette_partage`),
  KEY `id_fk_braldun_charrette_partage` (`id_fk_braldun_charrette_partage`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_partieplante`
--

CREATE TABLE IF NOT EXISTS `charrette_partieplante` (
  `id_fk_type_charrette_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_charrette_partieplante` int(11) NOT NULL,
  `id_fk_charrette_partieplante` int(11) NOT NULL,
  `quantite_charrette_partieplante` int(11) NOT NULL,
  `quantite_preparee_charrette_partieplante` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_charrette_partieplante`,`id_fk_type_plante_charrette_partieplante`,`id_fk_charrette_partieplante`),
  KEY `id_fk_type_plante_charrette_partieplante` (`id_fk_type_plante_charrette_partieplante`),
  KEY `id_fk_charrette_partieplante` (`id_fk_charrette_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_potion`
--

CREATE TABLE IF NOT EXISTS `charrette_potion` (
  `id_charrette_potion` int(11) NOT NULL,
  `id_fk_charrette_potion` int(11) NOT NULL,
  PRIMARY KEY (`id_charrette_potion`),
  KEY `id_fk_charrette_potion` (`id_fk_charrette_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_rune`
--

CREATE TABLE IF NOT EXISTS `charrette_rune` (
  `id_fk_charrette_rune` int(11) NOT NULL,
  `id_rune_charrette_rune` int(11) NOT NULL,
  PRIMARY KEY (`id_rune_charrette_rune`),
  KEY `id_fk_charrette_rune` (`id_fk_charrette_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `charrette_tabac`
--

CREATE TABLE IF NOT EXISTS `charrette_tabac` (
  `id_fk_type_charrette_tabac` int(11) NOT NULL,
  `id_fk_charrette_tabac` int(11) NOT NULL,
  `quantite_feuille_charrette_tabac` int(11) DEFAULT '0',
  PRIMARY KEY (`id_fk_type_charrette_tabac`,`id_fk_charrette_tabac`),
  KEY `id_fk_charrette_tabac` (`id_fk_charrette_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coffre`
--

CREATE TABLE IF NOT EXISTS `coffre` (
  `id_coffre` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_coffre` int(11) DEFAULT NULL,
  `id_fk_communaute_coffre` int(11) DEFAULT NULL,
  `quantite_peau_coffre` int(11) NOT NULL DEFAULT '0',
  `quantite_ration_coffre` int(11) NOT NULL DEFAULT '0',
  `quantite_cuir_coffre` int(11) NOT NULL DEFAULT '0',
  `quantite_fourrure_coffre` int(11) NOT NULL DEFAULT '0',
  `quantite_planche_coffre` int(11) NOT NULL DEFAULT '0',
  `quantite_castar_coffre` int(11) NOT NULL DEFAULT '0',
  `quantite_rondin_coffre` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_coffre`),
  KEY `id_fk_communaute_coffre` (`id_fk_communaute_coffre`),
  KEY `id_fk_braldun_coffre` (`id_fk_braldun_coffre`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=710 ;

-- --------------------------------------------------------

--
-- Structure de la table `coffre_aliment`
--

CREATE TABLE IF NOT EXISTS `coffre_aliment` (
  `id_coffre_aliment` int(11) NOT NULL,
  `id_fk_coffre_coffre_aliment` int(11) NOT NULL,
  PRIMARY KEY (`id_coffre_aliment`),
  KEY `id_fk_coffre_coffre_aliment` (`id_fk_coffre_coffre_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coffre_equipement`
--

CREATE TABLE IF NOT EXISTS `coffre_equipement` (
  `id_coffre_equipement` int(11) NOT NULL,
  `id_fk_coffre_coffre_equipement` int(11) NOT NULL,
  PRIMARY KEY (`id_coffre_equipement`),
  KEY `id_fk_coffre_coffre_equipement` (`id_fk_coffre_coffre_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coffre_graine`
--

CREATE TABLE IF NOT EXISTS `coffre_graine` (
  `id_fk_type_coffre_graine` int(11) NOT NULL,
  `quantite_coffre_graine` int(11) DEFAULT '0',
  `id_fk_coffre_coffre_graine` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_coffre_graine`,`id_fk_coffre_coffre_graine`),
  KEY `id_fk_coffre_coffre_graine` (`id_fk_coffre_coffre_graine`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coffre_ingredient`
--

CREATE TABLE IF NOT EXISTS `coffre_ingredient` (
  `id_fk_type_coffre_ingredient` int(11) NOT NULL,
  `quantite_coffre_ingredient` int(11) DEFAULT '0',
  `id_fk_coffre_coffre_ingredient` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_coffre_ingredient`,`id_fk_coffre_coffre_ingredient`),
  KEY `id_fk_coffre_coffre_ingredient` (`id_fk_coffre_coffre_ingredient`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coffre_materiel`
--

CREATE TABLE IF NOT EXISTS `coffre_materiel` (
  `id_coffre_materiel` int(11) NOT NULL,
  `id_fk_coffre_coffre_materiel` int(11) NOT NULL,
  PRIMARY KEY (`id_coffre_materiel`),
  KEY `id_fk_coffre_coffre_materiel` (`id_fk_coffre_coffre_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coffre_minerai`
--

CREATE TABLE IF NOT EXISTS `coffre_minerai` (
  `id_fk_type_coffre_minerai` int(11) NOT NULL,
  `quantite_brut_coffre_minerai` int(11) DEFAULT '0',
  `quantite_lingots_coffre_minerai` int(11) NOT NULL,
  `id_fk_coffre_coffre_minerai` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_coffre_minerai`,`id_fk_coffre_coffre_minerai`),
  KEY `id_fk_coffre_coffre_minerai` (`id_fk_coffre_coffre_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coffre_munition`
--

CREATE TABLE IF NOT EXISTS `coffre_munition` (
  `id_fk_type_coffre_munition` int(11) NOT NULL,
  `quantite_coffre_munition` int(11) DEFAULT '0',
  `id_fk_coffre_coffre_munition` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_coffre_munition`,`id_fk_coffre_coffre_munition`),
  KEY `id_fk_coffre_coffre_munition` (`id_fk_coffre_coffre_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coffre_partieplante`
--

CREATE TABLE IF NOT EXISTS `coffre_partieplante` (
  `id_fk_type_coffre_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_coffre_partieplante` int(11) NOT NULL,
  `quantite_coffre_partieplante` int(11) NOT NULL,
  `quantite_preparee_coffre_partieplante` int(11) NOT NULL,
  `id_fk_coffre_coffre_partieplante` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_coffre_partieplante`,`id_fk_type_plante_coffre_partieplante`,`id_fk_coffre_coffre_partieplante`),
  KEY `id_fk_type_plante_coffre_partieplante` (`id_fk_type_plante_coffre_partieplante`),
  KEY `id_fk_coffre_coffre_partieplante` (`id_fk_coffre_coffre_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coffre_potion`
--

CREATE TABLE IF NOT EXISTS `coffre_potion` (
  `id_coffre_potion` int(11) NOT NULL,
  `id_fk_coffre_coffre_potion` int(11) NOT NULL,
  PRIMARY KEY (`id_coffre_potion`),
  KEY `id_fk_coffre_coffre_potion` (`id_fk_coffre_coffre_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coffre_rune`
--

CREATE TABLE IF NOT EXISTS `coffre_rune` (
  `id_rune_coffre_rune` int(11) NOT NULL,
  `id_fk_coffre_coffre_rune` int(11) NOT NULL,
  PRIMARY KEY (`id_rune_coffre_rune`),
  KEY `id_fk_coffre_coffre_rune` (`id_fk_coffre_coffre_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `coffre_tabac`
--

CREATE TABLE IF NOT EXISTS `coffre_tabac` (
  `id_fk_type_coffre_tabac` int(11) NOT NULL,
  `quantite_feuille_coffre_tabac` int(11) DEFAULT '0',
  `id_fk_coffre_coffre_tabac` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_coffre_tabac`,`id_fk_coffre_coffre_tabac`),
  KEY `id_fk_coffre_coffre_tabac` (`id_fk_coffre_coffre_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `communaute`
--

CREATE TABLE IF NOT EXISTS `communaute` (
  `id_communaute` int(11) NOT NULL AUTO_INCREMENT,
  `nom_communaute` varchar(40) NOT NULL,
  `date_creation_communaute` datetime NOT NULL,
  `id_fk_braldun_gestionnaire_communaute` int(11) NOT NULL,
  `description_communaute` mediumtext,
  `site_web_communaute` varchar(255) DEFAULT NULL,
  `x_communaute` int(11) DEFAULT NULL,
  `y_communaute` int(11) DEFAULT NULL,
  `z_communaute` int(11) DEFAULT NULL,
  `css_communaute` mediumtext,
  `points_communaute` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_communaute`),
  UNIQUE KEY `id_fk_braldun_createur_communaute` (`id_fk_braldun_gestionnaire_communaute`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Structure de la table `competence`
--

CREATE TABLE IF NOT EXISTS `competence` (
  `id_competence` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_competence` varchar(255) NOT NULL DEFAULT '',
  `nom_competence` varchar(255) NOT NULL DEFAULT '',
  `description_competence` mediumtext NOT NULL,
  `niveau_requis_competence` int(11) NOT NULL DEFAULT '0',
  `niveau_sagesse_requis_competence` int(11) NOT NULL DEFAULT '0',
  `pi_cout_competence` int(11) NOT NULL DEFAULT '0',
  `px_gain_competence` int(11) NOT NULL DEFAULT '0',
  `balance_faim_competence` int(11) NOT NULL,
  `pourcentage_max_competence` int(11) NOT NULL DEFAULT '90',
  `pourcentage_init_competence` int(11) NOT NULL,
  `pa_utilisation_competence` int(11) NOT NULL DEFAULT '6',
  `pa_manquee_competence` int(11) NOT NULL DEFAULT '0',
  `type_competence` enum('basic','commun','metier','soule') NOT NULL DEFAULT 'basic',
  `id_fk_metier_competence` int(11) DEFAULT NULL,
  `id_fk_type_tabac_competence` int(11) DEFAULT NULL,
  `ordre_competence` int(11) NOT NULL,
  PRIMARY KEY (`id_competence`),
  KEY `id_fk_metier_competence` (`id_fk_metier_competence`),
  KEY `id_fk_type_tabac_competence` (`id_fk_type_tabac_competence`),
  KEY `ordre_competence` (`ordre_competence`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=74 ;

-- --------------------------------------------------------

--
-- Structure de la table `contrat`
--

CREATE TABLE IF NOT EXISTS `contrat` (
  `id_contrat` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_contrat` int(11) NOT NULL,
  `id_fk_cible_braldun_contrat` int(11) NOT NULL,
  `date_creation_contrat` datetime NOT NULL,
  `date_fin_contrat` datetime DEFAULT NULL,
  `gain_contrat` text,
  `type_contrat` enum('gredin','redresseur') NOT NULL DEFAULT 'gredin',
  `etat_contrat` enum('en cours','terminé','annulé') NOT NULL DEFAULT 'en cours',
  PRIMARY KEY (`id_contrat`),
  KEY `id_fk_braldun_contrat` (`id_fk_braldun_contrat`),
  KEY `id_fk_cible_braldun_contrat` (`id_fk_cible_braldun_contrat`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Structure de la table `couple`
--

CREATE TABLE IF NOT EXISTS `couple` (
  `id_fk_m_braldun_couple` int(11) NOT NULL,
  `id_fk_f_braldun_couple` int(11) NOT NULL,
  `date_creation_couple` datetime NOT NULL,
  `nb_enfants_couple` int(11) NOT NULL,
  `est_valide_couple` enum('oui','non') NOT NULL DEFAULT 'oui',
  PRIMARY KEY (`id_fk_m_braldun_couple`,`id_fk_f_braldun_couple`),
  UNIQUE KEY `id_fk_f_braldun_couple` (`id_fk_f_braldun_couple`),
  UNIQUE KEY `id_fk_m_braldun_couple` (`id_fk_m_braldun_couple`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `creation_bosquets`
--

CREATE TABLE IF NOT EXISTS `creation_bosquets` (
  `id_fk_type_bosquet_creation_bosquets` int(11) NOT NULL,
  `id_fk_environnement_creation_bosquets` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_bosquet_creation_bosquets`,`id_fk_environnement_creation_bosquets`),
  KEY `id_fk_environnement_creation_filons` (`id_fk_environnement_creation_bosquets`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `creation_buissons`
--

CREATE TABLE IF NOT EXISTS `creation_buissons` (
  `id_fk_type_buisson_creation_buissons` int(11) NOT NULL,
  `id_fk_environnement_creation_buissons` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_buisson_creation_buissons`,`id_fk_environnement_creation_buissons`),
  KEY `id_fk_environnement_creation_buissons` (`id_fk_environnement_creation_buissons`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `creation_minerais`
--

CREATE TABLE IF NOT EXISTS `creation_minerais` (
  `id_fk_type_minerai_creation_minerais` int(11) NOT NULL,
  `id_fk_environnement_creation_minerais` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_minerai_creation_minerais`,`id_fk_environnement_creation_minerais`),
  KEY `id_fk_environnement_creation_filons` (`id_fk_environnement_creation_minerais`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `creation_nid`
--

CREATE TABLE IF NOT EXISTS `creation_nid` (
  `id_fk_zone_creation_nid` int(11) NOT NULL,
  `id_fk_type_monstre_creation_nid` int(11) NOT NULL,
  `nb_monstres_ville_creation_nid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_fk_zone_creation_nid`,`id_fk_type_monstre_creation_nid`),
  KEY `id_fk_type_monstre_creation_nid` (`id_fk_type_monstre_creation_nid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `creation_plantes`
--

CREATE TABLE IF NOT EXISTS `creation_plantes` (
  `id_fk_type_plante_creation_plantes` int(11) NOT NULL,
  `id_fk_environnement_creation_plantes` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_plante_creation_plantes`,`id_fk_environnement_creation_plantes`),
  KEY `id_fk_environnement_creation_plantes` (`id_fk_environnement_creation_plantes`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `crevasse`
--

CREATE TABLE IF NOT EXISTS `crevasse` (
  `id_crevasse` int(11) NOT NULL AUTO_INCREMENT,
  `x_crevasse` int(11) NOT NULL,
  `y_crevasse` int(11) NOT NULL,
  `z_crevasse` int(11) NOT NULL DEFAULT '0',
  `id_fk_donjon_crevasse` int(11) DEFAULT NULL,
  `est_decouverte_crevasse` enum('oui','non') NOT NULL DEFAULT 'non',
  PRIMARY KEY (`id_crevasse`),
  UNIQUE KEY `x_crevasse` (`x_crevasse`,`y_crevasse`,`z_crevasse`),
  KEY `id_fk_donjon_crevasse` (`id_fk_donjon_crevasse`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=75 ;

-- --------------------------------------------------------

--
-- Structure de la table `donjon`
--

CREATE TABLE IF NOT EXISTS `donjon` (
  `id_donjon` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_lieu_donjon` int(11) NOT NULL,
  `id_fk_region_donjon` int(11) NOT NULL,
  `id_fk_pnj_donjon` int(11) NOT NULL,
  `id_fk_distinction_donjon` int(11) NOT NULL,
  PRIMARY KEY (`id_donjon`),
  KEY `id_fk_lieu_donjon` (`id_fk_lieu_donjon`),
  KEY `id_fk_region_donjon` (`id_fk_region_donjon`),
  KEY `id_fk_pnj_donjon` (`id_fk_pnj_donjon`),
  KEY `id_fk_distinction_donjon` (`id_fk_distinction_donjon`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Structure de la table `donjon_braldun`
--

CREATE TABLE IF NOT EXISTS `donjon_braldun` (
  `id_fk_braldun_donjon_braldun` int(11) NOT NULL,
  `id_fk_equipe_donjon_braldun` int(11) NOT NULL,
  `date_inscription_donjon_braldun` datetime DEFAULT NULL,
  PRIMARY KEY (`id_fk_braldun_donjon_braldun`,`id_fk_equipe_donjon_braldun`),
  KEY `id_fk_equipe_donjon_braldun` (`id_fk_equipe_donjon_braldun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `donjon_crevasse`
--

CREATE TABLE IF NOT EXISTS `donjon_crevasse` (
  `id_donjon_crevasse` int(11) NOT NULL AUTO_INCREMENT,
  `x_donjon_crevasse` int(11) NOT NULL,
  `y_donjon_crevasse` int(11) NOT NULL,
  `z_donjon_crevasse` int(11) NOT NULL DEFAULT '0',
  `id_fk_donjon_crevasse` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_donjon_crevasse`),
  UNIQUE KEY `x_donjon_crevasse` (`x_donjon_crevasse`,`y_donjon_crevasse`,`z_donjon_crevasse`),
  KEY `id_fk_donjon_donjon_crevasse` (`id_fk_donjon_crevasse`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Creation des crevasses pour les donjons' AUTO_INCREMENT=47 ;

-- --------------------------------------------------------

--
-- Structure de la table `donjon_equipe`
--

CREATE TABLE IF NOT EXISTS `donjon_equipe` (
  `id_donjon_equipe` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_donjon_equipe` int(11) NOT NULL,
  `id_fk_braldun_meneur_equipe` int(11) NOT NULL,
  `date_creation_donjon_equipe` datetime NOT NULL,
  `date_limite_inscription_donjon_equipe` datetime NOT NULL,
  `etat_donjon_equipe` enum('inscription','en_cours','termine','annule') NOT NULL DEFAULT 'inscription',
  `date_fin_donjon_equipe` datetime DEFAULT NULL,
  `niveau_moyen_donjon_equipe` int(11) NOT NULL,
  `id_fk_monstre_donjon_equipe` int(11) DEFAULT NULL COMMENT 'Id Boss',
  `date_mort_monstre_donjon_equipe` datetime DEFAULT NULL,
  PRIMARY KEY (`id_donjon_equipe`),
  KEY `id_fk_donjon_equipe` (`id_fk_donjon_equipe`),
  KEY `id_fk_braldun_meneur_equipe` (`id_fk_braldun_meneur_equipe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `donjon_nid`
--

CREATE TABLE IF NOT EXISTS `donjon_nid` (
  `id_donjon_nid` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_donjon_nid` int(11) NOT NULL,
  `id_fk_type_monstre_donjon_nid` int(11) NOT NULL,
  `id_fk_zone_nid_donjon_nid` int(11) NOT NULL,
  `x_donjon_nid` int(11) NOT NULL,
  `y_donjon_nid` int(11) NOT NULL,
  `z_donjon_nid` int(11) NOT NULL,
  `type_donjon_nid` enum('creation','echec') NOT NULL DEFAULT 'creation',
  PRIMARY KEY (`id_donjon_nid`),
  KEY `id_fk_type_monstre_donjon_nid` (`id_fk_type_monstre_donjon_nid`),
  KEY `id_fk_donjon_nid` (`id_fk_donjon_nid`),
  KEY `id_fk_zone_nid_donjon_nid` (`id_fk_zone_nid_donjon_nid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Structure de la table `donjon_palissade`
--

CREATE TABLE IF NOT EXISTS `donjon_palissade` (
  `id_donjon_palissade` int(11) NOT NULL AUTO_INCREMENT,
  `x_donjon_palissade` int(11) NOT NULL,
  `y_donjon_palissade` int(11) NOT NULL,
  `z_donjon_palissade` int(11) NOT NULL DEFAULT '0',
  `agilite_donjon_palissade` int(11) NOT NULL,
  `armure_naturelle_donjon_palissade` int(11) NOT NULL,
  `pv_max_donjon_palissade` int(11) NOT NULL,
  `pv_restant_donjon_palissade` int(11) NOT NULL,
  `est_destructible_donjon_palissade` enum('oui','non') NOT NULL DEFAULT 'oui',
  `id_fk_donjon_palissade` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_donjon_palissade`),
  UNIQUE KEY `xy_donjon_palissade` (`x_donjon_palissade`,`y_donjon_palissade`,`z_donjon_palissade`),
  KEY `id_fk_donjon_palissade` (`id_fk_donjon_palissade`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Structure de la table `eau`
--

CREATE TABLE IF NOT EXISTS `eau` (
  `id_eau` int(11) NOT NULL AUTO_INCREMENT,
  `x_eau` int(11) NOT NULL,
  `y_eau` int(11) NOT NULL,
  `z_eau` int(11) NOT NULL DEFAULT '0',
  `type_eau` enum('peuprofonde','profonde','lac','mer') NOT NULL,
  PRIMARY KEY (`id_eau`),
  UNIQUE KEY `x_eau` (`x_eau`,`y_eau`,`z_eau`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=213577 ;

-- --------------------------------------------------------

--
-- Structure de la table `echoppe`
--

CREATE TABLE IF NOT EXISTS `echoppe` (
  `id_echoppe` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_echoppe` int(11) NOT NULL,
  `x_echoppe` int(11) NOT NULL,
  `y_echoppe` int(11) NOT NULL,
  `z_echoppe` int(11) NOT NULL DEFAULT '0',
  `nom_echoppe` varchar(30) NOT NULL,
  `date_creation_echoppe` datetime NOT NULL,
  `commentaire_echoppe` mediumtext,
  `id_fk_metier_echoppe` int(11) NOT NULL,
  `quantite_castar_caisse_echoppe` int(11) NOT NULL DEFAULT '0',
  `quantite_planche_caisse_echoppe` int(11) NOT NULL DEFAULT '0',
  `quantite_peau_arriere_echoppe` int(11) NOT NULL DEFAULT '0',
  `quantite_rondin_arriere_echoppe` int(11) NOT NULL DEFAULT '0',
  `quantite_cuir_arriere_echoppe` int(11) NOT NULL DEFAULT '0',
  `quantite_fourrure_arriere_echoppe` int(11) NOT NULL DEFAULT '0',
  `quantite_planche_arriere_echoppe` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_echoppe`),
  KEY `id_fk_braldun_echoppe` (`id_fk_braldun_echoppe`),
  KEY `id_fk_metier_echoppe` (`id_fk_metier_echoppe`),
  KEY `x_echoppe` (`x_echoppe`,`y_echoppe`,`z_echoppe`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=116 ;

-- --------------------------------------------------------

--
-- Structure de la table `echoppe_aliment`
--

CREATE TABLE IF NOT EXISTS `echoppe_aliment` (
  `id_echoppe_aliment` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_aliment` int(11) NOT NULL,
  `date_echoppe_aliment` datetime NOT NULL,
  PRIMARY KEY (`id_echoppe_aliment`),
  KEY `id_fk_echoppe_echoppe_aliment` (`id_fk_echoppe_echoppe_aliment`),
  KEY `date_echoppe_aliment` (`date_echoppe_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `echoppe_equipement`
--

CREATE TABLE IF NOT EXISTS `echoppe_equipement` (
  `id_echoppe_equipement` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_equipement` int(11) NOT NULL,
  `date_echoppe_equipement` datetime NOT NULL,
  PRIMARY KEY (`id_echoppe_equipement`),
  KEY `id_fk_echoppe_echoppe_equipement` (`id_fk_echoppe_echoppe_equipement`),
  KEY `date_echoppe_equipement` (`date_echoppe_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `echoppe_graine`
--

CREATE TABLE IF NOT EXISTS `echoppe_graine` (
  `id_fk_type_echoppe_graine` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_graine` int(11) NOT NULL,
  `quantite_arriere_echoppe_graine` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fk_type_echoppe_graine`,`id_fk_echoppe_echoppe_graine`),
  KEY `id_fk_echoppe_echoppe_graine` (`id_fk_echoppe_echoppe_graine`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `echoppe_ingredient`
--

CREATE TABLE IF NOT EXISTS `echoppe_ingredient` (
  `id_fk_type_echoppe_ingredient` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_ingredient` int(11) NOT NULL,
  `quantite_arriere_echoppe_ingredient` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fk_type_echoppe_ingredient`,`id_fk_echoppe_echoppe_ingredient`),
  KEY `id_fk_echoppe_echoppe_ingredient` (`id_fk_echoppe_echoppe_ingredient`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `echoppe_materiel`
--

CREATE TABLE IF NOT EXISTS `echoppe_materiel` (
  `id_echoppe_materiel` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_materiel` int(11) NOT NULL,
  `date_echoppe_materiel` datetime NOT NULL,
  PRIMARY KEY (`id_echoppe_materiel`),
  KEY `id_fk_echoppe_echoppe_materiel` (`id_fk_echoppe_echoppe_materiel`),
  KEY `date_echoppe_materiel` (`date_echoppe_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `echoppe_minerai`
--

CREATE TABLE IF NOT EXISTS `echoppe_minerai` (
  `id_fk_type_echoppe_minerai` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_minerai` int(11) NOT NULL,
  `quantite_brut_arriere_echoppe_minerai` int(11) NOT NULL DEFAULT '0',
  `quantite_lingots_echoppe_minerai` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fk_type_echoppe_minerai`,`id_fk_echoppe_echoppe_minerai`),
  KEY `id_fk_echoppe_echoppe_minerai` (`id_fk_echoppe_echoppe_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `echoppe_munition`
--

CREATE TABLE IF NOT EXISTS `echoppe_munition` (
  `id_fk_type_echoppe_munition` int(11) NOT NULL,
  `quantite_echoppe_munition` int(11) DEFAULT '0',
  `id_fk_echoppe_echoppe_munition` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_echoppe_munition`,`id_fk_echoppe_echoppe_munition`),
  KEY `id_fk_echoppe_echoppe_munition` (`id_fk_echoppe_echoppe_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `echoppe_partieplante`
--

CREATE TABLE IF NOT EXISTS `echoppe_partieplante` (
  `id_fk_type_echoppe_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_echoppe_partieplante` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_partieplante` int(11) NOT NULL,
  `quantite_arriere_echoppe_partieplante` int(11) NOT NULL DEFAULT '0',
  `quantite_preparee_echoppe_partieplante` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fk_type_echoppe_partieplante`,`id_fk_type_plante_echoppe_partieplante`,`id_fk_echoppe_echoppe_partieplante`),
  KEY `id_fk_type_plante_echoppe_partieplante` (`id_fk_type_plante_echoppe_partieplante`),
  KEY `id_fk_echoppe_echoppe_partieplante` (`id_fk_echoppe_echoppe_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `echoppe_potion`
--

CREATE TABLE IF NOT EXISTS `echoppe_potion` (
  `id_echoppe_potion` int(11) NOT NULL,
  `id_fk_echoppe_echoppe_potion` int(11) NOT NULL,
  `date_echoppe_potion` datetime NOT NULL,
  PRIMARY KEY (`id_echoppe_potion`),
  KEY `id_fk_echoppe_potion` (`id_fk_echoppe_echoppe_potion`),
  KEY `date_echoppe_potion` (`date_echoppe_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `effet_braldun`
--

CREATE TABLE IF NOT EXISTS `effet_braldun` (
  `id_effet_braldun` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_cible_effet_braldun` int(11) DEFAULT NULL,
  `nb_tour_restant_effet_braldun` int(11) NOT NULL,
  `bm_type_effet_braldun` enum('bonus','malus') NOT NULL,
  `bm_effet_braldun` int(11) NOT NULL,
  `caract_effet_braldun` enum('FOR','AGI','VIG','SAG','PV','BBDF','VUE','ARM','POIDS','ATT','DEG','DEF','ATTDEGDEF','STOUT','PAMARCHER','TOUR') NOT NULL,
  `texte_effet_braldun` varchar(100) DEFAULT NULL,
  `texte_calcule_effet_braldun` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_effet_braldun`),
  KEY `id_fk_braldun_cible_effet_braldun` (`id_fk_braldun_cible_effet_braldun`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17854 ;

-- --------------------------------------------------------

--
-- Structure de la table `effet_monstre`
--

CREATE TABLE IF NOT EXISTS `effet_monstre` (
  `id_effet_monstre` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_monstre_cible_effet_monstre` int(11) NOT NULL,
  `nb_tour_restant_effet_monstre` int(11) NOT NULL,
  `bm_type_effet_monstre` enum('bonus','malus') NOT NULL,
  `bm_effet_monstre` int(11) NOT NULL,
  `caract_effet_monstre` enum('FOR','AGI','VIG','SAG','PV','BBDF','VUE','ARM','POIDS','ATT','DEG','DEF') NOT NULL,
  PRIMARY KEY (`id_effet_monstre`),
  KEY `id_fk_monstre_cible_effet_monstre` (`id_fk_monstre_cible_effet_monstre`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43583 ;

-- --------------------------------------------------------

--
-- Structure de la table `effet_mot_f`
--

CREATE TABLE IF NOT EXISTS `effet_mot_f` (
  `id_fk_braldun_effet_mot_f` int(11) NOT NULL,
  `id_fk_type_monstre_effet_mot_f` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_braldun_effet_mot_f`,`id_fk_type_monstre_effet_mot_f`),
  KEY `id_fk_type_monstre_effet_mot_f` (`id_fk_type_monstre_effet_mot_f`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `effet_potion_braldun`
--

CREATE TABLE IF NOT EXISTS `effet_potion_braldun` (
  `id_effet_potion_braldun` int(11) NOT NULL,
  `id_fk_braldun_cible_effet_potion_braldun` int(11) NOT NULL,
  `id_fk_braldun_lanceur_effet_potion_braldun` int(11) NOT NULL,
  `nb_tour_restant_effet_potion_braldun` int(11) NOT NULL,
  `bm_effet_potion_braldun` int(11) NOT NULL,
  PRIMARY KEY (`id_effet_potion_braldun`),
  KEY `id_fk_braldun_cible_effet_potion_braldun` (`id_fk_braldun_cible_effet_potion_braldun`),
  KEY `id_fk_braldun_lanceur_effet_potion_braldun` (`id_fk_braldun_lanceur_effet_potion_braldun`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `effet_potion_monstre`
--

CREATE TABLE IF NOT EXISTS `effet_potion_monstre` (
  `id_effet_potion_monstre` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_monstre_cible_effet_potion_monstre` int(11) NOT NULL,
  `id_fk_braldun_lanceur_effet_potion_monstre` int(11) NOT NULL,
  `nb_tour_restant_effet_potion_monstre` int(11) NOT NULL,
  `bm_effet_potion_monstre` int(11) NOT NULL,
  PRIMARY KEY (`id_effet_potion_monstre`),
  KEY `id_fk_monstre_cible_effet_potion_monstre` (`id_fk_monstre_cible_effet_potion_monstre`),
  KEY `id_fk_braldun_lanceur_effet_potion_monstre` (`id_fk_braldun_lanceur_effet_potion_monstre`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=505 ;

-- --------------------------------------------------------

--
-- Structure de la table `element`
--

CREATE TABLE IF NOT EXISTS `element` (
  `id_element` int(11) NOT NULL AUTO_INCREMENT,
  `x_element` int(11) NOT NULL,
  `y_element` int(11) NOT NULL,
  `z_element` int(11) NOT NULL DEFAULT '0',
  `quantite_peau_element` int(11) NOT NULL DEFAULT '0',
  `quantite_ration_element` int(11) NOT NULL DEFAULT '0',
  `quantite_cuir_element` int(11) NOT NULL DEFAULT '0',
  `quantite_fourrure_element` int(11) NOT NULL DEFAULT '0',
  `quantite_planche_element` int(11) NOT NULL DEFAULT '0',
  `quantite_castar_element` int(11) NOT NULL DEFAULT '0',
  `quantite_rondin_element` int(11) NOT NULL DEFAULT '0',
  `id_fk_butin_element` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_element`),
  UNIQUE KEY `x_element` (`x_element`,`y_element`,`z_element`,`id_fk_butin_element`),
  KEY `id_fk_butin_element` (`id_fk_butin_element`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7108 ;

-- --------------------------------------------------------

--
-- Structure de la table `element_aliment`
--

CREATE TABLE IF NOT EXISTS `element_aliment` (
  `id_element_aliment` int(11) NOT NULL,
  `x_element_aliment` int(11) NOT NULL,
  `y_element_aliment` int(11) NOT NULL,
  `z_element_aliment` int(11) NOT NULL DEFAULT '0',
  `date_fin_element_aliment` datetime NOT NULL,
  `id_fk_butin_element_aliment` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_element_aliment`),
  KEY `x_element_aliment` (`x_element_aliment`,`y_element_aliment`,`z_element_aliment`),
  KEY `id_fk_butin_element_aliment` (`id_fk_butin_element_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `element_equipement`
--

CREATE TABLE IF NOT EXISTS `element_equipement` (
  `id_element_equipement` int(11) NOT NULL,
  `x_element_equipement` int(11) NOT NULL,
  `y_element_equipement` int(11) NOT NULL,
  `z_element_equipement` int(11) NOT NULL DEFAULT '0',
  `date_fin_element_equipement` datetime NOT NULL,
  `id_fk_butin_element_equipement` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_element_equipement`),
  KEY `x_element_equipement` (`x_element_equipement`,`y_element_equipement`,`z_element_equipement`),
  KEY `id_fk_butin_element_equipement` (`id_fk_butin_element_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `element_graine`
--

CREATE TABLE IF NOT EXISTS `element_graine` (
  `x_element_graine` int(11) NOT NULL,
  `y_element_graine` int(11) NOT NULL,
  `z_element_graine` int(11) NOT NULL DEFAULT '0',
  `id_fk_type_element_graine` int(11) NOT NULL,
  `quantite_element_graine` int(11) DEFAULT '0',
  `date_fin_element_graine` datetime NOT NULL,
  `id_fk_butin_element_graine` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_fk_type_element_graine`,`x_element_graine`,`y_element_graine`,`z_element_graine`),
  KEY `id_fk_butin_element_graine` (`id_fk_butin_element_graine`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `element_ingredient`
--

CREATE TABLE IF NOT EXISTS `element_ingredient` (
  `x_element_ingredient` int(11) NOT NULL,
  `y_element_ingredient` int(11) NOT NULL,
  `z_element_ingredient` int(11) NOT NULL DEFAULT '0',
  `id_fk_type_element_ingredient` int(11) NOT NULL,
  `quantite_element_ingredient` int(11) DEFAULT '0',
  `date_fin_element_ingredient` datetime NOT NULL,
  `id_fk_butin_element_ingredient` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_fk_type_element_ingredient`,`x_element_ingredient`,`y_element_ingredient`,`z_element_ingredient`),
  KEY `id_fk_butin_element_ingredient` (`id_fk_butin_element_ingredient`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `element_materiel`
--

CREATE TABLE IF NOT EXISTS `element_materiel` (
  `id_element_materiel` int(11) NOT NULL,
  `x_element_materiel` int(11) NOT NULL,
  `y_element_materiel` int(11) NOT NULL,
  `z_element_materiel` int(11) NOT NULL DEFAULT '0',
  `date_fin_element_materiel` datetime NOT NULL,
  `id_fk_butin_element_materiel` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_element_materiel`),
  KEY `x_element_materiel` (`x_element_materiel`,`y_element_materiel`,`z_element_materiel`),
  KEY `id_fk_butin_element_materiel` (`id_fk_butin_element_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `element_minerai`
--

CREATE TABLE IF NOT EXISTS `element_minerai` (
  `x_element_minerai` int(11) NOT NULL,
  `y_element_minerai` int(11) NOT NULL,
  `z_element_minerai` int(11) NOT NULL DEFAULT '0',
  `id_fk_type_element_minerai` int(11) NOT NULL,
  `quantite_brut_element_minerai` int(11) DEFAULT '0',
  `quantite_lingots_element_minerai` int(11) NOT NULL,
  `date_fin_element_minerai` datetime NOT NULL,
  `id_fk_butin_element_minerai` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_fk_type_element_minerai`,`x_element_minerai`,`y_element_minerai`,`z_element_minerai`),
  KEY `id_fk_butin_element_minerai` (`id_fk_butin_element_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `element_munition`
--

CREATE TABLE IF NOT EXISTS `element_munition` (
  `x_element_munition` int(11) NOT NULL,
  `y_element_munition` int(11) NOT NULL,
  `z_element_munition` int(11) NOT NULL DEFAULT '0',
  `id_fk_type_element_munition` int(11) NOT NULL,
  `quantite_element_munition` int(11) NOT NULL,
  `date_fin_element_munition` datetime NOT NULL,
  `id_fk_butin_element_munition` int(11) DEFAULT NULL,
  PRIMARY KEY (`x_element_munition`,`y_element_munition`,`id_fk_type_element_munition`,`z_element_munition`),
  KEY `id_fk_type_element_munition` (`id_fk_type_element_munition`),
  KEY `id_fk_butin_element_munition` (`id_fk_butin_element_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `element_partieplante`
--

CREATE TABLE IF NOT EXISTS `element_partieplante` (
  `id_fk_type_element_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_element_partieplante` int(11) NOT NULL,
  `x_element_partieplante` int(11) NOT NULL,
  `y_element_partieplante` int(11) NOT NULL,
  `z_element_partieplante` int(11) NOT NULL DEFAULT '0',
  `quantite_element_partieplante` int(11) NOT NULL,
  `quantite_preparee_element_partieplante` int(11) NOT NULL,
  `date_fin_element_partieplante` datetime NOT NULL,
  `id_fk_butin_element_partieplante` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_fk_type_element_partieplante`,`id_fk_type_plante_element_partieplante`,`x_element_partieplante`,`y_element_partieplante`,`z_element_partieplante`),
  KEY `id_fk_type_plante_element_partieplante` (`id_fk_type_plante_element_partieplante`),
  KEY `id_fk_butin_element_partieplante` (`id_fk_butin_element_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `element_potion`
--

CREATE TABLE IF NOT EXISTS `element_potion` (
  `id_element_potion` int(11) NOT NULL,
  `x_element_potion` int(11) NOT NULL,
  `y_element_potion` int(11) NOT NULL,
  `z_element_potion` int(11) NOT NULL DEFAULT '0',
  `date_fin_element_potion` datetime NOT NULL,
  `id_fk_butin_element_potion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_element_potion`),
  KEY `x_element_potion` (`x_element_potion`,`y_element_potion`,`z_element_potion`),
  KEY `id_fk_butin_element_potion` (`id_fk_butin_element_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `element_rune`
--

CREATE TABLE IF NOT EXISTS `element_rune` (
  `x_element_rune` int(11) NOT NULL,
  `y_element_rune` int(11) NOT NULL,
  `z_element_rune` int(11) NOT NULL DEFAULT '0',
  `id_rune_element_rune` int(11) NOT NULL,
  `date_fin_element_rune` datetime NOT NULL,
  `id_fk_butin_element_rune` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_rune_element_rune`),
  KEY `x_element_rune` (`x_element_rune`,`y_element_rune`,`z_element_rune`),
  KEY `id_fk_butin_element_rune` (`id_fk_butin_element_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `element_tabac`
--

CREATE TABLE IF NOT EXISTS `element_tabac` (
  `id_fk_type_element_tabac` int(11) NOT NULL,
  `x_element_tabac` int(11) NOT NULL,
  `y_element_tabac` int(11) NOT NULL,
  `z_element_tabac` int(11) NOT NULL DEFAULT '0',
  `quantite_feuille_element_tabac` int(11) DEFAULT '0',
  `date_fin_element_tabac` datetime NOT NULL,
  `id_fk_butin_element_tabac` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_fk_type_element_tabac`,`x_element_tabac`,`y_element_tabac`,`z_element_tabac`),
  KEY `id_fk_butin_element_tabac` (`id_fk_butin_element_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `enquete`
--

CREATE TABLE IF NOT EXISTS `enquete` (
  `id_enquete` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_enquete` int(11) NOT NULL,
  `date_enquete` datetime NOT NULL,
  `message_enquete` mediumtext NOT NULL,
  `commentaire_enquete` mediumtext NOT NULL,
  `etat_enquete` enum('ouvert','en-cours','clos') NOT NULL DEFAULT 'ouvert',
  PRIMARY KEY (`id_enquete`),
  KEY `id_fk_braldun_enquete` (`id_fk_braldun_enquete`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Structure de la table `environnement`
--

CREATE TABLE IF NOT EXISTS `environnement` (
  `id_environnement` int(11) NOT NULL AUTO_INCREMENT,
  `nom_environnement` varchar(20) NOT NULL,
  `description_environnement` varchar(250) NOT NULL,
  `nom_systeme_environnement` varchar(20) NOT NULL,
  `image_environnement` varchar(100) NOT NULL,
  `est_quete_environnement` enum('oui','non') NOT NULL DEFAULT 'oui',
  PRIMARY KEY (`id_environnement`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Structure de la table `equipement`
--

CREATE TABLE IF NOT EXISTS `equipement` (
  `id_equipement` int(11) NOT NULL,
  `id_fk_recette_equipement` int(11) NOT NULL,
  `nb_runes_equipement` int(11) NOT NULL,
  `id_fk_mot_runique_equipement` int(11) DEFAULT NULL,
  `id_fk_region_equipement` int(11) NOT NULL,
  `etat_initial_equipement` int(11) NOT NULL,
  `etat_courant_equipement` int(11) NOT NULL,
  `vernis_template_equipement` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `poids_equipement` float NOT NULL DEFAULT '0',
  `force_equipement` int(11) DEFAULT NULL,
  `agilite_equipement` int(11) DEFAULT NULL,
  `vigueur_equipement` int(11) DEFAULT NULL,
  `sagesse_equipement` int(11) DEFAULT NULL,
  `armure_equipement` int(11) DEFAULT NULL,
  `attaque_equipement` int(11) DEFAULT NULL,
  `degat_equipement` int(11) DEFAULT NULL,
  `defense_equipement` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_equipement`),
  KEY `id_fk_mot_runique_equipement` (`id_fk_mot_runique_equipement`),
  KEY `id_fk_region_equipement` (`id_fk_region_equipement`),
  KEY `id_fk_recette_equipement` (`id_fk_recette_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `equipement_bonus`
--

CREATE TABLE IF NOT EXISTS `equipement_bonus` (
  `id_equipement_bonus` int(11) NOT NULL,
  `armure_equipement_bonus` int(11) NOT NULL,
  `agilite_equipement_bonus` int(11) NOT NULL,
  `force_equipement_bonus` int(11) NOT NULL,
  `sagesse_equipement_bonus` int(11) NOT NULL,
  `vigueur_equipement_bonus` int(11) NOT NULL,
  `vernis_bm_vue_equipement_bonus` int(11) DEFAULT NULL,
  `vernis_bm_armure_equipement_bonus` int(11) DEFAULT NULL,
  `vernis_bm_poids_equipement_bonus` float DEFAULT NULL,
  `vernis_bm_agilite_equipement_bonus` int(11) DEFAULT NULL,
  `vernis_bm_force_equipement_bonus` int(11) DEFAULT NULL,
  `vernis_bm_sagesse_equipement_bonus` int(11) DEFAULT NULL,
  `vernis_bm_vigueur_equipement_bonus` int(11) DEFAULT NULL,
  `vernis_bm_attaque_equipement_bonus` int(11) DEFAULT NULL,
  `vernis_bm_degat_equipement_bonus` int(11) DEFAULT NULL,
  `vernis_bm_defense_equipement_bonus` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_equipement_bonus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `equipement_rune`
--

CREATE TABLE IF NOT EXISTS `equipement_rune` (
  `id_equipement_rune` int(11) NOT NULL,
  `id_rune_equipement_rune` int(11) NOT NULL,
  `ordre_equipement_rune` int(11) NOT NULL,
  PRIMARY KEY (`id_equipement_rune`,`id_rune_equipement_rune`),
  KEY `id_rune_equipement_rune` (`id_rune_equipement_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `etape`
--

CREATE TABLE IF NOT EXISTS `etape` (
  `id_etape` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_quete_etape` int(11) NOT NULL,
  `id_fk_type_etape` int(11) NOT NULL,
  `id_fk_braldun_etape` int(11) NOT NULL COMMENT 'Dénormalisation',
  `libelle_etape` varchar(300) NOT NULL,
  `date_debut_etape` datetime DEFAULT NULL,
  `date_fin_etape` datetime DEFAULT NULL,
  `est_terminee_etape` enum('oui','non') NOT NULL DEFAULT 'non',
  `param_1_etape` int(11) DEFAULT NULL,
  `param_2_etape` int(11) DEFAULT NULL,
  `param_3_etape` int(11) DEFAULT NULL,
  `param_4_etape` int(11) DEFAULT NULL,
  `param_5_etape` int(11) DEFAULT NULL,
  `objectif_etape` int(11) NOT NULL DEFAULT '0',
  `ordre_etape` int(11) NOT NULL,
  PRIMARY KEY (`id_etape`),
  KEY `id_fk_quete_etape` (`id_fk_quete_etape`),
  KEY `id_fk_type_etape` (`id_fk_type_etape`),
  KEY `id_fk_braldun_etape` (`id_fk_braldun_etape`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8030 ;

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

CREATE TABLE IF NOT EXISTS `evenement` (
  `id_evenement` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_evenement` int(11) DEFAULT NULL,
  `id_fk_monstre_evenement` int(11) DEFAULT NULL,
  `date_evenement` datetime NOT NULL,
  `id_fk_type_evenement` int(11) NOT NULL,
  `details_evenement` varchar(1000) NOT NULL,
  `details_bot_evenement` mediumtext,
  `niveau_evenement` int(11) NOT NULL COMMENT 'Nivau du Braldûn ou du monstre lors de l''événément',
  `id_fk_soule_match_evenement` int(11) DEFAULT NULL,
  `tour_braldun_evenement` smallint(6) DEFAULT NULL,
  `tour_monstre_evenement` smallint(6) DEFAULT NULL,
  `action_evenement` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id_evenement`),
  KEY `idx_id_braldun_evenement` (`id_fk_braldun_evenement`),
  KEY `idx_id_monstre_evenement` (`id_fk_monstre_evenement`),
  KEY `date_evenement` (`date_evenement`),
  KEY `id_fk_soule_match_evenement` (`id_fk_soule_match_evenement`),
  KEY `id_fk_type_evenement` (`id_fk_type_evenement`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=662487 ;

-- --------------------------------------------------------

--
-- Structure de la table `evenement_communaute`
--

CREATE TABLE IF NOT EXISTS `evenement_communaute` (
  `id_evenement_communaute` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_communaute_evenement_communaute` int(11) DEFAULT NULL,
  `date_evenement_communaute` datetime NOT NULL,
  `id_fk_type_evenement_communaute` int(11) NOT NULL,
  `details_evenement_communaute` varchar(1000) NOT NULL,
  `details_bot_evenement_communaute` mediumtext,
  PRIMARY KEY (`id_evenement_communaute`),
  KEY `idx_id_communaute_evenement_communaute` (`id_fk_communaute_evenement_communaute`),
  KEY `date_evenement_communaute` (`date_evenement_communaute`),
  KEY `id_fk_type_evenement_communaute` (`id_fk_type_evenement_communaute`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=499 ;

-- --------------------------------------------------------

--
-- Structure de la table `filature`
--

CREATE TABLE IF NOT EXISTS `filature` (
  `id_filature` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_filature` int(11) NOT NULL,
  `id_fk_cible_braldun_filature` int(11) NOT NULL,
  `date_creation_filature` datetime NOT NULL,
  `date_fin_filature` datetime DEFAULT NULL,
  `etape_filature` enum('2','3','4') NOT NULL DEFAULT '2',
  PRIMARY KEY (`id_filature`),
  KEY `id_fk_braldun_filature` (`id_fk_braldun_filature`),
  KEY `id_fk_cible_braldun_filature` (`id_fk_cible_braldun_filature`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Structure de la table `filature_action`
--

CREATE TABLE IF NOT EXISTS `filature_action` (
  `id_filature_action` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_filature_action` int(11) NOT NULL,
  `id_fk_filature_action` int(11) NOT NULL,
  `x_min_filature_action` int(11) NOT NULL,
  `x_max_filature_action` int(11) NOT NULL,
  `y_min_filature_action` int(11) NOT NULL,
  `y_max_filature_action` int(11) NOT NULL,
  `message_filature_action` varchar(500) NOT NULL,
  PRIMARY KEY (`id_filature_action`),
  KEY `x_min_filature_action` (`x_min_filature_action`,`x_max_filature_action`,`y_min_filature_action`,`y_max_filature_action`),
  KEY `id_fk_filature_action` (`id_fk_filature_action`),
  KEY `id_fk_braldun_filature_action` (`id_fk_braldun_filature_action`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Structure de la table `filon`
--

CREATE TABLE IF NOT EXISTS `filon` (
  `id_filon` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_type_minerai_filon` int(11) NOT NULL,
  `x_filon` int(11) NOT NULL,
  `y_filon` int(11) NOT NULL,
  `z_filon` int(11) NOT NULL DEFAULT '0',
  `quantite_restante_filon` int(11) NOT NULL,
  `quantite_max_filon` int(11) NOT NULL,
  PRIMARY KEY (`id_filon`),
  KEY `id_fk_type_minerai_filon` (`id_fk_type_minerai_filon`),
  KEY `idx_x_filon_y_filon` (`x_filon`,`y_filon`,`z_filon`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=299429 ;

-- --------------------------------------------------------

--
-- Structure de la table `gardiennage`
--

CREATE TABLE IF NOT EXISTS `gardiennage` (
  `id_gardiennage` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_gardiennage` int(11) NOT NULL,
  `id_fk_gardien_gardiennage` int(11) NOT NULL,
  `date_debut_gardiennage` date NOT NULL,
  `date_fin_gardiennage` date NOT NULL,
  `nb_jours_gardiennage` int(11) NOT NULL,
  `commentaire_gardiennage` varchar(100) NOT NULL,
  PRIMARY KEY (`id_gardiennage`),
  KEY `id_gardien_gardiennage` (`id_fk_gardien_gardiennage`),
  KEY `id_fk_braldun_gardiennage` (`id_fk_braldun_gardiennage`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=205 ;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_monstre`
--

CREATE TABLE IF NOT EXISTS `groupe_monstre` (
  `id_groupe_monstre` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_type_groupe_monstre` int(11) NOT NULL,
  `date_creation_groupe_monstre` datetime NOT NULL,
  `id_fk_braldun_cible_groupe_monstre` int(11) DEFAULT NULL,
  `nb_membres_max_groupe_monstre` int(11) NOT NULL,
  `nb_membres_restant_groupe_monstre` int(11) NOT NULL,
  `phase_tactique_groupe_monstre` int(11) NOT NULL,
  `date_phase_tactique_groupe_monstre` datetime NOT NULL,
  `id_role_a_groupe_monstre` int(11) DEFAULT NULL,
  `id_role_b_groupe_monstre` int(11) DEFAULT NULL,
  `date_fin_tour_groupe_monstre` datetime DEFAULT NULL COMMENT 'DLA du dernier monstre à jouer dans ce groupe',
  `x_direction_groupe_monstre` int(11) NOT NULL,
  `y_direction_groupe_monstre` int(11) NOT NULL,
  `date_a_jouer_groupe_monstre` datetime DEFAULT NULL,
  PRIMARY KEY (`id_groupe_monstre`),
  KEY `id_fk_type_groupe_monstre` (`id_fk_type_groupe_monstre`),
  KEY `id_fk_braldun_cible_groupe_monstre` (`id_fk_braldun_cible_groupe_monstre`),
  KEY `date_a_jouer_groupe_monstre` (`date_a_jouer_groupe_monstre`),
  KEY `date_fin_tour_groupe_monstre` (`date_fin_tour_groupe_monstre`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10258 ;

-- --------------------------------------------------------

--
-- Structure de la table `historique_equipement`
--

CREATE TABLE IF NOT EXISTS `historique_equipement` (
  `id_historique_equipement` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_historique_equipement` int(11) NOT NULL,
  `date_historique_equipement` datetime NOT NULL,
  `details_historique_equipement` varchar(1000) NOT NULL,
  `id_fk_type_historique_equipement` int(11) NOT NULL,
  PRIMARY KEY (`id_historique_equipement`),
  KEY `id_fk_historique_equipement` (`id_fk_historique_equipement`),
  KEY `id_fk_type_historique_equipement` (`id_fk_type_historique_equipement`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6278 ;

-- --------------------------------------------------------

--
-- Structure de la table `historique_filature`
--

CREATE TABLE IF NOT EXISTS `historique_filature` (
  `id_historique_filature` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_filature_historique_filature` int(11) NOT NULL,
  `date_historique_filature` datetime NOT NULL,
  `details_historique_filature` varchar(1000) NOT NULL,
  PRIMARY KEY (`id_historique_filature`),
  KEY `id_fk_filature_historique_filature` (`id_fk_filature_historique_filature`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=78 ;

-- --------------------------------------------------------

--
-- Structure de la table `historique_materiel`
--

CREATE TABLE IF NOT EXISTS `historique_materiel` (
  `id_historique_materiel` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_historique_materiel` int(11) NOT NULL,
  `id_fk_type_historique_materiel` int(11) NOT NULL,
  `date_historique_materiel` datetime NOT NULL,
  `details_historique_materiel` varchar(1000) NOT NULL,
  PRIMARY KEY (`id_historique_materiel`),
  KEY `id_fk_historique_materiel` (`id_fk_historique_materiel`),
  KEY `id_fk_type_historique_materiel` (`id_fk_type_historique_materiel`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5948 ;

-- --------------------------------------------------------

--
-- Structure de la table `historique_potion`
--

CREATE TABLE IF NOT EXISTS `historique_potion` (
  `id_historique_potion` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_historique_potion` int(11) NOT NULL,
  `id_fk_type_historique_potion` int(11) NOT NULL,
  `date_historique_potion` datetime NOT NULL,
  `details_historique_potion` varchar(1000) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id_historique_potion`),
  KEY `id_fk_historique_potion` (`id_fk_historique_potion`),
  KEY `id_fk_type_historique_potion` (`id_fk_type_historique_potion`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1831 ;

-- --------------------------------------------------------

--
-- Structure de la table `historique_rune`
--

CREATE TABLE IF NOT EXISTS `historique_rune` (
  `id_historique_rune` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_historique_rune` int(11) NOT NULL,
  `id_fk_type_historique_rune` int(11) NOT NULL,
  `date_historique_rune` datetime NOT NULL,
  `details_historique_rune` varchar(1000) NOT NULL,
  PRIMARY KEY (`id_historique_rune`),
  KEY `id_fk_historique_rune` (`id_fk_type_historique_rune`),
  KEY `id_fk_historique_rune_2` (`id_fk_historique_rune`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13092 ;

-- --------------------------------------------------------

--
-- Structure de la table `ids_aliment`
--

CREATE TABLE IF NOT EXISTS `ids_aliment` (
  `id_ids_aliment` int(11) NOT NULL AUTO_INCREMENT,
  `date_creation_ids_aliment` datetime NOT NULL,
  PRIMARY KEY (`id_ids_aliment`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25980 ;

-- --------------------------------------------------------

--
-- Structure de la table `ids_coffre`
--

CREATE TABLE IF NOT EXISTS `ids_coffre` (
  `id_ids_coffre` int(11) NOT NULL AUTO_INCREMENT,
  `date_creation_ids_coffre` datetime NOT NULL,
  PRIMARY KEY (`id_ids_coffre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `ids_equipement`
--

CREATE TABLE IF NOT EXISTS `ids_equipement` (
  `id_ids_equipement` int(11) NOT NULL AUTO_INCREMENT,
  `date_creation_ids_equipement` datetime NOT NULL,
  PRIMARY KEY (`id_ids_equipement`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1551 ;

-- --------------------------------------------------------

--
-- Structure de la table `ids_lot`
--

CREATE TABLE IF NOT EXISTS `ids_lot` (
  `id_ids_lot` int(11) NOT NULL AUTO_INCREMENT,
  `date_creation_ids_lot` datetime NOT NULL,
  PRIMARY KEY (`id_ids_lot`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=927 ;

-- --------------------------------------------------------

--
-- Structure de la table `ids_materiel`
--

CREATE TABLE IF NOT EXISTS `ids_materiel` (
  `id_ids_materiel` int(11) NOT NULL AUTO_INCREMENT,
  `date_creation_ids_materiel` datetime NOT NULL,
  PRIMARY KEY (`id_ids_materiel`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=569 ;

-- --------------------------------------------------------

--
-- Structure de la table `ids_potion`
--

CREATE TABLE IF NOT EXISTS `ids_potion` (
  `id_ids_potion` int(11) NOT NULL AUTO_INCREMENT,
  `date_creation_ids_potion` datetime NOT NULL,
  PRIMARY KEY (`id_ids_potion`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=749 ;

-- --------------------------------------------------------

--
-- Structure de la table `ids_rune`
--

CREATE TABLE IF NOT EXISTS `ids_rune` (
  `id_ids_rune` int(11) NOT NULL AUTO_INCREMENT,
  `date_creation_ids_rune` datetime NOT NULL,
  PRIMARY KEY (`id_ids_rune`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3035 ;

-- --------------------------------------------------------

--
-- Structure de la table `info_jeu`
--

CREATE TABLE IF NOT EXISTS `info_jeu` (
  `id_info_jeu` int(11) NOT NULL AUTO_INCREMENT,
  `date_info_jeu` datetime NOT NULL,
  `titre_info_jeu` varchar(50) DEFAULT NULL,
  `type_info_jeu` enum('annonce','histoire') NOT NULL DEFAULT 'annonce',
  `text_info_jeu` text NOT NULL,
  `est_sur_accueil_info_jeu` enum('oui','non') NOT NULL DEFAULT 'oui',
  `lien_info_jeu` varchar(200) NOT NULL,
  `lien_wiki_info_jeu` varchar(200) NOT NULL,
  PRIMARY KEY (`id_info_jeu`),
  KEY `est_sur_accueil_info_jeu` (`est_sur_accueil_info_jeu`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=222 ;

-- --------------------------------------------------------

--
-- Structure de la table `jetable`
--

CREATE TABLE IF NOT EXISTS `jetable` (
  `id_jetable` int(11) NOT NULL AUTO_INCREMENT,
  `nom_jetable` varchar(40) NOT NULL,
  PRIMARY KEY (`id_jetable`),
  UNIQUE KEY `nom_jetable` (`nom_jetable`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=76 ;

-- --------------------------------------------------------

--
-- Structure de la table `laban`
--

CREATE TABLE IF NOT EXISTS `laban` (
  `id_fk_braldun_laban` int(11) NOT NULL,
  `quantite_peau_laban` int(11) NOT NULL DEFAULT '0',
  `quantite_ration_laban` int(11) NOT NULL DEFAULT '0',
  `quantite_cuir_laban` int(11) NOT NULL DEFAULT '0',
  `quantite_fourrure_laban` int(11) NOT NULL DEFAULT '0',
  `quantite_planche_laban` int(11) NOT NULL DEFAULT '0',
  `quantite_castar_laban` int(11) NOT NULL DEFAULT '0',
  `quantite_rondin_laban` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fk_braldun_laban`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `laban_aliment`
--

CREATE TABLE IF NOT EXISTS `laban_aliment` (
  `id_laban_aliment` int(11) NOT NULL,
  `id_fk_braldun_laban_aliment` int(11) NOT NULL,
  PRIMARY KEY (`id_laban_aliment`),
  KEY `id_fk_braldun_laban_aliment` (`id_fk_braldun_laban_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `laban_equipement`
--

CREATE TABLE IF NOT EXISTS `laban_equipement` (
  `id_laban_equipement` int(11) NOT NULL,
  `id_fk_braldun_laban_equipement` int(11) NOT NULL,
  PRIMARY KEY (`id_laban_equipement`),
  KEY `id_fk_braldun_laban_equipement` (`id_fk_braldun_laban_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `laban_graine`
--

CREATE TABLE IF NOT EXISTS `laban_graine` (
  `id_fk_type_laban_graine` int(11) NOT NULL,
  `id_fk_braldun_laban_graine` int(11) NOT NULL,
  `quantite_laban_graine` int(11) DEFAULT '0',
  PRIMARY KEY (`id_fk_type_laban_graine`,`id_fk_braldun_laban_graine`),
  KEY `id_fk_braldun_laban_graine` (`id_fk_braldun_laban_graine`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `laban_ingredient`
--

CREATE TABLE IF NOT EXISTS `laban_ingredient` (
  `id_fk_type_laban_ingredient` int(11) NOT NULL,
  `id_fk_braldun_laban_ingredient` int(11) NOT NULL,
  `quantite_laban_ingredient` int(11) DEFAULT '0',
  PRIMARY KEY (`id_fk_type_laban_ingredient`,`id_fk_braldun_laban_ingredient`),
  KEY `id_fk_braldun_laban_ingredient` (`id_fk_braldun_laban_ingredient`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `laban_materiel`
--

CREATE TABLE IF NOT EXISTS `laban_materiel` (
  `id_laban_materiel` int(11) NOT NULL,
  `id_fk_braldun_laban_materiel` int(11) NOT NULL,
  PRIMARY KEY (`id_laban_materiel`),
  KEY `laban_materiel_ibfk_2` (`id_fk_braldun_laban_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `laban_minerai`
--

CREATE TABLE IF NOT EXISTS `laban_minerai` (
  `id_fk_type_laban_minerai` int(11) NOT NULL,
  `id_fk_braldun_laban_minerai` int(11) NOT NULL,
  `quantite_brut_laban_minerai` int(11) DEFAULT '0',
  `quantite_lingots_laban_minerai` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_laban_minerai`,`id_fk_braldun_laban_minerai`),
  KEY `id_fk_braldun_laban_minerai` (`id_fk_braldun_laban_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `laban_munition`
--

CREATE TABLE IF NOT EXISTS `laban_munition` (
  `id_fk_type_laban_munition` int(11) NOT NULL,
  `id_fk_braldun_laban_munition` int(11) NOT NULL,
  `quantite_laban_munition` int(11) DEFAULT '0',
  PRIMARY KEY (`id_fk_type_laban_munition`,`id_fk_braldun_laban_munition`),
  KEY `id_fk_braldun_laban_munition` (`id_fk_braldun_laban_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `laban_partieplante`
--

CREATE TABLE IF NOT EXISTS `laban_partieplante` (
  `id_fk_type_laban_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_laban_partieplante` int(11) NOT NULL,
  `id_fk_braldun_laban_partieplante` int(11) NOT NULL,
  `quantite_laban_partieplante` int(11) NOT NULL,
  `quantite_preparee_laban_partieplante` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_laban_partieplante`,`id_fk_type_plante_laban_partieplante`,`id_fk_braldun_laban_partieplante`),
  KEY `id_fk_type_plante_laban_partieplante` (`id_fk_type_plante_laban_partieplante`),
  KEY `id_fk_braldun_laban_partieplante` (`id_fk_braldun_laban_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `laban_potion`
--

CREATE TABLE IF NOT EXISTS `laban_potion` (
  `id_laban_potion` int(11) NOT NULL,
  `id_fk_braldun_laban_potion` int(11) NOT NULL,
  PRIMARY KEY (`id_laban_potion`),
  KEY `id_fk_braldun_laban_potion` (`id_fk_braldun_laban_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `laban_rune`
--

CREATE TABLE IF NOT EXISTS `laban_rune` (
  `id_fk_braldun_laban_rune` int(11) NOT NULL,
  `id_rune_laban_rune` int(11) NOT NULL,
  `id_fk_braldun_identification_laban_rune` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_rune_laban_rune`),
  KEY `id_fk_braldun_laban_rune` (`id_fk_braldun_laban_rune`),
  KEY `id_fk_braldun_identification_laban_rune` (`id_fk_braldun_identification_laban_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `laban_tabac`
--

CREATE TABLE IF NOT EXISTS `laban_tabac` (
  `id_fk_type_laban_tabac` int(11) NOT NULL,
  `id_fk_braldun_laban_tabac` int(11) NOT NULL,
  `quantite_feuille_laban_tabac` int(11) DEFAULT '0',
  PRIMARY KEY (`id_fk_type_laban_tabac`,`id_fk_braldun_laban_tabac`),
  KEY `id_fk_braldun_laban_tabac` (`id_fk_braldun_laban_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lieu`
--

CREATE TABLE IF NOT EXISTS `lieu` (
  `id_lieu` int(11) NOT NULL AUTO_INCREMENT,
  `nom_lieu` varchar(50) NOT NULL,
  `description_lieu` mediumtext NOT NULL,
  `x_lieu` int(11) NOT NULL,
  `y_lieu` int(11) NOT NULL,
  `z_lieu` int(11) NOT NULL DEFAULT '0',
  `etat_lieu` int(11) NOT NULL,
  `id_fk_type_lieu` int(11) NOT NULL,
  `id_fk_ville_lieu` int(11) DEFAULT NULL,
  `id_fk_communaute_lieu` int(11) DEFAULT NULL,
  `date_creation_lieu` datetime NOT NULL,
  `est_soule_lieu` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_donjon_lieu` enum('oui','non') NOT NULL DEFAULT 'non',
  `niveau_lieu` int(11) NOT NULL DEFAULT '0',
  `niveau_prochain_lieu` int(11) NOT NULL DEFAULT '0',
  `nb_pa_depenses_lieu` int(11) DEFAULT NULL,
  `nb_castars_depenses_lieu` int(11) DEFAULT NULL,
  `date_entretien_lieu` datetime DEFAULT NULL,
  PRIMARY KEY (`id_lieu`),
  UNIQUE KEY `xy_lieu` (`x_lieu`,`y_lieu`,`z_lieu`),
  KEY `id_fk_type_lieu` (`id_fk_type_lieu`),
  KEY `id_fk_ville_lieu` (`id_fk_ville_lieu`),
  KEY `id_fk_communaute_lieu` (`id_fk_communaute_lieu`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1551 ;

-- --------------------------------------------------------

--
-- Structure de la table `lot`
--

CREATE TABLE IF NOT EXISTS `lot` (
  `id_lot` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_type_lot` int(11) NOT NULL,
  `id_fk_braldun_lot` int(11) DEFAULT NULL,
  `id_fk_vendeur_braldun_lot` int(11) DEFAULT NULL,
  `id_fk_communaute_lot` int(11) DEFAULT NULL,
  `id_fk_echoppe_lot` int(11) DEFAULT NULL,
  `poids_lot` float NOT NULL,
  `quantite_peau_lot` int(11) NOT NULL DEFAULT '0',
  `quantite_cuir_lot` int(11) NOT NULL DEFAULT '0',
  `quantite_fourrure_lot` int(11) NOT NULL DEFAULT '0',
  `quantite_planche_lot` int(11) NOT NULL DEFAULT '0',
  `quantite_castar_lot` int(11) NOT NULL DEFAULT '0',
  `quantite_rondin_lot` int(11) NOT NULL DEFAULT '0',
  `date_debut_lot` datetime NOT NULL,
  `date_fin_lot` datetime DEFAULT NULL,
  `commentaire_lot` varchar(300) DEFAULT NULL,
  `unite_1_lot` int(11) DEFAULT '0',
  `prix_1_lot` int(11) DEFAULT '0',
  PRIMARY KEY (`id_lot`),
  KEY `id_fk_communaute_lot` (`id_fk_communaute_lot`),
  KEY `id_fk_braldun_lot` (`id_fk_braldun_lot`),
  KEY `id_fk_echoppe_lot` (`id_fk_echoppe_lot`),
  KEY `id_fk_type_lot` (`id_fk_type_lot`),
  KEY `id_fk_vendeur_braldun_lot` (`id_fk_vendeur_braldun_lot`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=925 ;

-- --------------------------------------------------------

--
-- Structure de la table `lot_aliment`
--

CREATE TABLE IF NOT EXISTS `lot_aliment` (
  `id_lot_aliment` int(11) NOT NULL,
  `id_fk_lot_lot_aliment` int(11) NOT NULL,
  PRIMARY KEY (`id_lot_aliment`),
  KEY `id_fk_lot_lot_aliment` (`id_fk_lot_lot_aliment`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_equipement`
--

CREATE TABLE IF NOT EXISTS `lot_equipement` (
  `id_lot_equipement` int(11) NOT NULL,
  `id_fk_lot_lot_equipement` int(11) NOT NULL,
  PRIMARY KEY (`id_lot_equipement`),
  KEY `id_fk_lot_lot_equipement` (`id_fk_lot_lot_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_graine`
--

CREATE TABLE IF NOT EXISTS `lot_graine` (
  `id_fk_type_lot_graine` int(11) NOT NULL,
  `quantite_lot_graine` int(11) DEFAULT '0',
  `id_fk_lot_lot_graine` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_lot_graine`,`id_fk_lot_lot_graine`),
  KEY `id_fk_lot_lot_graine` (`id_fk_lot_lot_graine`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_historique`
--

CREATE TABLE IF NOT EXISTS `lot_historique` (
  `id_lot_historique` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_type_lot_historique` int(11) NOT NULL,
  `id_fk_braldun_lot_historique` int(11) DEFAULT NULL,
  `id_fk_vendeur_braldun_lot_historique` int(11) DEFAULT NULL,
  `id_fk_acheteur_braldun_lot_historique` int(11) DEFAULT NULL,
  `id_fk_communaute_lot_historique` int(11) DEFAULT NULL,
  `id_fk_echoppe_lot_historique` int(11) DEFAULT NULL,
  `poids_lot_historique` float NOT NULL,
  `date_debut_lot_historique` datetime NOT NULL,
  `date_fin_lot_historique` datetime DEFAULT NULL,
  `commentaire_lot_historique` varchar(300) DEFAULT NULL,
  `prix_1_lot_historique` int(11) DEFAULT '0',
  `resume_lot_historique` varchar(5000) DEFAULT NULL,
  `details_lot_historique` varchar(5000) DEFAULT NULL,
  `destination_lot_historique` varchar(200) NOT NULL,
  PRIMARY KEY (`id_lot_historique`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=927 ;

-- --------------------------------------------------------

--
-- Structure de la table `lot_ingredient`
--

CREATE TABLE IF NOT EXISTS `lot_ingredient` (
  `id_fk_type_lot_ingredient` int(11) NOT NULL,
  `quantite_lot_ingredient` int(11) DEFAULT '0',
  `id_fk_lot_lot_ingredient` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_lot_ingredient`,`id_fk_lot_lot_ingredient`),
  KEY `id_fk_lot_lot_ingredient` (`id_fk_lot_lot_ingredient`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_materiel`
--

CREATE TABLE IF NOT EXISTS `lot_materiel` (
  `id_lot_materiel` int(11) NOT NULL,
  `id_fk_lot_lot_materiel` int(11) NOT NULL,
  PRIMARY KEY (`id_lot_materiel`),
  KEY `id_fk_lot_lot_materiel` (`id_fk_lot_lot_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_minerai`
--

CREATE TABLE IF NOT EXISTS `lot_minerai` (
  `id_fk_type_lot_minerai` int(11) NOT NULL,
  `quantite_brut_lot_minerai` int(11) DEFAULT '0',
  `quantite_lingots_lot_minerai` int(11) NOT NULL,
  `id_fk_lot_lot_minerai` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_lot_minerai`,`id_fk_lot_lot_minerai`),
  KEY `id_fk_lot_lot_minerai` (`id_fk_lot_lot_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_munition`
--

CREATE TABLE IF NOT EXISTS `lot_munition` (
  `id_fk_type_lot_munition` int(11) NOT NULL,
  `quantite_lot_munition` int(11) DEFAULT '0',
  `id_fk_lot_lot_munition` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_lot_munition`,`id_fk_lot_lot_munition`),
  KEY `id_fk_lot_lot_munition` (`id_fk_lot_lot_munition`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_partieplante`
--

CREATE TABLE IF NOT EXISTS `lot_partieplante` (
  `id_fk_type_lot_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_lot_partieplante` int(11) NOT NULL,
  `quantite_lot_partieplante` int(11) NOT NULL,
  `quantite_preparee_lot_partieplante` int(11) NOT NULL,
  `id_fk_lot_lot_partieplante` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_lot_partieplante`,`id_fk_type_plante_lot_partieplante`,`id_fk_lot_lot_partieplante`),
  KEY `id_fk_type_plante_lot_partieplante` (`id_fk_type_plante_lot_partieplante`),
  KEY `id_fk_lot_lot_partieplante` (`id_fk_lot_lot_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_potion`
--

CREATE TABLE IF NOT EXISTS `lot_potion` (
  `id_lot_potion` int(11) NOT NULL,
  `id_fk_lot_lot_potion` int(11) NOT NULL,
  PRIMARY KEY (`id_lot_potion`),
  KEY `id_fk_lot_lot_potion` (`id_fk_lot_lot_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_prix_graine`
--

CREATE TABLE IF NOT EXISTS `lot_prix_graine` (
  `id_fk_type_lot_prix_graine` int(11) NOT NULL,
  `id_fk_lot_prix_graine` int(11) NOT NULL,
  `prix_lot_prix_graine` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fk_type_lot_prix_graine`,`id_fk_lot_prix_graine`),
  KEY `id_fk_lot_prix_graine` (`id_fk_lot_prix_graine`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_prix_ingredient`
--

CREATE TABLE IF NOT EXISTS `lot_prix_ingredient` (
  `id_fk_type_lot_prix_ingredient` int(11) NOT NULL,
  `id_fk_lot_prix_ingredient` int(11) NOT NULL,
  `prix_lot_prix_ingredient` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fk_type_lot_prix_ingredient`,`id_fk_lot_prix_ingredient`),
  KEY `id_fk_lot_prix_ingredient` (`id_fk_lot_prix_ingredient`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_prix_minerai`
--

CREATE TABLE IF NOT EXISTS `lot_prix_minerai` (
  `id_fk_type_lot_prix_minerai` int(11) NOT NULL,
  `id_fk_lot_prix_minerai` int(11) NOT NULL,
  `type_prix_minerai` enum('brut','lingot') NOT NULL,
  `prix_lot_prix_minerai` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fk_type_lot_prix_minerai`,`id_fk_lot_prix_minerai`,`type_prix_minerai`),
  KEY `id_fk_lot_prix_minerai` (`id_fk_lot_prix_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_prix_partieplante`
--

CREATE TABLE IF NOT EXISTS `lot_prix_partieplante` (
  `id_fk_type_lot_prix_partieplante` int(11) NOT NULL,
  `id_fk_type_plante_lot_prix_partieplante` int(11) NOT NULL,
  `id_fk_lot_prix_partieplante` int(11) NOT NULL,
  `type_prix_partieplante` enum('brute','preparee') NOT NULL,
  `prix_lot_prix_partieplante` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_fk_type_lot_prix_partieplante`,`id_fk_type_plante_lot_prix_partieplante`,`id_fk_lot_prix_partieplante`,`type_prix_partieplante`),
  KEY `id_fk_type_plante_lot_prix_partieplante` (`id_fk_type_plante_lot_prix_partieplante`),
  KEY `id_fk_lot_prix_partieplante` (`id_fk_lot_prix_partieplante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_rune`
--

CREATE TABLE IF NOT EXISTS `lot_rune` (
  `id_rune_lot_rune` int(11) NOT NULL,
  `id_fk_lot_lot_rune` int(11) NOT NULL,
  PRIMARY KEY (`id_rune_lot_rune`),
  KEY `id_fk_lot_lot_rune` (`id_fk_lot_lot_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `lot_tabac`
--

CREATE TABLE IF NOT EXISTS `lot_tabac` (
  `id_fk_type_lot_tabac` int(11) NOT NULL,
  `quantite_feuille_lot_tabac` int(11) DEFAULT '0',
  `id_fk_lot_lot_tabac` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_lot_tabac`,`id_fk_lot_lot_tabac`),
  KEY `id_fk_lot_lot_tabac` (`id_fk_lot_lot_tabac`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `materiel`
--

CREATE TABLE IF NOT EXISTS `materiel` (
  `id_materiel` int(11) NOT NULL,
  `id_fk_type_materiel` int(11) NOT NULL,
  PRIMARY KEY (`id_materiel`),
  KEY `id_fk_recette_materiel` (`id_fk_type_materiel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `mcompetence`
--

CREATE TABLE IF NOT EXISTS `mcompetence` (
  `id_mcompetence` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_mcompetence` varchar(20) NOT NULL,
  `nom_mcompetence` varchar(20) NOT NULL,
  `pa_utilisation_mcompetence` int(11) NOT NULL,
  `type_mcompetence` enum('fuite','prereperage','reperage','attaque','deplacement','riposte','end') NOT NULL DEFAULT 'attaque',
  PRIMARY KEY (`id_mcompetence`),
  UNIQUE KEY `nom_mcompetence` (`nom_mcompetence`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=50 ;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fromid` int(11) NOT NULL DEFAULT '0',
  `toid` int(11) NOT NULL DEFAULT '0',
  `toids` varchar(250) NOT NULL,
  `message` text NOT NULL,
  `date_message` datetime NOT NULL,
  `toread` int(1) NOT NULL DEFAULT '0',
  `totrash` int(1) NOT NULL DEFAULT '0',
  `totrashoutbox` int(1) NOT NULL DEFAULT '0',
  `archived` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `toid_toread` (`toid`,`toread`),
  KEY `toread_totrash_datum` (`toread`,`totrash`),
  KEY `totrash_totrashdate` (`totrash`),
  KEY `fromid` (`fromid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=216459 ;

-- --------------------------------------------------------

--
-- Structure de la table `messagerie_contacts`
--

CREATE TABLE IF NOT EXISTS `messagerie_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(40) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `userids` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=209 ;

-- --------------------------------------------------------

--
-- Structure de la table `metier`
--

CREATE TABLE IF NOT EXISTS `metier` (
  `id_metier` int(11) NOT NULL AUTO_INCREMENT,
  `nom_masculin_metier` varchar(20) NOT NULL,
  `nom_feminin_metier` varchar(20) NOT NULL,
  `nom_systeme_metier` varchar(20) NOT NULL,
  `description_metier` mediumtext NOT NULL,
  `construction_charrette_metier` enum('oui','non') NOT NULL,
  `construction_echoppe_metier` enum('oui','non') NOT NULL DEFAULT 'non',
  `niveau_min_metier` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_metier`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Structure de la table `monstre`
--

CREATE TABLE IF NOT EXISTS `monstre` (
  `id_monstre` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_type_monstre` int(11) NOT NULL,
  `id_fk_taille_monstre` int(11) NOT NULL,
  `id_fk_groupe_monstre` int(11) DEFAULT NULL,
  `id_fk_zone_nid_monstre` int(11) NOT NULL,
  `id_fk_donjon_monstre` int(11) DEFAULT NULL,
  `x_monstre` int(11) NOT NULL,
  `y_monstre` int(11) NOT NULL,
  `z_monstre` int(11) NOT NULL DEFAULT '0',
  `x_direction_monstre` int(11) NOT NULL,
  `y_direction_monstre` int(11) NOT NULL,
  `x_min_monstre` int(11) DEFAULT NULL,
  `y_min_monstre` int(11) DEFAULT NULL,
  `x_max_monstre` int(11) DEFAULT NULL,
  `y_max_monstre` int(11) DEFAULT NULL,
  `id_fk_braldun_cible_monstre` int(11) DEFAULT NULL,
  `pv_restant_monstre` int(11) NOT NULL,
  `pv_max_monstre` int(11) NOT NULL,
  `pa_monstre` int(11) NOT NULL,
  `niveau_monstre` int(11) NOT NULL,
  `vue_monstre` int(11) NOT NULL,
  `vue_malus_monstre` int(11) NOT NULL,
  `force_base_monstre` int(11) NOT NULL,
  `force_bm_monstre` int(11) NOT NULL,
  `force_bm_init_monstre` int(11) NOT NULL DEFAULT '0',
  `agilite_base_monstre` int(11) NOT NULL,
  `agilite_bm_monstre` int(11) NOT NULL,
  `agilite_bm_init_monstre` int(11) NOT NULL DEFAULT '0',
  `agilite_malus_monstre` int(11) NOT NULL,
  `sagesse_base_monstre` int(11) NOT NULL,
  `sagesse_bm_monstre` int(11) NOT NULL,
  `sagesse_bm_init_monstre` int(11) NOT NULL DEFAULT '0',
  `vigueur_base_monstre` int(11) NOT NULL,
  `vigueur_bm_monstre` int(11) NOT NULL,
  `vigueur_bm_init_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_attaque_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_defense_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_degat_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_init_attaque_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_init_defense_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_init_degat_monstre` int(11) NOT NULL DEFAULT '0',
  `regeneration_monstre` int(11) NOT NULL,
  `regeneration_malus_monstre` int(11) NOT NULL,
  `armure_naturelle_monstre` int(11) NOT NULL,
  `date_fin_tour_monstre` datetime NOT NULL,
  `duree_prochain_tour_monstre` time NOT NULL,
  `duree_base_tour_monstre` time NOT NULL,
  `nb_kill_monstre` int(11) NOT NULL,
  `date_creation_monstre` datetime NOT NULL,
  `est_mort_monstre` enum('oui','non') NOT NULL DEFAULT 'non',
  `date_a_jouer_monstre` datetime DEFAULT NULL,
  `date_fin_cadavre_monstre` datetime DEFAULT NULL,
  `est_depiaute_cadavre` enum('oui','non') NOT NULL DEFAULT 'non',
  `date_suppression_monstre` datetime DEFAULT NULL COMMENT 'Utilisé pour la disparition des gibiers',
  `nb_dla_jouees_monstre` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_monstre`),
  KEY `id_fk_groupe_monstre` (`id_fk_groupe_monstre`),
  KEY `id_fk_type_monstre` (`id_fk_type_monstre`),
  KEY `id_fk_taille_monstre` (`id_fk_taille_monstre`),
  KEY `id_fk_braldun_cible_monstre` (`id_fk_braldun_cible_monstre`),
  KEY `est_mort_monstre` (`est_mort_monstre`),
  KEY `date_a_jouer_monstre` (`date_a_jouer_monstre`),
  KEY `date_suppression_monstre` (`date_suppression_monstre`),
  KEY `idx_x_monstre_y_monstre` (`x_monstre`,`y_monstre`,`z_monstre`),
  KEY `id_fk_zone_nid_monstre` (`id_fk_zone_nid_monstre`),
  KEY `id_fk_donjon_monstre` (`id_fk_donjon_monstre`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=175015 ;

-- --------------------------------------------------------

--
-- Structure de la table `mot_runique`
--

CREATE TABLE IF NOT EXISTS `mot_runique` (
  `id_mot_runique` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_mot_runique` varchar(6) NOT NULL,
  `id_fk_type_piece_mot_runique` int(11) NOT NULL,
  `suffixe_mot_runique` varchar(15) NOT NULL,
  `coef_lune_changement_mot_runique` int(11) NOT NULL DEFAULT '50',
  `date_generation_mot_runique` datetime NOT NULL,
  `nb_total_rune_mot_runique` int(2) NOT NULL,
  `nb_rune_niveau_a_mot_runique` int(11) NOT NULL DEFAULT '0',
  `nb_rune_niveau_b_mot_runique` int(11) NOT NULL DEFAULT '0',
  `nb_rune_niveau_c_mot_runique` int(11) NOT NULL DEFAULT '0',
  `nb_rune_niveau_d_mot_runique` int(11) NOT NULL DEFAULT '0',
  `id_fk_type_rune_1_mot_runique` int(11) NOT NULL,
  `id_fk_type_rune_2_mot_runique` int(11) DEFAULT NULL,
  `id_fk_type_rune_3_mot_runique` int(11) DEFAULT NULL,
  `id_fk_type_rune_4_mot_runique` int(11) DEFAULT NULL,
  `id_fk_type_rune_5_mot_runique` int(11) DEFAULT NULL,
  `id_fk_type_rune_6_mot_runique` int(11) DEFAULT NULL,
  `effet_mot_runique` varchar(300) NOT NULL,
  PRIMARY KEY (`id_mot_runique`),
  UNIQUE KEY `nom_systeme_mot_runique` (`nom_systeme_mot_runique`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- Structure de la table `nid`
--

CREATE TABLE IF NOT EXISTS `nid` (
  `id_nid` int(11) NOT NULL AUTO_INCREMENT,
  `x_nid` int(11) NOT NULL,
  `y_nid` int(11) NOT NULL,
  `z_nid` int(11) NOT NULL,
  `nb_monstres_total_nid` int(11) NOT NULL,
  `nb_monstres_restants_nid` int(11) NOT NULL,
  `id_fk_zone_nid` int(11) NOT NULL,
  `id_fk_type_monstre_nid` int(11) NOT NULL,
  `id_fk_donjon_nid` int(11) DEFAULT NULL,
  `date_creation_nid` datetime NOT NULL,
  `date_generation_nid` datetime NOT NULL,
  PRIMARY KEY (`id_nid`),
  KEY `id_fk_type_monstre_nid` (`id_fk_type_monstre_nid`),
  KEY `id_fk_zone_nid` (`id_fk_zone_nid`),
  KEY `x_nid` (`x_nid`,`y_nid`,`z_nid`),
  KEY `nb_monstres_restants_nid` (`nb_monstres_restants_nid`),
  KEY `id_fk_donjon_nid` (`id_fk_donjon_nid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=30005 ;

-- --------------------------------------------------------

--
-- Structure de la table `nom`
--

CREATE TABLE IF NOT EXISTS `nom` (
  `id_nom` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(20) NOT NULL,
  PRIMARY KEY (`id_nom`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=63 ;

-- --------------------------------------------------------

--
-- Structure de la table `palissade`
--

CREATE TABLE IF NOT EXISTS `palissade` (
  `id_palissade` int(11) NOT NULL AUTO_INCREMENT,
  `x_palissade` int(11) NOT NULL,
  `y_palissade` int(11) NOT NULL,
  `z_palissade` int(11) NOT NULL DEFAULT '0',
  `agilite_palissade` int(11) NOT NULL,
  `armure_naturelle_palissade` int(11) NOT NULL,
  `pv_max_palissade` int(11) NOT NULL,
  `pv_restant_palissade` int(11) NOT NULL,
  `date_creation_palissade` datetime NOT NULL,
  `date_fin_palissade` datetime NOT NULL,
  `est_destructible_palissade` enum('oui','non') NOT NULL DEFAULT 'oui',
  `id_fk_donjon_palissade` int(11) DEFAULT NULL,
  `est_portail_palissade` enum('oui','non') NOT NULL DEFAULT 'non',
  `code_1_palissade` int(11) NOT NULL,
  `code_2_palissade` int(11) NOT NULL,
  `code_3_palissade` int(11) NOT NULL,
  `code_4_palissade` int(11) NOT NULL,
  PRIMARY KEY (`id_palissade`),
  UNIQUE KEY `xy_palissade` (`x_palissade`,`y_palissade`,`z_palissade`),
  KEY `date_fin_palissade` (`date_fin_palissade`),
  KEY `id_fk_donjon_palissade` (`id_fk_donjon_palissade`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=310671 ;

-- --------------------------------------------------------

--
-- Structure de la table `partage`
--

CREATE TABLE IF NOT EXISTS `partage` (
  `id_partage` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_declarant_partage` int(11) NOT NULL,
  `id_fk_braldun_declare_partage` int(11) NOT NULL,
  `date_declaration_partage` datetime NOT NULL,
  `commentaire_partage` mediumtext NOT NULL,
  PRIMARY KEY (`id_partage`),
  KEY `id_fk_braldun_declarant_partage` (`id_fk_braldun_declarant_partage`),
  KEY `id_fk_braldun_partage_partage` (`id_fk_braldun_declare_partage`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Structure de la table `petit_equipement`
--

CREATE TABLE IF NOT EXISTS `petit_equipement` (
  `id_petit_equipement` int(11) NOT NULL AUTO_INCREMENT,
  `nom_petit_equipement` varchar(50) NOT NULL,
  `id_fk_metier_petit_equipement` int(11) NOT NULL,
  PRIMARY KEY (`id_petit_equipement`),
  UNIQUE KEY `nom_petit_equipement` (`nom_petit_equipement`),
  KEY `id_fk_metier_petit_equipement` (`id_fk_metier_petit_equipement`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Structure de la table `plante`
--

CREATE TABLE IF NOT EXISTS `plante` (
  `id_plante` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_type_plante` int(11) NOT NULL,
  `x_plante` int(11) NOT NULL,
  `y_plante` int(11) NOT NULL,
  `z_plante` int(11) NOT NULL DEFAULT '0',
  `partie_1_plante` int(11) NOT NULL,
  `partie_2_plante` int(11) DEFAULT NULL,
  `partie_3_plante` int(11) DEFAULT NULL,
  `partie_4_plante` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_plante`),
  KEY `id_fk_type_plante` (`id_fk_type_plante`),
  KEY `idx_x_plante_y_plante` (`x_plante`,`y_plante`,`z_plante`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=109839 ;

-- --------------------------------------------------------

--
-- Structure de la table `potion`
--

CREATE TABLE IF NOT EXISTS `potion` (
  `id_potion` int(11) NOT NULL,
  `id_fk_type_potion` int(11) NOT NULL,
  `id_fk_type_qualite_potion` int(11) NOT NULL,
  `niveau_potion` int(11) NOT NULL,
  `date_utilisation_potion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_potion`),
  KEY `id_fk_type_potion` (`id_fk_type_potion`),
  KEY `id_fk_type_qualite_potion` (`id_fk_type_qualite_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `prenom_interdit`
--

CREATE TABLE IF NOT EXISTS `prenom_interdit` (
  `id_prenom_interdit` int(11) NOT NULL AUTO_INCREMENT,
  `texte_prenom_interdit` varchar(30) NOT NULL,
  PRIMARY KEY (`id_prenom_interdit`),
  UNIQUE KEY `texte_prenom_interdit` (`texte_prenom_interdit`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=51 ;

-- --------------------------------------------------------

--
-- Structure de la table `quete`
--

CREATE TABLE IF NOT EXISTS `quete` (
  `id_quete` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_lieu_quete` int(11) NOT NULL,
  `id_fk_braldun_quete` int(11) NOT NULL,
  `date_creation_quete` datetime NOT NULL,
  `date_fin_quete` datetime DEFAULT NULL,
  `gain_quete` text,
  `est_initiatique_quete` enum('oui','non') NOT NULL DEFAULT 'non',
  PRIMARY KEY (`id_quete`),
  UNIQUE KEY `id_fk_lieu_quete_2` (`id_fk_lieu_quete`,`id_fk_braldun_quete`),
  KEY `id_fk_braldun_quete` (`id_fk_braldun_quete`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1098 ;

-- --------------------------------------------------------

--
-- Structure de la table `rang_communaute`
--

CREATE TABLE IF NOT EXISTS `rang_communaute` (
  `id_rang_communaute` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_communaute_rang_communaute` int(11) NOT NULL,
  `ordre_rang_communaute` int(11) NOT NULL,
  `nom_rang_communaute` varchar(40) NOT NULL,
  `description_rang_communaute` varchar(200) NOT NULL,
  PRIMARY KEY (`id_rang_communaute`),
  KEY `id_fk_communaute_rang_communaute` (`id_fk_communaute_rang_communaute`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=221 ;

-- --------------------------------------------------------

--
-- Structure de la table `recette_aliments`
--

CREATE TABLE IF NOT EXISTS `recette_aliments` (
  `id_fk_type_aliment_recette_aliments` int(11) NOT NULL,
  `id_fk_type_ingredient_recette_aliments` int(11) NOT NULL,
  `quantite_recette_aliments` float NOT NULL,
  PRIMARY KEY (`id_fk_type_aliment_recette_aliments`,`id_fk_type_ingredient_recette_aliments`),
  KEY `id_fk_type_ingredient_recette_aliment` (`id_fk_type_ingredient_recette_aliments`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `recette_aliments_potions`
--

CREATE TABLE IF NOT EXISTS `recette_aliments_potions` (
  `id_fk_type_aliment_recette_aliments_potions` int(11) NOT NULL,
  `id_fk_type_potion_recette_aliments_potions` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_aliment_recette_aliments_potions`,`id_fk_type_potion_recette_aliments_potions`),
  KEY `id_fk_type_potion_recette_aliments_potions` (`id_fk_type_potion_recette_aliments_potions`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `recette_cout`
--

CREATE TABLE IF NOT EXISTS `recette_cout` (
  `id_fk_type_equipement_recette_cout` int(11) NOT NULL,
  `niveau_recette_cout` int(11) NOT NULL,
  `cuir_recette_cout` int(11) NOT NULL,
  `fourrure_recette_cout` int(11) NOT NULL,
  `planche_recette_cout` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_equipement_recette_cout`,`niveau_recette_cout`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `recette_cout_minerai`
--

CREATE TABLE IF NOT EXISTS `recette_cout_minerai` (
  `id_fk_type_equipement_recette_cout_minerai` int(11) NOT NULL COMMENT 'Identifiant sur la table recette_equipement',
  `id_fk_type_recette_cout_minerai` int(11) NOT NULL COMMENT 'Identifiant sur la table type_minerai',
  `niveau_recette_cout_minerai` int(11) NOT NULL,
  `quantite_recette_cout_minerai` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_equipement_recette_cout_minerai`,`id_fk_type_recette_cout_minerai`,`niveau_recette_cout_minerai`),
  KEY `id_fk_type_recette_cout_minerai` (`id_fk_type_recette_cout_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `recette_equipements`
--

CREATE TABLE IF NOT EXISTS `recette_equipements` (
  `id_recette_equipement` int(11) NOT NULL AUTO_INCREMENT,
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
  `etat_initial_recette_equipement` int(11) NOT NULL DEFAULT '2000',
  PRIMARY KEY (`id_recette_equipement`),
  UNIQUE KEY `id_fk_type_recette_equipement` (`id_fk_type_recette_equipement`,`niveau_recette_equipement`,`id_fk_type_qualite_recette_equipement`),
  KEY `id_fk_type_emplacement_recette_equipement` (`id_fk_type_emplacement_recette_equipement`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=865 ;

-- --------------------------------------------------------

--
-- Structure de la table `recette_materiel_cout`
--

CREATE TABLE IF NOT EXISTS `recette_materiel_cout` (
  `id_fk_type_materiel_recette_materiel_cout` int(11) NOT NULL,
  `cuir_recette_materiel_cout` int(11) NOT NULL,
  `fourrure_recette_materiel_cout` int(11) NOT NULL,
  `planche_recette_materiel_cout` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_materiel_recette_materiel_cout`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `recette_materiel_cout_minerai`
--

CREATE TABLE IF NOT EXISTS `recette_materiel_cout_minerai` (
  `id_fk_type_materiel_recette_materiel_cout_minerai` int(11) NOT NULL COMMENT 'Identifiant sur la table recette_materiel',
  `id_fk_type_recette_materiel_cout_minerai` int(11) NOT NULL COMMENT 'Identifiant sur la table type_minerai',
  `quantite_lingot_recette_materiel_cout_minerai` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_materiel_recette_materiel_cout_minerai`,`id_fk_type_recette_materiel_cout_minerai`),
  KEY `id_fk_type_recette_materiel_cout_minerai` (`id_fk_type_recette_materiel_cout_minerai`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `recette_materiel_cout_plante`
--

CREATE TABLE IF NOT EXISTS `recette_materiel_cout_plante` (
  `id_fk_type_materiel_recette_materiel_cout_plante` int(11) NOT NULL,
  `id_fk_type_plante_recette_materiel_cout_plante` int(11) NOT NULL,
  `id_fk_type_partieplante_recette_materiel_cout_plante` int(11) NOT NULL,
  `quantite_recette_materiel_cout_plante` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_materiel_recette_materiel_cout_plante`,`id_fk_type_plante_recette_materiel_cout_plante`,`id_fk_type_partieplante_recette_materiel_cout_plante`),
  KEY `id_fk_type_plante_recette_materiel_cout_plante` (`id_fk_type_plante_recette_materiel_cout_plante`),
  KEY `id_fk_type_partieplante_recette_materiel_cout_plante` (`id_fk_type_partieplante_recette_materiel_cout_plante`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `recette_potions`
--

CREATE TABLE IF NOT EXISTS `recette_potions` (
  `id_fk_type_potion_recette_potion` int(11) NOT NULL,
  `id_fk_type_plante_recette_potion` int(11) NOT NULL,
  `id_fk_type_partieplante_recette_potion` int(11) NOT NULL,
  `coef_recette_potion` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_potion_recette_potion`,`id_fk_type_plante_recette_potion`,`id_fk_type_partieplante_recette_potion`),
  KEY `id_fk_type_plante_recette_potion` (`id_fk_type_plante_recette_potion`),
  KEY `id_fk_type_partieplante_recette_potion` (`id_fk_type_partieplante_recette_potion`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `recette_vernis`
--

CREATE TABLE IF NOT EXISTS `recette_vernis` (
  `id_fk_type_potion_recette_vernis` int(11) NOT NULL,
  `id_fk_type_partieplante_recette_vernis` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_potion_recette_vernis`,`id_fk_type_partieplante_recette_vernis`),
  KEY `id_fk_type_partieplante_recette_vernis` (`id_fk_type_partieplante_recette_vernis`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `ref_monstre`
--

CREATE TABLE IF NOT EXISTS `ref_monstre` (
  `id_ref_monstre` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_type_ref_monstre` int(11) NOT NULL,
  `id_fk_taille_ref_monstre` int(11) NOT NULL,
  `niveau_min_ref_monstre` int(11) NOT NULL,
  `niveau_max_ref_monstre` int(11) NOT NULL,
  `min_niveau_vigueur_ref_monstre` int(11) NOT NULL,
  `max_niveau_vigueur_ref_monstre` int(11) NOT NULL,
  `min_niveau_agilite_ref_monstre` int(11) NOT NULL,
  `max_niveau_agilite_ref_monstre` int(11) NOT NULL,
  `min_niveau_sagesse_ref_monstre` int(11) NOT NULL,
  `max_niveau_sagesse_ref_monstre` int(11) NOT NULL,
  `min_niveau_force_ref_monstre` int(11) NOT NULL,
  `max_niveau_force_ref_monstre` int(11) NOT NULL,
  `bm_force_ref_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_agilite_ref_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_sagesse_ref_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_vigueur_ref_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_attaque_ref_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_defense_ref_monstre` int(11) NOT NULL DEFAULT '0',
  `bm_degat_ref_monstre` int(11) NOT NULL DEFAULT '0',
  `vue_ref_monstre` int(11) NOT NULL,
  `min_alea_pourcentage_armure_naturelle_ref_monstre` int(11) NOT NULL,
  `max_alea_pourcentage_armure_naturelle_ref_monstre` int(11) NOT NULL,
  `coef_pv_min_ref_monstre` float NOT NULL DEFAULT '1',
  `coef_pv_max_ref_monstre` float NOT NULL DEFAULT '1',
  `est_creation_pourcentage_ref_monstre` enum('oui','non') NOT NULL DEFAULT 'non',
  `pourcentage_force_ref_monstre` int(11) NOT NULL DEFAULT '0',
  `pourcentage_vigueur_ref_monstre` int(11) NOT NULL DEFAULT '0',
  `pourcentage_agilite_ref_monstre` int(11) NOT NULL DEFAULT '0',
  `pourcentage_sagesse_ref_monstre` int(11) NOT NULL DEFAULT '0',
  `coef_pi_ref_monstre` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_ref_monstre`),
  UNIQUE KEY `id_fk_type_taille_ref_monstre` (`id_fk_type_ref_monstre`,`id_fk_taille_ref_monstre`),
  KEY `id_fk_taille_ref_monstre` (`id_fk_taille_ref_monstre`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=153 ;

-- --------------------------------------------------------

--
-- Structure de la table `region`
--

CREATE TABLE IF NOT EXISTS `region` (
  `id_region` int(11) NOT NULL AUTO_INCREMENT,
  `nom_region` varchar(20) NOT NULL,
  `nom_systeme_region` varchar(20) CHARACTER SET latin1 NOT NULL,
  `description_region` mediumtext CHARACTER SET latin1 NOT NULL,
  `x_min_region` int(11) NOT NULL,
  `x_max_region` int(11) NOT NULL,
  `y_min_region` int(11) NOT NULL,
  `y_max_region` int(11) NOT NULL,
  `est_pvp_region` enum('oui','non') CHARACTER SET latin1 NOT NULL DEFAULT 'non',
  `id_fk_distinction_quete_region` int(11) NOT NULL,
  PRIMARY KEY (`id_region`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `id_role` int(4) NOT NULL AUTO_INCREMENT,
  `nom_systeme_role` varchar(20) NOT NULL,
  `nom_role` varchar(20) NOT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `nom_systeme_role` (`nom_systeme_role`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Structure de la table `route`
--

CREATE TABLE IF NOT EXISTS `route` (
  `id_route` int(11) NOT NULL AUTO_INCREMENT,
  `x_route` int(11) NOT NULL,
  `y_route` int(11) NOT NULL,
  `z_route` int(11) NOT NULL DEFAULT '0',
  `id_fk_braldun_route` int(11) DEFAULT NULL,
  `id_fk_echoppe_route` int(11) DEFAULT NULL,
  `date_creation_route` datetime NOT NULL,
  `date_fin_route` datetime DEFAULT NULL,
  `id_fk_type_qualite_route` int(11) DEFAULT NULL,
  `type_route` enum('route','balise','ville','echoppe','ruine') NOT NULL DEFAULT 'balise',
  `est_visible_route` enum('oui','non') NOT NULL DEFAULT 'oui',
  `id_fk_numero_route` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_route`),
  UNIQUE KEY `x_route` (`x_route`,`y_route`,`z_route`),
  KEY `id_fk_braldun_route` (`id_fk_braldun_route`),
  KEY `date_fin_route` (`date_fin_route`),
  KEY `id_fk_type_qualite_route` (`id_fk_type_qualite_route`),
  KEY `id_fk_numero_route` (`id_fk_numero_route`),
  KEY `id_fk_echoppe_route` (`id_fk_echoppe_route`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=105032 ;

-- --------------------------------------------------------

--
-- Structure de la table `route_numero`
--

CREATE TABLE IF NOT EXISTS `route_numero` (
  `id_route_numero` int(11) NOT NULL AUTO_INCREMENT,
  `description_route_numero` varchar(250) NOT NULL,
  `id_fk_gare_capitale_route_numero` int(11) NOT NULL,
  `id_fk_gare_province_route_numero` int(11) NOT NULL,
  `est_ouverte_route_numero` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_capitales_route_numero` enum('oui','non') NOT NULL,
  PRIMARY KEY (`id_route_numero`),
  KEY `id_fk_gare_capitale_route_numero` (`id_fk_gare_capitale_route_numero`),
  KEY `id_fk_gare_province_route_numero` (`id_fk_gare_province_route_numero`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Structure de la table `rune`
--

CREATE TABLE IF NOT EXISTS `rune` (
  `id_rune` int(11) NOT NULL,
  `id_fk_type_rune` int(11) NOT NULL,
  `est_identifiee_rune` enum('oui','non') NOT NULL DEFAULT 'non',
  PRIMARY KEY (`id_rune`),
  KEY `id_fk_type_rune` (`id_fk_type_rune`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `script`
--

CREATE TABLE IF NOT EXISTS `script` (
  `id_script` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_script` int(11) NOT NULL,
  `nom_script` varchar(50) NOT NULL,
  `date_debut_script` datetime NOT NULL,
  `date_fin_script` datetime DEFAULT NULL,
  `etat_script` enum('OK','KO','EN_COURS') NOT NULL DEFAULT 'EN_COURS',
  `message_script` varchar(200) NOT NULL,
  `type_script` enum('DYNAMIQUE','STATIQUE','APPEL') NOT NULL,
  `ip_script` varchar(100) NOT NULL,
  `hostname_script` varchar(200) NOT NULL,
  `url_script` varchar(300) NOT NULL,
  PRIMARY KEY (`id_script`),
  KEY `type_script` (`type_script`),
  KEY `id_fk_braldun_script_2` (`id_fk_braldun_script`,`date_debut_script`,`type_script`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=180429 ;

-- --------------------------------------------------------

--
-- Structure de la table `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id_fk_braldun_session` int(11) NOT NULL,
  `id_php_session` varchar(40) NOT NULL,
  `ip_session` varchar(50) NOT NULL,
  `date_derniere_action_session` datetime NOT NULL,
  PRIMARY KEY (`id_fk_braldun_session`),
  UNIQUE KEY `id_php_session` (`id_php_session`),
  KEY `date_derniere_action_session` (`date_derniere_action_session`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `sondage`
--

CREATE TABLE IF NOT EXISTS `sondage` (
  `id_sondage` int(11) NOT NULL AUTO_INCREMENT,
  `question_sondage` text NOT NULL,
  `date_debut_sondage` datetime NOT NULL,
  `date_fin_sondage` datetime NOT NULL,
  `etat_sondage` enum('NON_DEBUTE','EN_COURS','TERMINE') NOT NULL DEFAULT 'NON_DEBUTE',
  PRIMARY KEY (`id_sondage`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Structure de la table `sondage_reponse`
--

CREATE TABLE IF NOT EXISTS `sondage_reponse` (
  `id_sondage_reponse` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_sondage_reponse` int(11) NOT NULL,
  `id_fk_braldun_sondage_reponse` int(11) NOT NULL,
  `date_sondage_reponse` datetime NOT NULL,
  `commentaire_braldun_sondage_reponse` text,
  `reponse_1_sondage_reponse` varchar(200) DEFAULT NULL,
  `reponse_2_sondage_reponse` varchar(200) DEFAULT NULL,
  `reponse_3_sondage_reponse` varchar(200) DEFAULT NULL,
  `reponse_4_sondage_reponse` varchar(200) DEFAULT NULL,
  `reponse_5_sondage_reponse` varchar(200) DEFAULT NULL,
  `reponse_6_sondage_reponse` varchar(200) DEFAULT NULL,
  `reponse_7_sondage_reponse` varchar(200) DEFAULT NULL,
  `reponse_8_sondage_reponse` varchar(200) DEFAULT NULL,
  `reponse_9_sondage_reponse` varchar(200) DEFAULT NULL,
  `reponse_10_sondage_reponse` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_sondage_reponse`),
  KEY `id_fk_sondage_reponse` (`id_fk_sondage_reponse`,`id_fk_braldun_sondage_reponse`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=163 ;

-- --------------------------------------------------------

--
-- Structure de la table `soule_equipe`
--

CREATE TABLE IF NOT EXISTS `soule_equipe` (
  `id_soule_equipe` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_match_soule_equipe` int(11) NOT NULL,
  `date_entree_soule_equipe` datetime NOT NULL,
  `id_fk_braldun_soule_equipe` int(11) NOT NULL,
  `camp_soule_equipe` enum('a','b') NOT NULL DEFAULT 'a',
  `x_avant_braldun_soule_equipe` int(11) DEFAULT NULL,
  `y_avant_braldun_soule_equipe` int(11) DEFAULT NULL,
  `retour_xy_soule_equipe` enum('oui','non') NOT NULL DEFAULT 'oui',
  `nb_braldun_plaquage_soule_equipe` int(11) NOT NULL,
  `nb_plaque_soule_equipe` int(11) NOT NULL,
  `nb_passe_soule_equipe` int(11) NOT NULL DEFAULT '0',
  `nb_case_ballon_soule_equipe` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_soule_equipe`),
  KEY `id_fk_braldun_soule_equipe` (`id_fk_braldun_soule_equipe`),
  KEY `id_fk_match_soule_equipe` (`id_fk_match_soule_equipe`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=248 ;

-- --------------------------------------------------------

--
-- Structure de la table `soule_match`
--

CREATE TABLE IF NOT EXISTS `soule_match` (
  `id_soule_match` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_terrain_soule_match` int(11) NOT NULL,
  `date_debut_soule_match` datetime DEFAULT NULL,
  `date_fin_soule_match` datetime DEFAULT NULL,
  `nom_equipea_soule_match` varchar(100) DEFAULT NULL,
  `nom_equipeb_soule_match` varchar(100) DEFAULT NULL,
  `x_ballon_soule_match` int(11) DEFAULT NULL,
  `y_ballon_soule_match` int(11) DEFAULT NULL,
  `id_fk_joueur_ballon_soule_match` int(11) DEFAULT NULL,
  `nb_jours_quota_soule_match` int(11) NOT NULL,
  `camp_gagnant_soule_match` enum('a','b') DEFAULT NULL,
  `px_equipea_soule_match` int(11) NOT NULL,
  `px_equipeb_soule_match` int(11) NOT NULL,
  `html_fin_soule_match` longtext,
  `est_saison_soule_match` enum('oui','non') DEFAULT NULL,
  PRIMARY KEY (`id_soule_match`),
  KEY `id_fk_terrain_soule_match` (`id_fk_terrain_soule_match`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Structure de la table `soule_message`
--

CREATE TABLE IF NOT EXISTS `soule_message` (
  `id_soule_message` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_match_soule_message` int(11) NOT NULL,
  `id_fk_braldun_soule_message` int(11) NOT NULL,
  `camp_soule_message` enum('a','b') NOT NULL,
  `date_soule_message` datetime NOT NULL,
  `message_soule_message` varchar(1000) NOT NULL,
  PRIMARY KEY (`id_soule_message`),
  KEY `id_fk_match_soule_message` (`id_fk_match_soule_message`),
  KEY `id_fk_braldun_soule_message` (`id_fk_braldun_soule_message`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1184 ;

-- --------------------------------------------------------

--
-- Structure de la table `soule_nom_equipe`
--

CREATE TABLE IF NOT EXISTS `soule_nom_equipe` (
  `id_soule_nom_equipe` int(11) NOT NULL AUTO_INCREMENT,
  `nom_soule_nom_equipe` varchar(200) NOT NULL,
  PRIMARY KEY (`id_soule_nom_equipe`),
  UNIQUE KEY `nom_soule_nom_equipe` (`nom_soule_nom_equipe`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

--
-- Structure de la table `soule_terrain`
--

CREATE TABLE IF NOT EXISTS `soule_terrain` (
  `id_soule_terrain` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_soule_terrain` varchar(20) NOT NULL,
  `nom_soule_terrain` varchar(20) NOT NULL,
  `info_soule_terrain` varchar(40) NOT NULL,
  `niveau_soule_terrain` int(11) NOT NULL,
  `x_min_soule_terrain` int(11) NOT NULL,
  `x_max_soule_terrain` int(11) NOT NULL,
  `y_min_soule_terrain` int(11) NOT NULL,
  `y_max_soule_terrain` int(11) NOT NULL,
  PRIMARY KEY (`id_soule_terrain`),
  UNIQUE KEY `nom_systeme_soule_terrain` (`nom_systeme_soule_terrain`),
  UNIQUE KEY `niveau_soule_terrain` (`niveau_soule_terrain`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Structure de la table `stats_distinction`
--

CREATE TABLE IF NOT EXISTS `stats_distinction` (
  `id_stats_distinction` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_stats_distinction` int(11) NOT NULL,
  `mois_stats_distinction` date NOT NULL,
  `points_stats_distinction` int(11) NOT NULL,
  `niveau_braldun_stats_distinction` int(11) NOT NULL,
  PRIMARY KEY (`id_stats_distinction`),
  UNIQUE KEY `id_braldun_stats_distinction` (`id_fk_braldun_stats_distinction`,`mois_stats_distinction`,`niveau_braldun_stats_distinction`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=95790 ;

-- --------------------------------------------------------

--
-- Structure de la table `stats_experience`
--

CREATE TABLE IF NOT EXISTS `stats_experience` (
  `id_stats_experience` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_stats_experience` int(11) NOT NULL,
  `mois_stats_experience` date NOT NULL,
  `nb_px_perso_gagnes_stats_experience` int(11) NOT NULL,
  `nb_px_commun_gagnes_stats_experience` int(11) NOT NULL,
  `niveau_braldun_stats_experience` int(11) NOT NULL,
  PRIMARY KEY (`id_stats_experience`),
  UNIQUE KEY `id_braldun_stats_experience` (`id_fk_braldun_stats_experience`,`mois_stats_experience`,`niveau_braldun_stats_experience`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7310 ;

-- --------------------------------------------------------

--
-- Structure de la table `stats_fabricants`
--

CREATE TABLE IF NOT EXISTS `stats_fabricants` (
  `id_stats_fabricants` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_stats_fabricants` int(11) NOT NULL,
  `niveau_braldun_stats_fabricants` int(11) NOT NULL,
  `somme_niveau_piece_stats_fabricants` int(11) NOT NULL,
  `mois_stats_fabricants` date NOT NULL,
  `nb_piece_stats_fabricants` int(11) NOT NULL,
  `id_fk_metier_stats_fabricants` int(11) NOT NULL,
  PRIMARY KEY (`id_stats_fabricants`),
  UNIQUE KEY `id_fk_braldun_stats_fabricants` (`id_fk_braldun_stats_fabricants`,`niveau_braldun_stats_fabricants`,`mois_stats_fabricants`,`id_fk_metier_stats_fabricants`),
  KEY `id_fk_metier_stats_fabricants` (`id_fk_metier_stats_fabricants`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1082 ;

-- --------------------------------------------------------

--
-- Structure de la table `stats_mots_runiques`
--

CREATE TABLE IF NOT EXISTS `stats_mots_runiques` (
  `id_stats_mots_runiques` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_mot_runique_stats_mots_runiques` int(11) NOT NULL,
  `mois_stats_mots_runiques` date NOT NULL,
  `id_fk_type_piece_stats_mots_runiques` int(11) NOT NULL,
  `niveau_piece_stats_mots_runiques` int(11) NOT NULL,
  `nb_piece_stats_mots_runiques` int(11) NOT NULL,
  PRIMARY KEY (`id_stats_mots_runiques`),
  UNIQUE KEY `id_fk_mot_runique_stats_mots_runiques` (`id_fk_mot_runique_stats_mots_runiques`,`mois_stats_mots_runiques`,`id_fk_type_piece_stats_mots_runiques`,`niveau_piece_stats_mots_runiques`),
  KEY `id_fk_type_piece_stats_mots_runiques` (`id_fk_type_piece_stats_mots_runiques`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Structure de la table `stats_recolteurs`
--

CREATE TABLE IF NOT EXISTS `stats_recolteurs` (
  `id_stats_recolteurs` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_stats_recolteurs` int(11) NOT NULL,
  `mois_stats_recolteurs` date NOT NULL,
  `niveau_braldun_stats_recolteurs` int(11) NOT NULL,
  `nb_minerai_stats_recolteurs` int(11) NOT NULL,
  `nb_partieplante_stats_recolteurs` int(11) NOT NULL,
  `nb_peau_stats_recolteurs` int(11) NOT NULL,
  `nb_viande_stats_recolteurs` int(11) NOT NULL,
  `nb_bois_stats_recolteurs` int(11) NOT NULL,
  `nb_graines_stats_recolteurs` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_stats_recolteurs`),
  UNIQUE KEY `id_fk_braldun_stats_recolteurs` (`id_fk_braldun_stats_recolteurs`,`mois_stats_recolteurs`,`niveau_braldun_stats_recolteurs`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3263 ;

-- --------------------------------------------------------

--
-- Structure de la table `stats_reputation`
--

CREATE TABLE IF NOT EXISTS `stats_reputation` (
  `id_stats_reputation` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_stats_reputation` int(11) NOT NULL,
  `mois_stats_reputation` date NOT NULL,
  `points_gredin_stats_reputation` int(11) NOT NULL,
  `points_redresseur_stats_reputation` int(11) NOT NULL,
  `niveau_braldun_stats_reputation` int(11) NOT NULL,
  `points_gredin_total_stats_reputation` int(11) NOT NULL DEFAULT '0',
  `points_redresseur_total_stats_reputation` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_stats_reputation`),
  UNIQUE KEY `id_braldun_stats_reputation` (`id_fk_braldun_stats_reputation`,`mois_stats_reputation`,`niveau_braldun_stats_reputation`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=86589 ;

-- --------------------------------------------------------

--
-- Structure de la table `stats_routes`
--

CREATE TABLE IF NOT EXISTS `stats_routes` (
  `id_stats_routes` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_braldun_stats_routes` int(11) NOT NULL,
  `niveau_braldun_stats_routes` int(11) NOT NULL,
  `mois_stats_routes` date NOT NULL,
  `nb_stats_routes` int(11) NOT NULL,
  `id_fk_metier_stats_routes` int(11) NOT NULL,
  PRIMARY KEY (`id_stats_routes`),
  UNIQUE KEY `id_fk_braldun_stats_routes` (`id_fk_braldun_stats_routes`,`niveau_braldun_stats_routes`,`mois_stats_routes`,`id_fk_metier_stats_routes`),
  KEY `id_fk_metier_stats_routes` (`id_fk_metier_stats_routes`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=668 ;

-- --------------------------------------------------------

--
-- Structure de la table `stats_runes`
--

CREATE TABLE IF NOT EXISTS `stats_runes` (
  `id_stats_runes` int(11) NOT NULL AUTO_INCREMENT,
  `mois_stats_runes` date NOT NULL,
  `id_fk_type_rune_stats_runes` int(11) NOT NULL,
  `nb_rune_stats_runes` int(11) NOT NULL,
  PRIMARY KEY (`id_stats_runes`),
  UNIQUE KEY `mois_stats_runes` (`mois_stats_runes`,`id_fk_type_rune_stats_runes`),
  KEY `id_fk_type_rune_stats_runes` (`id_fk_type_rune_stats_runes`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=190 ;

-- --------------------------------------------------------

--
-- Structure de la table `stock_tabac`
--

CREATE TABLE IF NOT EXISTS `stock_tabac` (
  `id_stock_tabac` int(11) NOT NULL AUTO_INCREMENT,
  `date_stock_tabac` date NOT NULL,
  `id_fk_type_stock_tabac` int(11) NOT NULL,
  `id_fk_region_stock_tabac` int(11) NOT NULL,
  `nb_feuille_initial_stock_tabac` int(11) NOT NULL DEFAULT '0',
  `nb_feuille_restant_stock_tabac` int(11) NOT NULL,
  `prix_unitaire_vente_stock_tabac` int(11) NOT NULL,
  `prix_unitaire_reprise_stock_tabac` int(11) NOT NULL,
  PRIMARY KEY (`id_stock_tabac`),
  UNIQUE KEY `unique` (`date_stock_tabac`,`id_fk_type_stock_tabac`,`id_fk_region_stock_tabac`),
  KEY `stock_tabac_ibfk_3` (`id_fk_type_stock_tabac`),
  KEY `stock_tabac_ibfk_4` (`id_fk_region_stock_tabac`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15445 ;

-- --------------------------------------------------------

--
-- Structure de la table `taille_monstre`
--

CREATE TABLE IF NOT EXISTS `taille_monstre` (
  `id_taille_monstre` int(11) NOT NULL AUTO_INCREMENT,
  `nom_taille_m_monstre` varchar(20) NOT NULL COMMENT 'Nom de la taille au masculin',
  `nom_taille_f_monstre` varchar(20) NOT NULL COMMENT 'Nom de la taille au féminin',
  `pourcentage_taille_monstre` int(11) NOT NULL COMMENT 'Pourcentage d''apparition',
  `nb_cdm_taille_monstre` int(11) NOT NULL,
  PRIMARY KEY (`id_taille_monstre`),
  UNIQUE KEY `nom_taille_f_monstre` (`nom_taille_f_monstre`),
  UNIQUE KEY `nom_taille_m_monstre` (`nom_taille_m_monstre`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Structure de la table `testeur`
--

CREATE TABLE IF NOT EXISTS `testeur` (
  `id_testeur` int(11) NOT NULL AUTO_INCREMENT,
  `email_testeur` varchar(100) NOT NULL,
  `id_fk_nom_testeur` int(11) NOT NULL,
  `nom_testeur` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_testeur`),
  UNIQUE KEY `email_testeur` (`email_testeur`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=127 ;

-- --------------------------------------------------------

--
-- Structure de la table `tunnel`
--

CREATE TABLE IF NOT EXISTS `tunnel` (
  `id_tunnel` int(11) NOT NULL AUTO_INCREMENT,
  `x_tunnel` int(11) NOT NULL,
  `y_tunnel` int(11) NOT NULL,
  `z_tunnel` int(11) NOT NULL,
  `date_tunnel` datetime NOT NULL,
  `est_eboulable_tunnel` enum('oui','non') NOT NULL DEFAULT 'oui',
  PRIMARY KEY (`id_tunnel`),
  KEY `x_tunnel` (`x_tunnel`,`y_tunnel`,`z_tunnel`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=81689 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_aliment`
--

CREATE TABLE IF NOT EXISTS `type_aliment` (
  `id_type_aliment` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_aliment` varchar(50) NOT NULL,
  `nom_systeme_type_aliment` varchar(10) NOT NULL,
  `bbdf_base_type_aliment` int(11) DEFAULT NULL,
  `poids_unitaire_type_aliment` float NOT NULL,
  `type_bbdf_type_aliment` enum('simple','double','double_ameliore','triple','quadruple','quintuple') DEFAULT 'simple',
  `type_type_aliment` enum('manger','boire') NOT NULL DEFAULT 'manger',
  PRIMARY KEY (`id_type_aliment`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_bosquet`
--

CREATE TABLE IF NOT EXISTS `type_bosquet` (
  `id_type_bosquet` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_bosquet` varchar(20) NOT NULL,
  `nom_systeme_type_bosquet` varchar(10) NOT NULL,
  `description_type_bosquet` varchar(200) NOT NULL,
  `nb_creation_type_bosquet` int(11) NOT NULL,
  PRIMARY KEY (`id_type_bosquet`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_buisson`
--

CREATE TABLE IF NOT EXISTS `type_buisson` (
  `id_type_buisson` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_buisson` varchar(40) NOT NULL,
  `nom_systeme_type_buisson` varchar(10) NOT NULL,
  `description_type_buisson` varchar(200) NOT NULL,
  `nb_creation_type_buisson` int(11) NOT NULL,
  PRIMARY KEY (`id_type_buisson`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_categorie`
--

CREATE TABLE IF NOT EXISTS `type_categorie` (
  `id_type_categorie` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_categorie` varchar(30) NOT NULL,
  `nom_type_categorie` varchar(50) NOT NULL,
  `ordre_type_categorie` smallint(6) NOT NULL,
  PRIMARY KEY (`id_type_categorie`),
  UNIQUE KEY `nom_systeme_categorie` (`nom_systeme_type_categorie`,`nom_type_categorie`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_dependance`
--

CREATE TABLE IF NOT EXISTS `type_dependance` (
  `id_fk_type_lieu_type_dependance` int(11) NOT NULL,
  `id_fk_type_lieu_enfant_type_dependance` int(11) NOT NULL,
  `niveau_type_dependance` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_lieu_type_dependance`,`id_fk_type_lieu_enfant_type_dependance`),
  KEY `id_fk_type_lieu_enfant_type_dependance` (`id_fk_type_lieu_enfant_type_dependance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `type_distinction`
--

CREATE TABLE IF NOT EXISTS `type_distinction` (
  `id_type_distinction` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_distinction` varchar(50) NOT NULL,
  `nom_type_distinction` varchar(100) NOT NULL,
  `id_fk_lieu_type_distinction` int(11) DEFAULT NULL,
  `id_fk_type_categorie_distinction` int(11) NOT NULL,
  `points_type_distinction` int(11) NOT NULL,
  PRIMARY KEY (`id_type_distinction`),
  UNIQUE KEY `id_fk_lieu_type_distinction` (`id_fk_lieu_type_distinction`),
  KEY `id_fk_type_categorie_distinction` (`id_fk_type_categorie_distinction`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=123 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_emplacement`
--

CREATE TABLE IF NOT EXISTS `type_emplacement` (
  `id_type_emplacement` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_emplacement` varchar(20) NOT NULL,
  `nom_type_emplacement` varchar(20) NOT NULL,
  `ordre_emplacement` int(11) NOT NULL,
  `est_equipable_type_emplacement` enum('oui','non') NOT NULL DEFAULT 'oui',
  PRIMARY KEY (`id_type_emplacement`),
  KEY `nom_systeme_type_emplacement` (`nom_systeme_type_emplacement`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_equipement`
--

CREATE TABLE IF NOT EXISTS `type_equipement` (
  `id_type_equipement` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_equipement` varchar(50) CHARACTER SET utf8 NOT NULL,
  `region_1_nom_type_equipement` varchar(50) CHARACTER SET utf8 NOT NULL,
  `region_2_nom_type_equipement` varchar(50) CHARACTER SET utf8 NOT NULL,
  `region_3_nom_type_equipement` varchar(50) CHARACTER SET utf8 NOT NULL,
  `region_4_nom_type_equipement` varchar(50) CHARACTER SET utf8 NOT NULL,
  `region_5_nom_type_equipement` varchar(50) CHARACTER SET utf8 NOT NULL,
  `id_fk_type_munition_type_equipement` int(11) DEFAULT NULL,
  `description_type_equipement` varchar(300) CHARACTER SET utf8 DEFAULT NULL,
  `nb_runes_max_type_equipement` int(11) NOT NULL,
  `id_fk_metier_type_equipement` int(11) DEFAULT NULL,
  `id_fk_type_piece_type_equipement` int(11) NOT NULL,
  `nb_munition_type_equipement` int(11) NOT NULL DEFAULT '0',
  `genre_type_equipement` enum('masculin','feminin') NOT NULL DEFAULT 'masculin',
  `id_fk_type_ingredient_base_type_equipement` int(11) NOT NULL,
  `id_fk_donjon_type_equipement` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_type_equipement`),
  KEY `nom_type_equipement` (`nom_type_equipement`),
  KEY `id_fk_type_piece_type_equipement` (`id_fk_type_piece_type_equipement`),
  KEY `id_fk_type_munition_type_equipement` (`id_fk_type_munition_type_equipement`),
  KEY `id_fk_type_ingredient_base_type_equipement` (`id_fk_type_ingredient_base_type_equipement`),
  KEY `id_fk_donjon_type_equipement` (`id_fk_donjon_type_equipement`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=45 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_etape`
--

CREATE TABLE IF NOT EXISTS `type_etape` (
  `id_type_etape` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_etape` varchar(30) NOT NULL,
  `nom_type_etape` varchar(30) NOT NULL,
  `est_metier_type_etape` enum('oui','non') NOT NULL DEFAULT 'non',
  `est_initiatique_type_etape` enum('oui','non') NOT NULL DEFAULT 'non',
  PRIMARY KEY (`id_type_etape`),
  UNIQUE KEY `nom_systeme_type_etape` (`nom_systeme_type_etape`),
  KEY `est_metier_type_etape` (`est_metier_type_etape`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_etape_metier`
--

CREATE TABLE IF NOT EXISTS `type_etape_metier` (
  `id_fk_etape_type_etape_metier` int(11) NOT NULL,
  `id_fk_metier_type_etape_metier` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_etape_type_etape_metier`,`id_fk_metier_type_etape_metier`),
  KEY `id_fk_metier_type_etape_metier` (`id_fk_metier_type_etape_metier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `type_evenement`
--

CREATE TABLE IF NOT EXISTS `type_evenement` (
  `id_type_evenement` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_evenement` varchar(20) NOT NULL,
  PRIMARY KEY (`id_type_evenement`),
  UNIQUE KEY `nom_type_evenement` (`nom_type_evenement`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_evenement_communaute`
--

CREATE TABLE IF NOT EXISTS `type_evenement_communaute` (
  `id_type_evenement_communaute` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_evenement_communaute` varchar(60) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id_type_evenement_communaute`),
  UNIQUE KEY `nom_type_evenement_communaute` (`nom_type_evenement_communaute`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_graine`
--

CREATE TABLE IF NOT EXISTS `type_graine` (
  `id_type_graine` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_graine` varchar(20) CHARACTER SET latin1 NOT NULL,
  `nom_systeme_type_graine` varchar(20) CHARACTER SET latin1 NOT NULL,
  `description_type_graine` varchar(200) CHARACTER SET latin1 NOT NULL,
  `prefix_type_graine` varchar(3) NOT NULL,
  `type_type_graine` enum('nourriture','biere','tabac') NOT NULL DEFAULT 'nourriture',
  `id_fk_type_ingredient_type_graine` int(11) DEFAULT NULL,
  `id_fk_type_tabac_type_graine` int(11) DEFAULT NULL,
  `coef_poids_type_graine` int(11) NOT NULL DEFAULT '25',
  PRIMARY KEY (`id_type_graine`),
  KEY `id_fk_type_ingredient_type_graine` (`id_fk_type_ingredient_type_graine`),
  KEY `id_fk_type_tabac_type_graine` (`id_fk_type_tabac_type_graine`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_groupe_monstre`
--

CREATE TABLE IF NOT EXISTS `type_groupe_monstre` (
  `id_type_groupe_monstre` int(11) NOT NULL AUTO_INCREMENT,
  `nom_groupe_monstre` varchar(20) NOT NULL,
  `nb_membres_min_type_groupe_monstre` int(11) NOT NULL,
  `nb_membres_max_type_groupe_monstre` int(11) NOT NULL,
  `repeuplement_type_groupe_monstre` enum('oui','non') NOT NULL DEFAULT 'non',
  PRIMARY KEY (`id_type_groupe_monstre`),
  UNIQUE KEY `nom_groupe_monstre` (`nom_groupe_monstre`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_historique_equipement`
--

CREATE TABLE IF NOT EXISTS `type_historique_equipement` (
  `id_type_historique_equipement` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_historique_equipement` varchar(20) NOT NULL,
  PRIMARY KEY (`id_type_historique_equipement`),
  UNIQUE KEY `nom_type_historique_equipement` (`nom_type_historique_equipement`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_historique_materiel`
--

CREATE TABLE IF NOT EXISTS `type_historique_materiel` (
  `id_type_historique_materiel` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_historique_materiel` varchar(20) NOT NULL,
  PRIMARY KEY (`id_type_historique_materiel`),
  UNIQUE KEY `nom_type_historique_materiel` (`nom_type_historique_materiel`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_historique_potion`
--

CREATE TABLE IF NOT EXISTS `type_historique_potion` (
  `id_type_historique_potion` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_historique_potion` varchar(20) NOT NULL,
  PRIMARY KEY (`id_type_historique_potion`),
  UNIQUE KEY `nom_type_historique_potion` (`nom_type_historique_potion`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_historique_rune`
--

CREATE TABLE IF NOT EXISTS `type_historique_rune` (
  `id_type_historique_rune` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_historique_rune` varchar(20) NOT NULL,
  PRIMARY KEY (`id_type_historique_rune`),
  UNIQUE KEY `nom_type_historique_rune` (`nom_type_historique_rune`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_ingredient`
--

CREATE TABLE IF NOT EXISTS `type_ingredient` (
  `id_type_ingredient` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_ingredient` varchar(20) NOT NULL,
  `nom_type_ingredient` varchar(20) NOT NULL,
  `nom_pluriel_type_ingredient` varchar(30) DEFAULT NULL,
  `id_fk_type_minerai_ingredient` int(11) DEFAULT NULL,
  `id_fk_type_graine_ingredient` int(11) DEFAULT NULL,
  `est_cuisinier_type_ingredient` enum('oui','non') NOT NULL DEFAULT 'oui',
  `poids_unitaire_type_ingredient` float DEFAULT NULL,
  PRIMARY KEY (`id_type_ingredient`),
  UNIQUE KEY `nom_systeme_type_ingredient` (`nom_systeme_type_ingredient`,`nom_type_ingredient`),
  KEY `id_fk_type_minerai_ingredient` (`id_fk_type_minerai_ingredient`),
  KEY `id_fk_type_graine_ingredient` (`id_fk_type_graine_ingredient`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_lieu`
--

CREATE TABLE IF NOT EXISTS `type_lieu` (
  `id_type_lieu` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_lieu` varchar(20) NOT NULL,
  `nom_systeme_type_lieu` varchar(20) NOT NULL,
  `description_type_lieu` mediumtext NOT NULL,
  `niveau_min_type_lieu` int(2) NOT NULL,
  `pa_utilisation_type_lieu` int(1) NOT NULL,
  `est_alterable_type_lieu` enum('oui','non') NOT NULL,
  `est_franchissable_type_lieu` enum('oui','non') NOT NULL,
  `id_fk_type_lieu_communaute_type_lieu` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_type_lieu`),
  KEY `id_fk_type_lieu_communaute_type_lieu` (`id_fk_type_lieu_communaute_type_lieu`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=40 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_lieu_communaute`
--

CREATE TABLE IF NOT EXISTS `type_lieu_communaute` (
  `id_type_lieu_communaute` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_lieu_communaute` varchar(20) NOT NULL,
  `nom_type_lieu_communaute` varchar(20) NOT NULL,
  PRIMARY KEY (`id_type_lieu_communaute`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_lot`
--

CREATE TABLE IF NOT EXISTS `type_lot` (
  `id_type_lot` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_lot` varchar(50) NOT NULL,
  PRIMARY KEY (`id_type_lot`),
  UNIQUE KEY `nom_systeme_type_lot` (`nom_systeme_type_lot`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_materiel`
--

CREATE TABLE IF NOT EXISTS `type_materiel` (
  `id_type_materiel` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_materiel` varchar(50) NOT NULL,
  `nom_systeme_type_materiel` varchar(20) NOT NULL,
  `description_type_materiel` varchar(300) DEFAULT NULL,
  `id_fk_metier_type_materiel` int(11) NOT NULL,
  `durabilite_type_materiel` int(11) NOT NULL,
  `usure_type_materiel` int(11) NOT NULL,
  `capacite_type_materiel` int(11) NOT NULL,
  `poids_type_materiel` float NOT NULL DEFAULT '0',
  `force_base_min_type_materiel` int(11) NOT NULL DEFAULT '0',
  `agilite_base_min_type_materiel` int(11) NOT NULL DEFAULT '0',
  `sagesse_base_min_type_materiel` int(11) NOT NULL DEFAULT '0',
  `vigueur_base_min_type_materiel` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_type_materiel`),
  UNIQUE KEY `nom_type_materiel_2` (`nom_type_materiel`),
  UNIQUE KEY `nom_systeme_type_materiel` (`nom_systeme_type_materiel`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_materiel_assemble`
--

CREATE TABLE IF NOT EXISTS `type_materiel_assemble` (
  `id_base_type_materiel_assemble` int(11) NOT NULL,
  `id_supplement_type_materiel_assemble` int(11) NOT NULL,
  PRIMARY KEY (`id_base_type_materiel_assemble`,`id_supplement_type_materiel_assemble`),
  KEY `id_supplement_type_materiel_assemble` (`id_supplement_type_materiel_assemble`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `type_message`
--

CREATE TABLE IF NOT EXISTS `type_message` (
  `id_type_message` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_message` varchar(20) NOT NULL,
  `nom_type_message` varchar(30) NOT NULL,
  PRIMARY KEY (`id_type_message`),
  UNIQUE KEY `nom_systeme_type_message` (`nom_systeme_type_message`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_minerai`
--

CREATE TABLE IF NOT EXISTS `type_minerai` (
  `id_type_minerai` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_minerai` varchar(20) NOT NULL,
  `nom_systeme_type_minerai` varchar(10) NOT NULL,
  `description_type_minerai` varchar(200) NOT NULL,
  `nb_creation_type_minerai` int(11) NOT NULL,
  `prefix_type_minerai` varchar(3) NOT NULL DEFAULT 'de ',
  PRIMARY KEY (`id_type_minerai`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_monstre`
--

CREATE TABLE IF NOT EXISTS `type_monstre` (
  `id_type_monstre` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_monstre` varchar(30) NOT NULL,
  `genre_type_monstre` enum('feminin','masculin') NOT NULL COMMENT 'Genre du monstre : masculin ou féminin',
  `id_fk_type_groupe_monstre` int(11) NOT NULL,
  `nom_nid_type_monstre` varchar(30) NOT NULL DEFAULT 'Nid',
  `description_type_monstre` mediumtext CHARACTER SET utf8,
  `est_dans_quete_type_monstre` enum('oui','non') NOT NULL DEFAULT 'oui',
  PRIMARY KEY (`id_type_monstre`),
  KEY `id_fk_type_groupe_monstre` (`id_fk_type_groupe_monstre`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=47 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_monstre_mcompetence`
--

CREATE TABLE IF NOT EXISTS `type_monstre_mcompetence` (
  `id_fk_type_monstre_mcompetence` int(11) NOT NULL,
  `id_fk_mcompetence_type_monstre_mcompetence` int(11) NOT NULL,
  `ordre_type_monstre_mcompetence` int(11) NOT NULL,
  PRIMARY KEY (`id_fk_type_monstre_mcompetence`,`id_fk_mcompetence_type_monstre_mcompetence`),
  KEY `id_fk_mcompetence_type_monstre_mcompetence` (`id_fk_mcompetence_type_monstre_mcompetence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `type_munition`
--

CREATE TABLE IF NOT EXISTS `type_munition` (
  `id_type_munition` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_munition` varchar(15) NOT NULL,
  `nom_type_munition` varchar(15) NOT NULL,
  `nom_pluriel_type_munition` varchar(15) NOT NULL,
  PRIMARY KEY (`id_type_munition`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_partieplante`
--

CREATE TABLE IF NOT EXISTS `type_partieplante` (
  `id_type_partieplante` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_partieplante` varchar(20) NOT NULL,
  `nom_systeme_type_partieplante` varchar(10) NOT NULL,
  `description_type_partieplante` varchar(200) NOT NULL,
  PRIMARY KEY (`id_type_partieplante`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_piece`
--

CREATE TABLE IF NOT EXISTS `type_piece` (
  `id_type_piece` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_piece` varchar(10) NOT NULL,
  `nom_type_piece` varchar(20) NOT NULL,
  PRIMARY KEY (`id_type_piece`),
  UNIQUE KEY `nom_systeme_type_piece` (`nom_systeme_type_piece`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_plante`
--

CREATE TABLE IF NOT EXISTS `type_plante` (
  `id_type_plante` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_plante` varchar(20) NOT NULL,
  `nom_systeme_type_plante` varchar(200) NOT NULL,
  `prefix_type_plante` varchar(3) NOT NULL,
  `categorie_type_plante` enum('Arbre','Buisson','Fleur') NOT NULL,
  `id_fk_environnement_type_plante` int(11) NOT NULL,
  `id_fk_partieplante1_type_plante` int(11) NOT NULL,
  `id_fk_partieplante2_type_plante` int(11) DEFAULT NULL,
  `id_fk_partieplante3_type_plante` int(11) DEFAULT NULL,
  `id_fk_partieplante4_type_plante` int(11) DEFAULT NULL,
  `nb_creation_type_plante` int(11) NOT NULL,
  PRIMARY KEY (`id_type_plante`),
  KEY `id_fk_environnement_type_plante` (`id_fk_environnement_type_plante`),
  KEY `id_fk_partieplante1_type_plante` (`id_fk_partieplante1_type_plante`),
  KEY `id_fk_partieplante2_type_plante` (`id_fk_partieplante2_type_plante`),
  KEY `id_fk_partieplante3_type_plante` (`id_fk_partieplante3_type_plante`),
  KEY `id_fk_partieplante4_type_plante` (`id_fk_partieplante4_type_plante`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_potion`
--

CREATE TABLE IF NOT EXISTS `type_potion` (
  `id_type_potion` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_potion` varchar(20) NOT NULL,
  `caract_type_potion` enum('FOR','AGI','VIG','SAG','PV','VUE','ARM','POIDS','ATT','DEG','DEF') DEFAULT NULL,
  `de_type_potion` tinyint(4) NOT NULL DEFAULT '3',
  `bm_type_potion` enum('bonus','malus') DEFAULT NULL,
  `type_potion` enum('potion','vernis_reparateur','vernis_enchanteur') NOT NULL,
  `id_fk_type_ingredient_type_potion` int(11) DEFAULT NULL COMMENT 'type base à rénover',
  `template_m_type_potion` varchar(20) DEFAULT NULL,
  `template_f_type_potion` varchar(20) DEFAULT NULL,
  `caract2_type_potion` enum('FOR','AGI','VIG','SAG','PV','VUE','ARM','POIDS','ATT','DEG','DEF') DEFAULT NULL,
  `bm2_type_potion` enum('bonus','malus') DEFAULT NULL,
  PRIMARY KEY (`id_type_potion`),
  UNIQUE KEY `nom_type_potion` (`nom_type_potion`),
  KEY `id_fk_type_ingredient_type_potion` (`id_fk_type_ingredient_type_potion`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_qualite`
--

CREATE TABLE IF NOT EXISTS `type_qualite` (
  `id_type_qualite` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_qualite` varchar(10) NOT NULL,
  `nom_type_qualite` varchar(10) NOT NULL,
  `nom_aliment_type_qualite` varchar(10) NOT NULL,
  PRIMARY KEY (`id_type_qualite`),
  KEY `nom_systeme_type_qualite` (`nom_systeme_type_qualite`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_rang_communaute`
--

CREATE TABLE IF NOT EXISTS `type_rang_communaute` (
  `id_type_rang_communaute` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_rang_communaute` varchar(10) NOT NULL,
  `description_type_rang_communaute` varchar(500) NOT NULL,
  PRIMARY KEY (`id_type_rang_communaute`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_rune`
--

CREATE TABLE IF NOT EXISTS `type_rune` (
  `id_type_rune` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_rune` varchar(2) NOT NULL,
  `effet_type_rune` varchar(200) NOT NULL,
  `sagesse_type_rune` int(11) NOT NULL,
  `type_type_rune` enum('caracteristique','metier') NOT NULL,
  `niveau_type_rune` enum('a','b','c','d') NOT NULL,
  `image_type_rune` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id_type_rune`),
  UNIQUE KEY `nom_type_rune` (`nom_type_rune`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_tabac`
--

CREATE TABLE IF NOT EXISTS `type_tabac` (
  `id_type_tabac` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type_tabac` varchar(20) NOT NULL,
  `nom_court_type_tabac` varchar(15) CHARACTER SET utf8 NOT NULL,
  `nom_systeme_type_tabac` varchar(10) NOT NULL,
  `description_type_tabac` varchar(200) NOT NULL,
  PRIMARY KEY (`id_type_tabac`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_titre`
--

CREATE TABLE IF NOT EXISTS `type_titre` (
  `id_type_titre` int(11) NOT NULL AUTO_INCREMENT,
  `nom_masculin_type_titre` varchar(15) NOT NULL,
  `nom_feminin_type_titre` varchar(15) NOT NULL,
  `nom_systeme_type_titre` varchar(8) NOT NULL,
  `description_type_titre` varchar(10) NOT NULL,
  PRIMARY KEY (`id_type_titre`),
  UNIQUE KEY `nom_systeme_type_titre` (`nom_systeme_type_titre`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Structure de la table `type_unite`
--

CREATE TABLE IF NOT EXISTS `type_unite` (
  `id_type_unite` int(11) NOT NULL AUTO_INCREMENT,
  `nom_systeme_type_unite` varchar(10) NOT NULL,
  `nom_type_unite` varchar(10) NOT NULL,
  `nom_pluriel_type_unite` varchar(10) NOT NULL,
  PRIMARY KEY (`id_type_unite`),
  UNIQUE KEY `nom_systeme_type_unite` (`nom_systeme_type_unite`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Structure de la table `ville`
--

CREATE TABLE IF NOT EXISTS `ville` (
  `id_ville` int(11) NOT NULL AUTO_INCREMENT,
  `nom_ville` varchar(20) NOT NULL,
  `description_ville` varchar(200) NOT NULL,
  `nom_systeme_ville` varchar(20) NOT NULL,
  `id_fk_region_ville` int(11) NOT NULL,
  `est_capitale_ville` enum('oui','non') NOT NULL,
  `x_min_ville` int(11) NOT NULL,
  `y_min_ville` int(11) NOT NULL,
  `x_max_ville` int(11) NOT NULL,
  `y_max_ville` int(11) NOT NULL,
  `est_reliee_ville` enum('oui','non') NOT NULL,
  PRIMARY KEY (`id_ville`),
  KEY `id_fk_region_ville` (`id_fk_region_ville`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Structure de la table `zone`
--

CREATE TABLE IF NOT EXISTS `zone` (
  `id_zone` int(11) NOT NULL AUTO_INCREMENT,
  `id_fk_environnement_zone` int(11) NOT NULL,
  `nom_zone` varchar(100) NOT NULL,
  `description_zone` varchar(100) NOT NULL,
  `image_zone` varchar(100) NOT NULL,
  `x_min_zone` int(11) NOT NULL,
  `x_max_zone` int(11) NOT NULL,
  `y_min_zone` int(11) NOT NULL,
  `y_max_zone` int(11) NOT NULL,
  `z_zone` int(11) NOT NULL DEFAULT '0',
  `est_soule_zone` enum('oui','non') NOT NULL DEFAULT 'non',
  `id_fk_donjon_zone` int(11) DEFAULT NULL,
  `est_mine_zone` enum('oui','non') NOT NULL DEFAULT 'non',
  PRIMARY KEY (`id_zone`),
  KEY `id_fk_environnement_zone` (`id_fk_environnement_zone`),
  KEY `z_zone` (`z_zone`),
  KEY `id_fk_donjon_zone` (`id_fk_donjon_zone`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7607 ;

-- --------------------------------------------------------

--
-- Structure de la table `zone_nid`
--

CREATE TABLE IF NOT EXISTS `zone_nid` (
  `id_zone_nid` int(11) NOT NULL AUTO_INCREMENT,
  `x_min_zone_nid` int(11) NOT NULL,
  `x_max_zone_nid` int(11) NOT NULL,
  `y_min_zone_nid` int(11) NOT NULL,
  `y_max_zone_nid` int(11) NOT NULL,
  `z_zone_nid` int(11) NOT NULL,
  `est_ville_zone_nid` enum('oui','non') NOT NULL DEFAULT 'non',
  `id_fk_donjon_zone_nid` int(11) DEFAULT NULL,
  `couverture_zone_nid` float DEFAULT NULL,
  `est_mine_zone_nid` enum('oui','non') NOT NULL DEFAULT 'non',
  PRIMARY KEY (`id_zone_nid`),
  KEY `id_fk_donjon_zone_nid` (`id_fk_donjon_zone_nid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=155 ;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `aliment`
--
ALTER TABLE `aliment`
  ADD CONSTRAINT `aliment_ibfk_1` FOREIGN KEY (`id_fk_type_aliment`) REFERENCES `type_aliment` (`id_type_aliment`),
  ADD CONSTRAINT `aliment_ibfk_2` FOREIGN KEY (`id_fk_type_qualite_aliment`) REFERENCES `type_qualite` (`id_type_qualite`),
  ADD CONSTRAINT `aliment_ibfk_3` FOREIGN KEY (`id_fk_effet_braldun_aliment`) REFERENCES `effet_braldun` (`id_effet_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `blabla`
--
ALTER TABLE `blabla`
  ADD CONSTRAINT `blabla_ibfk_1` FOREIGN KEY (`id_fk_braldun_blabla`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `bosquet`
--
ALTER TABLE `bosquet`
  ADD CONSTRAINT `bosquet_ibfk_1` FOREIGN KEY (`id_fk_type_bosquet_bosquet`) REFERENCES `type_bosquet` (`id_type_bosquet`);

--
-- Contraintes pour la table `braldun`
--
ALTER TABLE `braldun`
  ADD CONSTRAINT `braldun_ibfk_1` FOREIGN KEY (`id_fk_rang_communaute_braldun`) REFERENCES `rang_communaute` (`id_rang_communaute`) ON DELETE SET NULL,
  ADD CONSTRAINT `braldun_ibfk_2` FOREIGN KEY (`id_fk_region_creation_braldun`) REFERENCES `region` (`id_region`);

--
-- Contraintes pour la table `bralduns_cdm`
--
ALTER TABLE `bralduns_cdm`
  ADD CONSTRAINT `bralduns_cdm_ibfk_1` FOREIGN KEY (`id_fk_braldun_hcdm`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_cdm_ibfk_2` FOREIGN KEY (`id_fk_type_monstre_hcdm`) REFERENCES `type_monstre` (`id_type_monstre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `bralduns_competences`
--
ALTER TABLE `bralduns_competences`
  ADD CONSTRAINT `bralduns_competences_ibfk_1` FOREIGN KEY (`id_fk_braldun_hcomp`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_competences_ibfk_2` FOREIGN KEY (`id_fk_competence_hcomp`) REFERENCES `competence` (`id_competence`);

--
-- Contraintes pour la table `bralduns_competences_favorites`
--
ALTER TABLE `bralduns_competences_favorites`
  ADD CONSTRAINT `bralduns_competences_favorites_ibfk_1` FOREIGN KEY (`id_fk_competence_hcompf`) REFERENCES `competence` (`id_competence`),
  ADD CONSTRAINT `bralduns_competences_favorites_ibfk_2` FOREIGN KEY (`id_fk_braldun_hcompf`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `bralduns_distinction`
--
ALTER TABLE `bralduns_distinction`
  ADD CONSTRAINT `bralduns_distinction_ibfk_1` FOREIGN KEY (`id_fk_braldun_hdistinction`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_distinction_ibfk_2` FOREIGN KEY (`id_fk_type_distinction_hdistinction`) REFERENCES `type_distinction` (`id_type_distinction`);

--
-- Contraintes pour la table `bralduns_equipement`
--
ALTER TABLE `bralduns_equipement`
  ADD CONSTRAINT `bralduns_equipement_ibfk_2` FOREIGN KEY (`id_fk_braldun_hequipement`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_equipement_ibfk_3` FOREIGN KEY (`id_equipement_hequipement`) REFERENCES `equipement` (`id_equipement`);

--
-- Contraintes pour la table `bralduns_metiers`
--
ALTER TABLE `bralduns_metiers`
  ADD CONSTRAINT `bralduns_metiers_ibfk_1` FOREIGN KEY (`id_fk_braldun_hmetier`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_metiers_ibfk_2` FOREIGN KEY (`id_fk_metier_hmetier`) REFERENCES `metier` (`id_metier`);

--
-- Contraintes pour la table `bralduns_roles`
--
ALTER TABLE `bralduns_roles`
  ADD CONSTRAINT `bralduns_roles_ibfk_1` FOREIGN KEY (`id_fk_braldun_hroles`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_roles_ibfk_2` FOREIGN KEY (`id_fk_role_hroles`) REFERENCES `role` (`id_role`) ON DELETE CASCADE;

--
-- Contraintes pour la table `bralduns_titres`
--
ALTER TABLE `bralduns_titres`
  ADD CONSTRAINT `bralduns_titres_ibfk_2` FOREIGN KEY (`id_fk_braldun_htitre`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `bralduns_titres_ibfk_3` FOREIGN KEY (`id_fk_type_htitre`) REFERENCES `type_titre` (`id_type_titre`);

--
-- Contraintes pour la table `buisson`
--
ALTER TABLE `buisson`
  ADD CONSTRAINT `buisson_ibfk_1` FOREIGN KEY (`id_fk_type_buisson_buisson`) REFERENCES `type_buisson` (`id_type_buisson`);

--
-- Contraintes pour la table `butin`
--
ALTER TABLE `butin`
  ADD CONSTRAINT `butin_ibfk_1` FOREIGN KEY (`id_fk_braldun_butin`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `butin_partage`
--
ALTER TABLE `butin_partage`
  ADD CONSTRAINT `butin_partage_ibfk_1` FOREIGN KEY (`id_fk_braldun_butin_partage`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `butin_partage_ibfk_2` FOREIGN KEY (`id_fk_autorise_butin_partage`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `carnet`
--
ALTER TABLE `carnet`
  ADD CONSTRAINT `carnet_ibfk_1` FOREIGN KEY (`id_fk_braldun_carnet`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `champ`
--
ALTER TABLE `champ`
  ADD CONSTRAINT `champ_ibfk_1` FOREIGN KEY (`id_fk_braldun_champ`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `champ_ibfk_2` FOREIGN KEY (`id_fk_type_graine_champ`) REFERENCES `type_graine` (`id_type_graine`);

--
-- Contraintes pour la table `champ_taupe`
--
ALTER TABLE `champ_taupe`
  ADD CONSTRAINT `champ_taupe_ibfk_1` FOREIGN KEY (`id_fk_champ_taupe`) REFERENCES `champ` (`id_champ`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `charrette_aliment_ibfk_1` FOREIGN KEY (`id_charrette_aliment`) REFERENCES `aliment` (`id_aliment`) ON DELETE CASCADE,
  ADD CONSTRAINT `charrette_aliment_ibfk_2` FOREIGN KEY (`id_fk_charrette_aliment`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

--
-- Contraintes pour la table `charrette_equipement`
--
ALTER TABLE `charrette_equipement`
  ADD CONSTRAINT `charrette_equipement_ibfk_16` FOREIGN KEY (`id_charrette_equipement`) REFERENCES `equipement` (`id_equipement`),
  ADD CONSTRAINT `charrette_equipement_ibfk_17` FOREIGN KEY (`id_fk_charrette_equipement`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

--
-- Contraintes pour la table `charrette_graine`
--
ALTER TABLE `charrette_graine`
  ADD CONSTRAINT `charrette_graine_ibfk_1` FOREIGN KEY (`id_fk_type_charrette_graine`) REFERENCES `type_graine` (`id_type_graine`),
  ADD CONSTRAINT `charrette_graine_ibfk_2` FOREIGN KEY (`id_fk_charrette_graine`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

--
-- Contraintes pour la table `charrette_ingredient`
--
ALTER TABLE `charrette_ingredient`
  ADD CONSTRAINT `charrette_ingredient_ibfk_1` FOREIGN KEY (`id_fk_type_charrette_ingredient`) REFERENCES `type_ingredient` (`id_type_ingredient`),
  ADD CONSTRAINT `charrette_ingredient_ibfk_2` FOREIGN KEY (`id_fk_charrette_ingredient`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE;

--
-- Contraintes pour la table `charrette_materiel`
--
ALTER TABLE `charrette_materiel`
  ADD CONSTRAINT `charrette_materiel_ibfk_4` FOREIGN KEY (`id_fk_charrette_materiel`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE,
  ADD CONSTRAINT `charrette_materiel_ibfk_5` FOREIGN KEY (`id_charrette_materiel`) REFERENCES `materiel` (`id_materiel`) ON DELETE CASCADE;

--
-- Contraintes pour la table `charrette_materiel_assemble`
--
ALTER TABLE `charrette_materiel_assemble`
  ADD CONSTRAINT `charrette_materiel_assemble_ibfk_6` FOREIGN KEY (`id_charrette_materiel_assemble`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE,
  ADD CONSTRAINT `charrette_materiel_assemble_ibfk_7` FOREIGN KEY (`id_materiel_materiel_assemble`) REFERENCES `materiel` (`id_materiel`) ON DELETE CASCADE;

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
-- Contraintes pour la table `charrette_partage`
--
ALTER TABLE `charrette_partage`
  ADD CONSTRAINT `charrette_partage_ibfk_1` FOREIGN KEY (`id_fk_charrette_partage`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE,
  ADD CONSTRAINT `charrette_partage_ibfk_2` FOREIGN KEY (`id_fk_braldun_charrette_partage`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `charrette_potion_ibfk_6` FOREIGN KEY (`id_fk_charrette_potion`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE,
  ADD CONSTRAINT `charrette_potion_ibfk_7` FOREIGN KEY (`id_charrette_potion`) REFERENCES `potion` (`id_potion`) ON DELETE CASCADE;

--
-- Contraintes pour la table `charrette_rune`
--
ALTER TABLE `charrette_rune`
  ADD CONSTRAINT `charrette_rune_ibfk_4` FOREIGN KEY (`id_fk_charrette_rune`) REFERENCES `charrette` (`id_charrette`) ON DELETE CASCADE,
  ADD CONSTRAINT `charrette_rune_ibfk_5` FOREIGN KEY (`id_rune_charrette_rune`) REFERENCES `rune` (`id_rune`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `coffre_ibfk_1` FOREIGN KEY (`id_fk_braldun_coffre`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `coffre_ibfk_2` FOREIGN KEY (`id_fk_communaute_coffre`) REFERENCES `communaute` (`id_communaute`) ON DELETE CASCADE;

--
-- Contraintes pour la table `coffre_aliment`
--
ALTER TABLE `coffre_aliment`
  ADD CONSTRAINT `coffre_aliment_ibfk_1` FOREIGN KEY (`id_coffre_aliment`) REFERENCES `aliment` (`id_aliment`) ON DELETE CASCADE,
  ADD CONSTRAINT `coffre_aliment_ibfk_2` FOREIGN KEY (`id_fk_coffre_coffre_aliment`) REFERENCES `coffre` (`id_coffre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `coffre_equipement`
--
ALTER TABLE `coffre_equipement`
  ADD CONSTRAINT `coffre_equipement_ibfk_1` FOREIGN KEY (`id_coffre_equipement`) REFERENCES `equipement` (`id_equipement`) ON DELETE CASCADE,
  ADD CONSTRAINT `coffre_equipement_ibfk_2` FOREIGN KEY (`id_fk_coffre_coffre_equipement`) REFERENCES `coffre` (`id_coffre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `coffre_graine`
--
ALTER TABLE `coffre_graine`
  ADD CONSTRAINT `coffre_graine_ibfk_1` FOREIGN KEY (`id_fk_type_coffre_graine`) REFERENCES `type_graine` (`id_type_graine`),
  ADD CONSTRAINT `coffre_graine_ibfk_2` FOREIGN KEY (`id_fk_coffre_coffre_graine`) REFERENCES `coffre` (`id_coffre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `coffre_ingredient`
--
ALTER TABLE `coffre_ingredient`
  ADD CONSTRAINT `coffre_ingredient_ibfk_1` FOREIGN KEY (`id_fk_type_coffre_ingredient`) REFERENCES `type_ingredient` (`id_type_ingredient`),
  ADD CONSTRAINT `coffre_ingredient_ibfk_2` FOREIGN KEY (`id_fk_coffre_coffre_ingredient`) REFERENCES `coffre` (`id_coffre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `coffre_materiel`
--
ALTER TABLE `coffre_materiel`
  ADD CONSTRAINT `coffre_materiel_ibfk_1` FOREIGN KEY (`id_coffre_materiel`) REFERENCES `materiel` (`id_materiel`) ON DELETE CASCADE,
  ADD CONSTRAINT `coffre_materiel_ibfk_2` FOREIGN KEY (`id_fk_coffre_coffre_materiel`) REFERENCES `coffre` (`id_coffre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `coffre_minerai`
--
ALTER TABLE `coffre_minerai`
  ADD CONSTRAINT `coffre_minerai_ibfk_1` FOREIGN KEY (`id_fk_type_coffre_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `coffre_minerai_ibfk_2` FOREIGN KEY (`id_fk_coffre_coffre_minerai`) REFERENCES `coffre` (`id_coffre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `coffre_munition`
--
ALTER TABLE `coffre_munition`
  ADD CONSTRAINT `coffre_munition_ibfk_1` FOREIGN KEY (`id_fk_type_coffre_munition`) REFERENCES `type_munition` (`id_type_munition`),
  ADD CONSTRAINT `coffre_munition_ibfk_2` FOREIGN KEY (`id_fk_coffre_coffre_munition`) REFERENCES `coffre` (`id_coffre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `coffre_partieplante`
--
ALTER TABLE `coffre_partieplante`
  ADD CONSTRAINT `coffre_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_coffre_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `coffre_partieplante_ibfk_2` FOREIGN KEY (`id_fk_type_plante_coffre_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `coffre_partieplante_ibfk_3` FOREIGN KEY (`id_fk_coffre_coffre_partieplante`) REFERENCES `coffre` (`id_coffre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `coffre_potion`
--
ALTER TABLE `coffre_potion`
  ADD CONSTRAINT `coffre_potion_ibfk_1` FOREIGN KEY (`id_coffre_potion`) REFERENCES `potion` (`id_potion`) ON DELETE CASCADE,
  ADD CONSTRAINT `coffre_potion_ibfk_2` FOREIGN KEY (`id_fk_coffre_coffre_potion`) REFERENCES `coffre` (`id_coffre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `coffre_rune`
--
ALTER TABLE `coffre_rune`
  ADD CONSTRAINT `coffre_rune_ibfk_1` FOREIGN KEY (`id_rune_coffre_rune`) REFERENCES `rune` (`id_rune`) ON DELETE CASCADE,
  ADD CONSTRAINT `coffre_rune_ibfk_2` FOREIGN KEY (`id_fk_coffre_coffre_rune`) REFERENCES `coffre` (`id_coffre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `coffre_tabac`
--
ALTER TABLE `coffre_tabac`
  ADD CONSTRAINT `coffre_tabac_ibfk_1` FOREIGN KEY (`id_fk_type_coffre_tabac`) REFERENCES `type_tabac` (`id_type_tabac`),
  ADD CONSTRAINT `coffre_tabac_ibfk_2` FOREIGN KEY (`id_fk_coffre_coffre_tabac`) REFERENCES `coffre` (`id_coffre`) ON DELETE CASCADE;

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
-- Contraintes pour la table `contrat`
--
ALTER TABLE `contrat`
  ADD CONSTRAINT `contrat_ibfk_1` FOREIGN KEY (`id_fk_braldun_contrat`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `contrat_ibfk_2` FOREIGN KEY (`id_fk_cible_braldun_contrat`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `creation_bosquets`
--
ALTER TABLE `creation_bosquets`
  ADD CONSTRAINT `creation_bosquets_ibfk_3` FOREIGN KEY (`id_fk_type_bosquet_creation_bosquets`) REFERENCES `type_bosquet` (`id_type_bosquet`),
  ADD CONSTRAINT `creation_bosquets_ibfk_4` FOREIGN KEY (`id_fk_environnement_creation_bosquets`) REFERENCES `environnement` (`id_environnement`);

--
-- Contraintes pour la table `creation_buissons`
--
ALTER TABLE `creation_buissons`
  ADD CONSTRAINT `creation_buissons_ibfk_1` FOREIGN KEY (`id_fk_type_buisson_creation_buissons`) REFERENCES `type_buisson` (`id_type_buisson`),
  ADD CONSTRAINT `creation_buissons_ibfk_2` FOREIGN KEY (`id_fk_environnement_creation_buissons`) REFERENCES `environnement` (`id_environnement`);

--
-- Contraintes pour la table `creation_minerais`
--
ALTER TABLE `creation_minerais`
  ADD CONSTRAINT `creation_minerais_ibfk_1` FOREIGN KEY (`id_fk_type_minerai_creation_minerais`) REFERENCES `type_minerai` (`id_type_minerai`) ON DELETE CASCADE,
  ADD CONSTRAINT `creation_minerais_ibfk_2` FOREIGN KEY (`id_fk_environnement_creation_minerais`) REFERENCES `environnement` (`id_environnement`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `creation_nid`
--
ALTER TABLE `creation_nid`
  ADD CONSTRAINT `creation_nid_ibfk_1` FOREIGN KEY (`id_fk_zone_creation_nid`) REFERENCES `zone_nid` (`id_zone_nid`),
  ADD CONSTRAINT `creation_nid_ibfk_2` FOREIGN KEY (`id_fk_type_monstre_creation_nid`) REFERENCES `type_monstre` (`id_type_monstre`);

--
-- Contraintes pour la table `creation_plantes`
--
ALTER TABLE `creation_plantes`
  ADD CONSTRAINT `creation_plantes_ibfk_1` FOREIGN KEY (`id_fk_type_plante_creation_plantes`) REFERENCES `type_plante` (`id_type_plante`) ON DELETE CASCADE,
  ADD CONSTRAINT `creation_plantes_ibfk_2` FOREIGN KEY (`id_fk_environnement_creation_plantes`) REFERENCES `environnement` (`id_environnement`) ON DELETE CASCADE;

--
-- Contraintes pour la table `crevasse`
--
ALTER TABLE `crevasse`
  ADD CONSTRAINT `crevasse_ibfk_1` FOREIGN KEY (`id_fk_donjon_crevasse`) REFERENCES `donjon` (`id_donjon`) ON DELETE CASCADE;

--
-- Contraintes pour la table `donjon`
--
ALTER TABLE `donjon`
  ADD CONSTRAINT `donjon_ibfk_1` FOREIGN KEY (`id_fk_lieu_donjon`) REFERENCES `lieu` (`id_lieu`),
  ADD CONSTRAINT `donjon_ibfk_2` FOREIGN KEY (`id_fk_region_donjon`) REFERENCES `region` (`id_region`),
  ADD CONSTRAINT `donjon_ibfk_3` FOREIGN KEY (`id_fk_pnj_donjon`) REFERENCES `braldun` (`id_braldun`),
  ADD CONSTRAINT `donjon_ibfk_4` FOREIGN KEY (`id_fk_distinction_donjon`) REFERENCES `type_distinction` (`id_type_distinction`);

--
-- Contraintes pour la table `donjon_braldun`
--
ALTER TABLE `donjon_braldun`
  ADD CONSTRAINT `donjon_braldun_ibfk_1` FOREIGN KEY (`id_fk_braldun_donjon_braldun`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `donjon_braldun_ibfk_2` FOREIGN KEY (`id_fk_equipe_donjon_braldun`) REFERENCES `donjon_equipe` (`id_donjon_equipe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `donjon_crevasse`
--
ALTER TABLE `donjon_crevasse`
  ADD CONSTRAINT `donjon_crevasse_ibfk_1` FOREIGN KEY (`id_fk_donjon_crevasse`) REFERENCES `donjon` (`id_donjon`) ON DELETE CASCADE;

--
-- Contraintes pour la table `donjon_equipe`
--
ALTER TABLE `donjon_equipe`
  ADD CONSTRAINT `donjon_equipe_ibfk_1` FOREIGN KEY (`id_fk_donjon_equipe`) REFERENCES `donjon` (`id_donjon`) ON DELETE CASCADE,
  ADD CONSTRAINT `donjon_equipe_ibfk_2` FOREIGN KEY (`id_fk_braldun_meneur_equipe`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `donjon_nid`
--
ALTER TABLE `donjon_nid`
  ADD CONSTRAINT `donjon_nid_ibfk_1` FOREIGN KEY (`id_fk_donjon_nid`) REFERENCES `donjon` (`id_donjon`) ON DELETE CASCADE,
  ADD CONSTRAINT `donjon_nid_ibfk_2` FOREIGN KEY (`id_fk_type_monstre_donjon_nid`) REFERENCES `type_monstre` (`id_type_monstre`),
  ADD CONSTRAINT `donjon_nid_ibfk_3` FOREIGN KEY (`id_fk_zone_nid_donjon_nid`) REFERENCES `zone_nid` (`id_zone_nid`);

--
-- Contraintes pour la table `donjon_palissade`
--
ALTER TABLE `donjon_palissade`
  ADD CONSTRAINT `donjon_palissade_ibfk_1` FOREIGN KEY (`id_fk_donjon_palissade`) REFERENCES `donjon` (`id_donjon`) ON DELETE CASCADE;

--
-- Contraintes pour la table `echoppe`
--
ALTER TABLE `echoppe`
  ADD CONSTRAINT `echoppe_ibfk_1` FOREIGN KEY (`id_fk_braldun_echoppe`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_ibfk_2` FOREIGN KEY (`id_fk_metier_echoppe`) REFERENCES `metier` (`id_metier`);

--
-- Contraintes pour la table `echoppe_aliment`
--
ALTER TABLE `echoppe_aliment`
  ADD CONSTRAINT `echoppe_aliment_ibfk_1` FOREIGN KEY (`id_echoppe_aliment`) REFERENCES `aliment` (`id_aliment`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_aliment_ibfk_2` FOREIGN KEY (`id_fk_echoppe_echoppe_aliment`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `echoppe_equipement`
--
ALTER TABLE `echoppe_equipement`
  ADD CONSTRAINT `echoppe_equipement_ibfk_1` FOREIGN KEY (`id_echoppe_equipement`) REFERENCES `equipement` (`id_equipement`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_equipement_ibfk_2` FOREIGN KEY (`id_fk_echoppe_echoppe_equipement`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `echoppe_graine`
--
ALTER TABLE `echoppe_graine`
  ADD CONSTRAINT `echoppe_graine_ibfk_1` FOREIGN KEY (`id_fk_type_echoppe_graine`) REFERENCES `type_graine` (`id_type_graine`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_graine_ibfk_2` FOREIGN KEY (`id_fk_echoppe_echoppe_graine`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `echoppe_ingredient`
--
ALTER TABLE `echoppe_ingredient`
  ADD CONSTRAINT `echoppe_ingredient_ibfk_1` FOREIGN KEY (`id_fk_type_echoppe_ingredient`) REFERENCES `type_ingredient` (`id_type_ingredient`),
  ADD CONSTRAINT `echoppe_ingredient_ibfk_2` FOREIGN KEY (`id_fk_echoppe_echoppe_ingredient`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `echoppe_materiel`
--
ALTER TABLE `echoppe_materiel`
  ADD CONSTRAINT `echoppe_materiel_ibfk_1` FOREIGN KEY (`id_echoppe_materiel`) REFERENCES `materiel` (`id_materiel`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_materiel_ibfk_2` FOREIGN KEY (`id_fk_echoppe_echoppe_materiel`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `echoppe_minerai`
--
ALTER TABLE `echoppe_minerai`
  ADD CONSTRAINT `echoppe_minerai_ibfk_1` FOREIGN KEY (`id_fk_type_echoppe_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `echoppe_minerai_ibfk_2` FOREIGN KEY (`id_fk_echoppe_echoppe_minerai`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `echoppe_munition`
--
ALTER TABLE `echoppe_munition`
  ADD CONSTRAINT `echoppe_munition_ibfk_1` FOREIGN KEY (`id_fk_type_echoppe_munition`) REFERENCES `type_munition` (`id_type_munition`),
  ADD CONSTRAINT `echoppe_munition_ibfk_2` FOREIGN KEY (`id_fk_echoppe_echoppe_munition`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `echoppe_partieplante`
--
ALTER TABLE `echoppe_partieplante`
  ADD CONSTRAINT `echoppe_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_echoppe_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `echoppe_partieplante_ibfk_2` FOREIGN KEY (`id_fk_type_plante_echoppe_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `echoppe_partieplante_ibfk_3` FOREIGN KEY (`id_fk_echoppe_echoppe_partieplante`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `echoppe_potion`
--
ALTER TABLE `echoppe_potion`
  ADD CONSTRAINT `echoppe_potion_ibfk_1` FOREIGN KEY (`id_echoppe_potion`) REFERENCES `potion` (`id_potion`) ON DELETE CASCADE,
  ADD CONSTRAINT `echoppe_potion_ibfk_2` FOREIGN KEY (`id_fk_echoppe_echoppe_potion`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `effet_braldun`
--
ALTER TABLE `effet_braldun`
  ADD CONSTRAINT `effet_braldun_ibfk_1` FOREIGN KEY (`id_fk_braldun_cible_effet_braldun`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `effet_monstre`
--
ALTER TABLE `effet_monstre`
  ADD CONSTRAINT `effet_monstre_ibfk_1` FOREIGN KEY (`id_fk_monstre_cible_effet_monstre`) REFERENCES `monstre` (`id_monstre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `effet_mot_f`
--
ALTER TABLE `effet_mot_f`
  ADD CONSTRAINT `effet_mot_f_ibfk_1` FOREIGN KEY (`id_fk_braldun_effet_mot_f`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `effet_mot_f_ibfk_2` FOREIGN KEY (`id_fk_type_monstre_effet_mot_f`) REFERENCES `type_monstre` (`id_type_monstre`);

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
  ADD CONSTRAINT `effet_potion_monstre_ibfk_1` FOREIGN KEY (`id_fk_braldun_lanceur_effet_potion_monstre`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `effet_potion_monstre_ibfk_2` FOREIGN KEY (`id_fk_monstre_cible_effet_potion_monstre`) REFERENCES `monstre` (`id_monstre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `element`
--
ALTER TABLE `element`
  ADD CONSTRAINT `element_ibfk_1` FOREIGN KEY (`id_fk_butin_element`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL;

--
-- Contraintes pour la table `element_aliment`
--
ALTER TABLE `element_aliment`
  ADD CONSTRAINT `element_aliment_ibfk_2` FOREIGN KEY (`id_element_aliment`) REFERENCES `aliment` (`id_aliment`) ON DELETE CASCADE,
  ADD CONSTRAINT `element_aliment_ibfk_3` FOREIGN KEY (`id_fk_butin_element_aliment`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL;

--
-- Contraintes pour la table `element_equipement`
--
ALTER TABLE `element_equipement`
  ADD CONSTRAINT `element_equipement_ibfk_7` FOREIGN KEY (`id_fk_butin_element_equipement`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL,
  ADD CONSTRAINT `element_equipement_ibfk_8` FOREIGN KEY (`id_element_equipement`) REFERENCES `equipement` (`id_equipement`) ON DELETE CASCADE;

--
-- Contraintes pour la table `element_graine`
--
ALTER TABLE `element_graine`
  ADD CONSTRAINT `element_graine_ibfk_1` FOREIGN KEY (`id_fk_butin_element_graine`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL,
  ADD CONSTRAINT `element_graine_ibfk_2` FOREIGN KEY (`id_fk_type_element_graine`) REFERENCES `type_graine` (`id_type_graine`);

--
-- Contraintes pour la table `element_ingredient`
--
ALTER TABLE `element_ingredient`
  ADD CONSTRAINT `element_ingredient_ibfk_1` FOREIGN KEY (`id_fk_butin_element_ingredient`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL,
  ADD CONSTRAINT `element_ingredient_ibfk_2` FOREIGN KEY (`id_fk_type_element_ingredient`) REFERENCES `type_ingredient` (`id_type_ingredient`);

--
-- Contraintes pour la table `element_materiel`
--
ALTER TABLE `element_materiel`
  ADD CONSTRAINT `element_materiel_ibfk_1` FOREIGN KEY (`id_fk_butin_element_materiel`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL;

--
-- Contraintes pour la table `element_minerai`
--
ALTER TABLE `element_minerai`
  ADD CONSTRAINT `element_minerai_ibfk_2` FOREIGN KEY (`id_fk_type_element_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `element_minerai_ibfk_3` FOREIGN KEY (`id_fk_butin_element_minerai`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL;

--
-- Contraintes pour la table `element_munition`
--
ALTER TABLE `element_munition`
  ADD CONSTRAINT `element_munition_ibfk_1` FOREIGN KEY (`id_fk_type_element_munition`) REFERENCES `type_munition` (`id_type_munition`) ON DELETE CASCADE,
  ADD CONSTRAINT `element_munition_ibfk_2` FOREIGN KEY (`id_fk_butin_element_munition`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL;

--
-- Contraintes pour la table `element_partieplante`
--
ALTER TABLE `element_partieplante`
  ADD CONSTRAINT `element_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_element_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `element_partieplante_ibfk_2` FOREIGN KEY (`id_fk_type_plante_element_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `element_partieplante_ibfk_3` FOREIGN KEY (`id_fk_butin_element_partieplante`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL;

--
-- Contraintes pour la table `element_potion`
--
ALTER TABLE `element_potion`
  ADD CONSTRAINT `element_potion_ibfk_1` FOREIGN KEY (`id_fk_butin_element_potion`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL;

--
-- Contraintes pour la table `element_rune`
--
ALTER TABLE `element_rune`
  ADD CONSTRAINT `element_rune_ibfk_1` FOREIGN KEY (`id_fk_butin_element_rune`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL;

--
-- Contraintes pour la table `element_tabac`
--
ALTER TABLE `element_tabac`
  ADD CONSTRAINT `element_tabac_ibfk_1` FOREIGN KEY (`id_fk_type_element_tabac`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `element_tabac_ibfk_2` FOREIGN KEY (`id_fk_butin_element_tabac`) REFERENCES `butin` (`id_butin`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `etape_ibfk_1` FOREIGN KEY (`id_fk_quete_etape`) REFERENCES `quete` (`id_quete`) ON DELETE CASCADE,
  ADD CONSTRAINT `etape_ibfk_2` FOREIGN KEY (`id_fk_type_etape`) REFERENCES `type_etape` (`id_type_etape`),
  ADD CONSTRAINT `etape_ibfk_3` FOREIGN KEY (`id_fk_braldun_etape`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD CONSTRAINT `evenement_ibfk_1` FOREIGN KEY (`id_fk_braldun_evenement`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `evenement_ibfk_2` FOREIGN KEY (`id_fk_type_evenement`) REFERENCES `type_evenement` (`id_type_evenement`),
  ADD CONSTRAINT `evenement_ibfk_3` FOREIGN KEY (`id_fk_soule_match_evenement`) REFERENCES `soule_match` (`id_soule_match`) ON DELETE CASCADE,
  ADD CONSTRAINT `evenement_ibfk_4` FOREIGN KEY (`id_fk_monstre_evenement`) REFERENCES `monstre` (`id_monstre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `evenement_communaute`
--
ALTER TABLE `evenement_communaute`
  ADD CONSTRAINT `evenement_communaute_ibfk_1` FOREIGN KEY (`id_fk_communaute_evenement_communaute`) REFERENCES `communaute` (`id_communaute`) ON DELETE CASCADE,
  ADD CONSTRAINT `evenement_communaute_ibfk_2` FOREIGN KEY (`id_fk_type_evenement_communaute`) REFERENCES `type_evenement_communaute` (`id_type_evenement_communaute`);

--
-- Contraintes pour la table `filature`
--
ALTER TABLE `filature`
  ADD CONSTRAINT `filature_ibfk_1` FOREIGN KEY (`id_fk_braldun_filature`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `filature_ibfk_2` FOREIGN KEY (`id_fk_cible_braldun_filature`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `filature_action`
--
ALTER TABLE `filature_action`
  ADD CONSTRAINT `filature_action_ibfk_1` FOREIGN KEY (`id_fk_braldun_filature_action`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `filature_action_ibfk_2` FOREIGN KEY (`id_fk_filature_action`) REFERENCES `filature` (`id_filature`) ON DELETE CASCADE;

--
-- Contraintes pour la table `filon`
--
ALTER TABLE `filon`
  ADD CONSTRAINT `filon_ibfk_1` FOREIGN KEY (`id_fk_type_minerai_filon`) REFERENCES `type_minerai` (`id_type_minerai`);

--
-- Contraintes pour la table `gardiennage`
--
ALTER TABLE `gardiennage`
  ADD CONSTRAINT `gardiennage_ibfk_1` FOREIGN KEY (`id_fk_gardien_gardiennage`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `gardiennage_ibfk_2` FOREIGN KEY (`id_fk_braldun_gardiennage`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `historique_equipement_ibfk_1` FOREIGN KEY (`id_fk_type_historique_equipement`) REFERENCES `type_equipement` (`id_type_equipement`) ON DELETE CASCADE;

--
-- Contraintes pour la table `historique_filature`
--
ALTER TABLE `historique_filature`
  ADD CONSTRAINT `historique_filature_ibfk_1` FOREIGN KEY (`id_fk_filature_historique_filature`) REFERENCES `filature` (`id_filature`) ON DELETE CASCADE;

--
-- Contraintes pour la table `historique_materiel`
--
ALTER TABLE `historique_materiel`
  ADD CONSTRAINT `historique_materiel_ibfk_2` FOREIGN KEY (`id_fk_type_historique_materiel`) REFERENCES `type_materiel` (`id_type_materiel`) ON DELETE CASCADE;

--
-- Contraintes pour la table `historique_potion`
--
ALTER TABLE `historique_potion`
  ADD CONSTRAINT `historique_potion_ibfk_2` FOREIGN KEY (`id_fk_type_historique_potion`) REFERENCES `type_potion` (`id_type_potion`) ON DELETE CASCADE;

--
-- Contraintes pour la table `historique_rune`
--
ALTER TABLE `historique_rune`
  ADD CONSTRAINT `historique_rune_ibfk_2` FOREIGN KEY (`id_fk_type_historique_rune`) REFERENCES `type_rune` (`id_type_rune`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban`
--
ALTER TABLE `laban`
  ADD CONSTRAINT `laban_ibfk_1` FOREIGN KEY (`id_fk_braldun_laban`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban_aliment`
--
ALTER TABLE `laban_aliment`
  ADD CONSTRAINT `laban_aliment_ibfk_1` FOREIGN KEY (`id_laban_aliment`) REFERENCES `aliment` (`id_aliment`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_aliment_ibfk_2` FOREIGN KEY (`id_fk_braldun_laban_aliment`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban_equipement`
--
ALTER TABLE `laban_equipement`
  ADD CONSTRAINT `laban_equipement_ibfk_1` FOREIGN KEY (`id_laban_equipement`) REFERENCES `equipement` (`id_equipement`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_equipement_ibfk_2` FOREIGN KEY (`id_fk_braldun_laban_equipement`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban_graine`
--
ALTER TABLE `laban_graine`
  ADD CONSTRAINT `laban_graine_ibfk_1` FOREIGN KEY (`id_fk_type_laban_graine`) REFERENCES `type_graine` (`id_type_graine`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_graine_ibfk_2` FOREIGN KEY (`id_fk_braldun_laban_graine`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban_ingredient`
--
ALTER TABLE `laban_ingredient`
  ADD CONSTRAINT `laban_ingredient_ibfk_1` FOREIGN KEY (`id_fk_type_laban_ingredient`) REFERENCES `type_ingredient` (`id_type_ingredient`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_ingredient_ibfk_2` FOREIGN KEY (`id_fk_braldun_laban_ingredient`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban_materiel`
--
ALTER TABLE `laban_materiel`
  ADD CONSTRAINT `laban_materiel_ibfk_1` FOREIGN KEY (`id_laban_materiel`) REFERENCES `materiel` (`id_materiel`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_materiel_ibfk_2` FOREIGN KEY (`id_fk_braldun_laban_materiel`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban_minerai`
--
ALTER TABLE `laban_minerai`
  ADD CONSTRAINT `laban_minerai_ibfk_1` FOREIGN KEY (`id_fk_type_laban_minerai`) REFERENCES `type_minerai` (`id_type_minerai`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_minerai_ibfk_2` FOREIGN KEY (`id_fk_braldun_laban_minerai`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban_munition`
--
ALTER TABLE `laban_munition`
  ADD CONSTRAINT `laban_munition_ibfk_1` FOREIGN KEY (`id_fk_type_laban_munition`) REFERENCES `type_munition` (`id_type_munition`),
  ADD CONSTRAINT `laban_munition_ibfk_2` FOREIGN KEY (`id_fk_braldun_laban_munition`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban_partieplante`
--
ALTER TABLE `laban_partieplante`
  ADD CONSTRAINT `laban_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_plante_laban_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `laban_partieplante_ibfk_2` FOREIGN KEY (`id_fk_braldun_laban_partieplante`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban_potion`
--
ALTER TABLE `laban_potion`
  ADD CONSTRAINT `laban_potion_ibfk_1` FOREIGN KEY (`id_laban_potion`) REFERENCES `potion` (`id_potion`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_potion_ibfk_2` FOREIGN KEY (`id_fk_braldun_laban_potion`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban_rune`
--
ALTER TABLE `laban_rune`
  ADD CONSTRAINT `laban_rune_ibfk_1` FOREIGN KEY (`id_rune_laban_rune`) REFERENCES `rune` (`id_rune`) ON DELETE CASCADE,
  ADD CONSTRAINT `laban_rune_ibfk_2` FOREIGN KEY (`id_fk_braldun_identification_laban_rune`) REFERENCES `braldun` (`id_braldun`) ON DELETE SET NULL,
  ADD CONSTRAINT `laban_rune_ibfk_3` FOREIGN KEY (`id_fk_braldun_laban_rune`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `laban_tabac`
--
ALTER TABLE `laban_tabac`
  ADD CONSTRAINT `laban_tabac_ibfk_1` FOREIGN KEY (`id_fk_type_laban_tabac`) REFERENCES `type_tabac` (`id_type_tabac`),
  ADD CONSTRAINT `laban_tabac_ibfk_2` FOREIGN KEY (`id_fk_braldun_laban_tabac`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lieu`
--
ALTER TABLE `lieu`
  ADD CONSTRAINT `lieu_ibfk_1` FOREIGN KEY (`id_fk_type_lieu`) REFERENCES `type_lieu` (`id_type_lieu`),
  ADD CONSTRAINT `lieu_ibfk_2` FOREIGN KEY (`id_fk_ville_lieu`) REFERENCES `ville` (`id_ville`),
  ADD CONSTRAINT `lieu_ibfk_3` FOREIGN KEY (`id_fk_communaute_lieu`) REFERENCES `communaute` (`id_communaute`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot`
--
ALTER TABLE `lot`
  ADD CONSTRAINT `lot_ibfk_1` FOREIGN KEY (`id_fk_type_lot`) REFERENCES `type_lot` (`id_type_lot`),
  ADD CONSTRAINT `lot_ibfk_2` FOREIGN KEY (`id_fk_vendeur_braldun_lot`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `lot_ibfk_3` FOREIGN KEY (`id_fk_communaute_lot`) REFERENCES `communaute` (`id_communaute`) ON DELETE CASCADE,
  ADD CONSTRAINT `lot_ibfk_4` FOREIGN KEY (`id_fk_echoppe_lot`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_aliment`
--
ALTER TABLE `lot_aliment`
  ADD CONSTRAINT `lot_aliment_ibfk_1` FOREIGN KEY (`id_fk_lot_lot_aliment`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE,
  ADD CONSTRAINT `lot_aliment_ibfk_2` FOREIGN KEY (`id_lot_aliment`) REFERENCES `aliment` (`id_aliment`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_equipement`
--
ALTER TABLE `lot_equipement`
  ADD CONSTRAINT `lot_equipement_ibfk_1` FOREIGN KEY (`id_fk_lot_lot_equipement`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE,
  ADD CONSTRAINT `lot_equipement_ibfk_2` FOREIGN KEY (`id_lot_equipement`) REFERENCES `equipement` (`id_equipement`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_graine`
--
ALTER TABLE `lot_graine`
  ADD CONSTRAINT `lot_graine_ibfk_1` FOREIGN KEY (`id_fk_type_lot_graine`) REFERENCES `type_graine` (`id_type_graine`),
  ADD CONSTRAINT `lot_graine_ibfk_2` FOREIGN KEY (`id_fk_lot_lot_graine`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_ingredient`
--
ALTER TABLE `lot_ingredient`
  ADD CONSTRAINT `lot_ingredient_ibfk_1` FOREIGN KEY (`id_fk_type_lot_ingredient`) REFERENCES `type_ingredient` (`id_type_ingredient`),
  ADD CONSTRAINT `lot_ingredient_ibfk_2` FOREIGN KEY (`id_fk_lot_lot_ingredient`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_materiel`
--
ALTER TABLE `lot_materiel`
  ADD CONSTRAINT `lot_materiel_ibfk_1` FOREIGN KEY (`id_lot_materiel`) REFERENCES `materiel` (`id_materiel`) ON DELETE CASCADE,
  ADD CONSTRAINT `lot_materiel_ibfk_2` FOREIGN KEY (`id_fk_lot_lot_materiel`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_minerai`
--
ALTER TABLE `lot_minerai`
  ADD CONSTRAINT `lot_minerai_ibfk_1` FOREIGN KEY (`id_fk_type_lot_minerai`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `lot_minerai_ibfk_2` FOREIGN KEY (`id_fk_lot_lot_minerai`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_munition`
--
ALTER TABLE `lot_munition`
  ADD CONSTRAINT `lot_munition_ibfk_1` FOREIGN KEY (`id_fk_type_lot_munition`) REFERENCES `type_munition` (`id_type_munition`),
  ADD CONSTRAINT `lot_munition_ibfk_2` FOREIGN KEY (`id_fk_lot_lot_munition`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_partieplante`
--
ALTER TABLE `lot_partieplante`
  ADD CONSTRAINT `lot_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_plante_lot_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `lot_partieplante_ibfk_2` FOREIGN KEY (`id_fk_lot_lot_partieplante`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE,
  ADD CONSTRAINT `lot_partieplante_ibfk_3` FOREIGN KEY (`id_fk_type_lot_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`);

--
-- Contraintes pour la table `lot_potion`
--
ALTER TABLE `lot_potion`
  ADD CONSTRAINT `lot_potion_ibfk_1` FOREIGN KEY (`id_lot_potion`) REFERENCES `potion` (`id_potion`) ON DELETE CASCADE,
  ADD CONSTRAINT `lot_potion_ibfk_2` FOREIGN KEY (`id_fk_lot_lot_potion`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_prix_graine`
--
ALTER TABLE `lot_prix_graine`
  ADD CONSTRAINT `lot_prix_graine_ibfk_1` FOREIGN KEY (`id_fk_type_lot_prix_graine`) REFERENCES `type_lot` (`id_type_lot`),
  ADD CONSTRAINT `lot_prix_graine_ibfk_2` FOREIGN KEY (`id_fk_lot_prix_graine`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_prix_ingredient`
--
ALTER TABLE `lot_prix_ingredient`
  ADD CONSTRAINT `lot_prix_ingredient_ibfk_1` FOREIGN KEY (`id_fk_type_lot_prix_ingredient`) REFERENCES `type_lot` (`id_type_lot`),
  ADD CONSTRAINT `lot_prix_ingredient_ibfk_2` FOREIGN KEY (`id_fk_lot_prix_ingredient`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_prix_minerai`
--
ALTER TABLE `lot_prix_minerai`
  ADD CONSTRAINT `lot_prix_minerai_ibfk_1` FOREIGN KEY (`id_fk_type_lot_prix_minerai`) REFERENCES `type_lot` (`id_type_lot`),
  ADD CONSTRAINT `lot_prix_minerai_ibfk_2` FOREIGN KEY (`id_fk_lot_prix_minerai`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_prix_partieplante`
--
ALTER TABLE `lot_prix_partieplante`
  ADD CONSTRAINT `lot_prix_partieplante_ibfk_1` FOREIGN KEY (`id_fk_type_lot_prix_partieplante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `lot_prix_partieplante_ibfk_2` FOREIGN KEY (`id_fk_type_plante_lot_prix_partieplante`) REFERENCES `type_plante` (`id_type_plante`),
  ADD CONSTRAINT `lot_prix_partieplante_ibfk_3` FOREIGN KEY (`id_fk_lot_prix_partieplante`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_rune`
--
ALTER TABLE `lot_rune`
  ADD CONSTRAINT `lot_rune_ibfk_1` FOREIGN KEY (`id_rune_lot_rune`) REFERENCES `rune` (`id_rune`) ON DELETE CASCADE,
  ADD CONSTRAINT `lot_rune_ibfk_2` FOREIGN KEY (`id_fk_lot_lot_rune`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `lot_tabac`
--
ALTER TABLE `lot_tabac`
  ADD CONSTRAINT `lot_tabac_ibfk_1` FOREIGN KEY (`id_fk_type_lot_tabac`) REFERENCES `type_tabac` (`id_type_tabac`),
  ADD CONSTRAINT `lot_tabac_ibfk_2` FOREIGN KEY (`id_fk_lot_lot_tabac`) REFERENCES `lot` (`id_lot`) ON DELETE CASCADE;

--
-- Contraintes pour la table `materiel`
--
ALTER TABLE `materiel`
  ADD CONSTRAINT `materiel_ibfk_1` FOREIGN KEY (`id_fk_type_materiel`) REFERENCES `type_materiel` (`id_type_materiel`);

--
-- Contraintes pour la table `messagerie_contacts`
--
ALTER TABLE `messagerie_contacts`
  ADD CONSTRAINT `messagerie_contacts_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `monstre`
--
ALTER TABLE `monstre`
  ADD CONSTRAINT `monstre_ibfk_1` FOREIGN KEY (`id_fk_type_monstre`) REFERENCES `type_monstre` (`id_type_monstre`),
  ADD CONSTRAINT `monstre_ibfk_2` FOREIGN KEY (`id_fk_taille_monstre`) REFERENCES `taille_monstre` (`id_taille_monstre`),
  ADD CONSTRAINT `monstre_ibfk_3` FOREIGN KEY (`id_fk_groupe_monstre`) REFERENCES `groupe_monstre` (`id_groupe_monstre`) ON DELETE CASCADE,
  ADD CONSTRAINT `monstre_ibfk_4` FOREIGN KEY (`id_fk_zone_nid_monstre`) REFERENCES `zone_nid` (`id_zone_nid`),
  ADD CONSTRAINT `monstre_ibfk_5` FOREIGN KEY (`id_fk_donjon_monstre`) REFERENCES `donjon` (`id_donjon`) ON DELETE CASCADE,
  ADD CONSTRAINT `monstre_ibfk_6` FOREIGN KEY (`id_fk_braldun_cible_monstre`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `nid`
--
ALTER TABLE `nid`
  ADD CONSTRAINT `nid_ibfk_1` FOREIGN KEY (`id_fk_zone_nid`) REFERENCES `zone_nid` (`id_zone_nid`),
  ADD CONSTRAINT `nid_ibfk_2` FOREIGN KEY (`id_fk_type_monstre_nid`) REFERENCES `type_monstre` (`id_type_monstre`),
  ADD CONSTRAINT `nid_ibfk_3` FOREIGN KEY (`id_fk_donjon_nid`) REFERENCES `donjon` (`id_donjon`) ON DELETE CASCADE;

--
-- Contraintes pour la table `palissade`
--
ALTER TABLE `palissade`
  ADD CONSTRAINT `palissade_ibfk_1` FOREIGN KEY (`id_fk_donjon_palissade`) REFERENCES `donjon` (`id_donjon`) ON DELETE CASCADE;

--
-- Contraintes pour la table `partage`
--
ALTER TABLE `partage`
  ADD CONSTRAINT `partage_ibfk_1` FOREIGN KEY (`id_fk_braldun_declarant_partage`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE,
  ADD CONSTRAINT `partage_ibfk_2` FOREIGN KEY (`id_fk_braldun_declare_partage`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `petit_equipement`
--
ALTER TABLE `petit_equipement`
  ADD CONSTRAINT `petit_equipement_ibfk_1` FOREIGN KEY (`id_fk_metier_petit_equipement`) REFERENCES `metier` (`id_metier`);

--
-- Contraintes pour la table `plante`
--
ALTER TABLE `plante`
  ADD CONSTRAINT `plante_ibfk_1` FOREIGN KEY (`id_fk_type_plante`) REFERENCES `type_plante` (`id_type_plante`);

--
-- Contraintes pour la table `potion`
--
ALTER TABLE `potion`
  ADD CONSTRAINT `potion_ibfk_1` FOREIGN KEY (`id_fk_type_potion`) REFERENCES `type_potion` (`id_type_potion`),
  ADD CONSTRAINT `potion_ibfk_2` FOREIGN KEY (`id_fk_type_qualite_potion`) REFERENCES `type_qualite` (`id_type_qualite`);

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
-- Contraintes pour la table `recette_aliments`
--
ALTER TABLE `recette_aliments`
  ADD CONSTRAINT `recette_aliments_ibfk_1` FOREIGN KEY (`id_fk_type_aliment_recette_aliments`) REFERENCES `type_aliment` (`id_type_aliment`),
  ADD CONSTRAINT `recette_aliments_ibfk_2` FOREIGN KEY (`id_fk_type_ingredient_recette_aliments`) REFERENCES `type_ingredient` (`id_type_ingredient`);

--
-- Contraintes pour la table `recette_aliments_potions`
--
ALTER TABLE `recette_aliments_potions`
  ADD CONSTRAINT `recette_aliments_potions_ibfk_1` FOREIGN KEY (`id_fk_type_aliment_recette_aliments_potions`) REFERENCES `type_aliment` (`id_type_aliment`),
  ADD CONSTRAINT `recette_aliments_potions_ibfk_2` FOREIGN KEY (`id_fk_type_potion_recette_aliments_potions`) REFERENCES `type_potion` (`id_type_potion`);

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
  ADD CONSTRAINT `route_ibfk_1` FOREIGN KEY (`id_fk_braldun_route`) REFERENCES `braldun` (`id_braldun`) ON DELETE SET NULL,
  ADD CONSTRAINT `route_ibfk_2` FOREIGN KEY (`id_fk_type_qualite_route`) REFERENCES `type_qualite` (`id_type_qualite`),
  ADD CONSTRAINT `route_ibfk_3` FOREIGN KEY (`id_fk_numero_route`) REFERENCES `route_numero` (`id_route_numero`),
  ADD CONSTRAINT `route_ibfk_4` FOREIGN KEY (`id_fk_echoppe_route`) REFERENCES `echoppe` (`id_echoppe`) ON DELETE CASCADE;

--
-- Contraintes pour la table `route_numero`
--
ALTER TABLE `route_numero`
  ADD CONSTRAINT `route_numero_ibfk_1` FOREIGN KEY (`id_fk_gare_capitale_route_numero`) REFERENCES `lieu` (`id_lieu`),
  ADD CONSTRAINT `route_numero_ibfk_2` FOREIGN KEY (`id_fk_gare_province_route_numero`) REFERENCES `lieu` (`id_lieu`);

--
-- Contraintes pour la table `rune`
--
ALTER TABLE `rune`
  ADD CONSTRAINT `rune_ibfk_1` FOREIGN KEY (`id_fk_type_rune`) REFERENCES `type_rune` (`id_type_rune`);

--
-- Contraintes pour la table `script`
--
ALTER TABLE `script`
  ADD CONSTRAINT `script_ibfk_1` FOREIGN KEY (`id_fk_braldun_script`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `session`
--
ALTER TABLE `session`
  ADD CONSTRAINT `session_ibfk_1` FOREIGN KEY (`id_fk_braldun_session`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sondage_reponse`
--
ALTER TABLE `sondage_reponse`
  ADD CONSTRAINT `sondage_reponse_ibfk_1` FOREIGN KEY (`id_fk_sondage_reponse`) REFERENCES `sondage` (`id_sondage`) ON DELETE CASCADE;

--
-- Contraintes pour la table `soule_equipe`
--
ALTER TABLE `soule_equipe`
  ADD CONSTRAINT `soule_equipe_ibfk_1` FOREIGN KEY (`id_fk_match_soule_equipe`) REFERENCES `soule_match` (`id_soule_match`) ON DELETE CASCADE,
  ADD CONSTRAINT `soule_equipe_ibfk_2` FOREIGN KEY (`id_fk_braldun_soule_equipe`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `soule_match`
--
ALTER TABLE `soule_match`
  ADD CONSTRAINT `soule_match_ibfk_1` FOREIGN KEY (`id_fk_terrain_soule_match`) REFERENCES `soule_terrain` (`id_soule_terrain`) ON DELETE CASCADE;

--
-- Contraintes pour la table `soule_message`
--
ALTER TABLE `soule_message`
  ADD CONSTRAINT `soule_message_ibfk_1` FOREIGN KEY (`id_fk_match_soule_message`) REFERENCES `soule_match` (`id_soule_match`) ON DELETE CASCADE,
  ADD CONSTRAINT `soule_message_ibfk_2` FOREIGN KEY (`id_fk_braldun_soule_message`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stats_distinction`
--
ALTER TABLE `stats_distinction`
  ADD CONSTRAINT `stats_distinction_ibfk_1` FOREIGN KEY (`id_fk_braldun_stats_distinction`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `stats_mots_runiques_ibfk_1` FOREIGN KEY (`id_fk_mot_runique_stats_mots_runiques`) REFERENCES `mot_runique` (`id_mot_runique`),
  ADD CONSTRAINT `stats_mots_runiques_ibfk_2` FOREIGN KEY (`id_fk_type_piece_stats_mots_runiques`) REFERENCES `type_piece` (`id_type_piece`);

--
-- Contraintes pour la table `stats_recolteurs`
--
ALTER TABLE `stats_recolteurs`
  ADD CONSTRAINT `stats_recolteurs_ibfk_1` FOREIGN KEY (`id_fk_braldun_stats_recolteurs`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stats_reputation`
--
ALTER TABLE `stats_reputation`
  ADD CONSTRAINT `stats_reputation_ibfk_1` FOREIGN KEY (`id_fk_braldun_stats_reputation`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stats_routes`
--
ALTER TABLE `stats_routes`
  ADD CONSTRAINT `stats_routes_ibfk_1` FOREIGN KEY (`id_fk_metier_stats_routes`) REFERENCES `metier` (`id_metier`),
  ADD CONSTRAINT `stats_routes_ibfk_2` FOREIGN KEY (`id_fk_braldun_stats_routes`) REFERENCES `braldun` (`id_braldun`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stats_runes`
--
ALTER TABLE `stats_runes`
  ADD CONSTRAINT `stats_runes_ibfk_1` FOREIGN KEY (`id_fk_type_rune_stats_runes`) REFERENCES `type_rune` (`id_type_rune`);

--
-- Contraintes pour la table `stock_tabac`
--
ALTER TABLE `stock_tabac`
  ADD CONSTRAINT `stock_tabac_ibfk_1` FOREIGN KEY (`id_fk_type_stock_tabac`) REFERENCES `type_tabac` (`id_type_tabac`),
  ADD CONSTRAINT `stock_tabac_ibfk_2` FOREIGN KEY (`id_fk_region_stock_tabac`) REFERENCES `region` (`id_region`);

--
-- Contraintes pour la table `type_dependance`
--
ALTER TABLE `type_dependance`
  ADD CONSTRAINT `type_dependance_ibfk_1` FOREIGN KEY (`id_fk_type_lieu_enfant_type_dependance`) REFERENCES `type_lieu` (`id_type_lieu`);

--
-- Contraintes pour la table `type_distinction`
--
ALTER TABLE `type_distinction`
  ADD CONSTRAINT `type_distinction_ibfk_1` FOREIGN KEY (`id_fk_type_categorie_distinction`) REFERENCES `type_categorie` (`id_type_categorie`),
  ADD CONSTRAINT `type_distinction_ibfk_2` FOREIGN KEY (`id_fk_lieu_type_distinction`) REFERENCES `lieu` (`id_lieu`);

--
-- Contraintes pour la table `type_equipement`
--
ALTER TABLE `type_equipement`
  ADD CONSTRAINT `type_equipement_ibfk_1` FOREIGN KEY (`id_fk_type_munition_type_equipement`) REFERENCES `type_munition` (`id_type_munition`),
  ADD CONSTRAINT `type_equipement_ibfk_2` FOREIGN KEY (`id_fk_type_piece_type_equipement`) REFERENCES `type_piece` (`id_type_piece`),
  ADD CONSTRAINT `type_equipement_ibfk_3` FOREIGN KEY (`id_fk_type_ingredient_base_type_equipement`) REFERENCES `type_ingredient` (`id_type_ingredient`),
  ADD CONSTRAINT `type_equipement_ibfk_4` FOREIGN KEY (`id_fk_donjon_type_equipement`) REFERENCES `donjon` (`id_donjon`);

--
-- Contraintes pour la table `type_etape_metier`
--
ALTER TABLE `type_etape_metier`
  ADD CONSTRAINT `type_etape_metier_ibfk_1` FOREIGN KEY (`id_fk_etape_type_etape_metier`) REFERENCES `type_etape` (`id_type_etape`),
  ADD CONSTRAINT `type_etape_metier_ibfk_2` FOREIGN KEY (`id_fk_metier_type_etape_metier`) REFERENCES `metier` (`id_metier`);

--
-- Contraintes pour la table `type_graine`
--
ALTER TABLE `type_graine`
  ADD CONSTRAINT `type_graine_ibfk_1` FOREIGN KEY (`id_fk_type_ingredient_type_graine`) REFERENCES `type_ingredient` (`id_type_ingredient`),
  ADD CONSTRAINT `type_graine_ibfk_2` FOREIGN KEY (`id_fk_type_tabac_type_graine`) REFERENCES `type_tabac` (`id_type_tabac`);

--
-- Contraintes pour la table `type_ingredient`
--
ALTER TABLE `type_ingredient`
  ADD CONSTRAINT `type_ingredient_ibfk_1` FOREIGN KEY (`id_fk_type_minerai_ingredient`) REFERENCES `type_minerai` (`id_type_minerai`),
  ADD CONSTRAINT `type_ingredient_ibfk_2` FOREIGN KEY (`id_fk_type_graine_ingredient`) REFERENCES `type_graine` (`id_type_graine`);

--
-- Contraintes pour la table `type_lieu`
--
ALTER TABLE `type_lieu`
  ADD CONSTRAINT `type_lieu_ibfk_1` FOREIGN KEY (`id_fk_type_lieu_communaute_type_lieu`) REFERENCES `type_lieu_communaute` (`id_type_lieu_communaute`);

--
-- Contraintes pour la table `type_materiel_assemble`
--
ALTER TABLE `type_materiel_assemble`
  ADD CONSTRAINT `type_materiel_assemble_ibfk_1` FOREIGN KEY (`id_base_type_materiel_assemble`) REFERENCES `type_materiel` (`id_type_materiel`),
  ADD CONSTRAINT `type_materiel_assemble_ibfk_2` FOREIGN KEY (`id_supplement_type_materiel_assemble`) REFERENCES `type_materiel` (`id_type_materiel`);

--
-- Contraintes pour la table `type_monstre`
--
ALTER TABLE `type_monstre`
  ADD CONSTRAINT `type_monstre_ibfk_1` FOREIGN KEY (`id_fk_type_groupe_monstre`) REFERENCES `type_groupe_monstre` (`id_type_groupe_monstre`);

--
-- Contraintes pour la table `type_monstre_mcompetence`
--
ALTER TABLE `type_monstre_mcompetence`
  ADD CONSTRAINT `type_monstre_mcompetence_ibfk_1` FOREIGN KEY (`id_fk_type_monstre_mcompetence`) REFERENCES `type_monstre` (`id_type_monstre`),
  ADD CONSTRAINT `type_monstre_mcompetence_ibfk_2` FOREIGN KEY (`id_fk_mcompetence_type_monstre_mcompetence`) REFERENCES `mcompetence` (`id_mcompetence`);

--
-- Contraintes pour la table `type_plante`
--
ALTER TABLE `type_plante`
  ADD CONSTRAINT `type_plante_ibfk_1` FOREIGN KEY (`id_fk_partieplante1_type_plante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `type_plante_ibfk_2` FOREIGN KEY (`id_fk_partieplante2_type_plante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `type_plante_ibfk_3` FOREIGN KEY (`id_fk_partieplante3_type_plante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `type_plante_ibfk_4` FOREIGN KEY (`id_fk_partieplante4_type_plante`) REFERENCES `type_partieplante` (`id_type_partieplante`),
  ADD CONSTRAINT `type_plante_ibfk_5` FOREIGN KEY (`id_fk_environnement_type_plante`) REFERENCES `environnement` (`id_environnement`);

--
-- Contraintes pour la table `type_potion`
--
ALTER TABLE `type_potion`
  ADD CONSTRAINT `type_potion_ibfk_1` FOREIGN KEY (`id_fk_type_ingredient_type_potion`) REFERENCES `type_ingredient` (`id_type_ingredient`);

--
-- Contraintes pour la table `ville`
--
ALTER TABLE `ville`
  ADD CONSTRAINT `ville_ibfk_1` FOREIGN KEY (`id_fk_region_ville`) REFERENCES `region` (`id_region`);

--
-- Contraintes pour la table `zone`
--
ALTER TABLE `zone`
  ADD CONSTRAINT `zone_ibfk_1` FOREIGN KEY (`id_fk_environnement_zone`) REFERENCES `environnement` (`id_environnement`),
  ADD CONSTRAINT `zone_ibfk_2` FOREIGN KEY (`id_fk_donjon_zone`) REFERENCES `donjon` (`id_donjon`);

--
-- Contraintes pour la table `zone_nid`
--
ALTER TABLE `zone_nid`
  ADD CONSTRAINT `zone_nid_ibfk_1` FOREIGN KEY (`id_fk_donjon_zone_nid`) REFERENCES `donjon` (`id_donjon`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
