<?php

declare(strict_types=1);

namespace App\Application\Services;

use Slim\App;
use PDO;

class ProductServices
{

    public function __construct(App $app, array $param)
    {
        $this->container = $app->getContainer();
        $this->getProducts($param);
    }

    
    /**
     * Get all product data from database
     */
    private function getProducts(array $param)
    {
         /** Initialize database */
         $db = $this->container->get(PDO::class);        

         /** Set default search */
         $searchString = "";    
         
         /**
          * try to get filtered value
          */
         if($param['filter'] != 'all') {
             $searchString = " AND name LIKE '%{$param['filter']}%'";
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
          
         $this->results = $this->build($results);
        
    }

    /**
     * Always retun something
     */
    public function getResult() : array
    {
        return (empty($this->results)) ? array('Product not found') : $this->results;
    }


    /**
    * Now that we have all the data,
    * let's format it them accoding to needs    
    */
    public function build($elements, $parentId = 0)
    {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->build($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
    
        return $branch;
    }


}