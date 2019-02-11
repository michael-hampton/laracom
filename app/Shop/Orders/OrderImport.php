<?php

namespace App\Shop\Orders;

use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Import\BaseImport;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\VoucherCodes\Repositories\VoucherCodeRepository;
use App\Shop\Vouchers\Repositories\VoucherRepository;
use App\Shop\CourierRates\Repositories\CourierRateRepository;
use App\RabbitMq\Worker;

class OrderImport extends BaseImport {

    /**
     *
     * @var type 
     */
    protected $requiredFields = array(
        'order_id',
        'channel',
        'customer',
        'courier',
        'voucher_code',
        'product',
        'quantity',
        'price'
    );

    /**
     *
     * @var type 
     */
    private $arrOrders = [];

    /**
     *
     * @var type 
     */
    private $arrStatuses;

    /**
     *
     * @var type 
     */
    private $courier;

    /**
     *
     * @var type 
     */
    private $channel;

    /**
     *
     * @var type 
     */
    private $deliveryAddress;

    /**
     *
     * @var type 
     */
    private $voucherAmount = 0;

    /**
     *
     * @var type 
     */
    private $arrCouriers = [];

    /**
     *
     * @var type 
     */
    private $arrCustomers = [];

    /**
     *
     * @var type 
     */
    private $arrVouchers = [];

    /**
     *
     * @var type 
     */
    private $arrProducts = [];

    /**
     *
     * @var type 
     */
    private $arrExistingProducts = [];

    /**
     *
     * @var type 
     */
    private $arrChannels = [];
    private $arrOrderVouchers = [];

    /**
     *
     * @var type 
     */
    private $productRepo;

    /**
     *
     * @var type 
     */
    private $orderTotal = 0;

    /**
     *
     * @var type 
     */
    private $objWorker;

    /**
     *
     * @var type 
     */
    private $objCustomer;

    /**
     *
     * @var type 
     */
    private $objCourierRate;

    /**
     *
     * @var type 
     */
    private $shipping;
    private $lineCount = 1;
    private $voucherCodeRepo;
    private $voucherRepo;

    /**
     * 
     * @param CourierRepository $courierRepo
     * @param OrderStatusRepository $orderStatusRepo
     * @param ChannelRepository $channelRepo
     * @param ProductRepository $productRepo
     * @param CustomerRepository $customerRepo,
     * @param VoucherCodeRepository $voucherCodeRepo
     * @param CourierRateRepository $courierRateRepo
     * * @param VoucherRepository $voucherRepo
     * @param Worker $worker
     */
    public function __construct(
    CourierRepository $courierRepo, OrderStatusRepository $orderStatusRepo, ChannelRepository $channelRepo, ProductRepository $productRepo, CustomerRepository $customerRepo, VoucherCodeRepository $voucherCodeRepo, CourierRateRepository $courierRateRepo, VoucherRepository $voucherRepo, Worker $worker
    ) {
        parent::__construct();
        $this->productRepo = $productRepo;
        $this->arrCouriers = $courierRepo->listCouriers()->keyBy('id');
        $this->arrChannels = $channelRepo->listChannels()->keyBy('id');
        $this->objCustomer = $customerRepo->listCustomers()->keyBy('id');
        $this->arrCustomers = array_change_key_case($this->objCustomer->keyBy('name')->toArray(), CASE_LOWER);
        $this->arrVoucherCodes = $voucherCodeRepo->listVoucherCode()->keyBy('id');
        $this->arrExistingProducts = array_change_key_case($productRepo->listProducts()->keyBy('name')->toArray(), CASE_LOWER);
        $this->objCourierRate = $courierRateRepo;
        $this->arrStatuses = $orderStatusRepo->listOrderStatuses()->keyBy('name');
        $this->arrVouchers = $voucherRepo->listVoucher()->keyBy('id');
        $this->voucherCodeRepo = $voucherCodeRepo;
        $this->voucherRepo = $voucherRepo;
        $this->objWorker = $worker;
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
            $this->validateCourier($order['courier']);
            $this->validateCustomer($order['customer']);
            $this->validateCustomerAddress();
            $this->validateProduct($order['product']);
            $this->buildOrderProduct($order);
            $this->setOrderTotal($order);
            $this->validateVoucher($order['voucher_code'], $order['order_id']);
            $this->calculateShippingCost();

            $this->lineCount++;

            if (!empty($this->arrErrors))
            {
                continue;
            }

            $this->buildOrder($order);
        }

