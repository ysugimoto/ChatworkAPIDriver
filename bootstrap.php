<?php

/**
 * ====================================================================
 * 
 * Chatwork API Driver bootstrap file
 * 
 * Define Path constants and load Core files.
 * 
 * @author Yoshiaki Sugimoto <sugimoto@wnotes.net>
 * 
 * ====================================================================
 */

// Application boot path definition
define('CHATWORK_BASE_PATH', dirname(__FILE__));

// Load core class file
require_once(CHATWORK_BASE_PATH . '/Chatwork/API.php');

// Register autoloader
spl_autoload_register(array('Chatwork_API', 'load'));


