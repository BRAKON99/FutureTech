<?php
// Настройки для корректной работы с JSON
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

// Подключаем PHPMailer
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    // Проверяем метод запроса
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        echo json_encode(['success' => false, 'error' => 'Только POST метод разрешен']);
        exit;
    }

    // Получаем данные из формы
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phoneNumber = trim($_POST['phoneNumber'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $agreement = isset($_POST['agreement']);

    // Валидация данных
    $errors = [];

    if (empty($firstName)) {
        $errors['firstName'] = 'Имя обязательно для заполнения';
    }

    if (empty($email)) {
        $errors['email'] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email адрес';
    }

    if (empty($message)) {
        $errors['message'] = 'Сообщение обязательно для заполнения';
    }

    if (!$agreement) {
        $errors['agreement'] = 'Необходимо согласие с условиями';
    }

    // Если есть ошибки валидации
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'errors' => $errors,
            'error' => 'Пожалуйста, исправьте ошибки в форме'
        ]);
        exit;
    }

    // Логируем данные
    $log = date('Y-m-d H:i:s') . " | " . $firstName . " " . $lastName . " | " . $email . " | " . substr($message, 0, 100) . "\n";
    file_put_contents('contact_log.txt', $log, FILE_APPEND);

    // Создаем экземпляр PHPMailer
    $mail = new PHPMailer(true);

    // Настройки SMTP сервера (Gmail)
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pa1nzwer@gmail.com'; // Замените на ваш Gmail
    $mail->Password = 'lyoi iyjw ktyx gebj'; // Замените на пароль приложения
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // Настройки отправителя и получателя
    $mail->setFrom('pa1nzwer@gmail.com', 'FutureTech Website');
    $mail->addAddress('ksa.444@mail.ru', 'Admin'); // Замените на вашу почту
    $mail->addReplyTo($email, $firstName . ' ' . $lastName);

    // Контент письма
    $mail->isHTML(true);
    $mail->Subject = 'Новое сообщение с сайта FutureTech';

    $email_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
            .field { margin-bottom: 15px; }
            .field-label { font-weight: bold; color: #555; }
            .field-value { margin-top: 5px; }
            .message-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Новое сообщение с сайта FutureTech</h2>
                <p>Дата: " . date('d.m.Y H:i:s') . "</p>
            </div>
            
            <div class='field'>
                <div class='field-label'>Имя:</div>
                <div class='field-value'>" . htmlspecialchars($firstName) . " " . htmlspecialchars($lastName) . "</div>
            </div>
            
            <div class='field'>
                <div class='field-label'>Email:</div>
                <div class='field-value'>" . htmlspecialchars($email) . "</div>
            </div>";

    if (!empty($phoneNumber)) {
        $email_body .= "
            <div class='field'>
                <div class='field-label'>Телефон:</div>
                <div class='field-value'>" . htmlspecialchars($phoneNumber) . "</div>
            </div>";
    }

    $email_body .= "
            <div class='field'>
                <div class='field-label'>Сообщение:</div>
                <div class='message-box'>" . nl2br(htmlspecialchars($message)) . "</div>
            </div>
            
            <div class='field'>
                <div class='field-label'>IP адрес:</div>
                <div class='field-value'>" . $_SERVER['REMOTE_ADDR'] . "</div>
            </div>
        </div>
    </body>
    </html>";

    $mail->Body = $email_body;

    // Отправляем письмо
    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Ваше сообщение успешно отправлено! Мы свяжемся с вами в ближайшее время.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Произошла ошибка при отправке сообщения. Попробуйте позже.'
    ]);
}
?> 