<?php

/**
 * Flexshare class.
 *
 * @category   apps
 * @package    flexshare
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2003-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/flexshare/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\flexshare;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('flexshare');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\ftp\ProFTPd as ProFTPd;
use \clearos\apps\groups\Group_Factory as Group_Factory;
use \clearos\apps\network\Iface_Manager as Iface_Manager;
use \clearos\apps\network\Network_Utils as Network_Utils;
use \clearos\apps\samba_common\Samba as Samba;
use \clearos\apps\users\User_Factory as User_Factory;
use \clearos\apps\web_server\Httpd as Httpd;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('base/Folder');
clearos_load_library('base/Shell');
clearos_load_library('ftp/ProFTPd');
clearos_load_library('groups/Group_Factory');
clearos_load_library('network/Iface_Manager');
clearos_load_library('network/Network_Utils');
clearos_load_library('samba_common/Samba');
clearos_load_library('users/User_Factory');
clearos_load_library('web_server/Httpd');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\File_No_Match_Exception as File_No_Match_Exception;
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;
use \clearos\apps\flexshare\Flexshare_Not_Found_Exception as Flexshare_Not_Found_Exception;
use \clearos\apps\flexshare\Flexshare_Parameter_Not_Found_Exception as Flexshare_Parameter_Not_Found_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/File_No_Match_Exception');
clearos_load_library('base/File_Not_Found_Exception');
clearos_load_library('base/Validation_Exception');
clearos_load_library('flexshare/Flexshare_Not_Found_Exception');
clearos_load_library('flexshare/Flexshare_Parameter_Not_Found_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Flexshare class.
 *
 * @category   apps
 * @package    flexshare
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2003-2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/flexshare/
 */

class Flexshare extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const LOG_TAG = 'flexshare';
    const FILE_CONFIG = '/etc/clearos/flexshare.conf';
    const FILE_SMB_VIRTUAL = 'flexshare.conf';
    const FILE_FSTAB_CONFIG = '/etc/fstab';
    const PATH_ROOT = '/var/flexshare';
    const PATH_TEMP = '/var/tmp';
    const PATH_CONFIGLET = '/etc/clearos/flexshare.d';
    const FILE_INITIALIZED = '/var/clearos/flexshare/initialized';
    const SHARE_PATH = '/var/flexshare/shares';
    const HTTPD_LOG_PATH = '/var/log/httpd';
    const WEB_VIRTUAL_HOST_PATH = '/etc/httpd/conf.d';
    const FTP_VIRTUAL_HOST_PATH = '/etc/proftpd.d';
    const SMB_VIRTUAL_HOST_PATH = '/etc/samba';
    const CMD_VALIDATE_HTTPD = '/usr/sbin/httpd';
    const CMD_VALIDATE_PROFTPD = '/usr/sbin/proftpd';
    const CMD_VALIDATE_SMBD = '/usr/bin/testparm';
    const CMD_MOUNT = '/bin/mount';
    const CMD_UMOUNT = '/bin/umount';
    const CMD_PHP = '/usr/clearos/sandbox/usr/bin/php';
    const CMD_UPDATE_PERMS = '/usr/sbin/updateflexperms';
    const CONSTANT_ACCOUNT_USERNAME = 'flexshare';
    const CONSTANT_FILES_USERNAME = 'flexshares';
    const CONSTANT_WEB_APP_USERNAME = 'apache';
    const MBOX_HOSTNAME = 'localhost';
    const DEFAULT_PORT_WEB = 80;
    const DEFAULT_PORT_FTP = 21;
    const DEFAULT_PORT_FTPS = 900;
    const DEFAULT_PORT_FTPES = 900;
    const DEFAULT_SSI_PARAM = 'IncludesNOExec';
    const REGEX_OPEN = '/^<Share\s(.*)>$/i';
    const REGEX_CLOSE = '/^<\/Share>$/i';
    const ACCESS_LAN = 0;
    const ACCESS_ALL = 1;
    const POLICY_DONOT_WRITE = 0;
    const POLICY_OVERWRITE = 1;
    const POLICY_BACKUP = 2;
    const SAVE_REQ_CONFIRM = 0;
    const SAVE_AUTO = 1;
    const PERMISSION_NONE = 0;
    const PERMISSION_READ = 1;
    const PERMISSION_WRITE = 2;
    const PERMISSION_WRITE_PLUS = 3;
    const PERMISSION_READ_WRITE = 4;
    const PERMISSION_READ_WRITE_PLUS = 5;
    const DIR_INDEX_LIST = 'index.htm index.html index.php index.php3 default.html index.cgi';
    const CASE_HTTP = 1;
    const CASE_HTTPS = 2;
    const CASE_CUSTOM_HTTP = 3;
    const CASE_CUSTOM_HTTPS = 4;
    const PREFIX = 'flex-';
    const FTP_PASV_MIN = 60000;
    const FTP_PASV_MAX = 61000;
    const TYPE_ALL = 'all';
    const TYPE_WEB_SITE = 'web_site';
    const TYPE_WEB_APP = 'web_app';
    const TYPE_FILE_SHARE = 'file_share';
    const WRITE_WARNING = '
