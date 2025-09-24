<?php
function required($field, $value) {
  if (empty(trim($value))) {
    throw new Exception("$field is required.");
  }
}

function validEmail($email) {
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception("Invalid email format.");
  }
}
