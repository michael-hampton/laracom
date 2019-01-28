
php
namespace App\Shop\Orders;
use App\Shop\Categories\Repositories\CategoryRepository;
use App\Shop\Brands\Repositories\BrandRepository;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Import\BaseImport;
class OrderImport extends BaseImport {
    protected $requiredFields = array(
        'name',
        'channels',
        'categories',
        'brand',
        'sku',
        'description',
        'quantity',
        'price',
        'sale_price',
        'weight',
        'mass_unit',
        'length',
        'width',
        'height',
        'distance_unit'
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
    private $arrBrands = [];
    /**
     *
     * @var type 
     */
    private $arrCategories = [];
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
     * @param CategoryRepository $categoryRepo
     * @param BrandRepository $brandRepo
     * @param ChannelRepository $channelRepo
     * @param ProductRepository $productRepo
     */
    public function __construct(
    CategoryRepository $categoryRepo, BrandRepository $brandRepo, ChannelRepository $channelRepo, ProductRepository $productRepo
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


            $order = array_map('trim', $order);
            
            foreach ($order as $key => $params) {
                $this->checkRule(['key' => $key, 'value' => $params]);
            }
            

            //$arrSelectedCategories = $this->validateCategories($order['categories']);
            $channel = $this->validateChannels($order['channel']);
            
            //$brand = $this->validateBrand($order['brand']);
            
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

    private function buildOrderProduct($order)
    {
            $this->arrProducts[$order['order_id']][] = array(
                    'product' => $product->name,
                    'id' => $product->id,
                    'quantity' => $order['quantity']
                );
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
            $this->arrErrors[] = 'File ' . $file . ' does not exist.';
            return false;
        }
        $this->importCsv($file);
        return empty($this->arrErrors);
    }
    private function buildOrder($order, $arrSelectedCategories, $arrSelectedChannels, $brand) {
            $this->arrOrders[$order['order_id']] = [
                    'reference' => md5(uniqid(mt_rand(), true) . microtime(true)),
                    'courier_id' => $courier->id,
                    'customer_id' => $customer[0]->id,
                    'voucher_code' => $voucherCodeId,
                    'voucher_id' => !empty($order['voucher_code']) ? $order['voucher_code'] : null,
                    'address_id' => $deliveryAddress->id,
                    'order_status_id' => $os->id,
                    'payment' => 'import',
                    'discounts' => $voucherAmount,
                    'total_shipping' => $shippingCost,
                    'total_products' => 0,
                    'total' => $orderTotal,
                    'total_paid' => 0,
                    'delivery_method' => $shipping,
                    'channel' => $channel,
                    'tax' => 0,
                ];
    }
    /**
     * 
     * @param type $categories
     * @return type
     */
    private function validateCategories($categories) {
        $categories = $this->normalizeRow(explode(',', $categories));
        $arrSelectedCategories = [];
        foreach ($categories as $category) {
            if (!isset($this->arrCategories[$category])) {
                $this->arrErrors['category'] = "Category is invalid.";
                continue;
            }
            $arrSelectedCategories[] = $this->arrCategories[$category]['id'];
        }
        return $arrSelectedCategories;
    }
    /**
     * 
     * @param type $brand
     * @return boolean
     */
    private function validateBrand($brand) {
        $brandName = strtolower($brand);
        if (!isset($this->arrBrands[$brandName])) {
            $this->arrErrors['brand'] = "Brand is invalid.";
            return false;
        }
        $brand = $this->arrBrands[$brandName]['id'];
        return $brand;
    }
    private function validateChannels($channel) {
        $channel = trim($channel);
        
        
            if (!isset($this->arrChannels[$channel])) {
                $this->arrErrors['channel'] = "Channel is invalid.";
                return false;
            }
            
        return $this->arrChannels[$channel];
    }
}
