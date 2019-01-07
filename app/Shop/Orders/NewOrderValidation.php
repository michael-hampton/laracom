<?php
namespace App/Shop/Orders/Validation;

trait NewOrderValidation {

public function validateAddress($addressRepo, $id) {

try {
$addressRepo->findAddressById($id);
} catch() {
return false;
}
}
  
  public function validateCustomer($customerRepo, $id) {

try {
$customerRepo->findCustomerById($id);
} catch() {
return false;
}
}
  
  private function validateCustomerRef($customerRef) {

        if (strlen($customerRef) > 36) {
            return false;
        }

        try {
            $result = $this->listOrders()->where('customer_ref', $customerRef);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $result->isEmpty();
    }

    private function validateTotal($data, $cartItems) {
        $productTotal = 0;

        foreach ($cartItems as $cartItem) {

            $productTotal += $cartItem->price;
        }

        $total = $productTotal + $data['shipping'] + $data['tax'];

        if (!empty($data['discounts']) && $data['discounts'] > 0) {
            $total -= $data['discounts'];
        }


        if (round($total, 2) !== round($data['total'], 2)) {

            return false;
        }

        return true;
    }

}
