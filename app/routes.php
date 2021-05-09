<?php
declare(strict_types=1);

use App\Application\Helper\treeHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {

    session_start();

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/api/list/{filter}', function (Request $request, Response $response, $args) {        
        
        /** Start session */
        session_destroy();

        /** Initialize database */
        $db = $this->get(PDO::class);        

        /** Set default search */
        $searchString = "";    
        
        /**
         * try to get filtered value
         */
        if($args['filter'] != 'all') {
            $searchString = " AND name LIKE '%{$args['filter']}%'";
        }
        
        /**
         * Assuming we only have few items in the DB,
         * let's get all of them and filter by parent-child         * 
         */
        $query = $db->query(
                    "SELECT  
                        id,
                        name,
                        parent_id 
                    FROM    
                        (select * from products order by parent_id, id) products_sorted,
                        (select @pv := '0') initialisation
                    WHERE find_in_set(parent_id, @pv)
                        AND length(@pv := concat(@pv, ',', id)) $searchString");                    
        
        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        /**
         * Now that we have all the data,
         * let's format it them accoding to needs
         *  App\Application\Helper\treeHelper
         */
        $list = array();       
        $tree = new treeHelper;
        $list = $tree->build($results);
        
        /**
         * Set CSRF Token
         */
        $csrf = $this->get('csrf');
        $nameKey = $csrf->getTokenNameKey();
        $valueKey = $csrf->getTokenValueKey();
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);
        
        $list['token'] = [
            $nameKey => $name,
            $valueKey => $value
        ];
        
        $response->getBody()->write(json_encode($list));

        return $response->withHeader("Content-Type", "application/json");
    })->add('csrf');

    /**
     * Login and generate Token
     * email: doi.jao@gmail.com
     * password: Password@123
     */    
    $app->post('/api/auth', function (Request $request, Response $response) {
       
        $param = $request->getParsedBody();        
        $db = $this->get(PDO::class);

        $query = $db->query("SELECT * FROM users WHERE email = '{$param['email']}'");
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Verify the hash against the password entered. Print the result depending if they match
        if (password_verify($param['password'], $results[0]['password'])) {
            // do not display password
            unset($results[0]['password']);

            $csrf = $this->get('csrf');                       
            
            $results[0]['token'] = $csrf->generateToken();

            $return = $results;
        } 
        else {
            $return = array('User not found');
        }

        $response->getBody()->write(json_encode($return));

        return $response->withHeader("Content-Type", "application/json");
    });

};
