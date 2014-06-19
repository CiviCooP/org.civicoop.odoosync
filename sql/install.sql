CREATE TABLE IF NOT EXISTS `civicrm_odoo_entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity` varchar(255) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `odoo_id` int(11) DEFAULT NULL, 
  `change_date` datetime NOT NULL,
  `first_sync_date` datetime DEFAULT NULL,
  `last_sync_date` datetime DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;