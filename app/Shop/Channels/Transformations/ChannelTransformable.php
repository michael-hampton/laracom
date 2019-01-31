<?php

namespace App\Shop\Channels\Transformations;

use App\Shop\Channels\Channel;
use Illuminate\Support\Facades\Storage;

trait ChannelTransformable {

    /**
     * Transform the channel
     *
     * @param Channel $channel
     * @return Channel
     */
    protected function transformChannel(Channel $channel) {
        $channelObj = new Channel;
        $channelObj->id = (int) $channel->id;
        $channelObj->name = $channel->name;
        $channelObj->email = $channel->email;
        $channelObj->description = $channel->description;
        $channelObj->allocate_on_order = $channel->allocate_on_order; #
        $channelObj->has_priority = $channel->has_priority;
        $channelObj->backorders_enabled = $channel->backorders_enabled;
        $channelObj->strict_validation = $channel->strict_validation;
        $channelObj->partial_shipment = $channel->partial_shipment;
        $channelObj->send_received_email = $channel->send_received_email;
        $channelObj->send_hung_email = $channel->send_hung_email;
        $channelObj->send_backorder_email = $channel->send_backorder_email;
        $channelObj->send_dispatched_email = $channel->send_dispatched_email;
        $channelObj->cover = $channel->cover;
         $channelObj->status = $channel->status;
        return $channelObj;
    }

}
