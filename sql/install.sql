CREATE TABLE IF NOT EXISTS `civicrm_odoo_entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity` varchar(255) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `odoo_id` int(11) DEFAULT NULL, 
  `change_date` datetime NOT NULL,
  `sync_date` datetime DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `weight` INT(11) NOT NULL default '0',
  `last_error` TEXT NULL ,
  `last_error_date` DATETIME NULL
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `civicrm_odoo_sync_error_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity` varchar(255) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `odoo_id` int(11) DEFAULT NULL, 
  `date` datetime NOT NULL,
  `action` varchar(255) DEFAULT NULL,
  `error` text NULL ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;