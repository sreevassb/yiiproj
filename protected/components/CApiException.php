<?php
/**
 * CApiException class file.
 *
 * @author Shiva Dharana <shiva.purpletalk.com>
 *
 */

/**
 * CApiException represents an exception caused by invalid operations of end-users in RESTful Apis.
 *
 * The HTTP error code can be obtained via {@link statusCode}.
 * Error handlers may use this status code to decide how to format the error page.
 *
 * Shiva Dharana <shiva.purpletalk.com>
 */
class CApiException extends CException
{
    /**
     * @var integer HTTP status code, such as 403, 404, 500, etc.
     */
    public $statusCode;

    public $errors = array();
    
    private static $errorMessages = array(
        '403' => 'Unauthorised request',
        '400' => 'Bad Request',
        '404' => 'Service not found',
        '500' => 'Internal server error'
    );

    /**
     * Constructor.
     * @param integer $status HTTP status code, such as 404, 500, etc.
     * @param string $message error message
     * @param integer $code error code
     */
    public function __construct($status, $message=null, $code=0, $errors = array())
    {
        $this->statusCode = $status;
        $this->errors = $errors;
        
        if(empty($message)){
            $message = @self::$errorMessages[$status];
        }

        parent::__construct($message,$code);
    }
    
    /**
     * Returns http status code
     * @return integer
     */
    public function getStatusCode(){
        return $this->statusCode;
    }

    /**
     * Returns array of errors given by app
     * @return array
     */
    public function getErrors(){
        return $this->errors;
    }
}
