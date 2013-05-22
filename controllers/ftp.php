<?php

/**
 * Flexshare FTP controller.
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
 * Flexshare FTP controller.
 *
 * @category   apps
 * @package    flexshare
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/flexshare/
 */

class FTP extends ClearOS_Controller
{
    /**
     * Flexshare FTP default controller.
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
     * FTP edit view.
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
     * FTP view view.
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
     * Common form.
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
        $this->load->library('flexshare/Flexshare');

        // Validation
        //-----------

        $this->form_validation->set_policy('enabled', 'flexshare/Flexshare', 'validate_ftp_enabled', TRUE);
        $this->form_validation->set_policy('group_permission', 'flexshare/Flexshare', 'validate_ftp_group_permission', TRUE);
        $this->form_validation->set_policy('group_greeting', 'flexshare/Flexshare', 'validate_ftp_group_greeting', FALSE);

        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $this->flexshare->set_ftp_override_port($share, FALSE, 21);
                $this->flexshare->set_ftp_allow_passive($share, TRUE, '60000', '60999');
                $this->flexshare->set_ftp_group_permission($share, $this->input->post('group_permission'));
                $this->flexshare->set_ftp_group_greeting($share, $this->input->post('group_greeting'));

                // Set enabled after all parameters have been set
                $this->flexshare->set_ftp_enabled($share, $this->input->post('enabled'));

                $this->page->set_status_updated();
                redirect('/flexshare/summary/'. $share);
            } catch (Exception $e) {
                // TODO: using non-standard exception handling
                $this->page->set_message(clearos_exception_message($e));
            }
        }


        // Load view data
        //--------------- 

        try {
            $data['form_type'] = $form_type;
            $data['share'] = $this->flexshare->get_share($share);
            $data['group_permission_options'] = $this->flexshare->get_ftp_permission_options();
            $data['anonymous_permission_options'] = $this->flexshare->get_ftp_permission_options();

            $url = 'ftps://' . getenv('SERVER_ADDR') . '/' . $share;
            $data['server_url'] = "<a target='_blank' href='$url'>$url</a>";
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Defaults

        if ((int)$data['share']['FtpPassivePortMin'] == 0)
            $data['share']['FtpPassivePortMin'] = Flexshare::FTP_PASV_MIN;

        if ((int)$data['share']['FtpPassivePortMax'] == 0)
            $data['share']['FtpPassivePortMax'] = Flexshare::FTP_PASV_MAX;

        if (empty($data['share']['FtpGroupPermission']))
            $data['share']['FtpGroupPermission'] = Flexshare::PERMISSION_READ_WRITE_PLUS;

        if (empty($data['share']['FtpEnabled']))
            $data['share']['FtpEnabled'] = FALSE;

        // Load the views
        //---------------

        $this->page->view_form('flexshare/ftp', $data, lang('flexshare_ftp'));
    }
}
