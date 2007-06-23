CREATE TABLE `evenement` (
`id_evenement` INT NOT NULL AUTO_INCREMENT,
`id_hobbit_evenement` INT NOT NULL ,
`date_evenement` DATETIME NOT NULL ,
`type_evenement` INT NOT NULL ,
`details_evenement` VARCHAR( 1000 ) NOT NULL ,
PRIMARY KEY ( `id_evenement` ) ,
INDEX ( `id_hobbit_evenement` )
) ENGINE = innodb;