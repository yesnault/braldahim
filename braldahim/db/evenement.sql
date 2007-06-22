CREATE TABLE `evenement` (
`id_evenement` INT NOT NULL ,
`id_hobbit_evenement` INT NOT NULL ,
`date_evenement` INT NOT NULL ,
`type_evenement` INT NOT NULL ,
`details_evenement` VARCHAR( 1000 ) NOT NULL ,
PRIMARY KEY ( `id_evenement` ) ,
INDEX ( `id_hobbit_evenement` )
) ENGINE = innodb;