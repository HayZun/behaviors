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

      } else {
         // Upgrade

         $mig->addField($table, 'tickets_id_format',        'string');
         $mig->addField($table, 'remove_from_ocs',          'bool');
         $mig->addField($table, 'is_requester_mandatory',   'bool');

         // version 0.78.0 - feature #2801 Forbid change of ticket's creation date
         $mig->addField($table, 'is_ticketdate_locked',     'bool');

         // Version 0.80.0 - set_use_date_on_state now handle in GLPI
         $mig->dropField($table, 'set_use_date_on_state');

         // Version 0.80.4 - feature #3171 additional notifications
         $mig->addField($table, 'add_notif',                'bool');

         // Version 0.83.0 - groups now have is_requester and is_assign attribute
         $mig->dropField($table, 'sql_user_group_filter');
         $mig->dropField($table, 'sql_tech_group_filter');

         // Version 0.83.1 - prevent update on ticket updated by another user
         $mig->addField($table, 'use_lock',                 'bool');

         // Version 0.83.4 - single tech/group #3857
         $mig->addField($table, 'single_tech_mode', "int {$default_key_sign} NOT NULL DEFAULT '0'");

         // Version 0.84.2 - solution description mandatory #2803
         $mig->addField($table, 'is_ticketsolution_mandatory', 'bool');
         //- ticket category mandatory #3738
         $mig->addField($table, 'is_ticketcategory_mandatory', 'bool');
         //- solution type mandatory for a problem  #5048
         $mig->addField($table, 'is_problemsolutiontype_mandatory', 'bool');

         // Version 0.90 - technician mandatory #5381
         $mig->addField($table, 'is_tickettech_mandatory', 'bool');

         // Version 1.3 - ticket location mandatory #5520
         $mig->addField($table, 'is_ticketlocation_mandatory', 'bool',
                        ['after' => 'is_ticketrealtime_mandatory']);

         // Version 1.5 - show my asset #5530
         $mig->addField($table, 'groupasset', 'bool', ['after' => 'single_tech_mode']);
         $mig->addField($table, 'myasset', 'bool', ['after' => 'single_tech_mode']);

         // Version 1.5.1 - config for clone #5531
         $mig->addField($table, 'clone', 'bool', ['after' => 'groupasset']);

         // Version 1.6.0 - delete newtech, newgroup dans newsupplier for notif. Now there are in the core
         $query = "UPDATE `glpi_notifications`
                   SET `event` = 'assign_user'
                   WHERE `event` = 'plugin_xivoglpi_ticketnewtech'";
         $DB->queryOrDie($query, "9.2 change notification assign user to core one");

         $query = "UPDATE `glpi_notifications`
                   SET `event` = 'assign_group'
                   WHERE `event` = 'plugin_xivoglpi_ticketnewgrp'";
         $DB->queryOrDie($query, "9.2 change notification assign group to core one");

         $query = "UPDATE `glpi_notifications`
                   SET `event` = 'assign_supplier'
                   WHERE `event` = 'plugin_xivoglpi_ticketnewsupp'";
         $DB->queryOrDie($query, "9.2 change notification assign supplier to core one");

         $query = "UPDATE `glpi_notifications`
                   SET `event` = 'observer_user'
                   WHERE `event` = 'plugin_xivoglpi_ticketnewwatch'";
         $DB->queryOrDie($query, "9.2 change notification add watcher to core one");

         $mig->addField($table, 'is_tickettasktodo', 'bool', ['after' => 'clone']);

         // version 2.1.0
         $mig->addField($table, 'is_tickettaskcategory_mandatory', 'bool',
                        ['after' => 'is_ticketcategory_mandatory']);
         $mig->addField($table, 'is_tickettechgroup_mandatory', 'bool',
                        ['after' => 'is_tickettech_mandatory']);

         // version 2.2.2
         $mig->addField($table, 'changes_id_format', 'VARCHAR(15) NULL',
                        ['after' => 'tickets_id_format']);

         // version 2.3.0
         $mig->addField($table, 'ticketsolved_updatetech', 'bool',
                        ['after' => 'use_assign_user_group']);
         $mig->addField($table, 'use_assign_user_group_update', 'bool',
                        ['after' => 'use_assign_user_group']);
         $mig->addField($table, 'is_ticketcategory_mandatory_on_assign', 'bool',
                        ['after' => 'is_ticketcategory_mandatory']);

         // version 2.6.0
         $mig->addField($table, 'is_changetasktodo', 'bool', ['after' => 'is_tickettasktodo']);
         $mig->addField($table, 'is_problemtasktodo', 'bool', ['after' => 'is_tickettasktodo']);
         $mig->addField($table, 'addfup_updatetech', 'bool', ['after' => 'clone']);

         //version 2.7.0
         $mig->changeField($table, 'date_mod', 'date_mod', "timestamp NULL DEFAULT NULL");
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
      echo "<td>".__("Ticket's number format", "xivoglpi")."</td><td width='20%'>";
      $tab = ['NULL' => Dropdown::EMPTY_VALUE];
      foreach (['Y000001', 'Ym0001', 'Ymd01', 'ymd0001'] as $fmt) {
         $tab[$fmt] = date($fmt) . '  (' . $fmt . ')';
      }
      Dropdown::showFromArray("tickets_id_format", $tab,
                              ['value' => $config->fields['tickets_id_format']]);
      echo "<td>".__('Delete computer in OCSNG when purged from GLPI', 'xivoglpi')."</td><td>";
      $plugin = new Plugin();
      if ($plugin->isActivated('uninstall') && $plugin->isActivated('ocsinventoryng')) {
         Dropdown::showYesNo('remove_from_ocs', $config->fields['remove_from_ocs']);
      } else {
         if (!$plugin->isActivated('uninstall')) {
           echo __("Plugin \"Item's uninstallation\" not installed", "xivoglpi")."\n";
         }
         if (!$plugin->isActivated('ocsinventoryng')) {
            echo __("Plugin \"OCS Inventory NG\" not installed", "xivoglpi");
         }
      }
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the associated item's group", "xivoglpi")."</td><td>";
      Dropdown::showYesNo("use_requester_item_group", $config->fields['use_requester_item_group']);
      echo "<td>".__("Show my assets", "xivoglpi")."</td><td>";
      Dropdown::showYesNo('myasset', $config->fields['myasset']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the requester's group", "xivoglpi")."</td><td>";
      Dropdown::showFromArray('use_requester_user_group', $yesnoall,
                              ['value' => $config->fields['use_requester_user_group']]);
      echo "<td>".__("Show assets of my groups", "xivoglpi")."</td><td>";
      Dropdown::showYesNo('groupasset', $config->fields['groupasset']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the technician's group", "xivoglpi")."</td><td>";
      Dropdown::showFromArray('use_assign_user_group', $yesnoall,
                              ['value' => $config->fields['use_assign_user_group']]);
      echo "</td><th colspan='2' class='center'>"._n('Notification', 'Notifications', 2,
            'xivoglpi');
      echo "</th></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Requester is mandatory", "xivoglpi")."</td><td>";
      Dropdown::showYesNo("is_requester_mandatory", $config->fields['is_requester_mandatory']);
      echo "<td>".__('Additional notifications', 'xivoglpi')."</td><td>";
      Dropdown::showYesNo('add_notif', $config->fields['add_notif']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2' class='center'>".__('Update of a ticket')."</th>";
      echo "</td><td class='tab_bg_2 b'>".__('Allow Clone', 'xivoglpi')."</td><td>";

      $tab = ['0' => __('No'),
              '1' => __("In the active entity", "xivoglpi"),
              '2' => __("In the item's entity", "xivoglpi")];
      Dropdown::showFromArray("clone", $tab, ['value' => $config->fields['clone']]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Duration is mandatory before ticket is solved/closed', 'xivoglpi')."</td><td>";
      Dropdown::showYesNo("is_ticketrealtime_mandatory",
                          $config->fields['is_ticketrealtime_mandatory']);
      echo "</td><th colspan=2' class='center'>".__('Update of a problem');
      echo "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Category is mandatory before ticket is solved/closed', 'xivoglpi')."</td><td>";
      Dropdown::showYesNo("is_ticketcategory_mandatory",
                          $config->fields['is_ticketcategory_mandatory']);
      echo "</td><td>".__('Type of solution is mandatory before problem is solved/closed', 'xivoglpi');
      echo "</td><td>";
      Dropdown::showYesNo("is_problemsolutiontype_mandatory",
                          $config->fields['is_problemsolutiontype_mandatory']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Type of solution is mandatory before ticket is solved/closed', 'xivoglpi');
      echo "</td><td>";
      Dropdown::showYesNo("is_ticketsolutiontype_mandatory",
                          $config->fields['is_ticketsolutiontype_mandatory']);
      echo "</td><td>".__('Block the solving/closing of a problem if task do to', 'xivoglpi');
      echo "</td><td>";
      Dropdown::showYesNo("is_problemtasktodo", $config->fields['is_problemtasktodo']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Category is mandatory when you assign a ticket', 'xivoglpi')."</td><td>";
      Dropdown::showYesNo("is_ticketcategory_mandatory_on_assign",
                          $config->fields['is_ticketcategory_mandatory_on_assign']);
      echo "</td><th colspan=2' class='center'>".__('New change');
      echo "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Description of solution is mandatory before ticket is solved/closed',
                          'xivoglpi');
      echo "</td><td>";
      Dropdown::showYesNo("is_ticketsolution_mandatory",
                          $config->fields['is_ticketsolution_mandatory']);
      echo "</td> <td>".__("Change's number format", "xivoglpi")."</td><td width='20%'>";
      $tab = ['NULL' => Dropdown::EMPTY_VALUE];
      foreach (['Y000001', 'Ym0001', 'Ymd01', 'ymd0001'] as $fmt) {
         $tab[$fmt] = date($fmt) . '  (' . $fmt . ')';
      }
      Dropdown::showFromArray("changes_id_format", $tab,
                              ['value' => $config->fields['changes_id_format']]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Technician assigned is mandatory before ticket is solved/closed', 'xivoglpi');
      echo "</td><td>";
      Dropdown::showYesNo("is_tickettech_mandatory",
                          $config->fields['is_tickettech_mandatory']);
      echo "</td><th colspan=2' class='center'>".__('Update of a change');
      echo "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group of technicians assigned is mandatory before ticket is solved/closed',
                     'xivoglpi');
      echo "</td><td>";
      Dropdown::showYesNo("is_tickettechgroup_mandatory",
                          $config->fields['is_tickettechgroup_mandatory']);
      echo "</td><td>".__('Block the solving/closing of a change if task do to', 'xivoglpi');
      echo "</td><td>";
      Dropdown::showYesNo("is_changetasktodo", $config->fields['is_changetasktodo']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Use the technician's group", "xivoglpi")."</td><td>";
      Dropdown::showFromArray('use_assign_user_group_update', $yesnoall,
                              ['value' => $config->fields['use_assign_user_group_update']]);
      echo "</td><th colspan='2' class='center'>".__('Comments');
      echo "</th></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Location is mandatory before ticket is solved/closed', 'xivoglpi');
      echo "</td><td>";
      Dropdown::showYesNo("is_ticketlocation_mandatory",
                           $config->fields['is_ticketlocation_mandatory']);
      echo "<td rowspan='7' colspan='2' class='center'>";
      Html::textarea(['name'            => 'comment',
                      'value'           => $config->fields['comment'],
                      'cols'            => '60',
                      'rows'            => '12',
                      'enable_ricktext' => false]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Task category is mandatory in a task', 'xivoglpi')."</td><td>";
      Dropdown::showYesNo("is_tickettaskcategory_mandatory",
      $config->fields['is_tickettaskcategory_mandatory']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("Deny change of ticket's creation date", "xivoglpi")."</td><td>";
      Dropdown::showYesNo("is_ticketdate_locked", $config->fields['is_ticketdate_locked']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Protect from simultaneous update', 'xivoglpi')."</td><td>";
      Dropdown::showYesNo("use_lock", $config->fields['use_lock']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Single technician and group', 'xivoglpi')."</td><td>";
      $tab = [0 => __('No'),
              1 => __('Single user and single group', 'xivoglpi'),
              2 => __('Single user or group', 'xivoglpi')];
      Dropdown::showFromArray('single_tech_mode', $tab,
                              ['value' => $config->fields['single_tech_mode']]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Block the solving/closing of a the ticket if task do to', 'xivoglpi');
      echo "</td><td>";
      Dropdown::showYesNo("is_tickettasktodo", $config->fields['is_tickettasktodo']);
      echo "</td><td colspan='2'></td></tr>";



      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2'></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>". __('Add the logged technician when solve ticket', 'xivoglpi');
      echo "</td><td>";
      Dropdown::showYesNo("ticketsolved_updatetech", $config->fields['ticketsolved_updatetech']);
      echo "</td><td colspan='2'></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>". __('Technician assignment when adding follow up', 'xivoglpi');
      echo "</td><td>";
      Dropdown::showYesNo("addfup_updatetech", $config->fields['addfup_updatetech']);
      echo "</td><td colspan='2'></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2'></th>";
      echo "<th colspan='2'>".sprintf(__('%1$s %2$s'), __('Last update'),
                                      Html::convDateTime($config->fields["date_mod"]));
      echo "</td></tr>";

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
