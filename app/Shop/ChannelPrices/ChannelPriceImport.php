<?php

namespace App\Shop\ChannelPrices;

use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Import\BaseImport;
use App\Shop\Channels\Repositories\WarehouseRepository;
use App\Shop\ChannelPrices\Repositories\ChannelPriceRepository;

class ChannelPriceImport extends BaseImport {

    /**
     *
     * @var type 
     */
    protected $expectedHeaders = array(
        'product',
        'channel',
        'price',
        'warehouse'
    );
    
    /**
     *
     * @var type 
     */
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

    /**
     *
     * @var type 
     */
    private $lineCount = 1;

    /**
     *
     * @var type 
     */
    private $objProduct;

    /**
     *
     * @var type 
     */
    private $objChannel;

    /**
     *
     * @var type 
     */
    private $objWarehouse;

    /**
     *
     * @var type 
     */
    private $arrChannelProducts;

    /**
     *
     * @var type 
     */
    private $channelPriceRepo;

    /**
     * 
     * @param WarehouseRepository $warehouseRepo
     * @param ChannelRepository $channelRepo
     * @param ProductRepository $productRepo
     * @param ChannelPriceRepository $channelPriceRepo
     */
    public function __construct(
    WarehouseRepository $warehouseRepo, ChannelRepository $channelRepo, ProductRepository $productRepo, ChannelPriceRepository $channelPriceRepo
    ) {
        parent::__construct();
        $this->productRepo = $productRepo;
        $this->arrWarehouses = array_change_key_case($warehouseRepo->listWarehouses()->keyBy('name')->toArray(), CASE_LOWER);
        $this->arrChannels = array_change_key_case($channelRepo->listChannels()->keyBy('name')->toArray(), CASE_LOWER);
        $this->arrProducts = array_change_key_case($productRepo->listProducts()->keyBy('name')->toArray(), CASE_LOWER);
        $this->channelPriceRepo = $channelPriceRepo;
    }

    /**
     * 
     * @param type $file
     * @return boolean
     */
    private function importCsv($file) {
        $handle = fopen($file, 'r');
        if (!$handle)
        {
            return false;
        }
        //Parse the first row, instantiate all the validators
        $row = $this->parseFirstRow($this->fgetcsv($handle));

        if (!empty($this->arrErrors))
        {
            return false;
        }

        while (($data = $this->fgetcsv($handle)) !== false)
        {
            $order = array_map('trim', $this->mapData($data));

            foreach ($order as $key => $params)
            {
                $this->checkRule(['key' => $key, 'value' => $params]);
            }

            $this->validateChannel($order['channel']);
            $this->validateWarehouse($order['warehouse']);
            $this->checkIfProductExists($order['product']);
            $this->validateCostPrice($order['price']);

            $this->lineCount++;

            if (!empty($this->arrErrors))
            {
                continue;
            }

            $this->addProductToChannel($order);
        }

        if (!empty($this->arrErrors))
        {
            return false;
        }

        if (!$this->saveImport())
        {
            $this->arrErrors[] = 'Failed to save import';
            return false;
        }

        fclose($handle);
        return true;
    }

    /**
     * 
     * @param type $data
     * @return type
     */
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

        foreach ($this->arrChannelProducts as $arrChannelProduct)
        {

            try {
                $this->channelPriceRepo->createChannelPrice($arrChannelProduct);
            } catch (Exception $ex) {
                $this->arrErrors[] = $ex->getMessage();
                return false;
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
        if (!file_exists($file))
        {
            $this->arrErrors[$this->lineCount]['file'] = 'File ' . $file . ' does not exist.';
            return false;
        }

        $this->importCsv($file);

        return empty($this->arrErrors);
    }

    /**
     * 
     * @param type $product
     * @return boolean
     */
    private function addProductToChannel($product) {

        $this->arrChannelProducts[] = [
            'product_id'  => $this->objProduct['id'],
            'description' => $this->objProduct['description'],
            'price'       => $product['price'],
            'warehouse'   => $this->objWarehouse['id'],
            'channel_id'  => $this->objChannel['id']
        ];

        return true;
    }

    /**
     * 
     * @param type $categories
     * @return type
     */
    private function validateWarehouse($warehouse) {

        $warehouse = trim(strtolower($warehouse));

        if (!isset($this->arrWarehouses[$warehouse]))
        {

            $this->arrErrors[$this->lineCount]['warehouse'] = "Warehouse is invalid.";
            return false;
        }

        $this->objWarehouse = $this->arrWarehouses[$warehouse];
        return true;
    }

    /**
     * 
     * @param type $channel
     * @return boolean
     */
    private function validateChannel($channel) {

        if (!isset($this->arrChannels[$channel]))
        {
            $this->arrErrors[$this->lineCount]['channel'] = "Channel is invalid.";
            return false;
        }

        $this->objChannel = $this->arrChannels[$channel];

        return true;
    }

    /**
     * 
     * @param type $price
     * @return boolean
     */
    private function validateCostPrice($price) {

        if ($price < $this->objProduct['cost_price'])
        {
            $this->arrErrors[$this->lineCount]['price'] = 'The price cannot be lower than the cost price of the product';
            return false;
        }

        return true;
    }

    /**
     * 
     * @param type $productName
     * @return boolean
     */
    private function checkIfProductExists($productName) {
        $productName = trim(strtolower($productName));

        if (!isset($this->arrProducts[$productName]))
        {
            $this->arrErrors[$this->lineCount]['product'] = 'The product you are trying to create already exists';
            return false;
        }

        $this->objProduct = $this->arrProducts[$productName];

        return true;
    }

}
