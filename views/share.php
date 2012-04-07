<?php

/**
 * Flexshare add/edit view.
 *
 * @category   Apps
 * @package    Flexshare
 * @subpackage Views
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
$this->load->language('flexshare');

///////////////////////////////////////////////////////////////////////////////
// Form modes
///////////////////////////////////////////////////////////////////////////////

if ($form_type === 'edit') {
    $read_only = FALSE;
    $share_name_read_only = TRUE;
    $form_path = '/flexshare/share/edit/'. $name;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/flexshare/summary/' . $name)
    );
} else if ($form_type === 'view') {
    $read_only = TRUE;
    $share_name_read_only = TRUE;
    $form_path = '/flexshare/share/edit/'. $name;
    $buttons = array(
        anchor_edit('/app/flexshare/share/edit/' . $name),
        anchor_custom('/app/flexshare', lang('base_return_to_summary'))
    );
} else {
    $read_only = FALSE;
    $share_name_read_only = FALSE;
    $form_path = '/flexshare/share/add';
    $buttons = array(
        form_submit_add('submit'),
        anchor_cancel('/app/flexshare')
    );
}

///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

echo form_open($form_path . '/' . $share);
echo form_header(lang('base_settings'));

echo field_input('name', $name, lang('flexshare_share_name'), $share_name_read_only);
echo field_toggle_enable_disable('enabled', $enabled, lang('base_status'), $read_only);
echo field_input('description', $description, lang('base_description'), $read_only);
echo field_dropdown('group', $group_options, $group, lang('flexshare_group'), $read_only);
if (! $use_default)
    echo field_dropdown('directory', $directories, $directory, lang('flexshare_directory'), $read_only);

echo field_button_set($buttons);

echo form_footer();
echo form_close();
