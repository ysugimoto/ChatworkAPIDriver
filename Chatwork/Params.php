<?php if ( ! defined('CHATWORK_BASE_PATH') ) exit('Access denied.');

/**
 * ====================================================================
 * 
 * Chatwork API parameters interface class
 * 
 * Management paramster check, validate, and format.
 * API interface methods required to receive this class instance.
 * 
 * @author Yoshiaki Sugimoto <sugimoto@wnotes.net>
 * 
 * ====================================================================
 */
class Chatwork_Params extends Chatwork_Validator {
    
    /**
     * Parameters stack
     * @var array
     */
    protected $arg = array();
    
    /**
     * Constructor
     */
    public function __construct($args = array())
    {
        $this->arg = ( is_object($args) ) ? get_object_vars($args) : (array)$args;
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Add parameter
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function add($key, $value)
    {
        $this->arg[$key] = $value;
        return $this;
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Remove parameter
     * @param string $key
     * @return $this
     */
    public function remove($key)
    {
        if ( isset($this->arg[$key]) )
        {
            unset($this->arg[$key]);
        }
        
        return $this;
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get parameter value
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->{$key};
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Format Query string paramter
     * @param array $ignores
     * @return string
     */
    public function toURIParams($ignores = array())
    {
        $param = array();
        foreach ( $this->arg as $key => $value )
        {
            if ( $value !== '' && ! in_array($key, $ignores) )
            {
                $param[] = rawurlencode($key) . '=' . rawurlencode($value);
            }
        }
        
        return implode('&', $param);
    }
    
    
    // ---------------------------------------------------------------
    
    /**
     * @Magic method getter
     */
    public function __get($name)
    {
        return ( isset($this->arg[$name]) ) ? $this->arg[$name] : null;
    }
    
    // ---------------------------------------------------------------
    
    /**
     * @Magic method setter
     */
    public function __set($name, $value)
    {
        $this->add($name, $value);
    }
}
