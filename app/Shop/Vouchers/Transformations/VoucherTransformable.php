<?php

namespace App\Shop\Vouchers\Transformations;

use App\Shop\Vouchers\Voucher;
use App\Shop\Channels\Channel;
use App\Shop\Channels\Repositories\ChannelRepository;

trait VoucherTransformable {

    /**
     * Transform the voucher
     *
     * @param Voucher $voucher
     * @return Voucher
     */
    public function transformVoucher(\App\Shop\Vouchers\Voucher $voucher) {
                
        $channel = null;
        
        if(!empty($voucher->channel)) {
            $channelRepo = new ChannelRepository(new Channel);
            $channel = $channelRepo->findChannelById($voucher->channel);
        }   
        
        $obj = new Voucher;
        $obj->id = $voucher->id;
        $obj->coupon_code = $voucher->coupon_code;
        $obj->amount = $voucher->amount;
        $obj->amount_type = $voucher->amount_type;
        $obj->name = $voucher->name;
        $obj->description = $voucher->description;
        $obj->expiry_date = $voucher->expiry_date;
        $obj->start_date = $voucher->start_date;
        $obj->status = $voucher->status;
        $obj->channel = $voucher->channel;
        $obj->channel_name = !is_null($channel) ? $channel->name : 'NO CHANNEL';
        $obj->scope_type = $voucher->scope_type;
        $obj->scope_value = $voucher->scope_value;
        
        return $obj;
    }

}
