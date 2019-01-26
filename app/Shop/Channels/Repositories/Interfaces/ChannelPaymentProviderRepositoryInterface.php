<?php

namespace App\Shop\Channels\Repositories\Interfaces;

use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Channels\ChannelPaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use App\Shop\Channels\Channel;
use App\Shop\Channels\PaymentProvider;

interface ChannelPaymentProviderRepositoryInterface extends BaseRepositoryInterface {

    /**
     * 
     * @param array $params
     */
    public function createChannelPaymentProvider(array $params): ChannelPaymentProvider;

    /**
     * 
     * @param array $data
     */
    public function updateChannelPaymentProvider(array $data): bool;

    /**
     * 
     * @param \App\Shop\Channels\Repositories\Interfaces\Channel $channel
     */
    public function getProvidersForChannel(Channel $channel);

    /**
     * 
     * @param \App\Shop\Channels\Repositories\Interfaces\PaymentProvider $objPaymentProvider
     */
    public function deleteChannelFromProvider(PaymentProvider $objPaymentProvider);
}
