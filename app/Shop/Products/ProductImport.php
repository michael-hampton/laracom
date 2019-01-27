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
	
	/**
	 * Delimiter character
	 *
	 * @access protected
	 * @var string
	 */
	protected $delimiter = ',';
	
	/**
	 * Enclosure character
	 *
	 * @access protected
	 * @var string
	 */
	protected $enclosure = '"';
    
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

    public function __construct(
        CategoryRepositoryInterface $categoryRepo, 
        BrandRepositoryInterface $brandRepo, 
        ChannelRepositoryInterface $channelRepo
    ) {
        $this->arrCategories = array_change_key_case($categoryRepo->listCategories()->keyBy('name')->toArray(), CASE_LOWER);
        $this->arrBrands = array_change_key_case($brandRepo->listBrands()->keyBy('name')->toArray(), CASE_LOWER);
        $this->arrChannels = array_change_key_case($channelRepo->listChannels()->keyBy('name')->toArray(), CASE_LOWER);
    }
    
    public function importCsv() {
	    
	    		
	    $handle = fopen($file, 'r');
		
	    if(!$handle) {
		    return false;
	    }
	    
	    //Parse the first row, instantiate all the validators
            $row = $this->parseFirstRow($this->fgetcsv($handle));
		
	    if(!empty($this->arrErrors)) {
		    return false;
	    }
        
       
            $firstLine = true;
            while(($data = $this->fgetcsv($handle)) !== false) {
                
                /*if($firstLine)
                {
                    // Set the headers:
                    $firstLine = false;
                    
                    $this->parseFirstRow($data);
        
                    // Validate the headers:
                    //$this->validateHeader($data);
 
                    // Go to the next row:
                    continue;
                }*/
                
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
                
                
                $order = array_map('trim', $order);
                $arrSelectedCategories = $this->validateCategories($order['categories']);
                $arrSelectedChannels = $this->validateChannels($order['channels');                                                
                $brand = $this->validateBrand($order['brand']);
                
                if(!empty($arrErrors)) {
                    return false;
                }
                
                $this->buildProduct($order);
                                                                      
            }
            fclose($handle);
        
    }
                                                                      
        	/**
	 * Logs missing required fields
	 *
	 * @access protected
	 * @param array $row
	 * @param array $and
	 * @param array $or
	 * @return void
	 */
	private function logMissingRequiredFields(array $row, array $and = [], array $or = []) {
		
        if (!empty($and)){
			$required = implode('", "', array_diff($and, $row));
			
            if (!empty($required)){
				$this->arrErrors[] = sprintf(
					'The following missing columns are required: "%s".',
					$required
				);
			}
		}
        
		if(!empty($or)){
			$logOrError = function($fields) use ($row){
				$diff = array_diff($fields, $row);
				if (!count($diff)){
					$this->arrErrors[] = sprintf(
						'At least one of the following columns is required: "%s".',
						implode($diff, '", "')
					);
				}
			};
			
            array_walk($or, $logOrError->bindTo($this));
		}
	}                                                     
                                                                      
    
                                                                      
     /**
	 * Normalizes the data in a row.
	 *
	 * @access protected
	 * @param array $row
	 * @return array
	 */
	private function normalizeRow(array $row) {
		return array_filter(array_map('trim', array_map('strtolower', $row)));
	}
                                                                      
     /**
	 * Parses the first row
	 *
	 * Checks for duplicate column names and ensures all required fields are present
	 *
	 * @param array $data
	 * @access protected
	 * @return array $row normalized
	 */
	protected function parseFirstRow(array $row) {
		$row = $this->normalizeRow($row);
		$duplicateKeys = array_diff_key($row, array_unique($row));
		
        if(!empty($duplicateKeys)) {
			$duplicateKeys = implode($duplicateKeys, '", "');
			$this->arrErrors[] = sprintf('The following columns are duplicated: "%s".', $duplicateKeys);
		}
        
		if(empty($this->arrErrors)) {
			$this->checkRequiredFields($row);
		}
		return $row;
	}
                                                                      
    	/**
	 * Verifies that required fields are all present and logs errors if missing.
	 *
	 * @access protected
	 * @param array $row
	 * @return void
	 */
	private function checkRequiredFields(array $row){
		$required = $this->requiredFields;
		
        // Fields that must all be present
		$and = array_filter($required, 'is_string');
		
        // Fields where at least one must be present
		$or = array_filter($required, 'is_array');
		
        /**
		 * The following block checks if required fields are all present
		 * and logs any errors errors
		 */
		if (
			// number of fields is less than the required count
			count($row) < count($required) ||
			// $or fields are required, but not present
			(!empty($or) && !$this->orFieldsValid($or, $row)) ||
			// remaining fields are not present
			count(array_intersect($and, $row)) !== count($and)
		){
			$this->logMissingRequiredFields($row, $and, $or);
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
                                                                      
    /**
	 * Verifies that required fields are all present and logs errors if missing.
	 *
	 * @access protected
	 * @param array $row
	 * @return void
	 */
	protected function checkRequiredFields(array $row){
		$required = $this->requiredFields;
        
		// Fields that must all be present
		$and = array_filter($required, 'is_string');
        
		// Fields where at least one must be present
		$or = array_filter($required, 'is_array');
        
		/**
		 * The following block checks if required fields are all present
		 * and logs any errors errors
		 */
		if (
			// number of fields is less than the required count
			count($row) < count($required) ||
            // $or fields are required, but not present
			(!empty($or) && !$this->orFieldsValid($or, $row)) ||
			// remaining fields are not present
			count(array_intersect($and, $row)) !== count($and)
		){
			$this->logMissingRequiredFields($row, $and, $or);
		}
	}
                                                                      
    /**
	 * Given a file pointer resource, return the next row from the file
	 *
	 * @access public
	 * @param Resource $handle
	 * @return array|null|false
	 * @throws \InvalidArgumentException If $handle is not a valid resource
	 */
	public function fgetcsv($handle){
		$result = fgetcsv($handle, 0, $this->delimiter, $this->enclosure, $this->escape);
		if ($result === null){
			throw new \Exception('File pointer resource used in fgetcsv is invalid');
		}
		return $result;
	}
                                                                      
    /**
	 * Logs missing required fields
	 *
	 * @access protected
	 * @param array $row
	 * @param array $and
	 * @param array $or
	 * @return void
	 */
	protected function logMissingRequiredFields(array $row, array $and = [], array $or = []) {
		if (!empty($and)){
			$required = implode('", "', array_diff($and, $row));
			if (!empty($required)){
				$this->errors[] = sprintf(
					'The following missing columns are required: "%s".',
					$required
				);
			}
		}
		if(!empty($or)){
			$logOrError = function($fields) use ($row){
				$diff = array_diff($fields, $row);
				if (!count($diff)){
					$this->errors[] = sprintf(
						'At least one of the following columns is required: "%s".',
						implode($diff, '", "')
					);
				}
			};
			array_walk($or, $logOrError->bindTo($this));
		}
	}
                                                             
    private function validateCategories($categories) {
        
        $categories = $this->normalizeRow(explode(',', $categories));
                
        $arrSelectedCategories = [];
                
        foreach ($categories as $category) {
                    
            if (!isset($arrCategories[$category])) {
                $this->arrErrors['category'] = "Category is invalid.";
                continue;
            }
                
            $arrSelectedCategories[] = $arrCategories[$category]['id'];
            
        }
        
        return $arrSelectedCategories;
    }
            
    private function validateBrand($brand) {
        
        $brandName = strtolower($brand);
                
        if (!isset($arrBrands[$brandName])) {
            $this->arrErrors['brand'] = "Brand is invalid.";
            continue;
        }
            
        $brand = $arrBrands[$brandName]['id'];
        
        
        return $brand;
    }
                                                                      
    /**
	 * Checks if arrays of fields in `$or` have at least one value present in `$fields`.
	 *
	 * @access protected
	 * @param array $or
	 * @param array $fields
	 * @return boolean
	 */
	protected function orFieldsValid(array $or, array $fields) {
		$valid = true;
		foreach($or as $requiredFields){
			$valid = count(array_intersect($requiredFields, $fields)) > 0;
			if (!$valid){
				break;
			}
		}
		return $valid;
	}
                                                              
    
    private function validateChannels($channels) {
        $channels = $this->normalizeRow(explode(',', $channels]));
                
        $arrSelectedChannels = [];
                
        foreach ($channels as $channel) {
                    
            if (!isset($arrChannels[$channel])) {
                $this->arrErrors['channel'] = "Channel is invalid.";
                continue;
            }
                
            $arrSelectedChannels[] = $arrChannels[$channel]['id'];
            
        }
        
        return $arrSelectedChannels
    }

}
