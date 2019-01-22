<?php

namespace App\Shop\Channels\Repositories;

use App\Shop\Base\BaseRepository;
use App\Shop\Channels\Exceptions\ChannelInvalidArgumentException;
use App\Shop\Channels\Exceptions\ChannelNotFoundException;
use App\Shop\Channels\ChannelPaymentProvider;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Products\Product;
use App\Shop\Channels\Transformations\ChannelTransformable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use App\Shop\Channels\Channel;

class ChannelPaymentProviderRepository extends BaseRepository {

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
        return $this->model->where('channel_id', $channel->id)->pluck('payment_provider_id');
    }

}
