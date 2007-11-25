CREATE TABLE `type_equipement` (
  `id_type_equipement` int(11) NOT NULL auto_increment,
  `nom_type_equipement` varchar(30) NOT NULL,
  `description_type_equipement` varchar(300) default NULL,
  PRIMARY KEY  (`id_type_equipement`),
  KEY `nom_type_equipement` (`nom_type_equipement`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;