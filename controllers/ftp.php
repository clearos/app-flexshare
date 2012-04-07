<?php

/**
 * Flexshare FTP controller.
 *
 * @category   Apps
 * @package    Flexshare
 * @subpackage Controllers
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
 * @category   Apps
 * @package    Flexshare
 * @subpackage Controllers
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
        if (empty($share))
            $share = $this->session->userdata('flexshare');

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
        $this->form_validation->set_policy('server_url', 'flexshare/Flexshare', 'validate_ftp_server_url', TRUE);
        $this->form_validation->set_policy('require_ssl', 'flexshare/Flexshare', 'validate_ftp_require_ssl', TRUE);
        $this->form_validation->set_policy('override_port', 'flexshare/Flexshare', 'validate_ftp_override_port_state', TRUE);
        $this->form_validation->set_policy('port', 'flexshare/Flexshare', 'validate_ftp_override_port');
        $this->form_validation->set_policy('allow_passive', 'flexshare/Flexshare', 'validate_ftp_allow_passive_state', TRUE);

        if ($this->input->post('allow_passive')) {
            $this->form_validation->set_policy('passive_min_port', 'flexshare/Flexshare', 'validate_ftp_passive_port', TRUE);
            $this->form_validation->set_policy('passive_max_port', 'flexshare/Flexshare', 'validate_ftp_passive_port', TRUE);
        }

        $this->form_validation->set_policy('group_permission', 'flexshare/Flexshare', 'validate_ftp_group_permission', TRUE);
        $this->form_validation->set_policy('group_greeting', 'flexshare/Flexshare', 'validate_ftp_group_greeting', FALSE);

        /* TODO: disable anonymous, see if anyone uses it
        $this->form_validation->set_policy('allow_anonymous', 'flexshare/Flexshare', 'validate_ftp_allow_anonymous', TRUE);
        $this->form_validation->set_policy('anonymous_greeting', 'flexshare/Flexshare', 'validate_ftp_anonymous_greeting', FALSE);

        if ($this->input->post('allow_anonymous'))
            $this->form_validation->set_policy('anonymous_permission', 'flexshare/Flexshare', 'validate_ftp_anonymous_permission', TRUE);
        */

        $form_ok = $this->form_validation->run();

        // Extra validation
        //-----------------

        if ($form_ok) {
            if ($this->input->post('allow_passive')) {
                if ($this->input->post('passive_min_port') >= $this->input->post('passive_max_port')) {
                    $this->form_validation->set_error('passive_max_port', lang('flexshare_port_range_invalid'));
                    $form_ok = FALSE;
                }
            }
        }

        // Handle form submit
        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $this->flexshare->set_ftp_server_url($share, $this->input->post('server_url'));
                $this->flexshare->set_ftp_require_ssl($share, $this->input->post('require_ssl'));
                $this->flexshare->set_ftp_override_port(
                    $share,
                    $this->input->post('override_port'),
                    (!$this->input->post('port') ? 2121 : $this->input->post('port'))
                );
                $this->flexshare->set_ftp_allow_passive(
                    $share,
                    $this->input->post('allow_passive'),
                    $this->input->post('passive_min_port'),
                    $this->input->post('passive_max_port')
                );

                $this->flexshare->set_ftp_group_permission($share, $this->input->post('group_permission'));
                $this->flexshare->set_ftp_group_greeting($share, $this->input->post('group_greeting'));

                /*  TODO: disable anonymous for now.
                $this->flexshare->set_ftp_allow_anonymous($share, $this->input->post('allow_anonymous'));
                $this->flexshare->set_ftp_anonymous_greeting($share, $this->input->post('anonymous_greeting'));

                if ($this->input->post('anonymous_permission'))
                    $this->flexshare->set_ftp_anonymous_permission($share, $this->input->post('anonymous_permission'));
                */

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
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Defaults
        if ((int)$data['share']['FtpPort'] == 0)
            $data['share']['FtpPort'] = Flexshare::DEFAULT_PORT_FTP;

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
