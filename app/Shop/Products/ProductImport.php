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
        
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            $firstLine = true;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                
                if($firstLine)
                {
                    // Set the headers:
                    $firstLine = false;
                    $headers = array_flip($row);
        
                    // Validate the headers:
                    if($headers !== $this->expectedHeaders)
                    {
                        throw new Exception('Invalid headers. Aborting import.');
                    }
 
                    // Go to the next row:
                    continue;
                }
                
                list(
                        $order['name'],
                        $order['channels'],
                        $order['categories'],
                        $order['brand'],
                        $order['sku'],
                        $order['description'],
                        $order['quantity'],
                        $order['price'],
                        $order['sale_price'],
                        $order['weight'],
                        $order['mass_unit'],
                        $order['length'],
                        $order['width'],
                        $order['height'],
                        $order['distance_unit'],
                        ) = $data;
                $line++;
                $csv_errors = Validator::make(
                                $order, (new ProductImportRequest())->rules()
                        )->errors();
                
                $order = array_map('trim', $order);
                $categories = array_map('strtolower', explode(',', $order['categories']));
                $arrSelectedCategories = [];
                foreach ($categories as $category) {
                    if (!isset($arrCategories[$category])) {
                        $csv_errors->add('category', "Category is invalid.");
                    } else {
                        $arrSelectedCategories[] = $arrCategories[$category]['id'];
                    }
                }
                
                $arrSelectedChannels = $this->validateChannels($order['channels');                                                
                                                                      $brandName = strtolower($order['brand']);
                if (!isset($arrBrands[$brandName])) {
                    $csv_errors->add('brand', "Brand is invalid.");
                } else {
                    $brand = $arrBrands[$brandName]['id'];
                }
                
                if ($csv_errors->any()) {
                    return redirect()->back()
                                    ->withErrors($csv_errors, 'import')
                                    ->with('error_line', $line);
                }
                $arrProducts[] = [
                    'name' => $order['name'],
                    'sku' => $order['sku'],
                    'description' => $order['description'],
                    'quantity' => $order['quantity'],
                    'price' => $order['price'],
                    'status' => 1,
                    'weight' => $order['weight'],
                    'mass_unit' => $order['mass_unit'],
                    'sale_price' => $order['sale_price'],
                    'length' => $order['length'],
                    'width' => $order['width'],
                    'height' => $order['height'],
                    'distance_unit' => $order['distance_unit'],
                    'categories' => $arrSelectedCategories,
                    'channels' => $arrSelectedChannels,
                    'brand_id' => $brand
                ];
            }
            fclose($handle);
        }
        
        
    }
                                                             
    private function validateCategories($categories) {
        
    }
            
    private function validateBrand($brand) {
        
    }
                                                              
    
    private function validateChannels($channels) {
        $channels = array_map('strtolower', explode(',', $channels]));
                
        $arrSelectedChannels = [];
                
        foreach ($channels as $channel) {
                    
            if (!isset($arrChannels[$channel])) {
                $csv_errors->add('channel', "Channel is invalid.");
            } else {
                $arrSelectedChannels[] = $arrChannels[$channel]['id'];
            }
        }
        
        return $arrSelectedChannels
    }

}
