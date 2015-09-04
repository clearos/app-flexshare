<?php

/**
 * Flexshare Web controller.
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
 * Flexshare Web controller.
 *
 * @category   apps
 * @package    flexshare
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/flexshare/
 */

class Web extends ClearOS_Controller
{
    /**
     * Flexshare Web default controller.
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
     * @param string $share share
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
     * @param string $share share
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
        $this->load->library('web_server/Httpd');
        $this->load->library('flexshare/Flexshare');

        // Validation
        //-----------

        $this->form_validation->set_policy('web_access', 'flexshare/Flexshare', 'validate_web_access', TRUE);
        $this->form_validation->set_policy('show_index', 'flexshare/Flexshare', 'validate_web_show_index', TRUE);
        $this->form_validation->set_policy('follow_symlinks', 'flexshare/Flexshare', 'validate_web_follow_symlinks', TRUE);
        $this->form_validation->set_policy('ssi', 'flexshare/Flexshare', 'validate_web_allow_ssi', TRUE);
        $this->form_validation->set_policy('htaccess', 'flexshare/Flexshare', 'validate_web_htaccess_override', TRUE);
        $this->form_validation->set_policy('require_ssl', 'flexshare/Flexshare', 'validate_web_require_ssl', TRUE);
        $this->form_validation->set_policy('ssl_certificate', 'flexshare/Flexshare', 'validate_web_ssl_certificate', TRUE);
        $this->form_validation->set_policy('override_port', 'flexshare/Flexshare', 'validate_web_override_port_state', TRUE);
        $this->form_validation->set_policy('require_authentication', 'flexshare/Flexshare', 'validate_web_require_authentication', TRUE);
        $this->form_validation->set_policy('php', 'flexshare/Flexshare', 'validate_web_php', TRUE);
        $this->form_validation->set_policy('cgi', 'flexshare/Flexshare', 'validate_web_cgi', TRUE);
        $this->form_validation->set_policy('web_access', 'flexshare/Flexshare', 'validate_web_access', TRUE);

        if ($this->input->post('override_port'))
            $this->form_validation->set_policy('port', 'flexshare/Flexshare', 'validate_web_override_port', TRUE);

        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $this->flexshare->set_web_server_name($share, $this->input->post('server_name'));
                $this->flexshare->set_web_access($share, $this->input->post('web_access'));
                $this->flexshare->set_web_show_index($share, $this->input->post('show_index'));
                $this->flexshare->set_web_follow_symlinks($share, $this->input->post('follow_symlinks'));
                $this->flexshare->set_web_allow_ssi($share, $this->input->post('ssi'));
                $this->flexshare->set_web_htaccess_override($share, $this->input->post('htaccess'));
                $this->flexshare->set_web_require_ssl($share, $this->input->post('require_ssl'));
                $this->flexshare->set_web_ssl_certificate($share, $this->input->post('ssl_certificate'));
                $this->flexshare->set_web_override_port(
                    $share,
                    $this->input->post('override_port'),
                    (!$this->input->post('port') ? 80 : $this->input->post('port'))
                );
                $this->flexshare->set_web_require_authentication($share, $this->input->post('require_authentication'));
                $this->flexshare->set_web_php($share, $this->input->post('php'));
                $this->flexshare->set_web_cgi($share, $this->input->post('cgi'));

                // Set enabled after all parameters have been set
                $this->flexshare->set_web_enabled($share, $this->input->post('enabled'));

                redirect('/flexshare/shares/summary/'. $share);
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //--------------- 

        try {
            $data['form_type'] = $form_type;
            $data['share'] = $this->flexshare->get_share($share);
            $data['accessibility_options'] = $this->flexshare->get_web_access_options();
            $data['ssl_certificate_options'] = $this->flexshare->get_web_ssl_certificate_options();
            $data['server_name'] = $this->httpd->get_server_name();

            $protocol = ($data['share']['WebReqSsl']) ? 'https' : 'http';

            if ($data['share']['WebOverridePort'])
                $data['server_url'] = array( 
                    $protocol . "://" . $data['server_name'] . ":" . $data['share']['WebPort'] . "/flexshare/$share",
                    $protocol . "://$share." . $data['server_name'] . ":" . $data['share']['WebPort']
                ); 
            else
                $data['server_url'] = array(
                    $protocol . "://" . $data['server_name'] . "/flexshare/$share",
                    $protocol . "://$share." . $data['server_name']
                ); 

            // Default Port
            if ((int)$data['share']['WebPort'] == 0)
                $data['share']['WebPort'] = Flexshare::DEFAULT_PORT_WEB;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Defaults
        if (! isset($data['share']['WebEnabled']))
            $data['share']['WebEnabled'] = FALSE;

        if (! isset($data['share']['WebHtaccessOverride']))
            $data['share']['WebHtaccessOverride'] = TRUE;

        if (! isset($data['share']['WebReqSsl']))
            $data['share']['WebReqSsl'] = TRUE;

        if (! isset($data['share']['WebReqAuth']))
            $data['share']['WebReqAuth'] = TRUE;

        if (! isset($data['share']['WebShowIndex']))
            $data['share']['WebShowIndex'] = TRUE;

        if (! isset($data['share']['WebPhp']))
            $data['share']['WebPhp'] = TRUE;

        if (! isset($data['share']['WebCgi']))
            $data['share']['WebCgi'] = TRUE;

        // Load the views
        //---------------

        $this->page->view_form('flexshare/web', $data, lang('flexshare_web'));
    }
}
