<?php if ( ! defined('CHATWORK_BASE_PATH') ) exit('Access denied.');

/**
 * ====================================================================
 * 
 * Chatwork API Driver Core class
 * 
 * Implement some API call interface methods.
 * 
 * @author Yoshiaki Sugimoto <sugimoto@wnotes.net>
 * 
 * ====================================================================
 */

class Chatwork_API {
    
    /**
     * API Request endpoints
     */
    const REQUEST_BASE   = 'https://api.chatwork.com/v1';
    const ME_PATH        = '/me';
    const MY_STATUS_PATH = '/my/status';
    const MY_TASKS_PATH  = '/my/tasks';
    const CONTACTS_PATH  = '/contacts';
    const ROOMS_PATH     = '/rooms';
    
    /**
     * API parameters endpoints format
     */
    const ROOM_DETAIL         = '/rooms/%d';
    const ROOM_MEMBERS        = '/rooms/%d/members';
    const ROOM_MESSAGES       = '/rooms/%d/messages';
    const ROOM_MESSAGE_DETAIL = '/rooms/%d/messages/%d';
    const ROOM_TASKS          = '/rooms/%d/tasks';
    const ROOM_TASK_DETAIL    = '/rooms/%d/tasks/%d';
    const ROOM_FILES          = '/rooms/%d/files';
    const ROOM_FILE_DETAIL    = '/rooms/%d/files/%d';
    
    /**
     * Autoload handler
     * @access public static
     * @param  string $className
     */
    public static function load($className)
    {
        $classPath = str_replace('_', '/', $className);
        if ( file_exists(CHATWORK_BASE_PATH . '/' . $classPath . '.php') ) {
            require_once(CHATWORK_BASE_PATH . '/' . $classPath . '.php');
        }
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Create request parameters syntax
     * @param array
     * @return Chatwork_Params
     */
    public static function createParams($args)
    {
        return new Chatwork_Params($args);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Constructor
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey     = $apiKey;
        $this->connection = new Chatwork_Connector($apiKey);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get my information data
     * @return stdClass
     */
    public function getInfo()
    {
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . self::ME_PATH
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get my status
     * @return stdClass
     */
    public function getStatus()
    {
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . self::MY_STATUS_PATH
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get my tasks
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function getTasks($params = null)
    {
        if ( ! ($params instanceof Chatwork_Params) )
        {
            $params = new Chatwork_Params();
        }
        if ( TRUE !== ($valid = $params->isValidMyTaskRequest()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $suffix = ( $params->toURIParams() !== '' ) ? '?' . $params->toURIParams() : '';
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . self::MY_TASKS_PATH . $suffix
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get my contact list
     * @return stdClass
     */
    public function getContacts()
    {
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . self::CONTACTS_PATH
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get my chatrooms
     * @return stdClass
     */
    public function getRooms()
    {
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . self::ROOMS_PATH
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get room detail info
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function getRoomDetail(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidRoomID()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . sprintf(self::ROOM_DETAIL, $params->room_id)
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Create new chatroom
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function createRoom(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidCreateRoomRequest()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'POST',
            self::REQUEST_BASE . self::ROOMS_PATH,
            array(),
            $params->toURIParams()
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Upadte room info
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function updateRoom(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidUpdateRoomRequest()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'PUT',
            self::REQUEST_BASE . sprintf(self::ROOM_DETAIL, $params->room_id),
            array(),
            $params->toURIParams(array('room_id'))
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Leave the room
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function leaveRoom(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidRoomID()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $params->action_type = 'leave';
        
        $response = $this->connection->request(
            'DELETE',
            self::REQUEST_BASE . sprintf(self::ROOM_DETAIL, $params->room_id),
            array(),
            $params->toURIParams()
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Delete room
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function deleteRoom(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidRoomID()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $params->action_type = 'delete';
        
        $response = $this->connection->request(
            'DELETE',
            self::REQUEST_BASE . sprintf(self::ROOM_DETAIL, $params->room_id),
            array(),
            $params->toURIParams()
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get room members
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function getRoomMembers(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidRoomID()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . sprintf(self::ROOM_MEMBERS, $params->room_id)
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Update room members
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function updateRoomMembers(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidUpdateRoomMembers()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'PUT',
            self::REQUEST_BASE . sprintf(self::ROOM_MEMBERS, $params->room_id),
            array(),
            $params->toURIParams(array('room_id'))
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get room message posts
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     * @TODO implement
     */
    public function getRoomMessages(Chatwork_Params $params)
    {
        // not implemented at 2013/12/03
        throw new Chatwork_Exception('Sorry, this API has not implemented.');
        /*
        if ( TRUE !== ($valid = $params->isValidRoomID()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . sprintf(self::ROOM_MESSAGES, $params->room_id)
        );
        
        return $this->makeResponse($response);
        */
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Post mesage to room
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function postRoomMessage(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidPostRoomMessage()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'POST',
            self::REQUEST_BASE . sprintf(self::ROOM_MESSAGES, $params->room_id),
            array(),
            $params->toURIParams(array('room_id'))
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get room message detail
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function getRoomMessageDetail(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidGetRoomMessage()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . sprintf(self::ROOM_MESSAGE_DETAIL, $params->room_id, $params->message_id)
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get room task list
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function getRoomTasks(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidRoomID()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . sprintf(self::ROOM_TASKS, $params->room_id)
                               . '?' . $params->toURIParams(array('room_id'))
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Add toom task
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function addRoomTask(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidAddRoomTask()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'POST',
            self::REQUEST_BASE . sprintf(self::ROOM_TASKS, $params->room_id),
            array(),
            $params->toURIParams(array('room_id'))
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get room task detail
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function getRoomTaskDetail(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidRoomID())
            || TRUE !== ($valid = $params->isValidTaskID()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . sprintf(self::ROOM_TASK_DETAIL, $params->room_id, $params->task_id)
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get room uploaded files info
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function getRoomFiles(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidGetRoomFiles()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $suffix = ( $params->toURIParams() !== '' ) ? '?' . $params->toURIParams() : '';
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . sprintf(self::ROOM_FILES, $params->room_id) . $suffix
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Get room uploaded file detail
     * @param  Chatwork_Params $params
     * @return stdClass
     * @throws Chatwork_Exception
     */
    public function getRoomFileDetail(Chatwork_Params $params)
    {
        if ( TRUE !== ($valid = $params->isValidGetRoomFileDetail()) )
        {
            throw new Chatwork_Exception($valid);
        }
        
        $response = $this->connection->request(
            'GET',
            self::REQUEST_BASE . sprintf(self::ROOM_FILES, $params->room_id)
        );
        
        return $this->makeResponse($response);
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Make/format API response
     * @access protected
     * @param  stdClass $response
     * @return stdClass
     * @throws Chatwork_Exception
     */
    protected function makeResponse($response)
    {
        $body = ( function_exists('json_decode') )
                 ? json_decode($response->body)
                 : Chatwork_Json::decode($response->body);
        
        if ( preg_match('/^2[0-9]{2}$/', (string)$response->status) )
        {
            return $body;
        }
        else
        {
           throw new Chatwork_Exception($body->errors[0]);
        }
    }
}