#----------------------------------------------------------------
# WARNING: This file is automatically created by webconfig.
#----------------------------------------------------------------
';

    protected $access = array(
                            self::PERMISSION_NONE => 'PORT QUIT',
                            self::PERMISSION_READ => 'CWD READ DIRS PORT QUIT',
                            self::PERMISSION_WRITE => 'CWD WRITE DIRS PORT QUIT',
                            self::PERMISSION_WRITE_PLUS => 'CWD WRITE DIRS PORT QUIT',
                            self::PERMISSION_READ_WRITE => 'CWD READ WRITE DIRS PORT QUIT',
                            self::PERMISSION_READ_WRITE_PLUS => 'CWD READ WRITE DIRS PORT QUIT'
                        );
    protected $bad_ports = array('81', '82', '83');
    protected $shares = NULL;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Flexshare constructor.
     */

    function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Activates a share.
     *
     * For shares that are created via configlets (e.g. web apps), this
     * method can be called post-install to activate the share.
     *
     * @param string $name flexshare name
     *
     * @return void
     * @throws Engine_Exception
     */

    function activate($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $share = $this->get_share($name);

        $folder = new Folder($share['ShareDir']);

        if (!$folder->exists()) {
            $folder->create('apache', 'allusers', '0775'); // FIXME
        }

        $this->set_share_state($name, TRUE, TRUE);
    }

    /**
     * Adds a new Flexshare.
     *
     * @param string  $name        flexshare name
     * @param string  $description brief description of the flexshare
     * @param string  $group       group owner of the flexshare
     * @param string  $directory   directory
     * @param boolean $type        Flexshare type
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function add_share($name, $description, $group, $directory, $type = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $name = strtolower($name);

        // if directory = root share path... tack on name
        if ($directory == self::SHARE_PATH)
            $directory .= '/' . $name;

        // Validate
        // --------

        Validation_Exception::is_valid($this->validate_name($name));
        Validation_Exception::is_valid($this->validate_description($description));
        Validation_Exception::is_valid($this->validate_group($group));
        Validation_Exception::is_valid($this->validate_directory($directory));

        // Windows limitations
        //--------------------

        $groupobj = Group_Factory::create($name);

        if ($groupobj->exists())
            throw new Validation_Exception(lang('flexshare_name_overlaps_with_group'));

        $userobj = User_Factory::create($name);

        if ($userobj->exists())
            throw new Validation_Exception(lang('flexshare_name_overlaps_with_username'));

        $file = new File(self::FILE_CONFIG);

        if (! $file->exists()) {
            $file->create('root', 'root', '0640');
            $file->add_lines("# Flexshare Configuration");
        }

        // Check for non-uniques
        if (count($file->get_search_results("<Share $name>")) > 0)
            throw new Engine_Exception(lang('share_already_exists'));

        // Handle the type
        // This parameter used to be a boolean $internal.
        // Keep this logic for now

        if (($type === FALSE) || ($type === self::TYPE_FILE_SHARE))
            $internal = '';
        else if (($type === TRUE) || ($type === self::TYPE_WEB_SITE))
            $internal = 1;
        else if ($type === self::TYPE_WEB_APP)
            $internal = 2;

        // Create folder (if necessary) and add skeleton
        $folder = new Folder(self::SHARE_PATH . "/$name");

        if (! $folder->exists()) {
            $folder_owner = (empty($internal)) ? self::CONSTANT_FILES_USERNAME : self::CONSTANT_WEB_APP_USERNAME;
            $folder->create($folder_owner, $group, '0775');
        }

        // Add it
        $newshare = "<Share $name>\n" .
                    "  ShareDescription=$description\n" .
                    "  ShareGroup=$group\n" .
                    "  ShareCreated=" . time() . "\n" .
                    "  ShareModified=" . time() . "\n" .
                    "  ShareEnabled=0\n" .
                    "  ShareDir=" . self::SHARE_PATH . "/$name\n" .
                    "  ShareInternal=$internal\n" .
                    "</Share>\n"
                    ;
        $file->add_lines($newshare);

        $this->shares = NULL; // Force a configuration reload

        // Now set directory
        $this->set_directory($name, $directory);
    }

    /**
     * Deletes an existing flexshare.
     *
     * @param string  $name       flexshare name
     * @param boolean $delete_dir boolean flag to delete share directory and any files it contains
     *
     * @return void
     * @throws Engine_Exception
     */

    function delete_share($name, $delete_dir)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_name($name));

        // Set directory back to default
        // This will remove any mount points

        $defaultdir = self::SHARE_PATH . '/' . $name;
        $this->set_directory($name, $defaultdir);

        $file = new File(self::FILE_CONFIG);

        if (! $file->exists())
            throw new File_Not_Found_Exception(self::FILE_CONFIG);

        // Backup in case we need to go back to original
        $file->move_to(self::PATH_TEMP . "/flexshare.conf.orig");

        // Create new file in parallel
        $newfile = new File(self::FILE_CONFIG . ".cctmp", TRUE);

        if ($newfile->exists())
            $newfile->delete();

        $newfile->create('root', 'root', '0644');

        $lines = $file->get_contents_as_array();
        $found = FALSE;
        $match = array();
        $new_lines = '';

        foreach ($lines as $line) {
            if (preg_match(self::REGEX_OPEN, $line, $match) && $match[1] == $name) {
                $found = TRUE;
            } elseif (preg_match(self::REGEX_CLOSE, $line) && $found) {
                $found = FALSE;
                continue;
            }

            if ($found)
                continue;

            $new_lines .= "$line\n";
        }

        $newfile->add_lines($new_lines);
        $newfile->move_to(self::FILE_CONFIG);

        $this->shares = NULL; // Force a configuration reload

        try {
            $this->_generate_web_flexshares();
            $this->_generate_ftp_flexshares();
            $this->_generate_file_flexshares();

            try {
                $file->delete();
            } catch (Exception $ignore) {
                // Just log
            }
        } catch (Exception $e) {
            // Any exception here, toggle...well, toggle.
            $file->move_to(self::FILE_CONFIG);
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        // If you get here, it's OK to delete (as required)
        if ($delete_dir) {
            try {
                $folder = new Folder(self::SHARE_PATH . "/$name");
                if ($folder->exists())
                    $folder->delete(TRUE);
            } catch (Exception $e) {
                // Just log
            }
        }
    }

    /**
     * Returns existence of a Flexshare.
     *
     * @param string  $name        flexshare name
     *
     * @return boolean TRUE if share exists
     * @throws Engine_Exception
     */

    function exists($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $share_info = $this->get_share($name);
        } catch (Flexshare_Not_Found_Exception $e) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Returns configured group for given share.
     *
     * @param string $name flexshare name
     *
     * @return string group name
     * @throws Flexshare_Not_Found_Exception, Engine_Exception
     */

    function get_group($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_get_parameter($name, 'ShareGroup');
    }

    /**
     * Returns information on a specific flexshare configuration.
     *
     * @param string $name flexshare name
     *
     * @return array information of flexshare
     * @throws Flexshare_Not_Found_Exception, Engine_Exception
     */

    function get_share($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $shares = $this->_get_shares(self::TYPE_ALL);

        if (empty($shares[$name]))
            throw new Flexshare_Not_Found_Exception($name, CLEAROS_INFO);

        return $shares[$name];
    }

    /**
     * Returns a list of Flexshares.
     *
     * @param string $type type of Flexshare
     *
     * @return array summary of Flexshares
     * @throws Engine_Exception
     */

    function get_shares($type = self::TYPE_FILE_SHARE)
    {
        clearos_profile(__METHOD__, __LINE__);

        return $this->_get_shares($type);
    }

    /**
     * Returns a list of defined Flexshares.
     *
     * @param boolean $hide_internal hide internal shares
     *
     * @return array summary of flexshares
     * @throws Engine_Exception
     */

    function get_share_summary($hide_internal = TRUE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $type = ($hide_internal) ? self::TYPE_FILE_SHARE : self::TYPE_ALL;
        
        return $this->_get_shares($type);
    }

    /**
     * Returns a list of directory options to map to flexshare.
     *
     * @param string $name the flex share name
     *
     * @return array
     */

    function get_dir_options($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $options = array();

        // Custom
        try {
            $custom_data = $this->_get_global_parameter('FlexshareDirCustom');
            if (! empty($custom_data)) {
                $list = preg_split("/\\|/", $this->_get_global_parameter('FlexshareDirCustom'));
                foreach ($list as $custom) {
                    list ($desc, $path) = preg_split("/:/", $custom);
                    $options[$path] = $desc . ' (' . $path . ")\n";
                }
            }
        } catch (Flexshare_Parameter_Not_Found_Exception $e) {
            // Ignore
        } catch (Engine_Exception $e) {
            // Ignore
        }

        // If $name is NULL, fancy up the path displayed
        if ($name == NULL)
            $display_name = preg_replace('/ /', '_', strtoupper(lang('flexshare_share_name'))); 
        else
            $display_name = $name;

        // Default
        $options[self::SHARE_PATH . '/' . $name] = lang('base_default') . ' (' . self::SHARE_PATH . '/' . $display_name . ")";

        return $options;
    }

    /**
     * Returns a list of valid web access options for a flexshare.
     *
     * @return array
     */

    function get_web_access_options()
    {
        clearos_profile(__METHOD__, __LINE__);

        $options = array(
           self::ACCESS_LAN => lang('flexshare_access_lan'),
           self::ACCESS_ALL => lang('base_all')
        );

        return $options;
    }

    /**
     * Returns a list of valid FTP permission options for a flexshare.
     *
     * @return array
     */

    function get_ftp_permission_options()
    {
        clearos_profile(__METHOD__, __LINE__);

        $options = array(
           self::PERMISSION_READ => lang('flexshare_read_only'),
           self::PERMISSION_READ_WRITE_PLUS => lang('flexshare_read_write')
        );

        return $options;
    }

    /**
     * Returns FTP state.
     *
     * @param string $share Flexshare
     *
     * @return boolean TRUE if FTP is enabled
     */

    function get_ftp_state($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $share = $this->get_share($name);

        $state = isset($share['FtpEnabled']) ? (bool) $share['FtpEnabled'] :  FALSE;
     
        return $state;
    }

    /**
     * Returns a list of valid FTP umask options for a flexshare.
     *
     * @return array
     */

    function get_ftp_umask_options()
    {
        clearos_profile(__METHOD__, __LINE__);

        // Umask is inverted.
        $options = array(
            7 => "---",
            6 => "--x",
            5 => "-w-",
            4 => "-wx",
            3 => "r--",
            2 => "r-x",
            1 => "rw-",
            0 => "rwx"
        );

        return $options;
    }

    /**
     * Returns a list of valid file permission options for a flexshare.
     *
     * @return array
     */

    function get_file_permission_options()
    {
        clearos_profile(__METHOD__, __LINE__);

        $options = array(
            self::PERMISSION_READ => lang('flexshare_read_only'),
            self::PERMISSION_READ_WRITE => lang('flexshare_read_write')
        );

        return $options;
    }

    /**
     * Returns file state.
     *
     * @param string $share Flexshare
     *
     * @return boolean TRUE if FTP is enabled
     */

    function get_file_state($name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $share = $this->get_share($name);

        $state = isset($share['FileEnabled']) ? (bool) $share['FileEnabled'] :  FALSE;
     
        return $state;
    }

    /**
     * Sets a flex share's description.
     *
     * @param string $name        flexshare name
     * @param string $description flexshare description
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_description($name, $description)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Validate
        Validation_Exception::is_valid($this->validate_description($description));

        $this->_set_parameter($name, 'ShareDescription', $description);
    }

    /**
     * Sets a flexshare's group owner.
     *
     * @param string $name  flexshare name
     * @param string $group flexshare group owner
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_group($name, $group)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_group($group));

        if ($this->_get_parameter($name, 'ShareGroup') == $group)
            return;

        $this->_set_parameter($name, 'ShareGroup', $group);
        $state = 0;

        if ($this->_get_parameter($name, 'ShareEnabled'))
            $state = (int)$this->_get_parameter($name, 'ShareEnabled');

        $this->set_share_state($name, $state, TRUE);
    }

    /**
     * Sets a flexshare's root directory.
     *
     * @param string $name      flexshare name
     * @param string $directory flex share directory
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_directory($name, $directory)
    {
        clearos_profile(__METHOD__, __LINE__);

        $directory = trim($directory);
        $defaultdir = self::SHARE_PATH . '/' . $name;

        if (!isset($directory) || !$directory)
            $directory = $defaultdir;

        // Validate
        Validation_Exception::is_valid($this->validate_directory($directory));

        $this->_update_folder_links($name, $directory);

        $this->_set_parameter($name, 'ShareDir', $directory);
    }

    /**
     * Sets the state of a flexshare.
     *
     * @param string $name   flexshare name
     * @param string $state  state
     * @param string $force  force re-creation of config files
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_share_state($name, $state, $force = FALSE)
    {
        clearos_profile(__METHOD__, __LINE__);

        $share = $this->get_share($name);
        $state_value = ($state) ? 1 : 0;

        // Do we need to generates configs again?
        if ($force || $this->_get_parameter($name, 'ShareEnabled') != $state_value) {

            // Set flag
            $this->_set_parameter($name, 'ShareEnabled', $state_value);

            $this->_generate_web_flexshares();
            $this->_generate_ftp_flexshares();
            $this->_generate_file_flexshares();
        }

        $this->_update_folder_links($name, $this->_get_parameter($name, 'ShareDir'));
        $this->_update_folder_attributes($share['ShareDir'], $share['ShareOwner'], $share['ShareGroup']);
    }

    ////////////////////
    //     W E B      //
    ////////////////////

    /**
     * Sets the directory alias of web-based access.
     *
     * @param string $name  flexshare name
     * @param string $alias directory alias
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_web_directory_alias($name, $alias)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_web_directory_alias($alias));

        $this->_set_parameter($name, 'WebDirectoryAlias', $alias);
    }

    /**
     * Sets the directory alias of web-based access.
     *
     * @param string $name  flexshare name
     * @param string $alias directory alias
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_web_directory_alias_alternate($name, $alias)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_web_directory_alias($alias));

        $this->_set_parameter($name, 'WebDirectoryAliasAlternate', $alias);
    }

    /**
     * Sets the enabled of web-based access.
     *
     * @param string $name    flexshare name
     * @param bool   $enabled web enabled
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_web_enabled($name, $enabled)
    {
        clearos_profile(__METHOD__, __LINE__);

        // TODO: kludgy, the realm name is now automatically set to the flexshare description
        $share = $this->get_share($name);
        $this->_set_parameter($name, 'WebRealm', $share['ShareDescription']);

        $this->_set_parameter($name, 'WebEnabled', ($enabled ? 1: 0));
        $this->_generate_web_flexshares();
    }

    /**
     * Sets the server alias of web-based access.
     *
     * @param string $name         flexshare name
     * @param string $server_alias server alias
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_web_server_alias($name, $server_alias)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_web_server_alias($server_alias));

        $this->_set_parameter($name, 'WebServerAlias', $server_alias);
    }

    /**
     * Sets the alternate server alias of web-based access.
     *
     * @param string $name         flexshare name
     * @param string $server_alias server alias
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_web_server_alias_alternate($name, $server_alias)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_web_server_alias($server_alias));

        $this->_set_parameter($name, 'WebServerAliasAlternate', $server_alias);
    }

    /**
     * Sets the server name of web-based access.
     *
     * @param string $name        flexshare name
     * @param string $server_name server name
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_web_server_name($name, $server_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_web_server_name($server_name));

        $this->_set_parameter($name, 'WebServerName', $server_name);
    }

    /**
     * Sets the alternate server name of web-based access.
     *
     * @param string $name        flexshare name
     * @param string $server_name server name
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_web_server_name_alternate($name, $server_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_web_server_name($server_name));

        $this->_set_parameter($name, 'WebServerNameAlternate', $server_name);
    }

    /**
     * Sets whether to allow an index of files to be displayed in browser.
     *
     * @param string $name       flexshare name
     * @param bool   $show_index boolean flag to determine to show file index
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_web_show_index($name, $show_index)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'WebShowIndex', $show_index);
    }

    /**
     * Sets whether to follow sym links.
     *
     * @param string $name            flexshare name
     * @param bool   $follow_symlinks boolean flag to determine to follow sym links
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_web_follow_symlinks($name, $follow_symlinks)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'WebFollowSymLinks', $follow_symlinks);
    }

    /**
     * Sets whether to allow server side includes.
     *
     * @param string $name flexshare name
     * @param bool   $ssi  boolean flag to determine whether to allow SSI's
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_web_allow_ssi($name, $ssi)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'WebAllowSSI', $ssi);
    }

    /**
     * Sets whether to allow override of options if .htaccess file is found.
     *
     * @param string $name     flexshare name
     * @param bool   $htaccess boolean flag to determine whether to allow htaccess override
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_web_htaccess_override($name, $htaccess)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'WebHtaccessOverride', $htaccess);
    }

    /**
     * Sets an override flag to use custom port on the flexshare.
     *
     * @param string $name          flexshare name
     * @param bool   $override_port boolean flag
     * @param int    $port          port
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_web_override_port($name, $override_port, $port)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($override_port && ($port == 80 || $port == 443))
            throw new Engine_Exception(lang('flexshare_non_custom_port_warning'), CLEAROS_ERROR);

        $shares = $this->_get_shares(self::TYPE_ALL);

        $ssl = isset($shares[$name]['WebReqSsl']) ? $shares[$name]['WebReqSsl'] : '';

        $inuse_ports = array();

        foreach ($shares as $share_name => $share) {
            if (($name != $share_name) && ($ssl != $share['WebReqSsl']))
                $inuse_ports[] = $share['WebPort'];
        }

        if ($override_port && (in_array($port, $this->bad_ports) || in_array($port, $inuse_ports)))
            throw new Validation_Exception(lang('flexshare_port_already_in_use'));

        $this->_set_parameter($name, 'WebOverridePort', $override_port);
        $this->_set_parameter($name, 'WebPort', $port);
    }

    /**
     * Sets the require SSL flag for the flexshare.
     *
     * @param string $name  flexshare name
     * @param bool   $state state
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_web_require_ssl($name, $state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'WebReqSsl', $state);
    }

    /**
     * Sets the require authentication flag for the flexshare.
     *
     * @param string $name  flexshare name
     * @param bool   $state state
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_web_require_authentication($name, $state)
    {
        clearos_profile(__METHOD__, __LINE__);

        // If no auth required, check e-mail restricts access
        $prevent = TRUE;

        if (!$state) {
            $share = $this->get_share($name);
            if (isset($share['EmailRestrictAccess']) && $share['EmailRestrictAccess'])
                $prevent = FALSE;
            if (!isset($share['EmailEnabled']) || !$share['EmailEnabled'])
                $prevent = FALSE;
            if (!isset($share['WebEnabled']) || !$share['WebEnabled'])
                $prevent = FALSE;
            if ((!isset($share['WebPhp']) || !$share['WebPhp']) && (!isset($share['WebCgi']) || !$share['WebCgi']))
                $prevent = FALSE;
            if (isset($share['WebAccess']) && (int)$share['WebAccess'] == self::ACCESS_LAN)
                $prevent = FALSE;
        } else {
            $prevent = FALSE;
        }

        if ($prevent)
            throw new Engine_Exception(FLEXSHARE_LANG_WARNING_CONFIG, COMMON_WARNING);

        $this->_set_parameter($name, 'WebReqAuth', $state);
    }

    /**
     * Sets the realm name of web-based access.
     *
     * @param string $name  flexshare name
     * @param bool   $realm a realm name
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_web_realm($name, $realm)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Validate
        // --------
        Validation_Exception::is_valid($this->validate_web_realm($realm));

    }

    /**
     * Sets the access interface for the flexshare.
     *
     * @param string $name   flexshare name
     * @param int    $access Intranet, Internet or Any
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_web_access($name, $access)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Validate
        // --------
        Validation_Exception::is_valid($this->validate_web_access($access));

        // If web access is ALL, check e-mail restricts access
        $prevent = TRUE;
        if ((int)$access == self::ACCESS_LAN) {
            $share = $this->get_share($name);
            if (isset($share['EmailRestrictAccess']) && $share['EmailRestrictAccess'])
                $prevent = FALSE;
            if (!isset($share['EmailEnabled']) || !$share['EmailEnabled'])
                $prevent = FALSE;
            if (!isset($share['WebEnabled']) || !$share['WebEnabled'])
                $prevent = FALSE;
            if (isset($share['WebReqAuth']) && $share['WebReqAuth'])
                $prevent = FALSE;
            if ((!isset($share['WebPhp']) || !$share['WebPhp']) && (!isset($share['WebCgi']) || !$share['WebCgi']))
                $prevent = FALSE;
        } else {
            $prevent = FALSE;
        }

        if ($prevent)
            throw new Engine_Exception(FLEXSHARE_LANG_WARNING_CONFIG, COMMON_WARNING);

        $this->_set_parameter($name, 'WebAccess', $access);
    }

    /**
     * Sets the groups allowed to access this flexshare.
     *
     * @param string $name   flexshare name
     * @param array  $access group access array
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_web_group_access($name, $access)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'WebGroupAccess', implode(' ', $access));
    }

    /**
     * Sets parameter allowing PHP executeon on the flexshare.
     *
     * @param string $name    flexshare name
     * @param bool   $web_php PHP enabled or not
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_web_php($name, $web_php)
    {
        clearos_profile(__METHOD__, __LINE__);

        // If PHP enabled, check e-mail restricts access
        $prevent = TRUE;
        if ($web_php) {
            $share = $this->get_share($name);
            if (isset($share['EmailRestrictAccess']) && $share['EmailRestrictAccess'])
                $prevent = FALSE;
            if (!isset($share['EmailEnabled']) || !$share['EmailEnabled'])
                $prevent = FALSE;
            if (!isset($share['WebEnabled']) || !$share['WebEnabled'])
                $prevent = FALSE;
            if (isset($share['WebReqAuth']) && $share['WebReqAuth'])
                $prevent = FALSE;
            if (isset($share['WebAccess']) && (int)$share['WebAccess'] == self::ACCESS_LAN)
                $prevent = FALSE;

        } else {
            $prevent = FALSE;
        }

        if ($prevent)
            throw new Engine_Exception(FLEXSHARE_LANG_WARNING_CONFIG, COMMON_WARNING);

        $this->_set_parameter($name, 'WebPhp', $web_php);
    }

    /**
     * Sets parameter allowing CGI executeon on the flexshare.
     *
     * @param string $name    flexshare name
     * @param bool   $web_cgi CGI enabled or not
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_web_cgi($name, $web_cgi)
    {
        clearos_profile(__METHOD__, __LINE__);

        // If cgi enabled, check e-mail restricts access
        $prevent = TRUE;
        if ($web_cgi) {
            $share = $this->get_share($name);
            if (isset($share['EmailRestrictAccess']) && $share['EmailRestrictAccess'])
                $prevent = FALSE;
            if (!isset($share['EmailEnabled']) || !$share['EmailEnabled'])
                $prevent = FALSE;
            if (!isset($share['WebEnabled']) || !$share['WebEnabled'])
                $prevent = FALSE;
            if (isset($share['WebReqAuth']) && $share['WebReqAuth'])
                $prevent = FALSE;
            if (isset($share['WebAccess']) && (int)$share['WebAccess'] == self::ACCESS_LAN)
                $prevent = FALSE;
        } else {
            $prevent = FALSE;
        }

        if ($prevent)
            throw new Engine_Exception(FLEXSHARE_LANG_WARNING_CONFIG, COMMON_WARNING);

        $this->_set_parameter($name, 'WebCgi', $web_cgi);
    }

    ////////////////////
    //     F T P      //
    ////////////////////

    /**
     * Sets the enabled of ftp-based access.
     *
     * @param string $name    flexshare name
     * @param bool   $enabled ftp enabled
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_ftp_enabled($name, $enabled)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FtpEnabled', $enabled);
        $this->_generate_ftp_flexshares();
    }

    /**
     * Sets the server URL of FTP based access.
     *
     * @param string $name       flexshare name
     * @param string $server_url server URL
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_ftp_server_url($name, $server_url)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Validate
        // --------

        Validation_Exception::is_valid($this->validate_ftp_server_url($server_url));

        $this->_set_parameter($name, 'FtpServerUrl', $server_url);
    }

    /**
     * Sets an override flag to use custom port on the flexshare.
     *
     * @param string $name          flexshare name
     * @param bool   $override_port boolean flag
     * @param int    $port          port FTP listens on for this flexshare
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_ftp_override_port($name, $override_port, $port)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($override_port && ($port == self::DEFAULT_PORT_FTP || $port == self::DEFAULT_PORT_FTPS))
            throw new Engine_Exception(lang('flexshare_non_custom_port_warning'), CLEAROS_ERROR);

        if ($override_port && ($port == 21 || $port == 990))
            throw new Engine_Exception(lang('flexshare_ftp_cannot_use_default_ports'), CLEAROS_ERROR);

        // Find all ports and see if any conflicts with n-1
        if ($override_port) {
            $shares = $this->_get_shares(self::TYPE_ALL);

            foreach ($shares as $share_name => $share) {
                if ($share_name != $name) {
                    if ((int)$share["FtpPort"] == ($port - 1)) {
                        throw new Engine_Exception(lang('flexshare_ftp_port_conflict'), CLEAROS_ERROR);
                    } else if (((int)$share["FtpPort"] -1) == $port) {
                        throw new Engine_Exception(lang('flexshare_ftp_port_conflict'), CLEAROS_ERROR);
                    }
                }
            }
        }

        $this->_set_parameter($name, 'FtpOverridePort', $override_port);
        $this->_set_parameter($name, 'FtpPort', $port);
    }

    /**
     * Sets the allow passive port (PASV) flag for the flexshare.
     *
     * @param string $name          flexshare name
     * @param bool   $allow_passive boolean flag
     * @param int    $port_min      minimum port range
     * @param int    $port_max      maximum port range
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_ftp_allow_passive($name, $allow_passive, $port_min = self::FTP_PASV_MIN, $port_max = self::FTP_PASV_MAX)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($allow_passive)
            Validation_Exception::is_valid($this->validate_passive_port_range($port_min, $port_max));

        $this->_set_parameter($name, 'FtpAllowPassive', $allow_passive);

        if ($allow_passive) {
            $this->_set_parameter($name, 'FtpPassivePortMin', $port_min);
            $this->_set_parameter($name, 'FtpPassivePortMax', $port_max);
        }
    }

    /**
     * Sets the FTP protocol state.
     *
     * @param string $name  flexshare name
     * @param bool   $state boolean flag
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_ftp_protocol_ftp($name, $state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FtpEnableFtp', $state);
    }

    /**
     * Sets the FTPES protocol state.
     *
     * @param string $name  flexshare name
     * @param bool   $state boolean flag
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_ftp_protocol_ftpes($name, $state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FtpEnableFtpes', $state);
    }

    /**
     * Sets the FTPS protocol state.
     *
     * @param string $name  flexshare name
     * @param bool   $state boolean flag
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_ftp_protocol_ftps($name, $state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FtpEnableFtps', $state);
    }

    /**
     * Sets the greeting message for ftp-based group access.
     *
     * @param string $name     flexshare name
     * @param string $greeting greeting displayed on user login
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_ftp_group_greeting($name, $greeting)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FtpGroupGreeting', $greeting);
    }

    /**
     * Sets the groups allowed to access this flexshare.
     *
     * @param string $name   flexshare name
     * @param array  $access group access array
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_ftp_group_access($name, $access)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FtpGroupAccess', implode(' ', $access));
    }

    /**
     * Sets the group permission allowed to access this flexshare.
     *
     * @param string $name       flexshare name
     * @param int    $permission read/write permissions extended to useers with group access
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_ftp_group_permission($name, $permission)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_ftp_group_permission($permission));

        $this->_set_parameter($name, 'FtpGroupPermission', $permission);
    }

    ////////////////////////////////
    //    F I L E   (S A M B A)   //
    ////////////////////////////////

    /**
     * Sets the audit log state.
     *
     * @param string $name  flexshare name
     * @param bool   $state state of audit logging
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_file_audit_log($name, $state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FileAuditLog', $state);
    }

    /**
     * Sets the audit log state.
     *
     * @param string $name  flexshare name
     * @param bool   $state state of audit logging
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_file_browseable($name, $state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FileBrowseable', $state);
    }

    /**
     * Sets file sharing comment for the flexshare.
     *
     * @param string $name    flexshare name
     * @param string $comment a flexshare/fileshare comment
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_file_comment($name, $comment)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FileComment', $comment);
    }

    /**
     * Sets the enabled of file-based (SAMBA) access.
     *
     * @param string $name    flexshare name
     * @param bool   $enabled file enabled
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_file_enabled($name, $enabled)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FileEnabled', $enabled);
        $this->_generate_file_flexshares();
    }

    /**
     * Sets file sharing permissions for the flexshare.
     *
     * @param string $name       flexshare name
     * @param string $permission a valid permission
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_file_permission($name, $permission)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FilePermission', $permission);
    }

    /**
     * Sets file sharing public access flag for the flexshare.
     *
     * @param string $name          flexshare name
     * @param bool   $public_access a boolean flag
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_file_public_access($name, $public_access)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FilePublicAccess', $public_access);
    }

    /**
     * Sets the recycle bin state.
     *
     * @param string $name  flexshare name
     * @param bool   $state state of recycle bin option
     *
     * @return void
     * @throws Engine_Exception
     */

    function set_file_recycle_bin($name, $state)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_set_parameter($name, 'FileRecycleBin', $state);
    }

    /**
     * Updates folder attributes.
     *
     * Too much command line hacking will leave the group ownership of
     * files out of whack.  This method fixes this common issue.
     *
     * @return void
     */

    public function update_share_permissions()
    {
        clearos_profile(__METHOD__, __LINE__);

        $shares = $this->_get_shares(self::TYPE_ALL);

        foreach ($shares as $name => $detail)
            $this->_update_folder_attributes($detail['ShareDir'], $detail['ShareOwner'], $detail['ShareGroup']);
    }

    /**
     * Upgrades virtual hosts implementation.
     *
     * See tracker #1219 for more information
     *
     * @return void
     */

    public function upgrade_virtual_hosts()
    {
        clearos_profile(__METHOD__, __LINE__);

        $shares = $this->_get_shares(self::TYPE_WEB_SITE);

        foreach ($shares as $name => $share) {
            if ($share['WebEnabled'] == FALSE) {
                clearos_log('flexshare', 'converting web server virtual host: ' . $name);

                // Load old configuration files
                //-----------------------------
                try {
                    if (empty($share['WebDefaultSite']))
                        $vhost_filename = 'virtual.' . $name . '.conf';
                    else
                        $vhost_filename = 'clearos.default.conf';

                    $file = new File(self::WEB_VIRTUAL_HOST_PATH . '/' . $vhost_filename);
                    $alias = $file->lookup_value('/\s*ServerAlias\s*/');
                } catch (File_No_Match_Exception $e) {
                    $alias = '';
                } catch (File_Not_Found_Exception $e) {
                    $alias = '';
                }

                // Set sane defaults to match existing capabilities
                //-------------------------------------------------

                $this->set_web_access($name, self::ACCESS_ALL);
                $this->set_web_allow_ssi($name, TRUE);
                $this->set_web_cgi($name, FALSE);
                $this->set_web_follow_symlinks($name, TRUE);
                $this->set_web_htaccess_override($name, TRUE);
                $this->set_web_override_port($name, FALSE, 80);
                $this->set_web_php($name, TRUE);
                $this->set_web_realm($name, $share['ShareDescription']);
                $this->set_web_require_authentication($name, FALSE);

                // FIXME: this seems to be enable/disable now ?
                // $this->set_web_require_ssl($name, $state);
                
                $this->set_web_server_alias($name, $alias);
                $this->set_web_server_name($name, $name);
                $this->set_web_show_index($name, TRUE);
                $this->set_web_enabled($name, TRUE);

                // Delete old file
                $file->delete();
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for flexshare name.
     *
     * @param string $name flexshare name
     *
     * @return mixed void if name is valid, errmsg otherwise
     */

    function validate_name($name)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (!preg_match("/^([A-Za-z0-9\-\.\_]+)$/", $name))
            return lang('flexshare_invalid_name');
    }

    /**
     * Validation routine for a group.
     *
     * @param string $group a system group
     *
     * @return mixed void if group is valid, errmsg otherwise
     */

    function validate_group($group)
    {
        clearos_profile(__METHOD__, __LINE__);

        $group = Group_Factory::create($group);

        if (! $group->exists())
            return lang('flexshare_invalid_group');
    }

    /**
     * Validation routine for an owner.
     *
     * @param string $owner a system owner
     *
     * @return mixed void if owner is valid, errmsg otherwise
     */

    function validate_owner($owner)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (empty($owner))
            return lang('flexshare_invalid_owner');
    }

    /**
     * Validation routine for password.
     *
     * @param string $password password
     *
     * @return mixed void if group is valid, errmsg otherwise
     */
    /**
     * Validation routine for password.
     *
     * @param string $password password
     *
     * @return mixed void if group is valid, errmsg otherwise
     */

    function validate_password($password)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for description.
     *
     * @param string $description flexshare description
     *
     * @return mixed void if description is valid, errmsg otherwise
     */

    function validate_description($description)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!preg_match("/^([A-Za-z0-9\-\.\_\' ]*)$/", $description))
            return lang('flexshare_invalid_description');
    }

    /**
     * Validation routine for directory path.
     *
     * @param string $dir directory path for flexshare
     *
     * @return mixed void if directory is valid, errmsg otherwise
     */

    function validate_directory($dir)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!preg_match("/^([A-Za-z0-9\-\.\_\/]+)$/", $dir))
            return lang('flexshare_invalid_dir');
    }

    /**
     * Validation routine for flexshare file comment.
     *
     * @param string $comment file comment
     *
     * @return mixed void if invalid, errmsg otherwise
     */

    function validate_file_comment($comment)
    {
        clearos_profile(__METHOD__, __LINE__);

        // TODO: validate
        if (FALSE)
            return lang('flexshare_invalid_file_comment');
    }

    /**
     * Validation routine for web directory alias.
     *
     * @param string $alias directory alias
     *
     * @return mixed void if directory alias is valid, errmsg otherwise
     */

    function validate_web_directory_alias($alias)
    {
        clearos_profile(__METHOD__, __LINE__);

        return;
    }

    /**
     * Validation routine for web server alias.
     *
     * @param string $server_alias web server alias
     *
     * @return mixed void if web server alias is valid, errmsg otherwise
     */

    function validate_web_server_alias($server_alias)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!clearos_library_installed('web_server/Httpd'))
            return;

        $httpd = new Httpd();

        return $httpd->validate_aliases($server_alias);
    }

    /**
     * Validation routine for web server name.
     *
     * @param string $server_name web server name
     *
     * @return mixed void if web server name is valid, errmsg otherwise
     */

    function validate_web_server_name($server_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!clearos_library_installed('web_server/Httpd'))
            return;

        $httpd = new Httpd();

        return $httpd->validate_server_name($server_name);
    }

    /**
     * Validation routine for web realm.
     *
     * @param string $realm web realm
     *
     * @return mixed void if web realm is valid, errmsg otherwise
     */

    function validate_web_realm($realm)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!preg_match("/^([A-Za-z0-9\-\.\_\/\' ]+)$/", $realm))
            return lang('flexshare_invalid_web_realm');
    }

    /**
     * Validation routine for flexshare FTP.
     *
     * @param boolean $status FTP flexshare status
     *
     * @return mixed void if status is valid, errmsg otherwise
     */

    function validate_ftp_enabled($status)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for FTP passive ports.
     *
     * @param int $port_min Port start
     * @param int $port_max Port end
     *
     * @return mixed void if ports are valid, errmsg otherwise
     */

    function validate_passive_port_range($port_min, $port_max)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_port_range($port_min, $port_max))
            return lang('flexshare_port_range_invalid');

        if ($port_min < 1023 || $port_max < 1023)
            return lang('flexshare_passive_port_below_min');
    }

    /**
     * Validation routine for FTP server URL.
     *
     * @param string $server_url FTP server URL
     *
     * @return mixed void if FTP server URL is valid, errmsg otherwise
     */

    function validate_ftp_server_url($server_url)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_hostname($server_url))
            return lang('flexshare_invalid_server_url');
    }

    /**
     * Validation routine for flexshare group permission on FTP.
     *
     * @param boolean $permission FTP flexshare group permission
     *
     * @return mixed void if invalid, errmsg otherwise
     */

    function validate_ftp_group_permission($permission)
    {
        clearos_profile(__METHOD__, __LINE__);
        $options = $this->get_ftp_permission_options();
        if (!array_key_exists($permission, $options))
            return lang('flexshare_invalid_permission');
    }

    /**
     * Validation routine for flexshare group greeting on FTP.
     *
     * @param boolean $greeting FTP flexshare group greeting
     *
     * @return mixed void if invalid, errmsg otherwise
     */

    function validate_ftp_group_greeting($greeting)
    {
        clearos_profile(__METHOD__, __LINE__);
        // Invalid characters in greeting?
        //if (preg_match("//" $greeting))
        //    return lang('flexshare_invalid_greeting');
    }

    /**
     * Validation routine for allow passive state.
     *
     * @param boolean $state state
     *
     * @return string error message if state is invalid
     */

    function validate_ftp_allow_passive_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for FTP override port.
     *
     * @param integer $port port number
     *
     * @return string error message if port is invalid
     */

    function validate_ftp_override_port($port)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_port($port))
            return lang('flexshare_port_invalid');
        if (($port == self::DEFAULT_PORT_FTP) || ($port == self::DEFAULT_PORT_FTPS))
            return lang('flexshare_non_custom_port_warning');
    }

    /**
     * Validation routine for FTP override port state.
     *
     * @param boolean $state state
     *
     * @return string error message if state is invalid
     */

    function validate_ftp_override_port_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for FTP passive port.
     *
     * @param integer $port port number
     *
     * @return string error message if port is invalid
     */

    function validate_ftp_passive_port($port)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_port($port))
            return lang('flexshare_port_invalid');
    }

    /**
     * Validation routine for FTP protocol state.
     *
     * @param boolean $state state
     *
     * @return string error message if state is invalid
     */

    function validate_ftp_protocol_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! clearos_is_valid_boolean($state))
            return lang('flexshare_ftp_state_invalid');
    }

    /**
     * Validation routine for flexshare web access on Web.
     *
     * @param boolean $accessibility Web access
     *
     * @return string error message if web access is invalid
     */

    function validate_web_access($accessibility)
    {
        clearos_profile(__METHOD__, __LINE__);

        $options = $this->get_web_access_options();

        if (!array_key_exists($accessibility, $options))
            return lang('flexshare_invalid_accessibility');
    }

    /**
     * Validation routine for web allow SSI.
     *
     * @param boolean $state state
     *
     * @return string error message if web allow SSI is invalid
     */

    function validate_web_allow_ssi($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! clearos_is_valid_boolean($state))
            return lang('flexshare_allow_ssi_invalid');
    }

    /**
     * Validation routine for CGI state.
     *
     * @param boolean $state state
     *
     * @return string error message if CGI state is invalid
     */

    function validate_web_cgi($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! clearos_is_valid_boolean($state))
            return lang('flexshare_cgi_state_invalid');
    }

    /**
     * Validation routine for web follow symlinks.
     *
     * @param boolean $state state
     *
     * @return string error message if web follow symlinks is invalid
     */

    function validate_web_follow_symlinks($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! clearos_is_valid_boolean($state))
            return lang('flexshare_follow_symlinks_invalid');
    }

    /**
     * Validation routine for htaccess override.
     *
     * @param boolean $state state
     *
     * @return string error message if htaccess override is invalid
     */

    function validate_web_htaccess_override($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! clearos_is_valid_boolean($state))
            return lang('flexshare_htaccess_override_invalid');
    }

    /**
     * Validation routine for web override port.
     *
     * @param integer $port port number
     *
     * @return string error message if port is invalid
     */

    function validate_web_override_port($port)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! Network_Utils::is_valid_port($port))
            return lang('flexshare_port_invalid');

        if ($port == self::DEFAULT_PORT_WEB)
            return lang('flexshare_non_custom_port_warning');
    }

    /**
     * Validation routine for web override port.
     *
     * @param boolean $state state
     *
     * @return string error message if override port is invalid
     */

    function validate_web_override_port_state($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! clearos_is_valid_boolean($state))
            return lang('flexshare_override_port_invalid');
    }

    /**
     * Validation routine for PHP state.
     *
     * @param boolean $state state
     *
     * @return string error message if PHP state is invalid
     */

    function validate_web_php($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! clearos_is_valid_boolean($state))
            return lang('flexshare_php_state_invalid');
    }

    /**
     * Validation routine for require authentication.
     *
     * @param boolean $state state
     *
     * @return string error message if require authentication is invalid
     */

    function validate_web_require_authentication($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! clearos_is_valid_boolean($state))
            return lang('flexshare_require_authentication_invalid');
    }

    /**
     * Validation routine for require SSL.
     *
     * @param boolean $state state
     *
     * @return string error message if require SSL is invalid
     */

    function validate_web_require_ssl($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! clearos_is_valid_boolean($state))
            return lang('flexshare_require_ssl_invalid');
    }

    /**
     * Validation routine for web show index.
     *
     * @param boolean $state state
     *
     * @return string error message if web show index is invalid
     */

    function validate_web_show_index($state)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! clearos_is_valid_boolean($state))
            return lang('flexshare_show_index_invalid');
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E  M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Determines the web server case.
     *
     * @param int  $port     port
     * @param bool $ssl_flag flag
     *
     * @return  int case type
     */

    protected function _determine_web_server_case($port, $ssl_flag)
    {
        if ($port == 80)
            $case = self::CASE_HTTP;
        elseif ($port == 443)
            $case = self::CASE_HTTPS;
        elseif ($ssl_flag)
            $case = self::CASE_CUSTOM_HTTPS;
        else
            $case = self::CASE_CUSTOM_HTTP;

        return $case;
    }

    /**
     * Create the Samba configuration files for the specificed flexshare.
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _generate_file_flexshares()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!clearos_library_installed('samba_common/Samba'))
            return;

        $samba = new Samba();

        if (! $samba->is_file_server())
            return;

        // Create a unique file identifier
        $backup_key = time();

        // Backup original file
        $backup = new File(self::SMB_VIRTUAL_HOST_PATH . '/' . self::FILE_SMB_VIRTUAL);
        if ($backup->exists()) {
            $backup->move_to(self::PATH_TEMP . "/$backup_key.bak");
            $backup_exists = TRUE;
        } else {
            $backup_exists = FALSE;
        }

        // Samba is slightly different.  We dump all flexshare-related 'stuff' in one file
        $file = new File(self::SMB_VIRTUAL_HOST_PATH . '/' . self::FILE_SMB_VIRTUAL);
        if ($file->exists())
            $file->delete();

        $file->create('root', 'root', '0644');

        $shares = $this->_get_shares(self::TYPE_ALL);

        $linestoadd = '';

        // Recreate samba flexshare.conf

        foreach ($shares as $name => $share) {
            // If not enabled, continue through loop - we're re-creating lines here
            if (! isset($share['ShareEnabled']) || ! $share['ShareEnabled'])
                continue;

            if (! isset($share['FileEnabled']) || ! $share['FileEnabled'])
                continue;

            $linestoadd .= "[" . $name . "]\n";
            $linestoadd .= "\tpath = " . $share["ShareDir"] . "\n";
            $linestoadd .= "\tcomment = " . $share["FileComment"] . "\n";
            $linestoadd .= "\tbrowseable = Yes\n";

            if ((int)$share["FilePermission"] == self::PERMISSION_READ_WRITE)
                $linestoadd .= "\tread only = No\n";

            if (isset($share["FilePublicAccess"]) && $share["FilePublicAccess"]) {
                $linestoadd .= "\tguest ok = Yes\n";
            } else {
                $linestoadd .= "\tguest ok = No\n";
                $linestoadd .= "\tdirectory mask = 775\n";
                $linestoadd .= "\tcreate mask = 664\n";
                $linestoadd .= "\tvalid users = @\"%D" . '\\' . trim($share["ShareGroup"]) . "\", @" .
                    trim($share["ShareGroup"]) . "\n";
            }

            $linestoadd .= "\tveto files = /.flexshare*/\n";

            $vfsobject = "";

            if (isset($share["FileRecycleBin"]) && $share["FileRecycleBin"]) {
                $vfsobject .= " recycle:recycle";
                $linestoadd .= "\trecycle:repository = .trash/%U\n";
                $linestoadd .= "\trecycle:maxsize = 0\n";
                $linestoadd .= "\trecycle:versions = Yes\n";
                $linestoadd .= "\trecycle:keeptree = Yes\n";
                $linestoadd .= "\trecycle:touch = No\n";
                $linestoadd .= "\trecycle:directory_mode = 0775\n";
            }

            if (isset($share["FileAuditLog"]) && $share["FileAuditLog"]) {
                $vfsobject .= " full_audit:audit";
                $linestoadd .= "\taudit:prefix = %u\n";
                $linestoadd .= "\taudit:success = open opendir\n";
                $linestoadd .= "\taudit:failure = all\n";
                $linestoadd .= "\taudit:facility = LOCAL5\n";
                $linestoadd .= "\taudit:priority = NOTICE\n";
            }

            if ($vfsobject)
                $linestoadd .= "\tvfs object =$vfsobject\n";

            $linestoadd .= "\n";
        }

        $file->add_lines($linestoadd);

        // Make sure Samba has flexshare include
        //--------------------------------------

        $samba->add_include('/etc/samba/flexshare.conf');

        // Validate smbd configuration
        //----------------------------

        $config_ok = TRUE;

        try {
            $shell = new Shell();
            $options['validate_exit_code'] = FALSE;
            $exitcode = $shell->execute(self::CMD_VALIDATE_SMBD, '-s', FALSE, $options);
        } catch (Exception $e) {
            $config_ok = FALSE;
            clearos_log(self::LOG_TAG, "Invalid Samba config: " . clearos_exception_message($e));
        }

        if ($config_ok) {
            // Delete backups
            if ($backup_exists)
                $backup->delete();
        } else {
            // Recover backups
            if ($backup_exists) {
                try {
                    $backup->move_to(self::SMB_VIRTUAL_HOST_PATH . "/" . self::FILE_SMB_VIRTUAL);
                } catch (Exception $e) {
                    // Supresss error here...could be same file
                }
            }

            throw new Engine_Exception(lang('flexshare_config_validation_failed'));
        }
    }

    /**
     * Create the ProFtp configuration files for the specificed flexshare.
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _generate_ftp_flexshares()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!clearos_library_installed('ftp/ProFTPd'))
            return;

        $confs = array();
        $proftpd = new ProFTPd();

        // Create a unique file identifier
        $backup_key = time();

        // Get file listing in FTP confs dir
        $folder = new Folder(self::FTP_VIRTUAL_HOST_PATH);
        $confs = $folder->get_listing();
        $index = 0;

        foreach ($confs as $conf) {
            if (preg_match("/^" . self::PREFIX . ".*conf$/i", $conf)) {
                $conf_file = new File(self::FTP_VIRTUAL_HOST_PATH . "/" . $conf);
                // Backup existing file
                $conf_file->move_to(self::PATH_TEMP . "/$conf.$backup_key.bak");
            } else {
                unset($confs[$index]);
            }

            $index++;
        }

        $shares = $this->_get_shares(self::TYPE_ALL);
        $ftps_filename = '';

        // Recreate all virtual configs
        foreach ($shares as $name => $share) {

            $newlines = array();
            $append = FALSE;

            // If not enabled, continue through loop - we're re-creating lines here
            if (!isset($share['ShareEnabled']) || !$share['ShareEnabled'])
                continue;

            if (!isset($share['FtpEnabled']) || !$share['FtpEnabled'])
                continue;

            // Add group greeting file
            try {
                // This isn't fatal.  Log and continue on exception
                $file = new File(self::SHARE_PATH . "/$name/.flexshare-group.txt");
                if ($file->exists())
                    $file->delete();

                if ($share['FtpGroupGreeting']) {
                    $file->create("root", "root", 644);
                    $file->add_lines($share['FtpGroupGreeting'] . "\n");
                }
            } catch (Exception $e) {
                //
            }

            // Need to know which file we'll be writing to.
            // We determine this by port
            // Ie. /etc/proftpd.d/flex-<port>.conf

            // Port
            if ($share['FtpOverridePort'])
                $port = $share['FtpPort'];
            else
                $port = self::DEFAULT_PORT_FTP;

            // Passive mode flag
            $pasv = '';
            if ($share['FtpAllowPassive'])
                $pasv = ' PASV';

            // Overwrite permission
            if ((int)$share['FtpGroupPermission'] == self::PERMISSION_WRITE_PLUS)
                $group_write = 'on';
            else if ((int)$share['FtpGroupPermission'] == self::PERMISSION_READ_WRITE_PLUS)
                $group_write = 'on';
            else
                $group_write = 'off';

            // Create new file in parallel
            $filename = self::PREFIX . $port . '.conf';
            $ftps_filename = self::PREFIX . '990' . '.conf';

            // Add to confs array in case of failure
            if (!in_array($filename, $confs))
                $confs[] = $filename;

            $file = new File(self::FTP_VIRTUAL_HOST_PATH . "/" . $filename);
            $tempfile = new File(self::FTP_VIRTUAL_HOST_PATH . "/" . $filename . '.cctmp');

            if ($tempfile->exists())
                $tempfile->delete();

            $tempfile->create('root', 'root', '0644');

            if ($file->exists()) {
                $oldlines = $file->get_contents_as_array();
                $found_start = FALSE;

                $linestoadd = "";
                foreach ($oldlines as $line) {
                    if (preg_match("/^\s*# DNR:Webconfig start - $name$/", $line))
                        $found_start = TRUE;

                    if ($found_start && preg_match("/^\s*# DNR:Webconfig end - $name$/", $line)) {
                        $found_start = FALSE;
                        continue;
                    }

                    if ($found_start)
                        continue;

                    $linestoadd .= $line . "\n";

                    // We need to know if we are working on top of another define or not
                    $append = TRUE;
                }

                $tempfile->add_lines($linestoadd);
            }

            try {
                $proftpd_conf = new File(ProFTPd::FILE_CONFIG);
                $proftpd_conf->lookup_line("/Include \/etc\/proftpd.d\/\*.conf/i");
            } catch (File_No_Match_Exception $e) {
                // Need this line to include flexshare confs
                $proftpd_conf->add_lines("Include /etc/proftpd.d/*.conf\n");
            } catch (Exception $e) {
                throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
            }

            if (! $append) {
                $newlines[] = self::WRITE_WARNING;
                // Note: clearsync will automatically handle IP address changes
                $newlines[] = "<VirtualHost 127.0.0.1>";
                $newlines[] = "\tPort $port";
                $newlines[] = "\tDefaultRoot " . self::SHARE_PATH . "/";
                $newlines[] = "\tRequireValidShell off";
                $newlines[] = "\tAuthPam on";
                $newlines[] = "\tAuthPAMConfig proftpd";

                if ($share["FtpPassivePortMin"] && $share["FtpPassivePortMax"])
                    $newlines[] = "\tPassivePorts " . $share["FtpPassivePortMin"]  . " " . $share["FtpPassivePortMax"];

                $newlines[] = "\tCapabilitiesEngine on";
                $newlines[] = "\tCapabilitiesSet +CAP_CHOWN";

                $newlines[] = "";
                $newlines[] = "\t<Limit LOGIN CDUP PWD XPWD LIST PROT$pasv>";
                $newlines[] = "\t\tAllowAll";
                $newlines[] = "\t</Limit>";
                $newlines[] = "\t<Limit ALL>";
                $newlines[] = "\t\tDenyAll";
                $newlines[] = "\t</Limit>";
                $newlines[] = "";

                // FTPES (SSL)
                // if ($share['FtpEnableFtpes']) {
                    $newlines[] = "\t<IfModule mod_tls.c>";
                    $newlines[] = "\t\tTLSEngine on";
                    $newlines[] = "\t\tTLSLog /var/log/tls.log";
                    $newlines[] = "\t\tTLSOptions NoCertRequest";
                    $newlines[] = "\t\tTLSRequired off";
                    $newlines[] = "\t\tTLSRSACertificateFile /etc/pki/CA/bootstrap.crt";
                    $newlines[] = "\t\tTLSRSACertificateKeyFile /etc/pki/CA/bootstrap.key";
                    $newlines[] = "\t\tTLSVerifyClient off";
                    $newlines[] = "\t</IfModule>";
                    $newlines[] = "\n";
                // }
            } else {
                if ($share['FtpAllowPassive']) {
                    $tempfile->replace_lines(
                        "/\sPassivePorts \d+\s+\d+/",
                        "\tPassivePorts " . $share['FtpPassivePortMin']  . " " . $share['FtpPassivePortMax'] . "\n"
                    );
                }
            }

            // Add flexshare specific directory directives
            $newlines[] = "\t# DNR:Webconfig start - $name";
            $newlines[] = "\t<Directory " . self::SHARE_PATH . "/$name>";
            $newlines[] = "\t\tAllowOverwrite " . $group_write;
            $newlines[] = "\t\tAllowRetrieveRestart on";
            $newlines[] = "\t\tAllowStoreRestart on";
            $newlines[] = "\t\tDisplayChdir .flexshare-group.txt TRUE";
            $newlines[] = "\t\tHideNoAccess on";
            $newlines[] = "\t\tHideFiles (.flexshare)";
            $newlines[] = "\t\tGroupOwner \"" . $share["ShareGroup"] . "\"";
            $newlines[] = "\t\tUmask 0113 0002";
            $newlines[] = "\t\t<Limit " . $this->access[$share['FtpGroupPermission']] . "$pasv>";
            $newlines[] = "\t\t  AllowGroup \"" . $share['ShareGroup'] . "\"";
            $newlines[] = "\t\t  IgnoreHidden on";
            $newlines[] = "\t\t</Limit>";
            $newlines[] = "\t\t<Limit ALL>";
            $newlines[] = "\t\t  DenyAll";
            $newlines[] = "\t\t</Limit>";

            $newlines[] = "\t</Directory>";
            $newlines[] = "\t# DNR:Webconfig end - $name";
            $newlines[] = "";

            if ($append) {
                $tempfile->delete_lines("/<\/VirtualHost>/");
                $tempfile->add_lines(implode("\n", $newlines) . "\n</VirtualHost>\n");
            } else {
                $tempfile->add_lines(implode("\n", $newlines) . "\n</VirtualHost>\n");
            }

            $tempfile->move_to(self::FTP_VIRTUAL_HOST_PATH . "/" . $filename);
        }

        // Validate proftpd configuration before restarting server
        $config_ok = TRUE;

        try {
            $options['validate_exit_code'] = FALSE;
            $shell = new Shell();
            // TODO: this fails on DNS lookup issues
            //$exitcode = $shell->execute(self::CMD_VALIDATE_PROFTPD, '-t', TRUE, $options);
            $exitcode = 0;
        } catch (Exception $e) {
            $config_ok = FALSE;
        }

        if ($exitcode !== 0) {
            $config_ok = FALSE;
            $output = $shell->get_output();
            clearos_log(self::LOG_TAG, "Invalid ProFTP configuration!");

            foreach ($output as $line)
                clearos_log(self::LOG_TAG, $line);
        }

        foreach ($confs as $conf) {
            // Not a flexshare conf file
            if (!isset($conf))
                continue;

            $file = new File(self::PATH_TEMP . "/$conf.$backup_key.bak");

            if (! $file->exists()) {
                // Conf was newly created
                $file = new File(self::FTP_VIRTUAL_HOST_PATH . "/$conf");

                if (! $config_ok)
                    $file->delete();

                continue;
            }

            if ($config_ok) {
                // Delete backups
                $file->delete();
            } else {
                // Recover backups
                $file->move_to(self::FTP_VIRTUAL_HOST_PATH . "/$conf");
            }
        }

        if (!$config_ok)
            throw new Engine_Exception(lang('flexshare_config_validation_failed'));

        // Copy to FTPS configuration
        if (!empty($ftps_filename)) {
            $base_config = new File(self::FTP_VIRTUAL_HOST_PATH . '/' . $filename);
            $lines = $base_config->get_contents_as_array();
            $newlines = array();

            foreach ($lines as $line) {
                if (preg_match("/^\s*Port\s+[\d]+$/", $line))
                    $newlines[] = "\tPort 990";
                else if (preg_match("/^\s*TLSOptions\s+/", $line))
                    $newlines[] = "\t\tTLSOptions NoCertRequest UseImplicitSSL";
                else
                    $newlines[] = $line;
            }

            $file = new File(self::FTP_VIRTUAL_HOST_PATH . '/' . $ftps_filename);
            if ($file->exists())
                $file->delete();

            $file->create('root', 'root', '0644');
            $file->dump_contents_from_array($newlines);
        }

        if ($config_ok) {
            try {
                $proftpd = new ProFTPd();
                $proftpd->reset(TRUE);
            } catch (Exception $e) {
                // Keep going
            }
        }
    }

    /**
     * Create the Apache configuration files for the specificed flexshare.
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _generate_web_flexshares()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!clearos_library_installed('web_server/Httpd'))
            return;

        $httpd = new Httpd();
        $vhosts = array();
        $allow_list = '';

        // Create a unique file identifier
        $backup_key = time();

        // Get file listing in Apache vhost dir
        $folder = new Folder(self::WEB_VIRTUAL_HOST_PATH);
        $vhosts = $folder->get_listing();
        $index = 0;

        foreach ($vhosts as $vhost) {
            // Flexshares are prefixed with 'flexshare-'.  Find these files
            if (preg_match("/flex-443.conf|^" . self::PREFIX . ".*vhost$|^" . self::PREFIX . ".*conf$/i", $vhost)) {
                $vhost_file = new File(self::WEB_VIRTUAL_HOST_PATH . "/" . $vhost);
                // Backup existing file
                $vhost_file->move_to(self::PATH_TEMP . "/" . "$vhost.$backup_key.bak");
            } else {
                unset($vhosts[$index]);
            }

            $index++;
        }

        // Make sure default web site is first virtual host!
        $raw_shares = $this->_get_shares(self::TYPE_ALL);

        $shares = array();

        foreach ($raw_shares as $name => $share) {
            if (empty($share['WebDefaultSite']))
                $shares[] = $share;
            else
                array_unshift($shares, $share);
        }

        // Recreate all virtual configs
        $lans = NULL;
        $newlines = array();

        foreach ($shares as $share) {
            $name = $share['Name'];

            // Reset our loop variables
            unset($newlines);

            // If not enabled, continue through loop - we're re-creating lines here
            if (! isset($share['ShareEnabled']) || ! $share['ShareEnabled'])
                continue;

            if (! isset($share['WebEnabled']) || ! $share['WebEnabled'])
                continue;

            // Need to know which file we'll be writing to.
            // We determine this by port
            // Ie. /etc/httpd/conf.d/flexshare-<port>.conf

            // Port and extension
            //-------------------

            if ($share['WebOverridePort']) {
                $port = $share['WebPort'];
                $ssl = ($share['WebReqSsl']) ? '-ssl' : '';
            } else {
                $port = (isset($share['WebReqSsl']) && $share['WebReqSsl']) ? 443 : 80;
                $ssl = '';
            }

            if (($share['WebAccess'] == self::ACCESS_LAN) && ($lans === NULL)) {
                $ifacemanager = new Iface_Manager();
                $lans = $ifacemanager->get_most_trusted_networks();
            }

            $case = $this->_determine_web_server_case($port, $share['WebReqSsl']);

            // Create new file in parallel
            $filename = self::PREFIX . $port . $ssl . '.conf';
            $file = new File(self::WEB_VIRTUAL_HOST_PATH . '/' . $filename);

            if (! $file->exists())
                $vhosts[] = $filename;

            $newlines = array();

            if (! $file->exists()) {
                $newlines[] = self::WRITE_WARNING;
                // Only specify Listen directive for custom ports
                if ($case == self::CASE_CUSTOM_HTTP || $case == self::CASE_CUSTOM_HTTPS)
                    $newlines[] = "Listen *:$port\n";
                if ($case != self::CASE_HTTP)
                    $newlines[] = "NameVirtualHost *:$port\n";

                $newlines[] = "# Authentication mechanism";
                $newlines[] = "DefineExternalAuth pwauth pipe /usr/bin/pwauth";
                $newlines[] = "DefineExternalGroup pwauth pipe /usr/bin/unixgroup";
                $newlines[] = "";
            }

            // Legacy: some slight differences in behavior due to the old virtual host handling
            $server_names = array();
            $server_aliases = array();
            $share_aliases = array();
            $document_roots = array();

            if (empty($share['ShareInternal'])) {
                $server_names[] = $name . '.' . trim($share['WebServerName']);
                $server_aliases[] = empty($share['WebServerAlias']) ? '' : trim($share['WebServerAlias']);
                $share_aliases[] = "/flexshare/$name";
                $document_roots[] = self::SHARE_PATH . "/$name";
                $doc_comment = 'File Share';
                $access_log = trim($share['WebServerName']) . '_access_log common';
                $error_log = trim($share['WebServerName']) . '_error_log';
            } else if ($share['ShareInternal'] == 2) {
                $server_names[] = trim($share['WebServerName']);
                $server_names[] = trim($share['WebServerNameAlternate']);
                $server_aliases[] = trim($share['WebServerAlias']);
                $server_aliases[] = trim($share['WebServerAliasAlternate']);
                $share_aliases[] = trim($share['WebDirectoryAlias']);
                $share_aliases[] = trim($share['WebDirectoryAliasAlternate']);
                $document_roots[] = $share['ShareDir'] . '/live';
                $document_roots[] = $share['ShareDir'] . '/test';
                $doc_comment = 'Web App';
                $access_log = trim($share['WebServerName']) . '_access_log combined';
                $error_log = trim($share['WebServerName']) . '_error_log';
            } else {
                $server_names[] = trim($share['WebServerName']);
                $server_aliases[] = trim($share['WebServerAlias']);
                $share_aliases[] = "/flexshare/$name";
                $document_roots[] = $share['ShareDir'];
                $doc_comment = 'Web Site';

                if (empty($share['WebDefaultSite'])) {
                    $access_log = trim($share['WebServerName']) . '_access_log combined';
                    $error_log = trim($share['WebServerName']) . '_error_log';
                } else {
                    $access_log = 'access_log combined';
                    $error_log = 'error_log';
                }
            }

            $newlines[] = "";
            $newlines[] = "# -----------------------------------------------#";
            $newlines[] = "# $doc_comment";
            $newlines[] = "# -----------------------------------------------#";
            $newlines[] = "";

            // cgi-bin Alias must come first.
            if ($share['WebCgi']) {
                $cgifolder = new Folder(self::SHARE_PATH . "/$name/cgi-bin/");

                // FIXME: review for web apps
                if (!$cgifolder->exists())
                    $cgifolder->create(self::CONSTANT_FILES_USERNAME, self::CONSTANT_FILES_USERNAME, "0777");

                $newlines[] = "ScriptAlias /flexshare/$name/cgi-bin/ " . self::SHARE_PATH . "/$name/cgi-bin/";
            }

            $inx = 0;

            foreach ($server_names as $server_name) {
                if (empty($share['ShareInternal']))
                    $newlines[] = "Alias " . $share_aliases[$inx] . " " . self::SHARE_PATH . "/$name\n";
                else if ($share['ShareInternal'] == 2)
                    $newlines[] = "Alias " . $share_aliases[$inx] . " " . $document_roots[$inx] . "\n";

                $newlines[] = "<VirtualHost *:$port>";
                $newlines[] = "\tServerName " . $server_name;

                if (!empty($share['WebServerAlias']))
                    $newlines[] = "\tServerAlias " . $server_aliases[$inx];

                $newlines[] = "\tDocumentRoot " . $document_roots[$inx];
                $inx++;

                if ($share['WebCgi'])
                    $newlines[] = "\tScriptAlias /cgi-bin/ " . self::SHARE_PATH . "/$name/cgi-bin/";

                // Logging

                $newlines[] = "\tErrorLog " . self::HTTPD_LOG_PATH . "/" . $error_log;
                $newlines[] = "\tCustomLog " . self::HTTPD_LOG_PATH . "/" . $access_log;

                if ($share['WebReqSsl']) {
                    $newlines[] = "\tSSLEngine on\n" .
                        "\tSSLCertificateFile /etc/pki/tls/certs/localhost.crt\n" .
                        "\tSSLCertificateKeyFile /etc/pki/tls/private/localhost.key\n" .
                        "\t# No weak export crypto allowed\n" .
                        "\t# SSLCipherSuite ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP:+eNULL\n" .
                        "\tSSLCipherSuite ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:!EXP:+eNULL\n" .
                        "\tSetEnvIf User-Agent \".*MSIE.*\" " .
                        "nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0";
                }

                if ($share['WebReqAuth']) {
                    $newlines[] = "\tDefineExternalAuth pwauth pipe /usr/bin/pwauth";
                    $newlines[] = "\tDefineExternalGroup pwauth pipe /usr/bin/unixgroup";
                }

                $newlines[] = "</VirtualHost>\n";
            }

            if ($share['WebCgi']) {
                $newlines[] = "<Directory " . $share['ShareDir'] . "/cgi-bin>";
                $newlines[] = "\tOptions +ExecCGI";
                if ($share["WebAccess"] == self::ACCESS_LAN) {
                    $newlines[] = "\tOrder Deny,Allow";
                    $newlines[] = "\tDeny from all";
                    if (count($lans) > 0) {
                        $allow_list = '';
                        foreach ($lans as $lan)
                            $allow_list .= "$lan ";
                        $newlines[] = "\tAllow from " . $allow_list;
                    }
                }
                $newlines[] = "</Directory>\n";
            }

            $newlines[] = "<Directory " . $share['ShareDir'] . ">";
                $options = '';

            if ($share['WebShowIndex'])
                $options .= ' +Indexes';
            else
                $options .= ' -Indexes';

            if ($share['WebFollowSymLinks'])
                $options .= ' +FollowSymLinks';
            else
                $options .= ' -FollowSymLinks';

            if ($share['WebAllowSSI'])
                $options .= ' +' . self::DEFAULT_SSI_PARAM;
            else
                $options .= ' -' . self::DEFAULT_SSI_PARAM;

            if (strlen($options) > 0)
                $newlines[] = "\tOptions" . $options;

            if ($share['WebHtaccessOverride'])
                $newlines[] = "\tAllowOverride All";

            if ($share['WebReqAuth']) {
                $newlines[] = "\tAuthName \"" . $share['WebRealm'] . "\"";
                $newlines[] = "\tAuthType Basic";
                $newlines[] = "\tAuthBasicProvider external";
                $newlines[] = "\tAuthExternal pwauth";
                $newlines[] = "\tAuthzUnixgroup on";
                $newlines[] = "\tRequire group " . $share['ShareGroup'];
            }

            // LAN access
            //-----------

            if ($share['WebAccess'] == self::ACCESS_LAN) {
                $newlines[] = "\tOrder deny,allow";
                $newlines[] = "\tDeny from all";

                if (count($lans) > 0) {
                    $allow_list = '';
                    foreach ($lans as $lan)
                        $allow_list .= "$lan ";

                    $newlines[] = "\tAllow from " . $allow_list;
                }
            } else {
                $newlines[] = "\tOrder deny,allow";
                $newlines[] = "\tAllow from all";
            }

            // PHP support
            //------------

            if ($share['WebPhp']) {
                $newlines[] = "\tAddType text/html .php";
                $newlines[] = "\tAddHandler php5-script .php";
            } else {
                $newlines[] = "\tRemoveHandler .php";
                $newlines[] = "\tAddType application/x-httpd-php-source .php";
            }

            // TODO: the FollowSymLinks requirement is annoying ... still required?
            // if ($share['WebReqSsl'] && $share['WebFollowSymLinks']) {
            if ($share['WebReqSsl']) {
                $newlines[] = "\tRewriteEngine On";
                $newlines[] = "\tRewriteCond %{HTTPS} off";
                $newlines[] = "\tRewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI}";
            }

            // DAV (unsupported)
            $davcheck = self::SHARE_PATH . "/$name/.DAV";
            $davfile = new File($davcheck);
            if ($davfile->exists())
                $newlines[] = "\tDav on";

            $newlines[] = "</Directory>\n\n\n";

            if (! $file->exists())
                $file->create('root', 'root', '0644');

            $file->add_lines(implode("\n", $newlines));
        }

        // Validate httpd configuration before restarting server
        $config_ok = TRUE;

        try {
            $shell = new Shell();
            $shell_options['validate_exit_code'] = FALSE;
            $exitcode = $shell->execute(self::CMD_VALIDATE_HTTPD, '-t', TRUE, $shell_options);
        } catch (Exception $e) {
            // Backup out of commits
            $config_ok = FALSE;
        }

        if (($config_ok === FALSE) || ($exitcode != 0)) {
            $config_ok = FALSE;
            $output = $shell->get_output();
            clearos_log(self::LOG_TAG, "Invalid httpd configuration!");
            // Oops...we generated an invalid conf file
            foreach ($output as $line)
                clearos_log(self::LOG_TAG, $line);
        }

        foreach ($vhosts as $vhost) {
            // Not a flexshare vhost file
            if (!isset($vhost))
                continue;

            $file = new File(self::PATH_TEMP . "/$vhost.$backup_key.bak");

            if (! $file->exists()) {
                // Conf was newly created
                $file = new File(self::WEB_VIRTUAL_HOST_PATH . "/$vhost");
                if (! $config_ok)
                    $file->delete();

                continue;
            }

            if ($config_ok) {
                // Delete backups
                $file->delete();
            } else {
                // Recover backups
                $file->move_to(self::WEB_VIRTUAL_HOST_PATH . "/$vhost");
            }
        }

        if ($config_ok) {
            try {
                $httpd = new Httpd();
                $httpd->reset(TRUE);
            } catch (Exception $e) {
                // Keep going
            }
        } else {
            throw new Engine_Exception(lang('flexshare_config_validation_failed'), CLEAROS_ERROR);
        }
    }

    /**
     * Generic get global parameter routine.
     *
     * @param string $key key name
     *
     * @return string
     * @throws Engine_Exception, File_Not_Found_Exception, Flexshare_Parameter_Not_Found_Exception
     */

    protected function _get_global_parameter($key)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CONFIG);
            $retval = $file->lookup_value("/^\s*$key\s*=\s*/i");
        } catch (File_Not_Found_Exception $e) {
            throw new File_Not_Found_Exception($file->get_filename());
        } catch (File_No_Match_Exception $e) {
            throw new Flexshare_Parameter_Not_Found_Exception($key);
        }

        return $retval;
    }

    /**
     * Generic get share parameter routine.
     *
     * @param string $name flexshare name
     * @param string $key  key name
     *
     * @return string
     * @throws Engine_Exception, File_Not_Found_Exception, Flexshare_Parameter_Not_Found_Exception
     */

    protected function _get_parameter($name, $key)
    {
        clearos_profile(__METHOD__, __LINE__);

        $shares = $this->_get_shares(self::TYPE_ALL);

        if (isset($shares[$name][$key]))
            return $shares[$name][$key];
        else
            throw new Flexshare_Parameter_Not_Found_Exception($key);
    }

    /**
     * Returns a list of Flexshares.
     *
     * @param string $type type of Flexshare
     *
     * @return array summary of flexshares
     * @throws Engine_Exception
     */

    function _get_shares($type)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->shares === NULL) {
            $shares = array();

            $folder = new Folder(self::PATH_CONFIGLET);
            $configlets = $folder->get_recursive_listing();

            $configs = array(self::FILE_CONFIG);

            foreach ($configlets as $configlet) {
                if (preg_match('/\.conf$/', $configlet))
                    $configs[] = self::PATH_CONFIGLET . '/' . $configlet;
            }
                    
            foreach ($configs as $config_file) {
                $file = new File($config_file);

                if (!$file->exists())
                    continue;

                $lines = $file->get_contents_as_array();

                $match = array();

                foreach ($lines as $line) {
                    if (preg_match(self::REGEX_OPEN, $line, $match)) {
                        $share['Name'] = $match[1];
                    } elseif (preg_match("/^\s*([[:alpha:]]+)\s*=\s*(.*$)/i", $line, $match)) {
                        $share[$match[1]] = $match[2];
                    } elseif (preg_match(self::REGEX_CLOSE, $line)) {
                        // ShareConfig and ShareOwner are implied fields
                        $share['ShareConfig'] = $config_file;
                        $share['ShareOwner'] = ($share['ShareInternal'] == 2) ?  self::CONSTANT_WEB_APP_USERNAME : self::CONSTANT_FILES_USERNAME;
                        $share['WebDefaultSite'] = ($share['ShareDir'] == '/var/www/html') ? 1 : 0;

                        $shares[$share['Name']] = $share;

                        $share = array('WebEnabled' => 0, 'FtpEnabled' => 0, 'FileEnabled' => 0, 'EmailEnabled' => 0);
                    }
                }
            }

            $this->shares = $shares;
        }

        if ($type === self::TYPE_ALL) {
            return $this->shares;
        } else {
            $shares = array();

            foreach ($this->shares as $name => $details) {
                if ((($type === self::TYPE_FILE_SHARE) && empty($details['ShareInternal']))
                   || (($type === self::TYPE_WEB_SITE) && ($details['ShareInternal'] == 1))
                   || (($type === self::TYPE_WEB_APP) && ($details['ShareInternal'] == 2)))
                $shares[$name] = $details;
            }
        } 

        return $shares;
    }

    /**
     * Generic set parameter routine.
     *
     * @param string $name  flexshare name
     * @param string $key   key name
     * @param string $value value for the key
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _set_parameter($name, $key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        // Grab location of configuration (flexshare.conf or configlet) 
        $config_file = $this->_get_parameter($name, 'ShareConfig');

        // Convert carriage returns
        $value = preg_replace("/\n/", "", $value);

        // Update tag if it exists
        try {
            $match = FALSE;
            $file = new File($config_file);

            $needle = "/^\s*$key\s*=\s*/i";
            $match = $file->replace_lines_between($needle, "  $key=$value\n", "/<Share $name>/", "/<\/Share>/");
        } catch (File_No_Match_Exception $e) {
            // Do nothing
        }

        // If tag does not exist, add it
        if (! $match)
            $file->add_lines_after("  $key=$value\n", "/<Share $name>/");

        $this->shares = NULL; // Force a configuration reload

        // Update last modified
        if (preg_match("/^Web/", $key))
            $lastmod = "WebModified";
        else if (preg_match("/^Ftp/", $key))
            $lastmod = "FtpModified";
        else if (preg_match("/^File/", $key))
            $lastmod = "FileModified";
        else if (preg_match("/^Email/", $key))
            $lastmod = "EmailModified";
        else
            return;

        try {
            $mod = "  " . $lastmod . "=" . time() . "\n";
            $file->replace_lines_between("/" . $lastmod . "/", $mod, "/<Share $name>/", "/<\/Share>/");
        } catch (File_No_Match_Exception $e) {
            $file->add_lines_after($mod, "/<Share $name>/");
        }
    }

    /**
     * Sanity checks the group ownership.
     *
     * Too much command line hacking will leave the group ownership of
     * files out of whack.  This method fixes this common issue.
     *
     * @param string $directory share directory
     * @param string $owner     owner
     * @param string $group     group name
     *
     * @return void
     */

    protected function _update_folder_attributes($directory, $owner, $group)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->validate_directory($directory) || $this->validate_owner($owner) || $this->validate_group($group))
            return;
        
        try {
            $options['background'] = TRUE;

            $shell = new Shell();
            $shell->execute(self::CMD_UPDATE_PERMS, "'$directory' '$owner' '$group'", TRUE, $options);
        } catch (Exception $e) {
            // Not fatal
        }
    }

    /**
     * Update folder links
     *
     * @param String $name      Flexshare name
     * @param String $directory Flexshare path
     *
     * @return void
     */

    protected function _update_folder_links($name, $directory)
    {
        clearos_profile(__METHOD__, __LINE__);

        $shell = new Shell();
        $defaultdir = self::SHARE_PATH . '/' . $name;

        // Load fstab config
        $file = new Configuration_File(self::FILE_FSTAB_CONFIG, 'split', "\s", 6);
        $config = $file->load();

        // Umount any existing
        if ($this->_get_parameter($name, 'ShareDir') != $defaultdir) {
            $param = $defaultdir;
            $options['env'] = 'LANG=en_US';
            $options['validate_exit_code'] = FALSE;

            $folder = new Folder($defaultdir);

            if (! $folder->exists())
                $folder->create('flexshares', 'nobody', '0755');

            try {
                $retval = $shell->execute(self::CMD_UMOUNT, $param, TRUE, $options);
            } catch (Exception $e) {
                if (!preg_match('/.*not mounted.*/', $e->get_message()))
                    throw new Engine_Exception(lang('flexshare_device_busy'), CLEAROS_ERROR);
            }
        }

        // Mount new share
        if ($directory != $defaultdir && $this->_get_parameter($name, 'ShareEnabled')) {
            $param = "--bind '$directory' '$defaultdir'";
            $shell->execute(self::CMD_MOUNT, $param, TRUE);
        }

        // Check for entry in fstab
        if (isset($config[$this->_get_parameter($name, 'ShareDir')]))
            $file->delete_lines("/^" . preg_quote($this->_get_parameter($name, 'ShareDir'), "/") . ".*$/");

        if ($directory != $defaultdir && $this->_get_parameter($name, 'ShareEnabled'))
            $file->add_lines($directory . "\t" . $defaultdir . "\tnone\tdefaults,bind\t0 0\n");
    }
}
