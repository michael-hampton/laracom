<?php

namespace App\Shop\ChannelPrices\Repositories;

use App\Shop\Base\BaseRepository;
use App\Shop\ChannelPrices\Exceptions\ChannelPriceNotFoundException;
use App\Shop\ChannelPrices\Exceptions\ChannelPriceInvalidArgumentException;
use App\Shop\ChannelPrices\Transformations\ChannelPriceTransformable;
use App\Shop\ChannelPrices\ChannelPrice;
use App\Shop\Channels\Channel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Shop\Products\Product;

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
     * 
     * @param Channel $channel
     * @return type
     */
    public function getChannelVariations(Channel $channel) {

        return $this->model->where('channel_id', $channel->id)->whereNotNull('attribute_id')->get();
    }

    /**
     * 
     * @param Channel $channel
     * @return type
     */
    public function getChannelProducts(Channel $channel) {

        return $this->model->where('channel_id', $channel->id)->whereNull('attribute_id')->get();
    }

    public function getChannelProduct(Product $objProduct, Channel $channel) {
        return $this->model
                        ->where('product_id', $objProduct->id)
                        ->where('channel_id', $channel->id)
                        ->whereNull('attribute_id')->first();
    }

    /**
     * 
     * @param Product $product
     * @param Channel $channel
     * @return type
     */
    public function getAttributesByParentProduct(Product $product, Channel $channel) {

        return $this->model
                        ->where('channel_id', $channel->id)
                        ->where('product_id', $product->id)
                        ->whereNotNull('attribute_id')->get()
                        ->keyBy('attribute_id');
    }

    /**
     * 
     * @param int $id
     * @param Channel $channel
     * @return type
     */
    public function findChannelPriceByAttributeId(int $id, Channel $channel) {

        return $this->model->where(['attribute_id' => $id, 'channel_id' => $channel->id])->first();
    }

    /**
     * @param array $update
     * @return bool
     */
    public function updateChannelPrice(array $update): bool {
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

    /**
     * 
     * @param Channel $channel
     * @return type
     */
    public function getChannelProductIds(Channel $channel) {
        return $this->model->where('channel_id', $channel->id)->whereNull('attribute_id')->pluck('product_id');
    }

    /**
     * 
     * @param Channel $channel
     * @return type
     */
    public function getAvailiableProducts(Channel $channel) {

        $query = DB::table('products');

        $productIds = $this->getChannelProductIds($channel);

        $result = $query->select('products.*')
                ->whereNotIn('id', $productIds)
                ->get();

        return $result;
    }

    /**
     * 
     * @param int $id
     * @param Channel $channel
     * @return type
     */
    public function deleteAttribute(int $id, Channel $channel) {
        return $this->model->where(['attribute_id' => $id, 'channel_id' => $channel->id])->delete();
    }

    /**
     * 
     * @param Channel $channel
     * @return type
     */
    public function getAssignedProductsForChannel(Channel $channel) {
        $query = DB::table('products');

        $productIds = $this->getChannelProductIds($channel);

        $result = $query->select('products.*')
                ->whereIn('id', $productIds)
                ->get();

        return \App\Shop\Products\Product::hydrate($result->toArray());
    }

    /**
     * Create the courier
     *
     * @param array $params
     * @return Courier
     * @throws CourierInvalidArgumentException
     */
    public function createChannelPrice(array $params): ChannelPrice {
        try {
            return $this->create($params);
        } catch (QueryException $e) {
            throw new ChannelPriceInvalidArgumentException($e->getMessage());
        }
    }

    /**
     * 
     * @param Channel $channel
     * @return type
     */
    public function getAssignedVariationsForChannel(Channel $channel) {
        return $this->model->where('channel_id', $channel->id)->pluck('attribute_id');
    }

}
