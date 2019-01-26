<?php

namespace App\Shop\Products;

use App\Shop\Brands\Brand;
use App\Shop\Categories\Category;
use App\Shop\Channels\Channel;
use App\Shop\ProductAttributes\ProductAttribute;
use App\Shop\ProductImages\ProductImage;
use Gloudemans\Shoppingcart\Contracts\Buyable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ProductImport {
    
    private $expectedHeaders = array(
    'sku',
    'name',
    'price',
    'qty'
);
    
    private $requiredFields = array(
    'sku',
    'name',
    'price'
);

    public function __construct() {
       
    }
    
    public function importCsv() {
        
        if($firstLine)
    {
        // Set the headers:
        $firstLine = false;
        $headers = array_flip($row);
        
        // Validate the headers:
        if($headers !== $expectedHeaders)
        {
            throw new Exception('Invalid headers. Aborting import.');
        }
 
        // Go to the next row:
        continue;
    }
    }

}
