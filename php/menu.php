<?php
session_start();
?>
<style>
/* Styling ya hamburger menu */
nav {
    background-color: #333;
    padding: 10px;
    position: relative;
}

nav button {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
}

nav ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
    display: none;
    background-color: #333;
    position: absolute;
    top: 40px;
    left: 0;
    width: 200px;
}

nav ul li {
    border-bottom: 1px solid #444;
}

nav ul li a {
    color: white;
    text-decoration: none;
    display: block;
    padding: 10px;
}

nav ul li a:hover {
    background-color: #575757;
}
</style>

<nav>
    <button id="menu-toggle">☰</button>
    <ul id="menu-items">
        <li><a href="/index.php">Nyumbani</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/rsvp.php">RSVP</a></li>
            <li><a href="/gallery.php">Gallery</a></li>
            <li><a href="/gifts.php">Gifts</a></li>
            <li><a href="/payment.php">Payment</a></li>
            <li><a href="/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="/login/register.php">Jisajili</a></li>
            <li><a href="/login/index.php">Ingia</a></li>
        <?php endif; ?>
    </ul>
</nav>

<script>
document.getElementById("menu-toggle").addEventListener("click", function() {
    var menu = document.getElementById("menu-items");
    menu.style.display = (menu.style.display === "none" || menu.style.display === "") ? "block" : "none";
});
</script>
