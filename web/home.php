<?php session_start(); ?>
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
        <a href="logout.php"><button>Sign Out</button></a>
      <?php endif; ?>
    </header>
    <main>
      <div class="catalogue">
	   <?php
	   require '../db_connect.php';
	   $sql = "SELECT * FROM Product ORDER BY ProductID;";
	   $result = $pdo->query($sql);
           foreach ($result->fetchAll() as $index => $row) {
             $imgNum = $index + 1;
	     echo '<div class="card"
               data-name="'.htmlspecialchars($row['Name']).'"
               data-desc="'.htmlspecialchars($row['Description']).'"
               data-price="'.htmlspecialchars($row['Price']).'"
               data-stock="'.htmlspecialchars($row['Stock']).'"
               data-img="../meatballs/meatball'.$imgNum.'.png"
               style="cursor:pointer;">'."\r\n";
             echo '<img src="../meatballs/meatball'.$imgNum.'.png" alt="'.htmlspecialchars($row['Name']).'">';
             echo '<h3>'.htmlspecialchars($row['Name']).'</h3>'."\r\n";
             echo '<h4 class="description">'.htmlspecialchars($row['Description']).'</h4>'."\r\n";
             echo '<p class="price">$'.$row['Price'].'</p>'."\r\n";
             echo '<button class="add-to-cart">Add to Cart</button>'."\r\n";
             echo '<p class="stock in-stock">Stock: '.$row['Stock'].'</p>'."\r\n";
             echo '</div>'."\r\n";
           }
	   ?>
      </div>
    </main>
    <footer>
      <ul>
        <li><a href="empLogin.php"><b>Employee Login</b></a></li>
        <li><a href="empInventory.php"><b>Inventory Management</b></a></li>
        <li><a href="empOrder.php"><b>Order Fulfillment</b></a></li>
      </ul>
    </footer>

    <!-- Individual Meatball Popup elements -->
    <div id="modal-overlay" style="display:none;">
      <div id="modal-content">
        <button id="modal-close">✕</button>
        <img id="modal-img" src="" alt="">
        <h2 id="modal-name"></h2>
        <p id="modal-desc"></p>
        <p id="modal-price"></p>
        <button class="add-to-cart">Add to Cart</button>
        <p id="modal-stock"></p>
     </div>
    </div>

    <!-- JavaScript for popup functionality -->
    <script>
      document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-to-cart')) return;

            document.getElementById('modal-img').src = this.dataset.img;
            document.getElementById('modal-name').textContent = this.dataset.name;
            document.getElementById('modal-desc').textContent = this.dataset.desc;
            document.getElementById('modal-price').textContent = '$' + this.dataset.price;
            document.getElementById('modal-stock').textContent = 'Stock: ' + this.dataset.stock;
            document.getElementById('modal-overlay').style.display = 'flex';
        });
      });

      // Close on X button or clicking outside the modal
      document.getElementById('modal-close').addEventListener('click', closeModal);
      document.getElementById('modal-overlay').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
      });

      function closeModal() {
        document.getElementById('modal-overlay').style.display = 'none';
      }
    </script>
  </body>
</html>
