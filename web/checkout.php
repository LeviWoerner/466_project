<?php 
session_start(); 
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['user_id'];
$message = "";

if (isset($_POST['place_order'])) {
    $shipping = $_POST['shipping'];
    $billing = $_POST['billing'];

    if ($shipping == "" || $billing == "") {
        $message = "Please enter shipping and billing information.";
    } else {
        $stmt = $pdo->prepare("SELECT CartID FROM Cart WHERE UserID = ?");
        $stmt->execute([$userID]);
        $cart = $stmt->fetch();

        if (!$cart) {
            $message = "Your cart is empty.";
        } else {
            $cartID = $cart['CartID'];
            $stmt = $pdo->prepare(
            "SELECT CartItem.ProductID, CartItem.Quantity, Product.Stock
            FROM CartItem, Product
            WHERE CartItem.ProductID = Product.ProductID AND CartItem.CartID = ?");
            $stmt->execute([$cartID]);
            $items = $stmt->fetchAll();

            if (count($items) == 0) {
                $message = "Your cart is empty.";
            } else {
                $enoughStock = true;

                foreach ($items as $item) {
                    if ($item['Quantity'] > $item['Stock']) {
                        $enoughStock = false;
                    }
                }

                if (!$enoughStock) {
                    $message = "One or more items do not have enough stock.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO Orders
                        (UserID, Status, ShippingAddr, BillingInfo)
                        VALUES
                        (?, 'Pending', ?, ?)");
                    $stmt->execute([$userID, $shipping, $billing]);

                    $orderID = $pdo->lastInsertId();

                    foreach ($items as $item) {
                        $stmt = $pdo->prepare("
                            INSERT INTO OrderItem
                            (OrderID, ProductID, Quantity)
                            VALUES
                            (?, ?, ?)");
                        $stmt->execute([$orderID, $item['ProductID'], $item['Quantity']]);

                        $stmt = $pdo->prepare("
                            UPDATE Product
                            SET Stock = Stock - ?
                            WHERE ProductID = ?");
                        $stmt->execute([$item['Quantity'], $item['ProductID']]);
                    }

                    $stmt = $pdo->prepare("
                        DELETE FROM CartItem
                        WHERE CartID = ?");
                    $stmt->execute([$cartID]);
                    header("Location: order.php");
                    exit;
                }
            }
        }
    }
}                                
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Meatball Store</title>
    <link rel="stylesheet" href="../style.css">
  </head>
  <body>
    <header>
      <div>
        <h1>Meatball Mall</h1>
        <p>Satisfying all your meatball needs since yesterday.</p>
      </div>
      <nav>
        <ul>
          <li><a href="home.php"><b>Home</b></a></li>
          <li><a href="login.php"><b>Login</b></a></li>
          <li><a href="cart.php"><b>Cart</b></a></li>
          <li><a href="order.php"><b>Orders</b></a></li>
        </ul>
      </nav>
      <?php if (!empty($_SESSION['user_email'])): ?>
        <div class="user-info"><?= htmlspecialchars($_SESSION['user_email']) ?></div>
      <?php endif; ?>
    </header>
    <main>
      <h1>Checkout</h1>

      <?php
      if ($message != "") {
          echo "<p style='color:red;'>$message</p>";
      }

      $stmt = $pdo->prepare("
        SELECT Cart.CartID, Product.Name, Product.Price, CartItem.Quantity
        FROM Cart, CartItem, Product
        WHERE Cart.CartID = CartItem.CartID
        AND CartItem.ProductID = Product.ProductID
        AND Cart.UserID = ?");
      $stmt->execute([$userID]);

      $total = 0;
      $hasItems = false;

      echo "<h2>Order Summary</h2>";

      while ($row = $stmt->fetch()) {
          $hasItems = true;
          $subtotal = $row['Price'] * $row['Quantity'];
          $total +=$subtotal;

          echo "<p>{$row['Name']} - {$row['Quantity']} x {$row['Price']} = $subtotal</p>";
      }

      if (!$hasItems) {
          echo "<p>Your cart is empty.</p>";
          echo "<a href='home.php'>Back to Store</a>";
      } else {
          echo "<h2>Total: $$total</h2>";
      ?>

      <form method="POST" action="checkout.php">
        <p>
          Shipping Address:<br>
          <input type="text" name="shipping" maxlength="30" required>
        </p>

        <p>
          Billing Info:<br>
          <input type="text" name="billing" maxlength="16" required>
        </p>

        <p>
          <button type="submit" name="place_order">Place Order</button>
        </p>
      </form>
      <?php
      }
      ?>
    </main>
    <footer>
      <ul>
        <li><a href="empLogin.php"><b>Employee Login</b></a></li>
        <li><a href="empInventory.php"><b>Inventory Management</b></a></li>
        <li><a href="empOrder.php"><b>Order Fulfillment</b></a></li>
      </ul>
    </footer>
  </body>
</html>
