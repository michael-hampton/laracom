<?php

use Illuminate\Database\Seeder;
use App\Shop\Orders\Order;


class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {        
        factory(Order::class)->create();
    }
}
