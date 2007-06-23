CREATE TABLE `type_evenement` (
`id_type_evemenent` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`nom_type_evenement` VARCHAR( 20 ) NOT NULL ,
UNIQUE (
`nom_type_evenement`
)
) ENGINE = MYISAM ;
