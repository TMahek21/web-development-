<?php
if (extension_loaded("pdo_mysql"))
{
    echo "PDO MySQL driver is enabled!";
}
else
{
    echo "PDO MySQL driver is NOT enabled.";
}
?>