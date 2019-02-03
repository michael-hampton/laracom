<?php

namespace App\Shop\ChannelPrices;

use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Import\BaseImport;

class ChannelPriceImport extends BaseImport {

    protected $requiredFields = array(
        'product',
        'channel',
        'price',
        'warehouse'
    );

    /**
     *
     * @var type 
     */
    private $arrProducts = [];

    /**
     *
     * @var type 
     */
    private $arrWarehouses = [];

    /**
     *
     * @var type 
     */
    private $arrChannels = [];

    /**
     *
     * @var type 
     */
    private $productRepo;
    
    private $lineCount = 1;
    
    private $objProduct;

    /**
     * 
     * @param WarehouseRepository $warehouseRep
     * @param ChannelRepository $channelRepo
     * @param ProductRepository $productRepo
     */
    public function __construct(
    WarehouseRepository $warehouseRepo, ChannelRepository $channelRepo, ProductRepository $productRepo
    ) {
        parent::__construct();
        $this->productRepo = $productRepo;
        //$this->arrWarehouses = array_change_key_case($categoryRepo->listCategories()->keyBy('name')->toArray(), CASE_LOWER);

        $this->arrChannels = array_change_key_case($channelRepo->listChannels()->keyBy('name')->toArray(), CASE_LOWER);
        $this->arrProducts = array_change_key_case($productRepo->listProducts()->keyBy('name')->toArray(), CASE_LOWER);
    }

    /**
     * 
     * @param type $file
     * @return boolean
     */
    private function importCsv($file) {
        $handle = fopen($file, 'r');
        if (!$handle) {
            return false;
        }
        //Parse the first row, instantiate all the validators
        $row = $this->parseFirstRow($this->fgetcsv($handle));

        if (!empty($this->arrErrors)) {
            return false;
        }

        while (($data = $this->fgetcsv($handle)) !== false) {
            $order = array_map('trim', $this->mapData($data));

            foreach ($order as $key => $params) {
                $this->checkRule(['key' => $key, 'value' => $params]);
            }

            $this->validateChannel($order['channel']);

            $this->checkIfProductExists($order['product']);

            $this->lineCount++;

            if (!empty($this->arrErrors)) {
                continue;
            }

            $this->buildProduct($order, $arrSelectedCategories, $arrSelectedChannels, $brand);
        }

        if (!empty($this->arrErrors)) {
            return false;
        }

        if (!$this->saveImport()) {
            $this->arrErrors[] = 'Failed to save import';
            return false;
        }

        fclose($handle);
        return true;
    }

    /**
     * 
     * @param type $productName
     * @return boolean
     */
    private function checkIfProductExists($productName) {
        $productName = trim(strtolower($productName));
        
        if (!isset($this->arrProducts[$productName])) {
            $this->arrErrors[$this->lineCount]['product'] = 'The product you are trying to create already exists';
            return false;
        }
        
        $objProduct = $this->arrProducts[$productName];
        
        return true;
    }

    private function mapData($data) {
        list(
                $order['product'],
                $order['channel'],
                $order['price'],
                $order['warehouse']
                ) = $data;
        return $order;
    }

    /**
     * 
     * @return boolean
     */
    private function saveImport() {
        foreach ($this->arrProducts as $arrProduct) {
            $arrCategories = $arrProduct['categories'];
            $arrChannels = $arrProduct['channels'];
            unset($arrProduct['categories']);
            unset($arrProduct['channels']);
            $arrProduct['slug'] = str_slug($arrProduct['name']);
            $product = $this->productRepo->createProduct($arrProduct);
            $productRepo = new ProductRepository($product);
            // categories
            if (!empty($arrCategories)) {
                $productRepo->syncCategories($arrCategories);
            }
            // channels
            if (!empty($arrChannels)) {
                $productRepo->syncChannels($arrChannels);
            }
        }
        return true;
    }

    /**
     * Checks a CSV file for validity based on defined policies.
     *
     * Stops on the first violation
     *
     * @access public
     * @param string $file Full path
     * @return boolean
     */
    public function isValid($file) {
        if (!file_exists($file)) {
            $this->arrErrors[$this->lineCount]['file'] = 'File ' . $file . ' does not exist.';
            return false;
        }

        $this->importCsv($file);

        return empty($this->arrErrors);
    }

    private function buildProduct($order, $arrSelectedCategories, $arrSelectedChannels, $brand) {
        $this->arrProducts[] = [
            'product' => $order['product'],
            'price' => $order['price'],
            'warehouse' => $order['warehouse'],
            'channel' => $order['channel']
        ];
    }

    /**
     * 
     * @param type $categories
     * @return type
     */
    private function validateWarehouse($warehouse) {
        if (!isset($this->arrWarehouses[$warehouse])) {
            $this->arrErrors[$this->lineCount]['warehouse'] = "Warehouse is invalid.";
            return false;
        }

        return true;
    }

    private function validateChannel($channel) {

        if (!isset($this->arrChannels[$channel])) {
            $this->arrErrors[$this->lineCount]['channel'] = "Channel is invalid.";
            return false;
        }

        return true;
    }

    private function validateCostPrice($price) {

        if($price < $objProduct['cost_price']) {
            return false;
        }
        
        return true;
    }

}
