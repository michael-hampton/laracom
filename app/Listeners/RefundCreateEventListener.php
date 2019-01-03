<?php
namespace App\Listeners;
use App\Events\RefundsCreateEvent;
use App\Shop\Orders\Repositories\OrderRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
class RefundCreateEventListener {
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
     * @param  RefundsCreateEvent  $event
     * @return void
     */
    public function handle(RefundsCreateEvent $event) {
                       
        // send email to customer
        $orderRepo = new OrderRepository($event->order);
        $orderRepo->sendRefundEmailToCustomer();
                
        $orderRepo = new OrderRepository($event->order);
        $orderRepo->sendRefundEmailNotificationToAdmin();        
    }
}
