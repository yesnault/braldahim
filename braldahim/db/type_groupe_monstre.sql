CREATE TABLE `type_groupe_monstre` (
`id_type_groupe_monstre` INT NOT NULL ,
`nom_groupe_monstre` VARCHAR( 20 ) NOT NULL ,
PRIMARY KEY ( `id_type_groupe_monstre` ) ,
UNIQUE (
`nom_groupe_monstre`
)
) ENGINE = innodb;