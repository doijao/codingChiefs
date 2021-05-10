<?php

declare(strict_types=1);

namespace App\Application\Services;

use PDO;
use Slim\App;

class UserServices
{
    /**
     * 
     */
    public function __construct(App $app, array $param)
    {
        $this->container = $app->getContainer();
        $this->getUser($param);
    }
    
    /**
     * Process data if valid or not
     */
    private function isUserValid(array $results, array $param) : void
    {
        /**
         *  Verify the hash against the password entered. 
         *  Print the result depending if they match
         */
        if (password_verify(
                $param['password']
              , $results[0]['password'])) 
        {
            // do not display password
            unset($results[0]['password']);

            $csrf = $this->container->get('csrf');                       
            
            $results[0]['token'] = $csrf->generateToken();
            $this->results = $results;
        } 
        
    }   

    
    /**
     * Only get data from database
     */
    private function getUser(array $param) : void
    {   
        if(isset($param['password']) && isset($param['email'])) {

            $db = $this->container->get(PDO::class);

            $query = $db->query(
                    "SELECT 
                            * 
                        FROM 
                            users 
                        WHERE 
                            email = '{$param['email']}'");

            $results = $query->fetchAll(PDO::FETCH_ASSOC);
        
            $this->isUserValid($results, $param);
        }
    }


    /**
     * Always retun something
     */
    public function getResult() : array
    {
        return (empty($this->results)) ? array('User not found') : $this->results;
    }

}