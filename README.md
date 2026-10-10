# Resume Analyzer

AI-powered resume analysis web application built with PHP, MySQL, Tailwind CSS, and Google Gemini AI. It extracts text from PDF resumes, compares candidate qualifications against job descriptions, calculates ATS match scores, and generates downloadable PDF reports.

---

## 🛠️ Requirements & Tools Setup

> **Note:** The `tools/` and `uploads/` contents are excluded from Git (via `.gitignore`). After cloning this repository, you must set up the `tools/` folder for PDF text extraction and PDF generation.

### Expected Directory Structure
```text
Resumeanalyzer/
├── tools/
│   ├── poppler/
│   │   └── Library/
│   │       └── bin/
│   │           └── pdftotext.exe
│   └── tcpdf/
│       └── tcpdf.php
├── uploads/
│   └── .gitkeep
...
```

---

## 📥 Installation Steps for `tools/`

### 1. Create the `tools` Directory
In the root directory of the project, create the `tools` folder:

**Command Line (PowerShell):**
```powershell
mkdir tools
```

**Command Line (Bash / CMD):**
```bash
mkdir tools
```

---

### 2. Install TCPDF (for PDF Report Generation)

The project expects TCPDF at `tools/tcpdf/tcpdf.php`.

#### Option A: Via Git (Recommended)
Run the following command from the project root:
```bash
git clone https://github.com/tecnickcom/TCPDF.git tools/tcpdf
```

#### Option B: Manual Download
1. Download the latest release from the [TCPDF GitHub Repository](https://github.com/tecnickcom/TCPDF).
2. Extract the archive into `tools/` and rename the extracted folder to `tcpdf`.
3. Verify that `tools/tcpdf/tcpdf.php` is present.

---

### 3. Install Poppler (for PDF Text Extraction on Windows)

The resume analyzer uses `pdftotext.exe` from the Poppler package to parse PDF resumes. The project expects the executable at:
`tools/poppler/Library/bin/pdftotext.exe`

#### Option A: Via PowerShell
Run these commands in PowerShell from the project root:
```powershell
# Download latest Poppler Windows binary
Invoke-WebRequest -Uri "https://github.com/oschwartz10612/poppler-windows/releases/download/v24.08.0-0/Release-24.08.0-0.zip" -OutFile "tools\poppler.zip"

# Extract archive into tools
Expand-Archive -Path "tools\poppler.zip" -DestinationPath "tools"

# Rename extracted folder to 'poppler'
Rename-Item -Path "tools\poppler-24.08.0" -NewName "poppler"

# Delete temporary zip
Remove-Item "tools\poppler.zip"
```

#### Option B: Manual Download
1. Visit the [Poppler for Windows Releases](https://github.com/oschwartz10612/poppler-windows/releases).
2. Download the latest `Release-XX.XX.X-X.zip`.
3. Extract the contents inside the `tools/` directory.
4. Rename the extracted folder (e.g., `poppler-24.08.0`) to `poppler`.
5. Ensure the file path exists: `tools/poppler/Library/bin/pdftotext.exe`.

---

## 🚀 Full Project Setup

### 1. Database Configuration
1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`).
3. Create a database named `resume_analyzer`.
4. Import the SQL schema file located at:
   ```text
   SQL_file/resume_analyzer.sql
   ```
5. Check `config/db.php` if your MySQL username or password differs from default (`root` / empty password).

### 2. Configure Project URL & API Key
Open `config/config.php` and verify:
- **`$project_folder`**: Must match your folder name in `htdocs` (e.g. `/Resumeanalyzer` or `/resume-analyzer`).
- **`GEMINI_API_KEY`**: Provide your Google Gemini API key.

### 3. Run Application
Navigate to your application in the browser:
```text
http://localhost/Resumeanalyzer
```
