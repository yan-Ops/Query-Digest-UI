<?php

// No html errors. They break ajax
	ini_set('html_errors', 0);

// Set default settings here. Can be overridden in config.php.
    $settings = array();
    $settings['sqlColor'] = true;
	$settings['title'] = null;
	$settings['sampleLimit'] = 1;
    
    $settings['defaultColumnVis']['Checksum']      = true;
    $settings['defaultColumnVis']['Count']         = true;
    $settings['defaultColumnVis']['TotalMS']       = true;
    $settings['defaultColumnVis']['AvgMS']         = true;
    $settings['defaultColumnVis']['FirstSeen']     = true;
    $settings['defaultColumnVis']['LastSeen']      = true;
    $settings['defaultColumnVis']['Fingerprint']   = true;
    $settings['defaultColumnVis']['ReviewedOn']    = true;
    $settings['defaultColumnVis']['ReviewedBy']    = true;
    $settings['defaultColumnVis']['Comments']      = true;
    
	require_once('config.php');
    
// If the history_table is blank, hide a few extra columns
    if (!strlen($reviewhost['history_table'])) {
        $settings['defaultColumnVis']['Count']         = false;
        $settings['defaultColumnVis']['TotalMS']       = false;
        $settings['defaultColumnVis']['AvgMS']         = false;
    }
    
	require_once('util.php');
	require_once('libs/Database/Database.php');
    
    $options = array('dsn'                              => $reviewhost['dsn'],
                     PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                     );
    
	Database::connect(null, $reviewhost['user'], $reviewhost['password'], null, null, 'pdo', $options, 'review');

// Needed for SqlParser
	$dbh = new PDO($reviewhost['dsn'], $reviewhost['user'], $reviewhost['password'], $options);

    require_once('libs/sqlquery/SqlParser.php');
	require_once('classes/QueryRewrite.php');
