<?php

/**
 * Flexshare summary view.
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

$this->lang->load('base');
$this->lang->load('flexshare');

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('flexshare_name'),
    lang('flexshare_group'),
    lang('flexshare_options'),
    lang('base_enabled')
);

///////////////////////////////////////////////////////////////////////////////
// Anchors 
///////////////////////////////////////////////////////////////////////////////

$anchors = array(anchor_add('/app/flexshare/share/add'));

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($flexshares as $share) {

    $state = ($share['ShareEnabled']) ? 'disable' : 'enable';
    $buttons = array(
        anchor_edit('/app/flexshare/shares/summary/' . $share['Name']),
        anchor_delete('/app/flexshare/share/delete/' . $share['Name'])
    );

    $item['title'] = $share['Name'];
    $item['action'] = '/app/flexshare/shares/summary/' . $share['Name'];
    $item['anchors'] = button_set($buttons);
    $images = '';

    if ($share['WebEnabled'])
        $images .= "<img src='" . clearos_app_htdocs('flexshare') . "/icon_web.png'>";

    if ($share['FtpEnabled'])
        $images .= "<img src='" . clearos_app_htdocs('flexshare') . "/icon_ftp.png'>";

    if ($share['FileEnabled'])
        $images .= "<img src='" . clearos_app_htdocs('flexshare') . "/icon_samba.png'>";

    $item['details'] = array(
        $share['Name'],
        $share['ShareGroup'],
        $images,
        ($share['ShareEnabled'] ? '<div style=\'margin-left: 20;\' class=\'theme-field-checkbox-enabled\'></div>' : '')
    );

    $items[] = $item;
}

sort($items);

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('flexshare_flexshares'),
    $anchors,
    $headers,
    $items
);
