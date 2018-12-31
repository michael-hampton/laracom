<?php

namespace App\Shop\ChannelPrices\Repositories;

use App\Shop\Base\BaseRepository;
use App\Shop\ChannelPrices\Exceptions\ChannelPriceNotFoundException;
use App\Shop\ChannelPrices\Transformations\ChannelPriceTransformable;
use App\Shop\ChannelPrices\ChannelPrice;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ChannelPriceRepository extends BaseRepository implements ChannelPriceRepositoryInterface {

    use ChannelPriceTransformable;

    /**
     * ProductAttributeRepository constructor.
     * @param ProductAttribute $productAttribute
     */
    public function __construct(ChannelPrice $channelPrice) {
        parent::__construct($channelPrice);
        $this->model = $channelPrice;
    }

    /**
     * @param int $id
     * @return mixed
     * @throws ProductAttributeNotFoundException
     */
    public function findChannelPriceById(int $id) {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ChannelPriceNotFoundException($e);
        }
    }
    
    /**
     * @param array $update
     * @return bool
     */
    public function updateChannelPrice(array $update): bool
    {
        return $this->model->update($update);
    }

    /**
     * 
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return \App\Shop\ChannelPrices\Repositories\Collection
     */
    public function listChannelPrices(string $order = 'id', string $sort = 'desc', array $columns = ['*']) {
        return $this->all($columns, $order, $sort);
    }

}
