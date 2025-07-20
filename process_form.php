<?php
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check honeypot field to filter spam
    if (!empty($_POST['honeypot'])) {
        header("Location: index.html#contact?error=" . urlencode("Spam detected"));
        exit;
    }

    // Collect and sanitize form data
    $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
    $surname = filter_var($_POST['surname'] ?? '', FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_STRING);
    $info = filter_var($_POST['info'] ?? '', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($name) || empty($surname) || empty($email) || empty($phone) || empty($info)) {
        header("Location: index.html#contact?error=" . urlencode("All fields are required."));
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: index.html#contact?error=" . urlencode("Invalid email address."));
        exit;
    }

    // Email settings
    $to = "kazgutv@gmail.com";
    $subject = "New Contact Form Submission from $name $surname";
    $message = "Name: $name $surname\nEmail: $email\nPhone: $phone\nCleaning Details: $info";
    $headers = "From: noreply@nazlicleaning.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Send email
    if (mail($to, $subject, $message, $headers)) {
        header("Location: index.html#contact?success=" . urlencode("Thank you! Your submission has been sent. We'll contact you soon."));
        exit;
    } else {
        header("Location: index.html#contact?error=" . urlencode("Failed to send the email. Please try again or contact us directly at kazgutv@gmail.com."));
        exit;
    }
} else {
    // Redirect if accessed directly
    header("Location: index.html#contact");
    exit;
}
?>
