<?php
declare(strict_types=1);

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$errors = [];
$clean = [];
$practiceComplete = true; // TODO E: Set true only after completing B-D.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    $errors[] = 'Open order.html and submit the form using POST.';
} else {
    $customer = $_POST['customer'] ?? null;
    if (!is_string($customer) || trim($customer) === ''
        || strlen(trim($customer)) > 40) {
        $errors[] = 'Enter one chef name of 1-40 bytes.';
    } else {
        $clean['customer'] = trim($customer);
    }

    $size = $_POST['size'] ?? null;
    if (!is_string($size) || !in_array($size, ['small', 'medium', 'large'], true)) {
        $errors[] = 'Choose small, medium, or large.';
    } else {
        $clean['size'] = ucfirst($size);
    }

    // TODO B: Read and validate quantity; store an integer in $clean['quantity'].
    $quantity = $_POST['quantity'] ?? null;
    if (!is_string($quantity)) {
        $errors[] = 'Enter one quantity.';
    } else {
        $qty = filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        if ($qty === false) {
            $errors[] = 'Quantity must be a whole number from 1 to 5.';
        } else {
            $clean['quantity'] = $qty;
        }
    }

    // TODO C: Read and validate toppings; store a list in $clean['toppings'].
    $toppings = $_POST['toppings'] ?? [];
    $allowedToppings = ['mushroom', 'olive', 'pineapple'];
    if (!is_array($toppings) || count($toppings) > count($allowedToppings)) {
        $errors[] = 'Toppings must be a short list.';
    } else {
        $cleanToppings = [];
        $toppingsOk = true;
        foreach ($toppings as $topping) {
            if (!is_string($topping) || !in_array($topping, $allowedToppings, true)) {
                $toppingsOk = false;
            } else {
                $cleanToppings[] = $topping;
            }
        }
        if ($toppingsOk) {
            $clean['toppings'] = array_values(array_unique($cleanToppings));
        } else {
            $errors[] = 'Choose only mushroom, olive, or pineapple.';
        }
    }

    // TODO D: Read and validate notes; store a string in $clean['notes'].
    $notes = $_POST['notes'] ?? '';
    if (!is_string($notes)) {
        $errors[] = 'Kitchen note must be text.';
    } else {
        $notes = trim($notes);
        if (strlen($notes) > 200) {
            $errors[] = 'Kitchen note must be 200 bytes or fewer.';
        } else {
            $clean['notes'] = $notes;
        }
    }


    if (!$practiceComplete) {
        $errors[] = 'Learning checkpoint: finish TODOs B-D, then enable TODO E.';
    } elseif ($errors === []) {
        foreach (['customer', 'size', 'quantity', 'toppings', 'notes'] as $key) {
            if (!array_key_exists($key, $clean)) {
                $errors[] = 'Finish the PHP processing block for: ' . $key;
            }
        }
    }
    if ($errors !== []) {
        http_response_code(422);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Campus Crust | Practice ticket</title>
  <style>
    body { font: 16px/1.6 system-ui, sans-serif; max-width: 650px; margin: 30px auto; padding: 20px; background: #faf5e9; color: #352b22; }
    main { background: white; padding: 24px; border: 1px solid #ddcfb5; border-radius: 12px; }
    li { margin: 10px 0; overflow-wrap: anywhere; }
    .errors { color: #932d22; }
    a { color: #a53d23; }
  </style>
</head>
<body>
<main>
<?php if ($errors !== []): ?>
  <h1>Almost ready, Chef!</h1>
  <ul class="errors" role="alert">
    <?php foreach ($errors as $error): ?>
      <li><?= e($error) ?></li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <h1>Nice work, Chef <?= e($clean['customer']) ?>.</h1>
  <h2>Your practice ticket</h2>
  <ul>
  <?php
  $labels = [
      'customer' => 'Chef name',
      'size' => 'Pizza size',
      'quantity' => 'Quantity',
      'toppings' => 'Toppings',
      'notes' => 'Kitchen note'
  ];
  foreach ($labels as $key => $label):
      $value = $clean[$key];
      if ($key === 'toppings') {
          $value = $value === [] ? 'Plain cheese' : implode(', ', $value);
      } elseif ($key === 'notes' && $value === '') {
          $value = 'No special requests';
      }
  ?>
    <li><strong><?= e($label) ?>:</strong> <?= e((string) $value) ?></li>
  <?php endforeach; ?>
  </ul>
  <p>Practice complete. No order was saved, purchased, or sent to a kitchen.</p>
<?php endif; ?>
  <p><a href="order.html">Back to the pizza form</a></p>
</main>
</body>
</html>