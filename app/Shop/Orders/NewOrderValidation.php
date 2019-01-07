<?php
namespace App/Shop/Orders/Validation;

trait NewOrderValidation {

public function validateAddress(AddressRepositoryInterface $addressRepo, $id) {

try {
$addressRepo->findAddressById($id);
} catch(\Exception $e) {
  $this->validationFailures[] = 'Invalid address used';
return false;
}
}
  
  public function validateCustomer(CustomerRepositoryInterface $customerRepo, $id) {

try {
$customerRepo->findCustomerById($id);
} catch(\Exception $e) {
  $this->validationFailures[] = 'Invalid customer used';
return false;
}
}
  
  public function validateVoucherCode(VoucherRepositoryInterface $voucherRepo, $voucherCode) {
    
    try {
      $voucherRepo->getByVoucherCode($voucherCode);
    } catch(\Exception $e) {
      $this->validationFailures[] = 'Invalid voucher code used';
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
          $this->validationFailures[] = 'Invalid customer ref used';
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
           $this->validationFailures[] = 'Invalid totals';
            return false;
        }

        return true;
    }

}
