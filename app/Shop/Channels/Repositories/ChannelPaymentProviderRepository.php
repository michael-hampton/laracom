<?php

namespace App\Shop\Channels\Repositories;

use App\Shop\Base\BaseRepository;
use App\Shop\Channels\Exceptions\ChannelInvalidArgumentException;
use App\Shop\Channels\Exceptions\ChannelNotFoundException;
use App\Shop\Channels\ChannelPaymentProvider;
use App\Shop\Products\Product;
use App\Shop\Channels\Transformations\ChannelTransformable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use App\Shop\Channels\PaymentProvider;
use Illuminate\Support\Collection;
use App\Shop\Channels\Channel;
use Illuminate\Support\Facades\DB;
use App\Shop\Channels\Repositories\Interfaces\ChannelPaymentProviderRepositoryInterface;

class ChannelPaymentProviderRepository extends BaseRepository implements ChannelPaymentProviderRepositoryInterface {

    use ChannelTransformable;

    /**
     * ChannelRepository constructor.
     * @param Channel $channel
     */
    public function __construct(ChannelPaymentProvider $channelPaymentProvider) {
        parent::__construct($channelPaymentProvider);
        $this->model = $channelPaymentProvider;
    }

    /**
     * Create the channel
     *
     * @param array $params
     * @return Channel
     */
    public function createChannelPaymentProvider(array $params): ChannelPaymentProvider {

        try {
            $channelPaymentProvider = new ChannelPaymentProvider($params);
            $channelPaymentProvider->save();
            return $channelPaymentProvider;
        } catch (QueryException $e) {
            throw \Exception($e->getMessage());
        }
    }

    /**
     * Update the channel
     *
     * @param array $data
     *
     * @return bool
     * @throws ChannelInvalidArgumentException
     */
    public function updateChannelPaymentProvider(array $data): bool {
        try {
            return $this->model->where('id', $this->model->id)->update($data);
        } catch (QueryException $e) {
            throw new \Exception($e);
        }
    }

    public function getProvidersForChannel(Channel $channel) {

        $query = DB::table('channel_payment_providers');

        $query->join('payment_provider', 'payment_provider.id', '=', 'channel_payment_providers.payment_provider_id')
                ->where('channel_payment_providers.channel_id', $channel->id)
                ->select('payment_provider.*');

        $result = $query->get();

        return PaymentProvider::hydrate($result->toArray());

        // return $this->model->where('channel_id', $channel->id)->pluck('payment_provider_id');
    }

    /**
     * 
     * @param PaymentProvider $objPaymentProvider
     */
    public function deleteChannelFromProvider(PaymentProvider $objPaymentProvider) {
        $this->model->where('payment_provider_id', $objPaymentProvider->id)->delete();
    }

}
