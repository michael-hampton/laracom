<?php
namespace App\Shop\Channels\Repositories\Interfaces;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Channels\ChannelPaymentProvider;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
interface ChannelPaymentProviderRepositoryInterface extends BaseRepositoryInterface {
    
   public function createChannelPaymentProvider(array $params): ChannelPaymentProvider;
    
   public function updateChannelPaymentProvider(array $data): bool;
   
   public function getProvidersForChannel(Channel $channel);
    
   public function deleteChannelFromProvider(PaymentProvider);
}
