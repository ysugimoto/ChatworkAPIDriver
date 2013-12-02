<?php if ( ! defined('CHATWORK_BASE_PATH') ) exit('Access denied.');

/**
 * ====================================================================
 * 
 * Chatwork API validator
 * 
 * Management paramster validate.
 * 
 * @author Yoshiaki Sugimoto <sugimoto@wnotes.net>
 * 
 * ====================================================================
 */
class Chatwork_Validator {
    
    /**
     * Whitelist of accepted parameters array
     * @var array
     */
    protected $_taskStatusList  = array('open', 'done');
    protected $_roomActionTypes = array('leave', 'delete');
    protected $_iconPresetList  = array('group',    'check', 'document',
                                        'meeting',  'event', 'project',
                                        'business', 'study', 'security',
                                        'star',     'idea',  'heart',
                                        'magcup',   'beer',  'music',
                                        'sports',   'travel');
    
    // ---------------------------------------------------------------
    
    /**
     * Validate get my task list paramters
     * @return mixed
     */
    public function isValidMyTaskRequest()
    {
        if ( $this->assigned_by_account_id !== null )
        {
            if ( (int)$this->assigned_by_account_id === 0 )
            {
                return 'AccountID must be required.';
            }
        }
        
        // Validate: status string must be "open" or "done".
        if ( $this->status !== null && ! in_array($this->status, $this->_taskStatusList) )
        {
            return 'Invalid task status supplied: ' . $this->stauts;
        }
        
        return TRUE;
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Validate create room request paramters
     * @return mixed
     */
    public function isValidCreateRoomRequest()
    {
        if ( $this->name === null || $this->name === '' )
        {
            return 'name is required field.';
        }
        if ( $this->icon_preset !== null && ! in_array($this->icon_preset, $this->_iconPresetList) )
        {
            return 'Invalid icon preset supplied: ' . $this->icon_preset;
        }
        
        if ( ! $this->members_admin_ids || $this->members_admin_ids === '' )
        {
            return 'members_admin_ids is required field.';
        }
        else if ( ! $this->isCommaSplittedNumbers($this->members_admin_ids) )
        {
            return 'Invalid members_admin_ids supplied: ' . $this->members_admin_ids;
        }
        
        if ( ! $this->isCommaSplittedNumbers($this->members_member_ids) )
        {
            return 'Invalid members_member_ids supplied: ' . $this->members_member_ids;
        }
        
        if ( ! $this->isCommaSplittedNumbers($this->members_readonly_ids) )
        {
            return 'Invalid members_readonly_ids supplied: ' . $this->members_readonly_ids;
        }
        
        $this->members_admin_ids    = $this->formatCommaSplittedString($this->members_admin_ids);
        $this->members_member_ids   = $this->formatCommaSplittedString($this->members_member_ids);
        $this->members_readonly_ids = $this->formatCommaSplittedString($this->members_readonly_ids);
        
        return TRUE;
    }

    // ---------------------------------------------------------------
    
    /**
     * Validate room id
     * @return mixed
     */
    public function isValidRoomID()
    {
        if ( ! ctype_digit((string)$this->room_id) )
        {
            return 'room_id must be integer.';
        }
        
        return TRUE;
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Validate task id
     * @return mixed
     */
    public function isValidTaskID()
    {
        if ( ! ctype_digit((string)$this->task_id) )
        {
            return 'task_id must be integer.';
        }
        
        return TRUE;
    }

    // ---------------------------------------------------------------
    
    /**
     * Validate get room files request parameters
     * @return mixed
     */
    public function isValidGetRoomFiles()
    {
        if ( TRUE !== ($valid = $this->isValidRoomID()) )
        {
            return $valid;
        }
        
        if ( $this->account_id !== null && (int)$this->account_id === 0 )
        {
            return 'account_id must be integer.';
        }
        
        return TRUE;
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Validate get room file detail request prameters
     * @return mixed
     */
    public function isValidGetRoomFileDetail()
    {
        if ( TRUE !== ($valid = $this->isValidRoomID()) )
        {
            return $valid;
        }
        
        if ( $this->file_id !== null && (int)$this->file_id === 0 )
        {
            return 'account_id must be integer.';
        }
        
        if ( $this->create_download_url !== null && ! is_bool($this->create_download_url) )
        {
            return 'create_download_url must be boolan.';
        }
        
        $this->create_download_url = ( $this->create_download_url === TRUE ) ? 'true' : 'false';
        
        return TRUE;
    }

    // ---------------------------------------------------------------
    
    /**
     * Validate update room date request parameters
     * @return mixed
     */
    public function isValidUpdateRoomRequest()
    {
        if ( TRUE !== ($valid = $this->isValidRoomID()) )
        {
            return $valid;
        }
        
        if ( $this->icon_preset !== null && ! in_array($this->icon_preset, $this->_iconPresetList) )
        {
            return 'Invalid icon preset supplied: ' . $this->icon_preset;
        }
        
        return TRUE;
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Validate update room members request paramsters
     * @return mixed
     */
    public function isValidUpdateRoomMembers()
    {
        if ( TRUE !== ($valid = $this->isValidRoomID()) )
        {
            return $valid;
        }
        
        if ( $this->members_admin_ids === null || $this->members_admin_ids === '' )
        {
            return 'members_admin_ids is required field.';
        }
        else if ( ! $this->isCommaSplittedNumbers($this->members_admin_ids) )
        {
            return 'Invalid members_admin_ids supplied: ' . $this->members_admin_ids;
        }
        
        if ( ! $this->isCommaSplittedNumbers($this->members_member_ids) )
        {
            return 'Invalid members_member_ids supplied: ' . $this->members_member_ids;
        }
        
        if ( ! $this->isCommaSplittedNumbers($this->members_readonly_ids) )
        {
            return 'Invalid members_readonly_ids supplied: ' . $this->members_readonly_ids;
        }
        
        $this->members_admin_ids    = $this->formatCommaSplittedString($this->members_admin_ids);
        $this->members_member_ids   = $this->formatCommaSplittedString($this->members_member_ids);
        $this->members_readonly_ids = $this->formatCommaSplittedString($this->members_readonly_ids);
        
        return TRUE;
    }

    // ---------------------------------------------------------------
    
    /**
     * Validate post room message request parameters
     * @return mixed
     */
    public function isValidPostRoomMessage()
    {
        if ( TRUE !== ($valid = $this->isValidRoomID()) )
        {
            return $valid;
        }
        
        if ( $this->body === null || $this->body === '' )
        {
            return 'body is required field.';
        }
        
        return TRUE;
    }
    
    // ---------------------------------------------------------------
    
    /**
     * Validate get room messages request paramsters
     * @return mixed
     */
    public function isValidGetRoomMessage()
    {
        if ( TRUE !== ($valid = $this->isValidRoomID()) )
        {
            return $valid;
        }
        
        if ( $this->message_id === null || $this->message_id === '' )
        {
            return 'message_id is required field.';
        }
        
        return TRUE;
    }

    // ---------------------------------------------------------------
    
    /**
     * Validate add task to room request parameters
     * @return mixed
     */
    public function isValidAddRoomTask()
    {
        if ( TRUE !== ($valid = $this->isValidRoomID()) )
        {
            return $valid;
        }
        
        if ( $this->body === null || $this->body === '' )
        {
            return 'body is required field.';
        }
        
        if ( $this->limit !== null && ! ctype_digit($this->limit) )
        {
            return 'room_id must be integer.';
        }
        
        if ( $this->to_ids === null || $this->to_ids === '' )
        {
            return 'to_ids is required field.';
        }
        else if ( ! $this->isCommaSplittedNumbers($this->to_ids) )
        {
            return 'Invalid to_ids supplied: ' . $this->to_ids;
        }
        
        $this->to_ids = $this->formatCommaSplittedString($this->to_ids);
        
        return TRUE;
    }
    // ---------------------------------------------------------------
    
    /**
     * Validate data is comma splitted numbers
     * @param  string $str
     * @return bool
     */
    protected function isCommaSplittedNumbers($str)
    {
        if ( ! $str )
        {
            return TRUE;
        }
        
        if ( is_array($str) )
        {
            foreach ( $str as $val )
            {
                if ( ! ctype_digit($val) )
                {
                    return FALSE;
                }
            }
        }
        else
        {
            if ( ! preg_match('/^[0-9,]/', $str) )
            {
                return FALSE;
            }
        }
        
        return TRUE;
    }

    // ---------------------------------------------------------------
    
    /**
     * Format comma splitted numbers
     * @param  mixed $data
     * @return string
     */
    protected function formatCommaSplittedString($data)
    {
        $formatted = '';
        if ( $data )
        {
            $formatted = ( is_array($data) )
                           ? implode(',', array_map('trim', $data))
                           : $data;
        }
        
        return $formatted;
    }
}
