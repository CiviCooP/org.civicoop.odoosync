CREATE TABLE IF NOT EXISTS `civicrm_odoo_entity_sync` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity` varchar(255) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `change_date` datetime NOT NULL,
  `sync_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;