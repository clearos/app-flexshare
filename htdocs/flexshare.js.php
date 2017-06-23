<?php

/**
 * Javascript helper for Flexshare.
 *
 * @category   apps
 * @package    flexshare
 * @subpackage javascript
 * @author     ClearFoundation <developer@clearcenter.com>
 * @copyright  2011-2017 ClearFoundation
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
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('base');
clearos_load_language('flexshare');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type: application/x-javascript');
?>

$(document).ready(function() {

    $('#php').on('click', function(e) {
        handlePhp();
    });

    handlePhp();

    function handlePhp() {
        if ($('#php').val() == 0)
            $('#php_engine_field').hide();
        else
            $('#php_engine_field').show();
    }
});

var DEFAULT_PORT_HTTP = 80;
var DEFAULT_PORT_HTTPS = 443;
var DEFAULT_PORT_FTP = 2121;
var DEFAULT_PORT_FTPS = 2123;
function ftp_check_passive() {
    if ($('#allow_passive').val() == 0) {
        $('#passive_min_port').attr('disabled', true);
        $('#passive_max_port').attr('disabled', true);
    } else {
        $('#passive_min_port').attr('disabled', false);
        $('#passive_max_port').attr('disabled', false);
    }
}

function ftp_check_require_ssl() {
    if ($('#override_port').val() == 0) {
        if ($('#require_ssl').val() == 0)
            $('#port').val(DEFAULT_PORT_FTP);
        else
            $('#port').val(DEFAULT_PORT_FTPS);
    }
}

function ftp_check_override_port() {
    if ($('#override_port').val() == 0) {
        $('#port').attr('disabled', true);
        if ($('#require_ssl').val() == 0)
            $('#port').val(DEFAULT_PORT_FTP);
        else
            $('#port').val(DEFAULT_PORT_FTPS);
    } else {
        $('#port').attr('disabled', false);
    }
}

function ftp_check_allow_anon() {
    if ($('#allow_anonymous').val() == 0) {
        $('#anonymous_permission').attr('disabled', true);
        $('#anonymous_greeting').attr('disabled', true);
    } else {
        $('#anonymous_permission').attr('disabled', false);
        $('#anonymous_greeting').attr('disabled', false);
    }
}

function web_check_require_ssl() {
    if ($('#override_port').val() == 0) {
        if ($('#require_ssl').val() == 0)
            $('#port').val(DEFAULT_PORT_HTTP);
        else
            $('#port').val(DEFAULT_PORT_HTTPS);
    }
}

function web_check_override_port() {
    if ($('#override_port').val() == 0) {
        $('#port').attr('disabled', true);
        if ($('#require_ssl').val() == 0)
            $('#port').val(DEFAULT_PORT_HTTP);
        else
            $('#port').val(DEFAULT_PORT_HTTPS);
    } else {
        $('#port').attr('disabled', false);
    }
}

function email_check_restrict_access() {
    if ($('#restrict_access').val() == 0) {
        $('#acl').attr('disabled', true);
        $('#acl').attr('background', '#ffffff');
    } else {
        $('#acl').attr('disabled', false);
        $('#acl').attr('background', '#cccccc');
    }
}

function email_check_save() {
    if ($('#save').val() == 1) {
        $('#notify').attr('disabled', true);
    } else {
        $('#notify').attr('disabled', false);
    }
}

$(document).ready(function() {
    if ($(location).attr('href').match('.*/flexshare/shares/summary/.*')) {
        if ($('#enabled').val() != 1)
            $('#enabled_text').css('color', 'red');
    }
    // FTP
    if ($(location).attr('href').match('.*/ftp/.*')) {
        $('#port').attr('style', 'width: 50');
        $('#passive_min_port').attr('style', 'width: 50');
        $('#passive_max_port').attr('style', 'width: 50');
        $('#group_greeting').attr('style', 'width: 260');
        $('#anonymous_greeting').attr('style', 'width: 260');

        ftp_check_require_ssl();
        $('#require_ssl').change(function(event) {
            ftp_check_require_ssl();
        });

        ftp_check_passive();
        $('#allow_passive').change(function(event) {
            ftp_check_passive();
        });

        ftp_check_override_port();
        $('#override_port').change(function(event) {
            ftp_check_override_port();
        });
        ftp_check_allow_anon();
        $('#allow_anonymous').change(function(event) {
            ftp_check_allow_anon();
        });

    } else if ($(location).attr('href').match('.*/web/.*')) {
        $('#port').attr('style', 'width: 50');
        web_check_require_ssl();
        $('#require_ssl').change(function(event) {
            web_check_require_ssl();
        });
        web_check_override_port();
        $('#override_port').change(function(event) {
            web_check_override_port();
        });
    } else if ($(location).attr('href').match('.*/email/.*')) {
        $('#acl').attr('style', 'width: 250');
        email_check_restrict_access();
        $('#restrict_access').change(function(event) {
            email_check_restrict_access();
        });
        email_check_save();
        $('#save').change(function(event) {
            email_check_save();
        });
    }

});

// vim: syntax=javascript ts=4
