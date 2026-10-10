<img width="1920" height="1080" alt="Screenshot (6)" src="https://github.com/user-attachments/assets/228f015e-e5f9-467e-8f59-9cbc771a7eb2" /> <img width="1920" height="1080" alt="Screenshot (10)" src="https://github.com/user-attachments/assets/fc435318-156d-44c9-9a4f-97f3905d3a17" />
<img width="1920" height="1080" alt="Screenshot (7)" src="https://github.com/user-attachments/assets/e0a93a5d-3ff3-4fae-ba18-6d7707e4e475" />
<img width="1920" height="1080" alt="Screenshot (8)" src="https://github.com/user-attachments/assets/462cf1a8-e689-4d78-92de-d56f1e8abe1b" />
<img width="1920" height="1080" alt="Screenshot (10)" src="https://github.com/user-attachments/assets/570e6b34-b53b-4e6b-9ae8-996a415e6983" />
<img width="1920" height="1080" alt="Screenshot (9)" src="https://github.com/user-attachments/assets/5528665d-4ba6-43d5-aa17-53f7196158b0" />


# Resume Analyzer

Resume Analyzer is a PHP and MySQL web application that extracts text from PDF resumes, compares resumes with job descriptions and required skills, calculates a match score, and can create downloadable PDF reports. Gemini is used for AI-generated analysis where configured.

## 1. Requirements

