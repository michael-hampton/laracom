<?php

namespace App\Shop\Customers\Transformations;

use App\Shop\Customers\Customer;

trait CustomerTransformable
{
    protected function transformCustomer(Customer $customer)
    {
        $prop = new Customer;
        $prop->id = (int) $customer->id;
        $prop->name = $customer->name;
        $prop->email = $customer->email;
        $prop->status = (int) $customer->status;
        $prop->credit = $customer->credit;
        $prop->customer_type = $customer->customer_type;
        
        return $prop;
    }
}
