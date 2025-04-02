<?php
session_start();
session_destroy();
echo "Session cleared. <a href='login.php'>Login again</a>";
