<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Shop\Channels\Repositories;

use App\Shop\Channels\Channel;
use App\Shop\Channels\PaymentProvider;
use App\Shop\Base\BaseRepository;
use App\Shop\Channels\ChannelPaymentDetails;

/**
 * Description of ChannelPaymentDetailsRepository
 *
 * @author michael.hampton
 */
class ChannelPaymentDetailsRepository extends BaseRepository {

    /**
     * 
     * @param ChannelPaymentDetails $channelPaymentDetails
     */
    public function __construct(ChannelPaymentDetails $channelPaymentDetails) {
        parent::__construct($channelPaymentDetails);
        $this->model = $channelPaymentDetails;
    }

    /**
     * 
     * @param Channel $channel
     */
    public function getPaymentDetailsForChannel(Channel $channel, PaymentProvider $paymentProvider) {
        return $this->model
                        ->where('channel_id', $channel->id)
                        ->where('payment_provider_id', $paymentProvider->id)
                        ->get()
                        ->first();
    }

}
