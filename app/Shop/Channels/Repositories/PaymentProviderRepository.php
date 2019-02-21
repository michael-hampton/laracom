<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Shop\Channels\Repositories;

use App\Shop\Channels\PaymentProvider;
use App\Shop\Base\BaseRepository;

/**
 * Description of PaymentProviderRepository
 *
 * @author michael.hampton
 */
class PaymentProviderRepository extends BaseRepository {

    /**
     * 
     * @param PaymentProvider $paymentProvider
     */
    public function __construct(PaymentProvider $paymentProvider) {
        parent::__construct($paymentProvider);
        $this->model = $paymentProvider;
    }

    /**
     * 
     * @param string $name
     * @return type
     */
    public function findByName(string $name) {
        return $this->model
                        ->whereRaw('LOWER(`name`) LIKE ? ', [trim(strtolower($name)) . '%'])
                        ->get()
                        ->first();
    }

}
