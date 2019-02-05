<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Shop\Channels\Repositories;

use App\Shop\Channels\Channel;
use App\Shop\Channels\Exceptions\ChannelWarehouseNotFoundException;
use App\Shop\Channels\ChannelWarehouse;
use App\Shop\Base\BaseRepository;
use App\Shop\Channels\Warehouse;
use App\Shop\Channels\Exceptions\ChannelWarehouseInvalidArgumentException;

/**
 * Description of ChannelWarehouseRepository
 *
 * @author michael.hampton
 */
class ChannelWarehouseRepository extends BaseRepository {

    /**
     * 
     * @param ChannelWarehouse $channel_warehouse
     */
    public function __construct(ChannelWarehouse $channel_warehouse) {
        parent::__construct($channel_warehouse);
        $this->model = $channel_warehouse;
    }

    /**
     * 
     * @param array $params
     * @return \App\Shop\Channels\Repositories\ChannelWarehouse
     * @throws ChannelWarehouseInvalidArgumentException
     */
    public function createChannelWarehouse(array $params): ChannelWarehouse {
        try {
            return $this->create($params);
        } catch (QueryException $e) {
            throw new ChannelWarehouseInvalidArgumentException($e->getMessage());
        }

        return true;
    }

    /**
     * @return bool|null
     */
    public function deleteChannelWarehouse() : bool
    {
        return $this->model->delete();
    }
    
      /**
     * @param int $id
     * @return Attribute
     * @throws AttributeNotFoundException
     */
    public function findChannelWarehouseById(int $id) : ChannelWarehouse
    {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ChannelWarehouseNotFoundException($e);
        }
    }

    /**
     * 
     * @param Channel $channel
     */
    public function getWarehousesForChannel(Channel $channel) {
        return $this->model->where('channel_id', $channel->id)->get();
    }

}
