<?php

namespace App\Http\Controllers\Front\Payments;

use App\Http\Controllers\Controller;
use App\Shop\Carts\Repositories\Interfaces\CartRepositoryInterface;
use App\Shop\Vouchers\Repositories\Interfaces\VoucherRepositoryInterface;
use App\Shop\Checkout\CheckoutRepository;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\Channels\Channel;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Shipping\ShippingInterface;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Shippo_Shipment;
use Shippo_Transaction;

class BankTransferController extends Controller {

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepo;

    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepo;

    /**
     * @var VoucherRepositoryInterface
     */
    private $voucherRepo;

    /**
     * @var int $shipping
     */
    private $shippingFee;

    /**
     *
     * @var type 
     */
    private $rateObjectId;

    /**
     *
     * @var type 
     */
    private $shipmentObjId;

    /**
     *
     * @var type 
     */
    private $billingAddress;

    /**
     *
     * @var type 
     */
    private $carrier;

    /**
     *
     * @var type 
     */
    private $voucherId = 0;

    /**
     * BankTransferController constructor.
     *
     * @param Request $request
     * @param CartRepositoryInterface $cartRepository
     * @param ShippingInterface $shippingRepo
     */
    public function __construct(
    Request $request, CartRepositoryInterface $cartRepository, ShippingInterface $shippingRepo, ChannelRepositoryInterface $channelRepository, VoucherRepositoryInterface $voucherRepository
    ) {
        $this->cartRepo = $cartRepository;
        $this->channelRepo = $channelRepository;
        $fee = 0;
        $rateObjId = null;
        $shipmentObjId = null;
        $billingAddress = $request->input('billing_address');

        if ($request->has('rate')) {
            if ($request->input('rate') != '') {

                $rate_id = $request->input('rate');
                $rates = $shippingRepo->getRates($request->input('shipment_obj_id'));

                $rate = collect($rates->results)->filter(function ($rate) use ($rate_id) {
                            return $rate->object_id == $rate_id;
                        })->first();

                /**
                 * Change here
                 */
                $rate = $rates->results[0];

                $fee = $rate->amount;

                $rateObjId = $rate->object_id;
                $shipmentObjId = $request->input('shipment_obj_id');
                $this->carrier = $rate;
            }
        }

        $this->voucherRepo = $voucherRepository;

        if ($request->has('voucherCode')) {

            $voucherCode = $request->input('voucherCode');

            $this->voucherId = null;

            if (!empty($voucherCode)) {
                $this->voucherId = $this->voucherRepo->findVoucherById($voucherCode);
            }
        }

        $this->shippingFee = $fee;
        $this->rateObjectId = $rateObjId;
        $this->shipmentObjId = $shipmentObjId;
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {

        return view('front.bank-transfer-redirect', [
            'subtotal' => $this->cartRepo->getSubTotal(),
            'shipping' => $this->shippingFee,
            'tax' => $this->cartRepo->getTax(),
            'total' => $this->cartRepo->getTotal(2, $this->shippingFee, $this->voucherId),
            'rateObjectId' => $this->rateObjectId,
            'shipmentObjId' => $this->shipmentObjId,
            'billingAddress' => $this->billingAddress
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function store(Request $request) {
        $checkoutRepo = new CheckoutRepository;
        $orderStatusRepo = new OrderStatusRepository(new OrderStatus);
        $os = $orderStatusRepo->findByName('ordered');

        $channel = env('CHANNEL');

        $objChannel = Channel::where('name', $channel)->first();

        $order = $checkoutRepo->buildCheckoutItems([
            'reference' => Uuid::uuid4()->toString(),
            'courier_id' => 1, // @deprecated
            'customer_id' => $request->user()->id,
            'address_id' => $request->input('billing_address'),
            'order_status_id' => $os->id,
            'payment' => strtolower(config('bank-transfer.name')),
            'shipping' => $this->shippingFee,
            'discounts' => request()->session()->has('discount_amount') ? request()->session()->get('discount_amount', 1) : 0,
            'voucher_id' => $this->voucherId,
            'total_products' => $this->cartRepo->getSubTotal(),
            'total' => $this->cartRepo->getTotal(2, $this->shippingFee, $this->voucherId),
            'total_shipping' => $this->shippingFee,
            'total_paid' => 0,
            'channel' => $objChannel,
            'tax' => $this->cartRepo->getTax()
        ]);

        if (env('ACTIVATE_SHIPPING') == 1) {

            $shipment = Shippo_Shipment::retrieve($this->shipmentObjId);

            $details = [
                'shipment' => [
                    'address_to' => json_decode($shipment->address_to, true),
                    'address_from' => json_decode($shipment->address_from, true),
                    'parcels' => [json_decode($shipment->parcels[0], true)]
                ],
                'carrier_account' => $this->carrier->carrier_account,
                'servicelevel_token' => $this->carrier->servicelevel->token
            ];

            $transaction = Shippo_Transaction::create($details);

            if ($transaction['status'] != 'SUCCESS') {
                Log::error($transaction['messages']);
                return redirect()->route('checkout.index')->with('error', 'There is an error in the shipment details. Check logs.');
            }

            $orderRepo = new OrderRepository($order);
            $orderRepo->updateOrder([
                'courier' => $this->carrier->provider,
                'label_url' => $transaction['label_url'],
                'tracking_number' => $transaction['tracking_number']
            ]);
        }

//        $orderRepo = new OrderRepository($order);
//        $orderRepo->updateOrder([
//            'courier' => 1,
//            'label_url' => 'TEST LABEL',
//            'tracking_number' => '1234567890'
//        ]);

        Cart::destroy();

        return redirect()->route('accounts', ['tab' => 'orders'])->with('message', 'Order successful!');
    }

}
