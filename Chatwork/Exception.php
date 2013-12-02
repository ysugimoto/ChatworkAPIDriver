<?php if ( ! defined('CHATWORK_BASE_PATH') ) exit('Access denied.');

/**
 * ====================================================================
 * 
 * Chatwork API Exception class
 * 
 * Extend RuntimeException.
 * 
 * @author Yoshiaki Sugimoto <sugimoto@wnotes.net>
 * 
 * ====================================================================
 */
class Chatwork_Exception extends RuntimeException {
    
    public function __construct($message)
    {
        $this->message = $message;
    }
}
