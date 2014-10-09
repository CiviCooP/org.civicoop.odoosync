ALTER TABLE `civicrm_odoo_entity` ADD `lock` INT (11) NOT NULL default '0';
ALTER TABLE `civicrm_odoo_entity` ADD INDEX `lock` (`lock`, `action`);