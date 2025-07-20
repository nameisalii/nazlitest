<?php
// --------------- 1. SECURITY & SPAM CHECKS ----------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');           // direct visits → home
    exit;
}

// Honeypot: if the invisible field was filled in, it’s a bot
if (!empty($_POST['honeypot'])) {
    header('Location: index.html#contact?error=Spam+detected');
    exit;
}

// --------------- 2. SANITISE & VALIDATE INPUT -------------
$fields = [
    'name'    => FILTER_SANITIZE_SPECIAL_CHARS,
    'surname' => FILTER_SANITIZE_SPECIAL_CHARS,
    'email'   => FILTER_SANITIZE_EMAIL,
    'phone'   => FILTER_SANITIZE_SPECIAL_CHARS,
    'info'    => FILTER_SANITIZE_SPECIAL_CHARS,
];
$data = filter_input_array(INPUT_POST, $fields);

if (
    !$data['name']    || !$data['surname'] ||
    !filter_var($data['email'], FILTER_VALIDATE_EMAIL) ||
    !$data['phone']   || !$data['info']
) {
    header('Location: index.html#contact?error=Please+fill+in+all+fields+correctly');
    exit;
}

// --------------- 3. BUILD THE E-MAIL ----------------------
$to      = 'kazgutv@gmail.com';                     // <- your address
$subject = 'New cleaning enquiry from website';

$body  = "Name:     {$data['name']} {$data['surname']}\n";
$body .= "Email:    {$data['email']}\n";
$body .= "Phone:    {$data['phone']}\n\n";
$body .= "Cleaning details:\n{$data['info']}\n";

// --------------- 4. SEND VIA PHPMailer --------------------
// Install once at the project root:
//     composer require phpmailer/phpmailer
require __DIR__ . '/vendor/autoload.php';

$mail = new PHPMailer\PHPMailer\PHPMailer(true);

try {
    // (A) SMTP SETTINGS —  Replace with your provider settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->Port       = 587;
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Username   = 'yourgmail@gmail.com';        // full address
    $mail->Password   = 'APP-SPECIFIC-PASSWORD';      // NEVER reuse main pw

    // (B) MESSAGE HEADERS
    $mail->setFrom('no-reply@nazlicleaning.com', 'Nazli Cleaning Website');
    $mail->addAddress($to);
    $mail->addReplyTo($data['email'], "{$data['name']} {$data['surname']}");
    $mail->Subject = $subject;
    $mail->Body    = $body;

    // (C) SEND
    $mail->send();

    header('Location: index.html?success=Thank+you!+We+will+be+in+touch+#contact');
    exit;

} catch (Exception $e) {
    error_log('PHPMailer error: ' . $mail->ErrorInfo);   // log for yourself
    header('Location: index.html#contact?server_error');
    exit;
}