        if (!empty($this->arrErrors))
        {
            return false;
        }

        if (!$this->sendToQueue())
        {
            $this->arrErrors[] = 'Failed to add to queue';
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
                $order['order_id'],
                $order['channel'],
                $order['customer'],
                $order['courier'],
                $order['voucher_code'],
                $order['product'],
                $order['quantity'],
                $order['price']
                ) = $data;

        return $order;
    }

    /**
     * 
     * @param type $courier
     * @return boolean
     */
    private function validateCourier($courier) {
        $courier = trim(strtolower($courier));

        $arrCouriers = array_change_key_case($this->arrCouriers->keyBy('name')->toArray(), CASE_LOWER);

        if (!isset($arrCouriers[$courier]))
        {
            $this->arrErrors[$this->lineCount]['courier'] = "Courier is invalid.";
            return false;
        }

        $courierId = $arrCouriers[$courier]['id'];

        $this->courier = $this->arrCouriers[$courierId];
    }

    /**
     * 
     * @param type $customer
     * @return boolean
     */
    private function validateCustomer($customer) {
        $customer = trim(strtolower($customer));

        if (!isset($this->arrCustomers[$customer]))
        {
            $this->arrErrors[$this->lineCount]['customer'] = "Customer is invalid.";
            return false;
        }

        $this->customer = $this->arrCustomers[$customer];
    }

    /**
     * 
     * @param type $order
     * @return boolean
     */
    private function setOrderTotal($order) {

        $this->orderTotal += $order['price'];

        if (isset($this->arrOrders[$order['order_id']]['total']) && !empty($this->arrOrders[$order['order_id']]['total']))
        {
            $this->orderTotal += $this->arrOrders[$order['order_id']]['total'];
        }

        return true;
    }

    /**
     * 
     * @param type $order
     * @return boolean
     */
    private function buildOrderProduct($order) {

        if (empty($this->product))
        {

            $this->arrErrors[$this->lineCount]['product'] = 'Invalid product';
            return false;
        }

        $this->arrProducts[$order['order_id']][] = array(
            'product'  => $this->product['name'],
            'id'       => $this->product['id'],
            'quantity' => $order['quantity']
        );

        return true;
    }

    /**
     * 
     * @return boolean
     */
    private function validateCustomerAddress() {

        $customerId = $this->customer['id'];

        if (empty($customerId))
        {

            $this->arrErrors[$this->lineCount]['customer'] = 'Invalid customer';
            return false;
        }

        $objCustomer = $this->objCustomer[$customerId];

        $this->deliveryAddress = $objCustomer->addresses->first();

        return !empty($this->deliveryAddress);
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
     * @param type $order
     * @return boolean
     */
    private function buildOrder($order) {

        $os = $this->arrStatuses['Waiting Allocation'];

        $this->arrOrders[$order['order_id']] = [
            'reference'       => md5(uniqid(mt_rand(), true) . microtime(true)),
            'courier_id'      => $this->courier->id,
            'customer_id'     => $this->customer['id'],
            'voucher_code'    => !empty($this->objVoucher) ? $this->objVoucher->id : null,
            'voucher_id'      => !empty($this->objVoucher) ? $this->objVoucher : null,
            'address_id'      => $this->deliveryAddress->id,
            'order_status_id' => $os->id,
            'payment'         => 'import',
            'discounts'       => $this->voucherAmount,
            'total_shipping'  => $this->shippingCost,
            'total_products'  => 0,
            'total'           => $this->orderTotal,
            'total_paid'      => 0,
            'delivery_method' => $this->shipping,
            'channel'         => $this->channel,
            'tax'             => 0,
        ];

        $this->arrOrders[$order['order_id']]['products'] = $this->arrProducts[$order['order_id']];

        return true;
    }

    /**
     * 
     * @param type $categories
     * @return type
     */
    private function validateVoucher($voucherCode, $orderId) {

        $voucherCode = trim(strtolower($voucherCode));

        if (empty($voucherCode) || (isset($this->arrOrderVouchers[$orderId]) && in_array($voucherCode, $this->arrOrderVouchers[$orderId])))
        {

            return true;
        }

        $arrVoucherCodes = array_change_key_case($this->arrVoucherCodes->keyBy('voucher_code')->toArray(), CASE_LOWER);

        if (!isset($arrVoucherCodes[$voucherCode]))
        {
            $this->arrErrors[$this->lineCount]['voucher_code'] = "Voucher Code is invalid.";
            return false;
        }

        $voucherId = $arrVoucherCodes[$voucherCode]['voucher_id'];
        $voucherCodeId = $arrVoucherCodes[$voucherCode]['id'];

        if (!isset($this->arrVouchers[$voucherId]))
        {

            $this->arrErrors[$this->lineCount]['voucher_code'] = "Voucher Code is invalid.";
            return false;
        }

        //$this->objVoucher = $this->arrVoucherCodes[$voucherCodeId];
        $voucher = $this->arrVouchers[$voucherId];
        $voucherAmount = $voucher->amount;

        $this->objVoucher = $this->voucherCodeRepo->validateVoucherCode($this->channel, $voucherCode, null, $this->voucherRepo, false);


        if (!$this->objVoucher)
        {
            $this->arrErrors[$this->lineCount]['voucher_code'] = "Voucher Code is invalid.";
            return false;
        }

        switch ($voucher->amount_type)
        {
            case 'percentage':
                $this->orderTotal = $this->orderTotal - ($this->orderTotal * ($voucherAmount / 100));
                break;
            case 'fixed':
                $this->orderTotal -= $voucherAmount;
                break;
        }

        $this->orderTotal = round($this->orderTotal, 2);

        //$this->voucherAmount = $this->arrVouchers[$voucherId]->amount;
        $this->arrOrderVouchers[$orderId][] = $voucherCode;

        return true;
    }

    /**
     * 
     * @param type $brand
     * @return boolean
     */
    private function validateProduct($product) {
        $product = trim(strtolower($product));

        if (!isset($this->arrExistingProducts[$product]))
        {

            $this->arrErrors[$this->lineCount]['product'] = "Product is invalid.";
            return false;
        }

        $this->product = $this->arrExistingProducts[$product];
    }

    /**
     * 
     * @return boolean
     */
    private function calculateShippingCost() {

        if (empty($this->courier))
        {
            $this->arrErrors[$this->lineCount]['courier'] = 'invalid courier';
            return false;
        }

        $this->shipping = $this->objCourierRate->findShippingMethod($this->orderTotal, $this->courier, $this->channel, $this->deliveryAddress->country_id);

        $this->shippingCost = 0;

        if (!empty($this->shipping))
        {
            $this->shippingCost = $this->shipping->cost;
        }

        $this->orderTotal += $this->shippingCost;

        return true;
    }

    /**
     * 
     * @param type $channel
     * @return boolean
     */
    private function validateChannel($channel) {
        $channel = trim($channel);

        $arrChannels = array_change_key_case($this->arrChannels->keyBy('name')->toArray(), CASE_LOWER);

        if (!isset($arrChannels[$channel]))
        {
            $this->arrErrors[$this->lineCount]['channel'] = "Channel is invalid.";
            return false;
        }

        $channelId = $arrChannels[$channel]['id'];

        $this->channel = $this->arrChannels[$channelId];
        return true;
    }

    /**
     * 
     * @return boolean
     */
    private function sendToQueue() {

        $this->objWorker->execute(json_encode($this->arrOrders));

        return true;
    }

}
