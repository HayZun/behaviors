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
                     `date_mod` datetime NOT NULL,
                     `phones_inventory` tinyint(1) NOT NULL DEFAULT '0',
                     `lines_inventory` tinyint(1) NOT NULL DEFAULT '0',
                     `users_presence` tinyint(1) NOT NULL DEFAULT '0',
                     `auto_open` tinyint(1) NOT NULL DEFAULT '0',
                     `click2call` tinyint(1) NOT NULL DEFAULT '0',
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

      $config = self::getInstance();

      $config->showFormHeader();

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4' class='center' style='text-align:center;'>".__('Configuration Plugin Xivo', 'xivoglpi')."</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Phones inventory', 'xivoglpi')."</td><td>";
      Dropdown::showYesNo('phones_inventory', $config->fields['phones_inventory']);
      echo "</td><td>".__('Lines inventory', 'xivoglpi')."</td><td>";
      Dropdown::showYesNo('lines_inventory', $config->fields['lines_inventory']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Users presence', 'xivoglpi')."</td><td>";
      Dropdown::showYesNo('users_presence', $config->fields['users_presence']);
      echo "</td><td>".__('Auto-open tickets or users form', 'xivoglpi')."</td><td>";
      Dropdown::showYesNo('auto_open', $config->fields['auto_open']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Click2Call (requires xivo client to handle callto: links)', 'xivoglpi')."</td><td>";
      Dropdown::showYesNo('click2call', $config->fields['click2call']);
      echo "</td><td></td><td></td>";
      echo "</tr>";

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
