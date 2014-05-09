<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'flexshare';
$app['version'] = '1.6.0';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('flexshare_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('flexshare_app_name');
$app['category'] = lang('base_category_server');
$app['subcategory'] = lang('base_subcategory_file');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'app-mode-core', 
    'app-network-core',
    'app-storage-core >= 1:1.4.7',
    'app-tasks-core',
    'app-certificate-manager-core'
);

$app['core_directory_manifest'] = array(
    '/etc/clearos/flexshare.d' => array(),
    '/var/flexshare' => array(),
    '/var/flexshare/shares' => array(),
    '/var/clearos/flexshare' => array(),
    '/var/clearos/flexshare/backup' => array(),
);

$app['core_file_manifest'] = array( 
    'flexshare_default.conf' => array ( 'target' => '/etc/clearos/storage.d/flexshare_default.conf' ),
    'flexshare.conf' => array(
        'target' => '/etc/clearos/flexshare.conf',
        'mode' => '0600',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'flexshare' => array(
        'target' => '/usr/sbin/flexshare',
        'mode' => '0755',
    ),
    'updateflexperms' => array(
        'target' => '/usr/sbin/updateflexperms',
        'mode' => '0755',
    ),
    'app-flexshare.cron' => array(
        'target' => '/etc/cron.d/app-flexshare',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
);
