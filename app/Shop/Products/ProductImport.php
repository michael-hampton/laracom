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
    
    private $arrErrors = [];
    
    private $arrProducts = [];
    
    private $arrBrands = [];
    
    private $arrCategories = [];
    
    private $arrChannels = [];

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
                    
        
                    // Validate the headers:
                    $this->validateHeader($data);
 
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
                $this->arrErrors = Validator::make(
                                $order, (new ProductImportRequest())->rules()
                        )->errors();
                
                $order = array_map('trim', $order);
                $arrSelectedCategories = $this->validateCategories($order['categories']);
                $arrSelectedChannels = $this->validateChannels($order['channels');                                                
                $brand = $this->validateBrand($order['brand']);
                
                if ($this->arrErrors->any()) {
                    return redirect()->back()
                                    ->withErrors($this->arrErrors, 'import')
                                    ->with('error_line', $line);
                }
                
                $this->buildProduct($order);
                                                                      
            }
            fclose($handle);
        }
    }
                                                                      
    private function validateHeader($row) {
        
        $headers = array_flip($row);
        
        if($headers !== $this->expectedHeaders)
                    {
                        throw new Exception('Invalid headers. Aborting import.');
                    }
    }
                                                                      
    private function buildProduct($order) {
        $this->arrProducts[] = [
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
                                                             
    private function validateCategories($categories) {
        
        $categories = array_map('strtolower', explode(',', $categories));
                
        $arrSelectedCategories = [];
                
        foreach ($categories as $category) {
                    
            if (!isset($arrCategories[$category])) {
                $this->arrErrors->add('category', "Category is invalid.");
                continue;
            }
                
            $arrSelectedCategories[] = $arrCategories[$category]['id'];
            
        }
        
        return $arrSelectedCategories;
    }
            
    private function validateBrand($brand) {
        
        $brandName = strtolower($brand);
                
        if (!isset($arrBrands[$brandName])) {
            $this->arrErrors->add('brand', "Brand is invalid.");
            continue;
        }
            
        $brand = $arrBrands[$brandName]['id'];
        
        
        return $brand;
    }
                                                              
    
    private function validateChannels($channels) {
        $channels = array_map('strtolower', explode(',', $channels]));
                
        $arrSelectedChannels = [];
                
        foreach ($channels as $channel) {
                    
            if (!isset($arrChannels[$channel])) {
                $csv_errors->add('channel', "Channel is invalid.");
                continue;
            }
                
            $arrSelectedChannels[] = $arrChannels[$channel]['id'];
            
        }
        
        return $arrSelectedChannels
    }

}
