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

}
