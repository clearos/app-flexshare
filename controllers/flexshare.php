<?php

/**
 * Flexshare controller.
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

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Flexshare controller.
 *
 * @category   Apps
 * @package    Flexshare
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/flexshare/
 */

class Flexshare extends ClearOS_Controller
{
    /**
     * Flexshare server overview.
     *
     * @return view
     */

    function index()
    {
        // Show account status widget if we're not in a happy state
        //---------------------------------------------------------

        $this->load->module('accounts/status');

        if ($this->status->unhappy()) {
            $this->status->widget('flexshare');
            return;
        }

        // Load libraries
        //---------------

        $this->lang->load('flexshare');
        $this->load->library('flexshare/Flexshare');

        // Initialize
        //-----------

        try {
            $this->flexshare->initialize();
        } catch (Exception $e) {
            $this->page->set_message(clearos_exception_message($e));
        }

        // Load view data
        //---------------

        try {
            $data['flexshares'] = $this->flexshare->get_share_summary();
        } catch (Exception $e) {
            $this->page->set_message(clearos_exception_message($e));
        }
 
        // Load views
        //-----------

        $this->page->view_form('summary', $data, lang('flexshare_flexshare'));
    }

    /**
     * Flexshare summary view.
     *
     * @return view
     */

    function summary($share)
    {
        // Load libraries
        //---------------

        $this->lang->load('flexshare');

        // Load views
        //-----------


        // TODO: view_controllers does not support passing parameters.
        // It should!  In the meantime, we use a session variable as a dirty workaround

        $this->session->set_userdata('flexshare', $share);

        $views = array();

        $views[] = 'flexshare/share';

        // TODO: use API call instead of file_exists
        if (file_exists('/var/clearos/samba/initialized_local'))
            $views[] = 'flexshare/file';

        if (clearos_library_installed('ftp/ProFTPd'))
            $views[] = 'flexshare/ftp';

        if (clearos_library_installed('web/Httpd'))
            $views[] = 'flexshare/web';

        $this->page->view_controllers($views, lang('flexshare_summary'));
    }
}
