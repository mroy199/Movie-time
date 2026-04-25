<?php
require_once __DIR__ . "/config/db.php";

if ($conn) {
    echo "DB Connected ✅";
} else {
    echo "DB Failed ❌";
}