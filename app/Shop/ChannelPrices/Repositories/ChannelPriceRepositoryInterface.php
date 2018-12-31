<?php

namespace App\Shop\ChannelPrices\Repositories;

use App\Shop\Base\Interfaces\BaseRepositoryInterface;

interface ChannelPriceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * 
     * @param int $id
     */
    public function findChannelPriceById(int $id);
}
