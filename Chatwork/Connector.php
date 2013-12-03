<?php if ( ! defined('CHATWORK_BASE_PATH') ) exit('Access denied.');

/**
 * ====================================================================
 * 
 * Chatwork API service connector
 * 
 * Management request to Chatwork API server.
 * Supported cURL or Socket connection.
 * 
 * @author Yoshiaki Sugimoto <sugimoto@wnotes.net>
 * 
 * ====================================================================
 */
class Chatwork_Connector {
   
    /**
     * Request class instance
     * @var Request
     */
    protected $req;
    
    
    /**
     * Request URI
     * @var string
     */
    protected $uri;
    
    
    /**
     * Request method
     * @var string
     */
    protected $method   = 'GET';
    
    
    /**
     * Request headers
     * @var array
     */
    protected $header   = array();
    
    
    /**
     * Error string
     * @var string
     */
    protected $_error;
    
    
    /**
     * Post parameters
     * @var string
     */
    protected $postBody = '';
    
    /**
     * Connection timeout
     * @var int
     */
    public $connectTimeout = 30;
    
    /**
     * Request timeout
     * @var int
     */
    public $timeout = 30;
    
    /**
     * API Key
     * @var string
     */
    protected $apiKey;
    
    /**
     * Constructor
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }
    
    /**
     * Send request
     * 
     * @access public
     * @param  string $method
     * @param  string $uri
     * @param  array $header
     * @param  string $postBody
     * @return object
     */
    public function request($method = FALSE, $uri = '', $header = array(), $postBody = '')
    {
        $method   = ( $method )            ? strtoupper($method) : $this->method;
        $uri      = ( ! empty($uri) )      ? $uri                : $this->uri;
        $header   = ( count($header) > 0 ) ? $header             : $this->header;
        $postBody = ( ! empty($postBody) ) ? $postBody           : $this->postBody;
        
        // Send request on supported platform
        return ( extension_loaded('curl') )
                  ? $this->_curlRequest($method, $uri, $header, $postBody)
                  : $this->_socketRequest($method, $uri, $header, $postBody);
    }

