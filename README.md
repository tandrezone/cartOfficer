# CartOfficer

A lightweight, session-based PHP shopping cart composer package.  
Supports adding products (with variants), updating quantities, deleting items and creating an order that POSTs the cart payload to your own endpoint — all with a polished, accessible slide-in UI.

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP         | ≥ 7.4   |
| Composer    | any     |

No framework required — works with any PHP project.

---

## Installation

```bash
composer require tandrezone/cart-officer
```

---

## Quick Start

### 1. Create the cart endpoint (e.g. `cart.php`)

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use CartOfficer\Cart;
use CartOfficer\CartController;

session_start();                   // must be called before new Cart()

$cart       = new Cart();
$controller = new CartController($cart, '/orders'); // pass your order route

$controller->handle();             // reads $_POST / JSON body, writes JSON response
```

### 2. Add the CSS in your `<head>`

```html
<link rel="stylesheet" href="/vendor/tandrezone/cart-officer/public/css/cart.css">
```

> **Tip:** copy `public/css/cart.css` and `public/js/cart.js` to your own `public/` folder
> if you need to serve assets from a different path.

### 3. Configure the JS (optional, before the script tag)

```html
<script>
  window.CartOfficer = {
    cartEndpoint : '/cart',      // URL of your cart.php endpoint
    orderRoute   : '/orders',   // URL the Create Order button POSTs to
    currency     : 'USD',       // ISO 4217 currency code
    locale       : 'en-US',     // BCP 47 locale for Intl.NumberFormat
  };
</script>
```

### 4. Include the templates

Place the **cart button** wherever you want it (header, navbar, etc.):

```php
<?php include __DIR__ . '/vendor/tandrezone/cart-officer/templates/cart-button.php'; ?>
```

Place the **sidebar** (and overlay) once, just before `</body>`:

```php
<?php
$orderRoute = '/orders';   // shown as a hint; JS reads window.CartOfficer.orderRoute
include __DIR__ . '/vendor/tandrezone/cart-officer/templates/cart-sidebar.php';
?>
```

### 5. Add the JS before `</body>`

```html
<script src="/vendor/tandrezone/cart-officer/public/js/cart.js"></script>
```

---

## Adding an "Add to Cart" Button to a Product

Add the class `co-add-btn` and the required `data-*` attributes to any button:

```html
<button
  class="co-add-btn"
  data-product-id="42"
  data-product-variant="red-L"
  data-product-name="Cool T-Shirt"
  data-price="29.99"
  data-quantity="1"
  type="button">
  Add to cart
</button>
```

| Attribute              | Required | Description                             |
|------------------------|----------|-----------------------------------------|
| `data-product-id`      | ✅        | Unique product identifier               |
| `data-product-name`    | ✅        | Product name shown in the cart          |
| `data-price`           | ✅        | Unit price (numeric, e.g. `29.99`)      |
| `data-product-variant` | optional | Variant string (colour, size, SKU…)     |
| `data-quantity`        | optional | Units to add per click (default `1`)    |

> Clicking the button fires an AJAX request to `cartEndpoint` and updates the cart badge and sidebar automatically — **no page reload**.

---

## Complete Page Example

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Shop</title>
  <link rel="stylesheet" href="/vendor/tandrezone/cart-officer/public/css/cart.css">
</head>
<body>

  <!-- ── Header ── -->
  <header>
    <h1>My Shop</h1>

    <!-- Cart button (badge updates automatically) -->
    <?php include __DIR__ . '/vendor/tandrezone/cart-officer/templates/cart-button.php'; ?>
  </header>

  <!-- ── Product listing ── -->
  <main>
    <article>
      <h2>Cool T-Shirt</h2>
      <p>$29.99</p>

      <button
        class="co-add-btn"
        data-product-id="42"
        data-product-variant="blue-M"
        data-product-name="Cool T-Shirt"
        data-price="29.99"
        type="button">
        Add to cart
      </button>
    </article>
  </main>

  <!-- ── Cart sidebar (once per page) ── -->
  <?php include __DIR__ . '/vendor/tandrezone/cart-officer/templates/cart-sidebar.php'; ?>

  <!-- ── JS config + script ── -->
  <script>
    window.CartOfficer = {
      cartEndpoint : '/cart',
      orderRoute   : '/orders',
      currency     : 'USD',
      locale       : 'en-US',
    };
  </script>
  <script src="/vendor/tandrezone/cart-officer/public/js/cart.js"></script>
</body>
</html>
```

