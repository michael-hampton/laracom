
php
namespace App\Shop\Orders;
use App\Shop\Categories\Repositories\CategoryRepository;
use App\Shop\Brands\Repositories\BrandRepository;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Import\BaseImport;
class OrderImport extends BaseImport {
    
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

    private $arrStatuses;

    private $courier;

    private $channel;

    private $deliveryAddress;

    private $voucherAmount = 0;

    private $arrCouriers = [];

    private $arrCustomers = [];

    /**
     *
     * @var type 
     */
    private $arrProducts = [];

    private $arrExistingProducts = [];
    
   
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

    private $orderTotal = 0;

    /**
     * 
     * @param CourierRepository $courierRepo
     * @param OrderStatusRepository $orderStatusRepo
     * @param ChannelRepository $channelRepo
     * @param ProductRepository $productRepo
     * @param CustomerRepository $customerRepo,
     * @param VoucherCodeRepository $voucherCodeRepo
     */
    public function __construct(
        CourierRepository $courierRepo, 
        OrderStatusRepository $orderStatusRepo, 
        ChannelRepository $channelRepo, 
        ProductRepository $productRepo,
        CustomerRepository $customerRepo,
        VoucherCodeRepository $voucherCodeRepo,
        CourierRateRepository $courierRateRepo
    ) {
        parent::__construct();
        $this->productRepo = $productRepo;
        $this->arrCategories = array_change_key_case($categoryRepo->listCategories()->keyBy('name')->toArray(), CASE_LOWER);
        $this->arrBrands = array_change_key_case($brandRepo->listBrands()->keyBy('name')->toArray(), CASE_LOWER);
        $this->arrChannels = array_change_key_case($channelRepo->listChannels()->keyBy('name')->toArray(), CASE_LOWER);
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
        $firstLine = true;
        while (($data = $this->fgetcsv($handle)) !== false) {

            $order = array_map('trim', $this->mapData($data));
            
            foreach ($order as $key => $params) {
                $this->checkRule(['key' => $key, 'value' => $params]);
            }
            
            $this->validateChannel($order['channel']);
            
            if (!empty($this->arrErrors)) {
                return false;
            }
            
           $this->buildOrder($order, $arrSelectedCategories, $arrSelectedChannels, $brand);
        }
        
        if(!$this->saveImport()) {
            $this->arrErrors[] = 'Failed to save import';
            return false;
        }
        
        fclose($handle);
    }

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

    private function validateCourier($courier) {
           $courier = trim(strtolower($courier));
                if (!isset($arrCouriers[$courier])) {
                    $this->arrErrors['courier'] = "Courier is invalid.";
                    return false;
                }

        $this->courier = $arrCouriers[$courier]
    }
     
    private function validateCustomer($customer) {
        $customer = trim(strtolower($customer));
                
        if (!isset($this->arrCustomers[$customer])) {
            $this->arrErrors['customer'] = "Customer is invalid.";
            return false;
        }

        $this->customer = $this->arrCustomers[$customer];
    }

    private function setOrderTotal($order) {
        $this->orderTotal += $order['price'];

        if (isset($this->arrOrders[$order['order_id']]['total']) && !empty($this->arrOrders[$order['order_id']]['total'])) {
                    $this->orderTotal += $this->arrOrders[$order['order_id']]['total'];
                }
    }

    private function buildOrderProduct($order)
    {
            $this->arrProducts[$order['order_id']][] = array(
                    'product' => $product->name,
                    'id' => $product->id,
                    'quantity' => $order['quantity']
                );
    }

    private function validateCustomerAddress() {
        $this->deliveryAddress = $this->customerRepo->findAddresses()->first();
 
    }
    /**
     * 
     * @return boolean
     */
    private function saveImport() {
        
        
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
            $this->arrErrors[] = 'File ' . $file . ' does not exist.';
            return false;
        }
        $this->importCsv($file);
        return empty($this->arrErrors);
    }
    private function buildOrder($order, $arrSelectedCategories, $arrSelectedChannels, $brand) {
            
        $os = $this->arrStatuses['waiting allocation'];

        $this->arrOrders[$order['order_id']] = [
                    'reference' => md5(uniqid(mt_rand(), true) . microtime(true)),
                    'courier_id' => $this->courier->id,
                    'customer_id' => $this->customer,
                    'voucher_code' => $this->voucherCodeId,
                    'voucher_id' => !empty($order['voucher_code']) ? $order['voucher_code'] : null,
                    'address_id' => $this->deliveryAddress->id,
                    'order_status_id' => $os->id,
                    'payment' => 'import',
                    'discounts' => $this->voucherAmount,
                    'total_shipping' => $this->shippingCost,
                    'total_products' => 0,
                    'total' => $this->orderTotal,
                    'total_paid' => 0,
                    'delivery_method' => $this->shipping,
                    'channel' => $this->channel,
                    'tax' => 0,
                ];

               $this->arrOrders[$order['order_id']]['products'] = $arrProducts[$order['order_id']];
    }
    /**
     * 
     * @param type $categories
     * @return type
     */
    private function validateVoucher($voucherCode) {
        
        $voucherCode = trim(strtolower($voucherCode));

        if (!isset($arrVouchers[$voucherCode])) {
              $this->arrErrors['voucher_code'] = "Voucher Code is invalid.";
              return false;
        }
                    
        $voucher_id = $voucherCode->voucher_id;
        $this->objVoucher = $this->voucherRepo->findVoucherById($voucher_id);
        $this->voucherAmount = $objVoucher->amount;

return true;
}
    }
    /**
     * 
     * @param type $brand
     * @return boolean
     */
    private function validateProduct($product) {
        $product = trim(strtolower($product));
        
        if (!isset($this->arrExistingProducts[$product])) {
           
            $this->arrErrors['product'] = "Product is invalid.";
            return false;
        }
        $product = $this->arrExistingProducts[$product]['id'];
        return $product;
    }

    private function calculateShippingCost($courier) {
                $shipping = $objCourierRate->findShippingMethod($this->orderTotal, $courier, $channel, $this->deliveryAddress->country_id);
               
        $shippingCost = 0;
                
        if (!empty($shipping)) {
            $this->shippingCost = $shipping->cost;

        }
                
        $this->orderTotal += $shippingCost;
    }

    private function validateChannels($channel) {
        $channel = trim($channel);
        
        
            if (!isset($this->arrChannels[$channel])) {
                $this->arrErrors['channel'] = "Channel is invalid.";
                return false;
            }
            
        $this->channel = $this->arrChannels[$channel];
        return true;
    }
}
