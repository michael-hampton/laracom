<?php
namespace App\Listeners;
use App\Events\DispatchCreateEvent;
use App\Shop\Orders\Repositories\OrderRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
class DispatchCreateEventListener {
    /**
     * Create the event listener.
     *
     */
    public function __construct() {
        //
    }
    /**
     * Handle the event.
     *
     * @param  DispatchCreateEvent  $event
     * @return void
     */
    public function handle(DispatchCreateEvent $event) {
                       
        // send email to customer
        $orderRepo = new OrderRepository($event->order);
        $orderRepo->sendDispatchEmail();
                
        $orderRepo = new OrderRepository($event->order);
        $orderRepo->sendEmailNotificationToAdmin();        
    }
}