---

## Cart Sidebar Features

When the cart icon is clicked a slide-in panel opens with:

| Feature          | Description                                            |
|------------------|--------------------------------------------------------|
| Product table    | Lists product name, variant, unit price, quantity, line total |
| Quantity controls | `−` / `+` buttons and a direct input field; changes commit on blur or button press |
| Delete item      | Trash-icon button removes the line from the cart      |
| Grand total      | Recalculated automatically after every change          |
| **Create Order** | POSTs the full cart payload as `cart_payload` JSON to your `orderRoute` |
| Clear cart       | Empties the cart in one click (with confirmation)      |

---

## Handling the Order on Your Server

When the user clicks **Create Order**, CartOfficer POSTs a form to your `orderRoute` with the field `cart_payload` (JSON string).

```php
// orders.php
$payload = json_decode($_POST['cart_payload'] ?? '{}', true);

$items      = $payload['items'];      // array of cart lines
$total      = $payload['total'];      // float grand total
$itemCount  = $payload['item_count']; // int

// … persist to DB, generate invoice, etc.
```

Each item in `$payload['items']` has:

```json
{
  "product_id":      "42",
  "product_variant": "blue-M",
  "product_name":    "Cool T-Shirt",
  "price":           29.99,
  "quantity":        2
}
```

---

## PHP API

### `Cart`

```php
use CartOfficer\Cart;

$cart = new Cart();

$cart->add('id', 'variant', 'Name', 19.99, 2); // returns CartItem
$cart->update('id_variant', 3);                 // set quantity; 0 removes
$cart->remove('id_variant');                    // remove one line
$cart->clear();                                 // empty cart

$cart->items();   // CartItem[] keyed by "productId_variant"
$cart->count();   // total units
$cart->total();   // float grand total
$cart->isEmpty(); // bool
$cart->toArray(); // raw session array
```

### `CartItem`

```php
$item->productId;      // string
$item->productVariant; // string
$item->productName;    // string
$item->price;          // float
$item->quantity;       // int
$item->lineTotal();    // float  (price × quantity)
$item->key();          // string "productId_variant"
$item->toArray();      // array
```

### `CartController`

```php
use CartOfficer\CartController;

$controller = new CartController($cart, '/orders');
$controller->handle(); // auto-dispatch on $_POST['action'] / JSON body

// Or call actions directly:
$controller->actionGet();
$controller->actionAdd();
$controller->actionUpdate();
$controller->actionDelete();
$controller->actionClear();
$controller->actionOrder();
```

**Request parameters** (POST body or JSON):

| Action   | Required params                                                  |
|----------|------------------------------------------------------------------|
| `add`    | `product_id`, `product_name`, `price` (+ optional `product_variant`, `quantity`) |
| `update` | `key`, `quantity`                                                |
| `delete` | `key`                                                            |
| `clear`  | *(none)*                                                         |
| `order`  | *(none)*                                                         |
| `get`    | *(none)*                                                         |

All actions return JSON:

```json
{
  "items": [
    {
      "product_id": "42",
      "product_variant": "blue-M",
      "product_name": "Cool T-Shirt",
      "price": 29.99,
      "quantity": 2,
      "key": "42_blue-M",
      "line_total": 59.98
    }
  ],
  "total": 59.98,
  "item_count": 2
}
```

---

## CSRF Protection

The JS automatically reads `<meta name="csrf-token" content="…">` and includes the token as `_token` in the order form POST.  For AJAX cart operations you may add your own middleware or validate the `X-Requested-With` header.

---

## Customising Styles

Override CSS custom properties in your own stylesheet:

```css
:root {
  --co-primary:      #7c3aed;   /* button colour */
  --co-danger:       #e11d48;   /* delete / badge colour */
  --co-sidebar-width: 480px;    /* wider sidebar */
}
```

---

## License

MIT
