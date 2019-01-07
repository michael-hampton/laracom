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

}
