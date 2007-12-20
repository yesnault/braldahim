CREATE TABLE `laban_equipement` (
`id_laban_equipement` int( 11 ) NOT NULL AUTO_INCREMENT ,
`id_fk_recette_laban_equipement` int( 11 ) NOT NULL ,
`id_fk_hobbit_laban_equipement` int( 11 ) NOT NULL ,
`nb_runes_laban_equipement` int( 11 ) NOT NULL ,
`id_fk_type_rune_1_laban_equipement` int( 11 ) NULL default NULL ,
`id_fk_type_rune_2_laban_equipement` int( 11 ) NULL default NULL ,
`id_fk_type_rune_3_laban_equipement` int( 11 ) NULL default NULL ,
`id_fk_type_rune_4_laban_equipement` int( 11 ) NULL default NULL ,
`id_fk_type_rune_5_laban_equipement` int( 11 ) NULL default NULL ,
`id_fk_type_rune_6_laban_equipement` int( 11 ) NULL default NULL ,
PRIMARY KEY ( `id_laban_equipement` )
) ENGINE = InnoDB DEFAULT CHARSET = latin1;