- Windows with [XAMPP](https://www.apachefriends.org/) installed.
- Apache and MySQL/MariaDB enabled in the XAMPP Control Panel.
- PHP 8.x (the version bundled with a current XAMPP release is recommended).
- Composer, for installing the PHPMailer dependency used by email-based password resets.
- PHP extensions enabled in XAMPP:
  - `pdo_mysql` for the database connection.
  - `openssl` for encrypted SMTP connections.
  - `curl` and `mbstring` for Gemini requests and text handling.
  - `fileinfo` for checking uploaded resume files.
- Git, if you want to install TCPDF with the command below.
- Poppler for reading and rendering PDF files, installed inside this project's `tools` folder.
- TCPDF for generating downloadable PDF reports, installed inside this project's `tools` folder.
- Tesseract OCR for scanned/image-only PDFs (optional for text-based PDFs, needed for OCR).
- A Google Gemini API key for AI-generated analysis. Core skill matching can still be used without a working AI key, subject to the application's current behavior.
- A web browser.

> PHP must allow `shell_exec`; the PDF extraction and OCR steps call command-line tools. Check `php.ini` and remove `shell_exec` from `disable_functions` if it is disabled. Restart Apache after changing PHP configuration.

## 2. Put the project in XAMPP

- Copy or clone this project into XAMPP's web root. The expected location is:

  ```text
  C:\xampp\htdocs\Resumeanalyzer
  ```

- If the folder has a different name, open the project URL using that name. `BASE_URL` is detected from the project directory.
- Open a terminal in the project root before running the following setup commands.
- Install the PHP mail dependency:

  ```powershell
  composer install
  ```

- PHPMailer is installed through Composer and is required for password reset emails.

## 3. Create the tools and upload folders

- Create the required folders from PowerShell in the project root:

  ```powershell
  New-Item -ItemType Directory -Force tools\poppler, tools\tcpdf, uploads
  ```

- The application expects this structure:

  ```text
  Resumeanalyzer/
  ├── tools/
  │   ├── poppler/Library/bin/pdftotext.exe
  │   ├── poppler/Library/bin/pdftoppm.exe
  │   └── tcpdf/tcpdf.php
  └── uploads/
  ```

- `tools/` and uploaded resume files are excluded from Git. Install the tools again after a fresh clone or copy them separately when moving the project to another computer.

## 4. Install Poppler in `tools`

- Download a current Windows build from [Poppler for Windows releases](https://github.com/oschwartz10612/poppler-windows/releases).
- Extract the downloaded archive.
- Copy or move the extracted Poppler folder into this project's `tools` folder and name it `poppler`.
- Make sure both files exist at these exact paths:

  ```text
  tools\poppler\Library\bin\pdftotext.exe
  tools\poppler\Library\bin\pdftoppm.exe
  ```

- `pdftotext.exe` extracts text from regular PDFs. `pdftoppm.exe` converts scanned PDF pages to images for OCR.
- If the archive contains an extra nested directory, move its contents so `Library\bin` is directly under `tools\poppler`.

## 5. Install TCPDF in `tools`

- From the project root, clone TCPDF directly into the expected directory:

  ```powershell
  git clone https://github.com/tecnickcom/TCPDF.git tools/tcpdf
  ```

- If you download the repository archive instead, extract it and place the TCPDF files directly in `tools\tcpdf`.
- Confirm this file exists:

  ```text
  tools\tcpdf\tcpdf.php
  ```

- The application uses TCPDF when it generates a PDF report.

## 6. Install OCR support for scanned resumes (optional)

- Install Tesseract OCR for Windows from the [Tesseract documentation](https://github.com/tesseract-ocr/tessdoc) or a Windows installer linked there.
- Include the English language data (`eng`) during installation.
- The application checks these locations:

  ```text
  tools\tesseract\tesseract.exe
  C:\Program Files\Tesseract-OCR\tesseract.exe
  C:\Program Files (x86)\Tesseract-OCR\tesseract.exe
  ```

- If you install Tesseract in a custom location, set the `TESSERACT_PATH` environment variable to its executable path, or place it at `tools\tesseract\tesseract.exe`.
- Restart Apache after installing Tesseract or changing environment variables.

## 7. Create and configure the database

- Start **Apache** and **MySQL** from the XAMPP Control Panel.
- Open [phpMyAdmin](http://localhost/phpmyadmin).
- Create a database named `resume_analyzer`.
- Select that database and import the project schema:

  ```text
  SQL_file/resume_analyzer.sql
  ```

- Run the required-skills migration against the same database:

  ```text
  database/migrations/20261010_add_required_skills_to_positions.sql
  ```

- Run the audit-log migration after that migration:

  ```text
  database/migrations/20261010_create_audit_logs_and_remove_unused_tables.sql
  ```

- This creates `audit_logs`, copies any old `activity_logs` entries into it, then removes the five legacy tables that the application does not use: `activity_logs`, `analysis_keywords`, `job_descriptions`, `keywords`, and `reports`. The current local database was checked and each of those tables had zero rows. Back up another database before applying this cleanup migration if it contains data you want to keep.

- Import the password reset OTP table migration:

  ```text
  database/migrations/20261010_create_password_reset_otps.sql
  ```

- Import the authentication rate-limit migration for sign-in and password-reset throttling:

  ```text
  database/migrations/20261010_add_auth_rate_limits.sql
  ```

- Import `database/migrations/20261010_add_failed_login_locks.sql` to enable the account lock policy: five incorrect passwords pause sign-in for that email for five minutes; ten incorrect passwords lock the account until an administrator unlocks it. The admin user list shows whether an account was locked by failed attempts or by an administrator, including the administrator's name.

- To run the migration in phpMyAdmin, select `resume_analyzer`, open the **Import** tab, choose the migration file, then click **Import** or **Go**.
- If you are setting up an existing database that already has the tables, keep your data and run only the migration if the `positions.required_skills` column is missing. Do not import the base schema over an existing database unless you intend to replace its data.
- The default local database settings are `localhost`, `resume_analyzer`, `root`, and an empty password. For other environments, set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` as environment variables.
- Production deployments must set an HTTPS `APP_URL` and use a non-root database account with a strong password. Existing databases must also apply the authentication rate-limit migration above.

## 8. Configure the Gemini API key

- Set the `GEMINI_API_KEY` environment variable to your own Google Gemini API key. On Windows, use `setx GEMINI_API_KEY "your-new-key"`, then restart Apache.
- Do not publish or commit a real API key. Keep it private and revoke any key that was previously present in source control.
- AI-generated suggestions require internet access from PHP and the PHP `curl` extension. Configure the key before relying on AI analysis.

## 9. Configure password reset email

- Password reset sends a short-lived, one-time code through an SMTP email account. The SMTP sender can deliver to Gmail, Outlook, Hotmail, and other recipient addresses supported by that provider.
- Configure these environment variables in Windows PowerShell, replacing the example values with your provider's SMTP settings:

  ```powershell
  setx RESUME_SMTP_HOST "smtp.example.com"
  setx RESUME_SMTP_PORT "587"
  setx RESUME_SMTP_USERNAME "your-sender@example.com"
  setx RESUME_SMTP_PASSWORD "your-provider-app-password"
  setx RESUME_SMTP_ENCRYPTION "tls"
  setx RESUME_MAIL_FROM_EMAIL "your-sender@example.com"
  setx RESUME_MAIL_FROM_NAME "ResumeAI"
  ```

- Use the SMTP host, port, and encryption required by your email provider. For authenticated accounts, use an app password or SMTP credential when the provider requires it; do not use your normal account password unless the provider explicitly supports it.
- The `setx` commands save these values for your Windows user. Close and reopen the XAMPP Control Panel, then restart Apache so PHP can read them. If Apache runs as a Windows service, add the values as System environment variables instead.
- Keep SMTP credentials private. Do not commit them to the repository or paste them into `config/mail.php`; that file reads the environment variables.
- The reset flow sends a six-digit code to registered emails. Codes expire after 10 minutes, code entry is limited to five attempts, and resends are limited to once per minute. Responses do not disclose whether an email is registered. New passwords require at least 12 characters.

## 10. Check folder permissions

- Make sure the Apache/PHP process can write uploaded files into the project `uploads` folder.
- On a standard local XAMPP installation, the project folder is usually writable. If upload fails, grant the Windows account running Apache permission to write to `uploads`.
- Keep this folder in place even when it is empty; it stores uploaded resume PDFs.

## 11. Start and open the application

- In the XAMPP Control Panel, start **Apache** and **MySQL**.
- Open the application in a browser:

  ```text
  http://localhost/Resumeanalyzer/
  ```

- Register a user account or sign in.
- For resume analysis, add a position and enter each required skill as a separate skill, upload a PDF resume, choose the position, and start the analysis.
- Downloadable PDF reports require TCPDF. OCR for scanned resumes requires Poppler and Tesseract. Text-based PDFs require Poppler.
- Admins can open **Audit log** from the top navigation. It records account registration/sign-in, resume views/uploads/deletions/analysis, PDF report downloads, position changes, and administrator account/resume deletions. The page is admin-only and supports 25, 50, 80, or all rows with pagination.
- Admins can add accounts, edit user details, and lock or unlock standard user accounts from **Manage Users**. Locked accounts cannot sign in and see a message asking them to contact an administrator. Admin accounts and the currently signed-in administrator are protected from the lock control.

## 12. Verify the installation

- Confirm the following files exist:

  ```text
  tools\poppler\Library\bin\pdftotext.exe
  tools\poppler\Library\bin\pdftoppm.exe
  tools\tcpdf\tcpdf.php
  ```

- If using OCR, confirm Tesseract runs from PowerShell:

  ```powershell
  & 'C:\Program Files\Tesseract-OCR\tesseract.exe' --version
  ```

- Confirm phpMyAdmin shows the `resume_analyzer` tables and that the `positions` table has a `required_skills` column.
- Confirm `password_reset_otps` exists and the `vendor/autoload.php` file was created by Composer.
- Upload a text-based PDF to check extraction. For an image-only scanned PDF, verify Tesseract is installed and the English language data is available.

## Troubleshooting

- **Database connection failed:** Check that MySQL is running and that `config/db.php` has the right database name and credentials.
- **`required_skills` column not found:** Select `resume_analyzer` in phpMyAdmin and run `database/migrations/20261010_add_required_skills_to_positions.sql`.
- **`pdftotext.exe not found`:** Check that Poppler is installed under `tools\poppler\Library\bin` and that the executable name is `pdftotext.exe`.
- **Resume text is empty:** Confirm the PDF is readable. Scanned PDFs need Poppler's `pdftoppm.exe`, Tesseract OCR, and English language data.
- **PDF report generation fails:** Check that `tools\tcpdf\tcpdf.php` exists.
- **Upload fails:** Check that PHP can write to `uploads` and that the PDF is within PHP's upload size limits in `php.ini` (`upload_max_filesize` and `post_max_size`). Restart Apache after changing `php.ini`.
- **Gemini analysis fails:** Check the API key, PHP `curl` extension, internet access, and the API account's access/quota.
- **Password reset emails do not send:** Run `composer install`, verify `password_reset_otps` exists, check the SMTP environment variables, and restart Apache. Check the Apache/PHP error log for SMTP connection details.

## Current checkout setup status

- Poppler is present under `tools\poppler\Library\bin` (`pdftotext.exe` and `pdftoppm.exe` are available).
- TCPDF is present at `tools\tcpdf\tcpdf.php`.
- Tesseract OCR is installed at `C:\Program Files\Tesseract-OCR\tesseract.exe` on the current development computer.
- These local tools and uploads are ignored by Git, so their presence here does not guarantee they will be included in a fresh clone.

## 13. Deploy online for free (Oracle Cloud Always Free)

The app needs PHP, MySQL, Poppler, and Tesseract. Free PHP shared hosts often block command execution, and hosts with temporary databases or file storage can lose uploaded resumes. This deployment runs the PHP app, MySQL, and PDF tools together on an Oracle Cloud Always Free Linux VM; Caddy obtains and renews HTTPS certificates for a domain you control.

### Important before publishing

- The deployment starts with an empty database. It creates the current tables but does not copy local user accounts, resume files, analysis history, or other private records.
- The local `SQL_file/resume_analyzer.sql` contains sample user records and resume metadata. It is intentionally not used by this deployment.
- A Gemini API key was previously present in local configuration. It is no longer read from source; set a newly generated key in the server `.env` and revoke the old key in Google AI Studio/Google Cloud before making this project public.
- Password reset email only works after valid SMTP settings are entered in `.env`.
- Oracle requires payment-method verification to create a Free Tier account. Oracle describes Always Free resources as free for the life of the account, but only resources marked Always Free remain free; do not upgrade the account or select paid resources.
- A domain name is required for automatic HTTPS. Point its DNS A record at the VM's public IPv4 address before starting the app.

### Create the free server

- Create an [Oracle Cloud Free Tier account](https://www.oracle.com/cloud/free/), complete verification, and select your home region carefully.
- In the Oracle Cloud Console, create an **Always Free eligible** Ubuntu 24.04 VM. An Ampere A1 instance is suitable if available in your region; keep the total within the Always Free limits.
- Assign a public IPv4 address and add ingress rules for TCP ports `80` and `443`. Restrict SSH on port `22` to your own IP address.
- Point your domain's DNS A record to that public IP and wait until it resolves.
- Connect to the VM using SSH, then install Docker and Git:

  ```bash
  sudo apt update
  sudo apt install -y docker.io docker-compose-v2 git
  sudo systemctl enable --now docker
  sudo ufw allow 80/tcp
  sudo ufw allow 443/tcp
  ```

### Put the project on the server

- Push the reviewed project files to a Git repository that you control. Keep it private until the exposed Gemini key has been revoked and the project has been reviewed for any other secrets.
- On the VM, clone that repository and enter its project folder:

  ```bash
  git clone YOUR_PRIVATE_OR_PUBLIC_REPOSITORY_URL Resumeanalyzer
  cd Resumeanalyzer
  ```

- Create the server-only environment file and edit it. Do not commit or share `.env`:

  ```bash
  cp .env.example .env
  nano .env
  ```

- Generate two different database passwords with `openssl rand -hex 32`, set `APP_DOMAIN` to the domain name, and add the newly generated `GEMINI_API_KEY`.
- Restrict the environment file to the VM owner:

  ```bash
  chmod 600 .env
  ```
- To enable password reset, fill in the `RESUME_SMTP_*` values and sender email. Leave those empty only if password reset is not needed yet.
- Keep the MySQL port private; the Compose setup does not publish it to the internet.

### Start the application

- Build and start the app, HTTPS proxy, and database:

  ```bash
  sudo docker compose up -d --build
  ```

- The first database startup creates an empty schema from `database/schema.sql`. Docker volumes retain the MySQL database, uploaded PDFs, generated reports, and HTTPS certificates through app rebuilds and VM restarts.
- Open `https://YOUR_DOMAIN` and register the first account.
- Promote that account to administrator from the VM. Open the MySQL prompt, then run the SQL statement with the email used during registration:

  ```bash
  sudo docker compose exec db mysql -u root -p
  ```

  ```sql
  UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
  ```

- Sign out and back in so the administrator role is refreshed in the session.
- Check service output when troubleshooting:

  ```bash
  sudo docker compose logs --tail=100 app db caddy
  ```

### Back up and update

- Keep regular backups outside the VM. This deployment uses persistent Docker volumes, but the free server itself is not a backup service.
- Back up the database to a protected file:

  ```bash
  sudo docker compose exec -T db sh -lc 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > resume_analyzer_backup.sql
  ```

- Back up `uploads/` data from the `resume_uploads` Docker volume as well if you need to retain uploaded CVs.
- To update after reviewed code has been pushed, run `git pull` and `sudo docker compose up -d --build` in the project folder.
- Never upload private resume files to a public Git repository or send database backups through public issue trackers.
