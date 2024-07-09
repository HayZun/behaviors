<?php

class PluginXivoglpiConfig extends CommonDBTM {

   static private $_instance = NULL;
   static $rightname         = 'config';


   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }


   static function canView() {
      return Session::haveRight('config', READ);
   }


   static function getTypeName($nb=0) {
      return __('Setup');
   }


   function getName($with_comment=0) {
      return __('Xivoglpi', 'xivoglpi');
   }


   /**
    * Singleton for the unique config record
    */
   static function getInstance() {

      if (!isset(self::$_instance)) {
         self::$_instance = new self();
         if (!self::$_instance->getFromDB(1)) {
            self::$_instance->getEmpty();
         }
      }
      return self::$_instance;
   }


   static function install(Migration $mig) {
      global $DB;

      $table = 'glpi_plugin_xivoglpi_configs';
      $default_charset   = DBConnection::getDefaultCharset();
      $default_collation = DBConnection::getDefaultCollation();
      $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();
      if (!$DB->tableExists($table)) { //not installed

         $query = "CREATE TABLE `". $table."`(
                     `id` int $default_key_sign NOT NULL,
                     `use_requester_item_group` tinyint NOT NULL default '0',
                     `use_requester_user_group` tinyint NOT NULL default '0',
                     `is_ticketsolutiontype_mandatory` tinyint NOT NULL default '0',
                     `is_ticketsolution_mandatory` tinyint NOT NULL default '0',
                     `is_ticketcategory_mandatory` tinyint NOT NULL default '0',
                     `is_ticketcategory_mandatory_on_assign` tinyint NOT NULL default '0',
                     `is_tickettaskcategory_mandatory` tinyint NOT NULL default '0',
                     `is_tickettech_mandatory` tinyint NOT NULL default '0',
                     `is_tickettechgroup_mandatory` tinyint NOT NULL default '0',
                     `is_ticketrealtime_mandatory` tinyint NOT NULL default '0',
                     `is_ticketlocation_mandatory` tinyint NOT NULL default '0',
                     `is_requester_mandatory` tinyint NOT NULL default '0',
                     `is_ticketdate_locked` tinyint NOT NULL default '0',
                     `use_assign_user_group` tinyint NOT NULL default '0',
                     `use_assign_user_group_update` tinyint NOT NULL default '0',
                     `ticketsolved_updatetech` tinyint NOT NULL default '0',
                     `tickets_id_format` VARCHAR(15) NULL,
                     `changes_id_format` VARCHAR(15) NULL,
                     `is_problemsolutiontype_mandatory` tinyint NOT NULL default '0',
                     `remove_from_ocs` tinyint NOT NULL default '0',
                     `add_notif` tinyint NOT NULL default '0',
                     `use_lock` tinyint NOT NULL default '0',
                     `single_tech_mode` int $default_key_sign NOT NULL default '0',
                     `myasset` tinyint NOT NULL default '0',
                     `groupasset` tinyint NOT NULL default '0',
                     `clone` tinyint NOT NULL default '0',
                     `addfup_updatetech` tinyint NOT NULL default '0',
                     `is_tickettasktodo` tinyint NOT NULL default '0',
                     `is_problemtasktodo` tinyint NOT NULL default '0',
                     `is_changetasktodo` tinyint NOT NULL default '0',
                     `date_mod` timestamp NULL DEFAULT NULL,
                     `comment` text,
                     PRIMARY KEY  (`id`)
                   ) ENGINE=InnoDB  DEFAULT CHARSET = {$default_charset}
                     COLLATE = {$default_collation} ROW_FORMAT=DYNAMIC";
         $DB->queryOrDie($query, __('Error in creating glpi_plugin_xivoglpi_configs', 'xivoglpi').
                                 "<br>".$DB->error());

         $query = "INSERT INTO `$table`
                         (id, date_mod)
                   VALUES (1, NOW())";
         $DB->queryOrDie($query, __('Error during update glpi_plugin_xivoglpi_configs', 'xivoglpi').
                                 "<br>" . $DB->error());

      }
   }


   static function uninstall(Migration $mig) {
      $mig->dropTable('glpi_plugin_xivoglpi_configs');
   }


   static function showConfigForm($item) {

      $yesnoall = [0 => __('No'),
                   1 => __('First'),
                   2 => __('All')];

      $config = self::getInstance();

      $config->showFormHeader();

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2' class='center' width='60%'>".__('New ticket')."</th>";
      echo "<th colspan='2' class='center'>".__('Inventory', 'xivoglpi')."</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the associated item's group", "xivoglpi")."</td><td>";
      Dropdown::showYesNo("use_requester_item_group", $config->fields['use_requester_item_group']);
      echo "<td>".__("Show my assets", "xivoglpi")."</td><td>";
      Dropdown::showYesNo('myasset', $config->fields['myasset']);

      $config->showFormButtons(['formfooter' => true, 'candel'=>false]);

      return false;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Config') {
            return self::getName();
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Config') {
         self::showConfigForm($item);
      }
      return true;
   }
}
