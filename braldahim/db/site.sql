-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mar 09 Décembre 2008 à 22:46
-- Version du serveur: 5.0.41
-- Version de PHP: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Base de données: `braldahim_site`
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table `jos_uddeim`
-- 

CREATE TABLE `jos_uddeim` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fromid` int(11) NOT NULL default '0',
  `toid` int(11) NOT NULL default '0',
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
  KEY `totrash_totrashdate` (`totrash`,`totrashdate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `jos_uddeim_blocks`
-- 

CREATE TABLE `jos_uddeim_blocks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `blocker` int(11) NOT NULL default '0',
  `blocked` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `jos_uddeim_config`
-- 

CREATE TABLE `jos_uddeim_config` (
  `varname` tinytext NOT NULL,
  `value` tinytext NOT NULL,
  PRIMARY KEY  (`varname`(30))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `jos_uddeim_emn`
-- 

CREATE TABLE `jos_uddeim_emn` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  `popup` int(1) NOT NULL default '0',
  `public` int(1) NOT NULL default '0',
  `remindersent` int(11) NOT NULL default '0',
  `lastsent` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


-- 
-- Structure de la table `jos_users`
-- 

CREATE TABLE `jos_users` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `username` varchar(150) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `password` varchar(100) NOT NULL default '',
  `usertype` varchar(25) NOT NULL default '',
  `block` tinyint(4) NOT NULL default '0',
  `sendEmail` tinyint(4) default '0',
  `gid` tinyint(3) unsigned NOT NULL default '1',
  `registerDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastvisitDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `activation` varchar(100) NOT NULL default '',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `usertype` (`usertype`),
  KEY `idx_name` (`name`),
  KEY `gid_block` (`gid`,`block`),
  KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

