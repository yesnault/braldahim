 CREATE TABLE `groupe_monstre` (
`id_groupe_monstre` INT NOT NULL ,
`id_fk_type_groupe_monstre` INT NOT NULL ,
`date_creation_groupe_monstre` DATETIME NOT NULL ,
`id_cible_groupe_monstre` INT NOT NULL ,
`nb_membres_max_groupe_monstre` INT NOT NULL ,
`nb_membres_restant_groupe_monstre` INT NOT NULL ,
`phase_tactique_groupe_monstre` INT NOT NULL ,
`id_role_a_groupe_monstre` INT NOT NULL ,
`id_role_b_groupe_monstre` INT NOT NULL ,
`_groupe_monstre` INT NOT NULL ,
PRIMARY KEY ( `id_groupe_monstre` )
) ENGINE = innodb;