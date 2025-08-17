<?php
session_start();
session_destroy();
?>
<script>
localStorage.removeItem('cart');
</script>
<?php
header("Location: index.html");
exit();
?>