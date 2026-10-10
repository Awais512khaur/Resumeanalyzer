<?php
return [
    'host' => getenv('RESUME_SMTP_HOST') ?: '',
    'port' => (int)(getenv('RESUME_SMTP_PORT') ?: 587),
    'username' => getenv('RESUME_SMTP_USERNAME') ?: '',
    'password' => getenv('RESUME_SMTP_PASSWORD') ?: '',
    'encryption' => strtolower(getenv('RESUME_SMTP_ENCRYPTION') ?: 'tls'),
    'from_email' => getenv('RESUME_MAIL_FROM_EMAIL') ?: (getenv('RESUME_SMTP_USERNAME') ?: ''),
    'from_name' => getenv('RESUME_MAIL_FROM_NAME') ?: 'ResumeAI',
];