    // ---------------------------------------------------------------
    
    
    /**
     * Send cURL request
     * 
     * @access protected
     * @param  string $method
     * @param  string $uri
     * @param  array  $header
     * @param  string $postBody
     * @return object
     */
    protected function _curlRequest($method, $uri, $header, $postBody)
    {
        $handle    = curl_init();
        $userAgent = ( isset($_SERVER['HTTP_USER_AGENT']) ) ? $_SERVER['HTTP_USER_AGENT'] : 'ChatworkAPI Connector';
        $header[]  = 'X-ChatWorkToken: ' . $this->apiKey;
        curl_setopt_array(
                $handle,
                array(
                    CURLOPT_USERAGENT      => $userAgent,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
                    CURLOPT_TIMEOUT        => $this->timeout,
                    CURLOPT_HTTPHEADER     => (count($header) > 0) ? $header : array('Except:'),
                    CURLOPT_HEADER         => FALSE
                )
        );
        
        switch ( $method )
        {
            case 'POST':
                curl_setopt($handle, CURLOPT_POST, TRUE);
                if ( $postBody != '' )
                {
                    curl_setopt($handle, CURLOPT_POSTFIELDS, $postBody);
                }
                break;
            case 'PUT':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ( $postBody != '' )
                {
                    curl_setopt($handle, CURLOPT_POSTFIELDS, $postBody);
                }
                break;
            case 'DELETE':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if ( $postBody != '' )
                {
                    curl_setopt($handle, CURLOPT_POSTFIELDS, $postBody);
                }
                break;
        }
        curl_setopt($handle, CURLOPT_URL, $uri);
        //curl_setopt($handle, CURLINFO_HEADER_OUT, 1);
        
        $resp = curl_exec($handle);
        if ( ! $resp )
        {
            $resp = FALSE;
        }
        $response         = new stdClass;
        $response->status = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $response->body   = $resp;
        curl_close($handle);
        
        if ( preg_match('/30[1237]/', (string)$response->status) )
        {
            $movedURI = preg_replace('|.+href="([^"]+)".+|is', '$1', $response->body);
            return $this->request($method, $movedURI, $header, $postBody);
        }
        
        return $response;
    }
    
    
    // ---------------------------------------------------------------
    
    
    /**
     * Send socket request
     * 
     * @access protected
     * @param  string $method
     * @param  string $uri
     * @param  array $header
     * @param  string $postBody
     * @return object
     */
    protected function _fsockRequest($method, $uri, $header, $postBody)
    {
        // parse URLs
        $URL = parse_url($uri);
        
        $scheme = $URL['scheme'];
        $path   = $URL['path'];
        $host   = $URL['host'];
        $query  = (isset($URL['query'])) ? '?' . $URL['query'] : '';
        $port   = (isset($URL['port'])) ? $URL['port'] : ($scheme == 'https') ? 443 : 80;
        
        if ( $method === 'PUT' || $method === 'DELETE' )
        {
            if ( empty($query) )
            {
                $query = '?method=' . $method;
            }
            else
            {
                $query .= '&method=' . $method;
            }
        }
        
        $userAgent = ( isset($_SERVER['HTTP_USER_AGENT']) )
                       ? $_SERVER['HTTP_USER_AGENT']
                       : 'ChatworkAPI Connector';
        
        // build request-line-header
        $request = $method . ' ' . $path . $query . ' HTTP/1.1' . "\r\n"
                        . 'Host: ' . $host . "\r\n"
                        . 'User-Agent: ' . $userAgent. "\r\n";
        
        $header[] = 'X-ChatWorkToken: ' . $this->apiKey;
        if ( count($header) > 0 )
        {
            foreach ( $header as $head )
            {
                $request .= $head . "\r\n";
            }
        }
        
        if ( $method === 'POST' )
        {
            $fileUploads   = array();
            $contentLength = 0;
            if ( ! empty($postBody) )
            {
                if ( is_array($postBody) )
                {
                    $post = array();
                    foreach ( $postBody as $key => $param )
                    {
                        if ( substr($param, 0, 1) === '@' )
                        {
                            $file = array($key, substr($param, 1));
                            if ( ! file_exists($file[1]) )
                            {
                                throw new Exception('POST Upload file is not exists.');
                            }
                            // push stack
                            $fileUploads[] = $file;
                            continue;
                        }
                        // create url-encoded array data
                        $post[rawurlencode($key)] = rawurlencode($param);
                    }
                    // replace postBody
                    $postBody = $post;
                }
                else
                {
                    $contentLength = strlen($postBody);
                }
                
            }
            
            if ( count($fileUploads) === 0 )
            {
                $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $request .= 'Content-Length: ' . $contentLength . "\r\n";
                $request .= "\r\n";
                if ( is_array($postBody) )
                {
                    foreach ( $postBody as $key => $val )
                    {
                        $request .= $key . '=' . $val;
                    }
                }
                else
                {
                    $request .= $postBody;
                }
            }
            else
            {
                $boundary = md5('CwApiBoundary' . microtime());
                $postdata = '';
                $request .= 'Content-Type: multipart/form-data; boundary=' . $boundary . "\r\n";
                
                // multipart section --------------------------------------- //
                // text field
                foreach ( $postBody as $key => $post )
                {
                    // note key:post is already encoded.
                    $postdata .= '--' . $boundary . "\r\n";
                    $postdata .= 'Content-Disposition: form-data; name="' . $key . '"' . "\r\n";
                    $postdata .= 'Content-Type: text/plain' . "\r\n";
                    $postdata .= "\r\n";
                    $postdata .= $post . "\r\n";
                }
                
                // file upload
                $mime = Seezoo::$Importer->library('Mimetype');
                foreach ( $fileUploads as $upfile )
                {
                    $postdata .= "--" . $boundary . "\r\n";
                    $postdata .= 'Content-Disposition: form-data; name="' . rawurlencode($upfile[0]) . '"; filename="' . basename($upfile[1]) . '"' . "\r\n";
                    $postdata .= 'Content-Type: ' . $mime->detect($upfile[1]) . "\r\n";
                    $postdata .= "\r\n";
                    $postdata .= file_get_contents($upfile[1]) . "\r\n";
                }
                $postdata .= '--' . $boundary ."--\r\n";
                
                $contentLength = ( function_exists('mb_strlen') )
                                     ? mb_strlen($postdata, 'iso-8859-1')
                                     : strlen($postdata);
                $request .= 'Content-Length: ' . $contentLength . "\r\n";
                $request .= "\r\n";
                $request .= $postdata;
            }
        }
        else 
        {
            $request .= "\r\n";
        }
        
        $fp = @fsockopen($host, $port, $errno, $errstr);
        
        if ( ! $fp )
        {
            return FALSE;
        }
        
        // send request
        // If file upload request, requestdata maybe too long.
        // So, we try to loop request until request is sent all.
        $written = 0;
        for ( ; $written < strlen($request); $written += $fwrite )
        {
            $fwrite = fwrite($fp, substr($request, $written));
            if ( $fwrite === FALSE )
            {
                throw new Exception('Socket send request failed.');
            }
        }
        
        // get response
        $resp = '';
        while ( ! feof($fp) )
        {
            $resp .= fgets($fp, 4096);
        }
        fclose($fp);

        // split header
        $exp = explode("\r\n\r\n", $resp, 2);
        
        if ( count($exp) < 2 )
        {
            $body   = FALSE;
            $status = FALSE;
        }
        else 
        {
            
            // parse response code
            $status = preg_replace('#HTTP/[0-9\.]+\s([0-9]+)\s#u', '$1', $exp[0]);
            $body   = implode("\r\n\r\n", array_slice($exp, 1));
            
            if ( preg_match('/30[1237]/', (string)$response->status) )
            {
                $movedURI = preg_replace('|.+href="([^"]+)".+|is', '$1', $body);
                return $this->request($method, $movedURI, $header, $postBody);
            }
        }
        
        $response = new stdClass;
        $response->status = (int)$status;
        $response->body   = $body;
        
        return $response;
    }
}
