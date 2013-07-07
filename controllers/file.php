<?php

/**
 * Flexshare File controller.
 *
 * @category   apps
 * @package    flexshare
 * @subpackage controllers
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \Exception as Exception;

use \clearos\apps\flexshare\Flexshare as Flexshare;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Flexshare File controller.
 *
 * @category   apps
 * @package    flexshare
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/flexshare/
 */

class File extends ClearOS_Controller
{
    /**
     * Flexshare file default controller.
     *
     * @param string $share share
     *
     * @return view
     */

    function index($share)
    {
        $this->_form($share, 'summary');
    }

    /**
     * Edit view.
     *
     * @param string $share share name
     *
     * @return view
     */

    function edit($share)
    {
        $this->_form($share, 'edit');
    }

    /**
     * View view.
     *
     * @param string $share share name
     *
     * @return view
     */

    function view($share)
    {
        $this->_form($share, 'view');
    }

    /**
     * Flexshare edit view.
     *
     * @param string $share     share
     * @param string $form_type form type
     *
     * @return view
     */

    function _form($share, $form_type)
    {
        // Load libraries
        //---------------

        $this->lang->load('flexshare');
        $this->load->library('samba_common/Samba');
        $this->load->library('flexshare/Flexshare');

        // Handle form submit
        //-------------------

        if ($this->input->post('submit')) {
            try {
                // Set comment to the one defined by Flexshare
                $info = $this->flexshare->get_share($share);
                $this->flexshare->set_file_comment($share, $info['ShareDescription']);
                $this->flexshare->set_file_browseable($share, TRUE);

                $this->flexshare->set_file_permission($share, $this->input->post('file_permission'));
                $this->flexshare->set_file_recycle_bin($share, $this->input->post('recycle_bin'));
                $this->flexshare->set_file_audit_log($share, $this->input->post('audit_log'));
                $this->flexshare->set_file_enabled($share, $this->input->post('enabled'));

                redirect('/flexshare/shares/summary/' . $share);
            } catch (Exception $e) {
                $this->page->set_message(clearos_exception_message($e));
            }
        }

        // Load view data
        //--------------- 

        try {
            $data['form_type'] = $form_type;
            $data['share'] = $this->flexshare->get_share($share);
            $data['server_url'] = "\\\\" . $this->samba->get_netbios_name() . "\\" . $share;
            $data['permission_options'] = $this->flexshare->get_file_permission_options();

            // Defaults
            if (empty($data['share']['FileEnabled']))
                $data['share']['FileEnabled'] = FALSE;

            if (empty($data['share']['FilePermission']))
                $data['share']['FilePermission'] = Flexshare::PERMISSION_READ_WRITE;

            if (empty($data['share']['FileRecycleBin']))
                $data['share']['FileRecycleBin'] = FALSE;

            if (empty($data['share']['FileAuditLog']))
                $data['share']['FileAuditLog'] = FALSE;

        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load the views
        //---------------

        $this->page->view_form('flexshare/file', $data, lang('flexshare_windows_file_share'));
    }
}
