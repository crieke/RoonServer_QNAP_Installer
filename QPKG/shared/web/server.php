<!DOCTYPE html>
<html>
<head>

</head>
<body>
<pre>
<?php
$path = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

die(print_r($_SERVER));
?>
</pre>
</body>

</html>
