-- phpMyAdmin SQL Dump-- version 2.10.2-- http://www.phpmyadmin.net-- -- Serveur: localhost-- G�n�r� le : Ven 21 D�cembre 2007 � 22:45-- Version du serveur: 5.0.41-- Version de PHP: 5.2.3SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";-- -- Base de donn�es: `braldahim`-- -- ---------------------------------------------------------- -- Structure de la table `echoppe`-- CREATE TABLE `echoppe` (  `id_echoppe` int(11) NOT NULL auto_increment,  `id_fk_hobbit_echoppe` int(11) NOT NULL,  `x_echoppe` int(11) NOT NULL,  `y_echoppe` int(11) NOT NULL,  `date_creation_echoppe` datetime NOT NULL,  `id_fk_metier_echoppe` int(11) NOT NULL,  `quantite_peau_caisse_echoppe` int(11) NOT NULL default '0',  `quantite_castar_caisse_echoppe` int(11) NOT NULL default '0',  `quantite_planche_caisse_echoppe` int(11) NOT NULL default '0',  `quantite_peau_arriere_echoppe` int(11) NOT NULL default '0',  `quantite_rondin_arriere_echoppe` int(11) NOT NULL default '0',  `quantite_cuir_arriere_echoppe` int(11) NOT NULL default '0',  `quantite_fourrure_arriere_echoppe` int(11) NOT NULL default '0',  `quantite_planche_arriere_echoppe` int(11) NOT NULL default '0',  PRIMARY KEY  (`id_echoppe`),  KEY `xy_echoppe` (`x_echoppe`,`y_echoppe`),  KEY `id_hobbit_echoppe` (`id_fk_hobbit_echoppe`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;