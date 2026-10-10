# Resume Analyzer

Resume Analyzer is a PHP and MySQL web application that extracts text from PDF resumes, compares resumes with job descriptions and required skills, calculates a match score, and can create downloadable PDF reports. Gemini is used for AI-generated analysis where configured.

## 1. Requirements

- Windows with [XAMPP](https://www.apachefriends.org/) installed.
- Apache and MySQL/MariaDB enabled in the XAMPP Control Panel.
- PHP 8.x (the version bundled with a current XAMPP release is recommended).
- PHP extensions enabled in XAMPP:
  - `pdo_mysql` for the database connection.
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

- To run the migration in phpMyAdmin, select `resume_analyzer`, open the **Import** tab, choose the migration file, then click **Import** or **Go**.
- If you are setting up an existing database that already has the tables, keep your data and run only the migration if the `positions.required_skills` column is missing. Do not import the base schema over an existing database unless you intend to replace its data.
- Open `config/db.php` and update the host, database name, username, and password if your MySQL settings differ from the XAMPP defaults (`localhost`, `resume_analyzer`, `root`, and an empty password).

## 8. Configure the Gemini API key

- Open `config/config.php` and set `GEMINI_API_KEY` to your own Google Gemini API key.
- Do not publish or commit a real API key. Keep it private and replace any example or previously exposed key with your own.
- AI-generated suggestions require internet access from PHP and the PHP `curl` extension. Configure the key before relying on AI analysis.

## 9. Check folder permissions

- Make sure the Apache/PHP process can write uploaded files into the project `uploads` folder.
- On a standard local XAMPP installation, the project folder is usually writable. If upload fails, grant the Windows account running Apache permission to write to `uploads`.
- Keep this folder in place even when it is empty; it stores uploaded resume PDFs.

## 10. Start and open the application

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

## 11. Verify the installation

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
- Upload a text-based PDF to check extraction. For an image-only scanned PDF, verify Tesseract is installed and the English language data is available.

## Troubleshooting

- **Database connection failed:** Check that MySQL is running and that `config/db.php` has the right database name and credentials.
- **`required_skills` column not found:** Select `resume_analyzer` in phpMyAdmin and run `database/migrations/20261010_add_required_skills_to_positions.sql`.
- **`pdftotext.exe not found`:** Check that Poppler is installed under `tools\poppler\Library\bin` and that the executable name is `pdftotext.exe`.
- **Resume text is empty:** Confirm the PDF is readable. Scanned PDFs need Poppler's `pdftoppm.exe`, Tesseract OCR, and English language data.
- **PDF report generation fails:** Check that `tools\tcpdf\tcpdf.php` exists.
- **Upload fails:** Check that PHP can write to `uploads` and that the PDF is within PHP's upload size limits in `php.ini` (`upload_max_filesize` and `post_max_size`). Restart Apache after changing `php.ini`.
- **Gemini analysis fails:** Check the API key, PHP `curl` extension, internet access, and the API account's access/quota.

## Current checkout setup status

- Poppler is present under `tools\poppler\Library\bin` (`pdftotext.exe` and `pdftoppm.exe` are available).
- TCPDF is present at `tools\tcpdf\tcpdf.php`.
- Tesseract OCR is installed at `C:\Program Files\Tesseract-OCR\tesseract.exe` on the current development computer.
- These local tools and uploads are ignored by Git, so their presence here does not guarantee they will be included in a fresh clone.
