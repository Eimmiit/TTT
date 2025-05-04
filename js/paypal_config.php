<?php
// paypal_config.php

// Set environment: 'sandbox' or 'live'
define('PAYPAL_ENV', 'sandbox');

if (PAYPAL_ENV === 'sandbox') {
    define('PAYPAL_CLIENT_ID', 'YOUR_SANDBOX_CLIENT_ID_HERE');
    define('PAYPAL_SECRET', 'YOUR_SANDBOX_SECRET_HERE');
    define('PAYPAL_API_BASE', 'https://api-m.sandbox.paypal.com');
} else {
    define('PAYPAL_CLIENT_ID', 'AbyKDtf8hDSwxu-A2vELQBVca5T8bhObxp6TkbWqA2PXhx_wXfD6TOhqi4hbBywlLdNXwibw3fjx6vXi');
    define('PAYPAL_SECRET', 'ECwBWWQGE-Mv7so_S7R1r3zKvOnhN60I99zASvsw9jcTY9F0zPZDyJtzMXF0ZiXt2VMwffZ0L6clgLgB');
    define('PAYPAL_API_BASE', 'https://api-m.paypal.com');
}
?>
