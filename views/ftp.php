<?php

/**
 * Flexshare FTP edit view.
 *
 * @category   apps
 * @package    flexshare
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/flexshare/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//  
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->load->language('base');
$this->load->language('network');
$this->load->language('flexshare');

///////////////////////////////////////////////////////////////////////////////
// Form handler
///////////////////////////////////////////////////////////////////////////////

if ($form_type === 'edit') {
    $read_only = FALSE;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/flexshare/shares/summary/' . $share['Name']),
    );
} else { 
    $read_only = TRUE;
    $buttons = array(
        anchor_edit('/app/flexshare/ftp/edit/' . $share['Name']),
    );
}

///////////////////////////////////////////////////////////////////////////////
// Simple summary form
///////////////////////////////////////////////////////////////////////////////

if ($form_type === 'summary') {
    echo form_open('/flexshare/ftp/edit/' . $share['Name']);
    echo form_header(lang('flexshare_ftp'));

    echo field_toggle_enable_disable('ftp_enabled', $share['FtpEnabled'], lang('base_status'), $read_only);

    if ($share['FtpEnabled']) {
        echo field_dropdown('group_permission', $group_permission_options, $share['FtpGroupPermission'], lang('flexshare_permissions'), $read_only);
        echo field_view(lang('flexshare_server_url'), $server_url);
    }

    echo field_button_set($buttons);

    echo form_footer();
    echo form_close();

    return;
}

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open('/flexshare/ftp/edit/' . $share['Name']);
echo form_header(lang('flexshare_ftp'));

echo fieldset_header(lang('base_settings'));
echo field_toggle_enable_disable('enabled', $share['FtpEnabled'], lang('base_status'), $read_only);
echo field_dropdown('group_permission', $group_permission_options, $share['FtpGroupPermission'], lang('flexshare_permissions'), $read_only);
echo fieldset_footer();

echo fieldset_header(lang('flexshare_options'));
echo field_textarea('group_greeting', $share['FtpGroupGreeting'], lang('flexshare_greeting'), $read_only);
echo fieldset_footer();

echo fieldset_header(lang('flexshare_ports'));
echo field_input('ftps_port', '990', lang('flexshare_ftps_port'), TRUE);
echo field_input('ftpes_port', '21', lang('flexshare_ftp_and_ftpes_port'), TRUE);
echo field_toggle_enable_disable('ftp', TRUE, lang('flexshare_allow_unencrypted_ftp'), TRUE);
echo field_toggle_enable_disable('allow_passive', $share['FtpAllowPassive'], lang('flexshare_passive_mode'), TRUE);
echo field_input('passive_min_port', $share['FtpPassivePortMin'], lang('flexshare_passive_mode_from_port'), TRUE);
echo field_input('passive_max_port', $share['FtpPassivePortMax'], lang('flexshare_passive_mode_to_port'), TRUE);
echo fieldset_footer();

echo field_button_set($buttons);

echo form_footer();
echo form_close();
