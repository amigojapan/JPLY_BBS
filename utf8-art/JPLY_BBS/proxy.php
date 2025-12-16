<?php
header("Content-Type: text/plain; charset=UTF-8");
echo file_get_contents($_GET["url"]);
?>