<?php
/* vim:set softtabstop=4 shiftwidth=4 expandtab: */
/**
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPLv3)
 * Copyright 2001 - 2019 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

require_once '../lib/init.php';

if (!Access::check('interface', '100')) {
    UI::access_denied();

    return false;
}

UI::show_header();

// Switch on the actions
switch ($_REQUEST['action']) {
    case 'delete_record':
        if (!Core::form_verify('delete_access')) {
            UI::access_denied();

            return false;
        }
        Access::delete(filter_input(INPUT_GET, 'access_id', FILTER_SANITIZE_SPECIAL_CHARS));
        $url = AmpConfig::get('web_path') . '/admin/access.php';
        show_confirmation(T_('Deleted'), T_('Your Access List Entry has been removed'), $url);
    break;
    case 'show_delete_record':
        if (AmpConfig::get('demo_mode')) {
            break;
        }
        $access = new Access(Core::get_get('access_id'));
        show_confirmation(T_('Deletion Request'), T_('Are you sure you want to permanently delete') . ' ' . $access->name,
                'admin/access.php?action=delete_record&amp;access_id=' . $access->id, 1, 'delete_access');
    break;
    case 'add_host':

        // Make sure we've got a valid form submission
        if (!Core::form_verify('add_acl', 'post')) {
            UI::access_denied();

            return false;
        }

        Access::create($_POST);

        // Create Additional stuff based on the type
        if (Core::get_post('addtype') == 'stream' ||
            Core::get_post('addtype') == 'all'
        ) {
            $_POST['type'] = 'stream';
            Access::create($_POST);
        }
        if (Core::get_post('addtype') == 'all') {
            $_POST['type'] = 'interface';
            Access::create($_POST);
        }

        if (!AmpError::occurred()) {
            $url = AmpConfig::get('web_path') . '/admin/access.php';
            show_confirmation(T_('Added'), T_('Your new Access Control List(s) have been created'), $url);
        } else {
            $action = 'show_add_' . Core::get_post('type');
            require_once AmpConfig::get('prefix') . UI::find_template('show_add_access.inc.php');
        }
    break;
    case 'update_record':
        if (!Core::form_verify('edit_acl')) {
            UI::access_denied();

            return false;
        }
        $access = new Access(filter_input(INPUT_GET, 'access_id', FILTER_SANITIZE_SPECIAL_CHARS));
        $access->update($_POST);
        if (!AmpError::occurred()) {
            show_confirmation(T_('Updated'), T_('Access List Entry updated'), AmpConfig::get('web_path') . '/admin/access.php');
        } else {
            $access->format();
            require_once AmpConfig::get('prefix') . UI::find_template('show_edit_access.inc.php');
        }
    break;
    case 'show_add_current':
    case 'show_add_rpc':
    case 'show_add_local':
    case 'show_add_advanced':
        $action = Core::get_request('action');
        require_once AmpConfig::get('prefix') . UI::find_template('show_add_access.inc.php');
    break;
    case 'show_edit_record':
        $access = new Access(filter_input(INPUT_GET, 'access_id', FILTER_SANITIZE_SPECIAL_CHARS));
        $access->format();
        require_once AmpConfig::get('prefix') . UI::find_template('show_edit_access.inc.php');
    break;
    default:
        $list = array();
        $list = Access::get_access_lists();
        require_once AmpConfig::get('prefix') . UI::find_template('show_access_list.inc.php');
    break;
} // end switch on action
UI::show_footer();
