<?php
require_once __DIR__ . '/includes/send_mail.php';

$result = sendMovieTimeMail(
    'YOUR_TEST_EMAIL@gmail.com',
    'Test User',
    'MovieTime SMTP Test',
    '<h2>SMTP is working</h2><p>This email confirms your MovieTime mail setup works.</p>',
    'SMTP is working. This confirms your MovieTime mail setup works.'
);

echo '<pre>';
print_r($result);
echo '</pre>';