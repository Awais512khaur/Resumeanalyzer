<?php
$localConfigFile = __DIR__ . '/local.php';
$localConfig = is_file($localConfigFile) ? require $localConfigFile : [];
$localConfig = is_array($localConfig) ? $localConfig : [];

return [
    'host' => $localConfig['RESUME_SMTP_HOST'] ?? (getenv('RESUME_SMTP_HOST') ?: ''),
    'port' => (int)($localConfig['RESUME_SMTP_PORT'] ?? (getenv('RESUME_SMTP_PORT') ?: 587)),
    'username' => $localConfig['RESUME_SMTP_USERNAME'] ?? (getenv('RESUME_SMTP_USERNAME') ?: ''),
    'password' => $localConfig['RESUME_SMTP_PASSWORD'] ?? (getenv('RESUME_SMTP_PASSWORD') ?: ''),
    'encryption' => strtolower($localConfig['RESUME_SMTP_ENCRYPTION'] ?? (getenv('RESUME_SMTP_ENCRYPTION') ?: 'tls')),
    'from_email' => $localConfig['RESUME_MAIL_FROM_EMAIL'] ?? (getenv('RESUME_MAIL_FROM_EMAIL') ?: ($localConfig['RESUME_SMTP_USERNAME'] ?? (getenv('RESUME_SMTP_USERNAME') ?: ''))),
    'from_name' => $localConfig['RESUME_MAIL_FROM_NAME'] ?? (getenv('RESUME_MAIL_FROM_NAME') ?: 'ResumeAI'),
];
