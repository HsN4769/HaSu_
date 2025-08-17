<!DOCTYPE html>
<html>
<head>
    <title>Usajili wa Mwanachama</title>
</head>
<body>
    <?php include 'php/check_session.php'; ?>
<?php include 'menu.php'; ?>

    <h2>Ingiza Taarifa za Mwanachama</h2>
    <form action="scripts/add_member.php" method="POST">
        <label>Jina:</label><br>
        <input type="text" name="jina" required><br><br>

        <label>Namba ya Simu:</label><br>
        <input type="text" name="namba_sim" required><br><br>

        <label>Kiasi:</label><br>
        <input type="number" name="kiasi" required><br><br>

        <label>Status:</label><br>
        <select name="status">
            <option value="paid">Paid</option>
            <option value="pending">Pending</option>
        </select><br><br>

        <button type="submit">Sajili Mwanachama</button>
    </form>
</body>
</html> 