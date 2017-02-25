ALTER TABLE `baseline` 
CHANGE COLUMN `file_path` `file_path` VARCHAR( 255 ) ;

ALTER TABLE `history` 
CHANGE COLUMN `file_path` `file_path` VARCHAR( 255 ) ,
CHANGE COLUMN `hash_org` `hash_org` CHAR( 40 ) ,
CHANGE COLUMN `hash_new` `hash_new` CHAR( 40 ) ;

ALTER TABLE `scanned` 
ADD COLUMN `elapsed` varchar( 12 ) NOT NULL DEFAULT '0.0000' AFTER `changes` ,
ADD COLUMN `iterations` MEDIUMINT NOT NULL DEFAULT '0' AFTER `elapsed` ,
ADD COLUMN `count_current` MEDIUMINT NOT NULL DEFAULT '0' AFTER `iterations` ;