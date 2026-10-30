<?php
/**
 * Professional ATS Resume Analysis Engine
 * Handles intelligent skill extraction from full job descriptions, sentences, bullet points,
 * and comma/line lists, with case-insensitive matching and ATS scoring.
 */

class ResumeAnalysisEngine
{
    /**
     * Comprehensive catalog of technical & professional skills.
     * Maps lowercase lookup key => Canonical Professional Display Name.
     */
    private static array $skillCatalog = [
        // Programming Languages
        'php' => 'PHP',
        'python' => 'Python',
        'javascript' => 'JavaScript',
        'typescript' => 'TypeScript',
        'java' => 'Java',
        'c++' => 'C++',
        'c#' => 'C#',
        'ruby' => 'Ruby',
        'golang' => 'Go / Golang',
        'go' => 'Go / Golang',
        'rust' => 'Rust',
        'swift' => 'Swift',
        'kotlin' => 'Kotlin',
        'dart' => 'Dart',
        'scala' => 'Scala',
        'r' => 'R',
        'perl' => 'Perl',
        'bash' => 'Bash / Shell',
        'shell' => 'Bash / Shell',
        'powershell' => 'PowerShell',
        'sql' => 'SQL',

        // Frontend Technologies
        'html' => 'HTML5',
        'html5' => 'HTML5',
        'css' => 'CSS3',
        'css3' => 'CSS3',
        'sass' => 'Sass / SCSS',
        'scss' => 'Sass / SCSS',
        'tailwind' => 'Tailwind CSS',
        'tailwind css' => 'Tailwind CSS',
        'tailwindcss' => 'Tailwind CSS',
        'bootstrap' => 'Bootstrap',
        'react' => 'React',
        'reactjs' => 'React',
        'react.js' => 'React',
        'react native' => 'React Native',
        'next.js' => 'Next.js',
        'nextjs' => 'Next.js',
        'vue' => 'Vue.js',
        'vuejs' => 'Vue.js',
        'vue.js' => 'Vue.js',
        'nuxt' => 'Nuxt.js',
        'nuxtjs' => 'Nuxt.js',
        'angular' => 'Angular',
        'angularjs' => 'Angular',
        'svelte' => 'Svelte',
        'jquery' => 'jQuery',
        'redux' => 'Redux',
        'webpack' => 'Webpack',
        'vite' => 'Vite',

        // Backend & Frameworks
        'node.js' => 'Node.js',
        'nodejs' => 'Node.js',
        'node' => 'Node.js',
        'express' => 'Express.js',
        'express.js' => 'Express.js',
        'expressjs' => 'Express.js',
        'nestjs' => 'NestJS',
        'laravel' => 'Laravel',
        'symfony' => 'Symfony',
        'codeigniter' => 'CodeIgniter',
        'django' => 'Django',
        'flask' => 'Flask',
        'fastapi' => 'FastAPI',
        'spring' => 'Spring Boot',
        'spring boot' => 'Spring Boot',
        'asp.net' => 'ASP.NET',
        'asp.net core' => 'ASP.NET Core',
        '.net' => '.NET',
        'dotnet' => '.NET',
        'ruby on rails' => 'Ruby on Rails',
        'rails' => 'Ruby on Rails',
        'graphql' => 'GraphQL',
        'rest api' => 'REST APIs',
        'rest apis' => 'REST APIs',
        'restful' => 'REST APIs',
        'restful api' => 'REST APIs',
        'restful apis' => 'REST APIs',
        'api development' => 'API Development',
        'microservices' => 'Microservices',
        'grpc' => 'gRPC',
        'websockets' => 'WebSockets',

        // Databases & Caching
        'mysql' => 'MySQL',
        'postgresql' => 'PostgreSQL',
        'postgres' => 'PostgreSQL',
        'mongodb' => 'MongoDB',
        'redis' => 'Redis',
        'sqlite' => 'SQLite',
        'mariadb' => 'MariaDB',
        'oracle' => 'Oracle Database',
        'sql server' => 'MS SQL Server',
        'mssql' => 'MS SQL Server',
        'cassandra' => 'Apache Cassandra',
        'elasticsearch' => 'Elasticsearch',
        'dynamodb' => 'Amazon DynamoDB',
        'firebase' => 'Firebase',
        'prisma' => 'Prisma ORM',

        // Cloud & DevOps
        'aws' => 'AWS',
        'amazon web services' => 'AWS',
        'azure' => 'Microsoft Azure',
        'gcp' => 'Google Cloud (GCP)',
        'google cloud' => 'Google Cloud (GCP)',
        'docker' => 'Docker',
        'kubernetes' => 'Kubernetes',
        'k8s' => 'Kubernetes',
        'terraform' => 'Terraform',
        'ansible' => 'Ansible',
        'ci/cd' => 'CI/CD',
        'cicd' => 'CI/CD',
        'continuous integration' => 'CI/CD',
        'jenkins' => 'Jenkins',
        'github actions' => 'GitHub Actions',
        'gitlab ci' => 'GitLab CI',
        'linux' => 'Linux',
        'ubuntu' => 'Ubuntu',
        'nginx' => 'Nginx',
        'apache' => 'Apache',

        // Tools & Version Control
        'git' => 'Git',
        'github' => 'GitHub',
        'gitlab' => 'GitLab',
        'bitbucket' => 'Bitbucket',
        'jira' => 'Jira',
        'postman' => 'Postman',
        'swagger' => 'Swagger / OpenAPI',
        'figma' => 'Figma',

        // Testing & QA
        'unit testing' => 'Unit Testing',
        'phpunit' => 'PHPUnit',
        'jest' => 'Jest',
        'cypress' => 'Cypress',
        'selenium' => 'Selenium',
        'tdd' => 'Test-Driven Development (TDD)',

        // Methodologies & Architecture
        'agile' => 'Agile Methodology',
        'scrum' => 'Scrum',
        'kanban' => 'Kanban',
        'oop' => 'OOP (Object-Oriented Programming)',
        'object-oriented' => 'OOP (Object-Oriented Programming)',
        'mvc' => 'MVC Architecture',
        'system design' => 'System Design',
        'data structures' => 'Data Structures',
        'algorithms' => 'Algorithms',
        'devops' => 'DevOps',

        // Data & AI / ML
        'machine learning' => 'Machine Learning',
        'deep learning' => 'Deep Learning',
        'artificial intelligence' => 'Artificial Intelligence (AI)',
        'data analysis' => 'Data Analysis',
        'pandas' => 'Pandas',
        'numpy' => 'NumPy',
        'tensorflow' => 'TensorFlow',
        'pytorch' => 'PyTorch',
        'tableau' => 'Tableau',
        'power bi' => 'Power BI',

        // Core Professional Competencies
        'problem solving' => 'Problem Solving',
        'teamwork' => 'Teamwork',
        'leadership' => 'Leadership',
        'communication' => 'Communication Skills',
        'project management' => 'Project Management',
        'time management' => 'Time Management',
        'code review' => 'Code Review'
    ];

    /**
     * Aliases for accurate case-insensitive resume matching.
     */
    private static array $skillAliases = [
        'PHP' => ['php'],
        'Python' => ['python'],
        'JavaScript' => ['javascript', 'js', 'es6'],
        'TypeScript' => ['typescript', 'ts'],
        'Java' => ['java'],
        'C++' => ['c\+\+', 'cpp'],
        'C#' => ['c\#', 'c\s*sharp'],
        'Go / Golang' => ['golang', 'go'],
        'HTML5' => ['html', 'html5'],
        'CSS3' => ['css', 'css3'],
        'Tailwind CSS' => ['tailwind', 'tailwindcss'],
        'Bootstrap' => ['bootstrap'],
        'React' => ['react', 'reactjs', 'react\.js'],
        'React Native' => ['react\s*native'],
        'Next.js' => ['next\.js', 'nextjs'],
        'Vue.js' => ['vue', 'vuejs', 'vue\.js'],
        'Nuxt.js' => ['nuxt', 'nuxtjs', 'nuxt\.js'],
        'Angular' => ['angular', 'angularjs'],
        'Node.js' => ['node', 'nodejs', 'node\.js'],
        'Express.js' => ['express', 'expressjs', 'express\.js'],
        'Laravel' => ['laravel'],
        'Django' => ['django'],
        'Flask' => ['flask'],
        'Spring Boot' => ['spring\s*boot', 'spring\s*framework'],
        'ASP.NET' => ['asp\.net', '\.net'],
        'REST APIs' => ['rest\s*apis?', 'restful(\s*apis?)?', 'rest'],
        'GraphQL' => ['graphql'],
        'API Development' => ['api\s*development', 'apis?'],
        'MySQL' => ['mysql'],
        'PostgreSQL' => ['postgresql', 'postgres', 'psql'],
        'MongoDB' => ['mongodb', 'mongo'],
        'Redis' => ['redis'],
        'SQLite' => ['sqlite'],
        'AWS' => ['aws', 'amazon\s*web\s*services'],
        'Microsoft Azure' => ['azure', 'microsoft\s*azure'],
        'Google Cloud (GCP)' => ['gcp', 'google\s*cloud'],
        'Docker' => ['docker', 'containerization', 'containers'],
        'Kubernetes' => ['kubernetes', 'k8s'],
        'CI/CD' => ['ci\s*/\s*cd', 'ci\s+cd', 'continuous\s+integration'],
        'Git' => ['git', 'github', 'gitlab'],
        'Linux' => ['linux', 'ubuntu'],
        'Unit Testing' => ['unit\s*test(ing|s)?', 'phpunit', 'jest'],
        'Agile Methodology' => ['agile', 'scrum'],
        'OOP (Object-Oriented Programming)' => ['oop', 'object[\s-]oriented'],
        'Machine Learning' => ['machine\s*learning', 'ml'],
        'Artificial Intelligence (AI)' => ['artificial\s*intelligence', 'ai'],
        'Problem Solving' => ['problem\s*solving', 'troubleshooting'],
        'Communication Skills' => ['communication(\s*skills)?']
    ];

    /**
     * Normalizes text for exact token and phrase matching while preserving
     * technical symbols like +, #, . inside words (e.g. C++, C#, .NET, Node.js).
     */
    public static function normalizeText(string $text): string
    {
        // Replace structural punctuation with spaces
        $normalized = preg_replace('/[(),;:!?\[\]{}"\x{201C}\x{201D}\x{2018}\x{2019}]/u', ' ', $text);
        // Replace periods that act as sentence/list punctuation (followed by whitespace or end)
        $normalized = preg_replace('/\.(?=\s|$)/u', ' ', $normalized);
        // Replace forward slashes when surrounded by spaces
        $normalized = preg_replace('/\s+\/\s+/u', ' ', $normalized);
        // Normalize all whitespace runs to single space
        return ' ' . trim(preg_replace('/\s+/u', ' ', $normalized)) . ' ';
    }

    /**
     * Extracts skills intelligently from any input:
     * - Sentences (e.g. "We need a developer skilled in PHP, MySQL, and Docker.")
     * - Bullet points (e.g. "• Experience with React and Tailwind CSS")
     * - Comma-separated or newline-separated skill lists
     * - Full pasted job descriptions
     */
    public static function extractSkills(string $rawInput, array $positionSkills = []): array
    {
        $rawInput = trim($rawInput);
        if ($rawInput === '') {
            return $positionSkills;
        }

        $detectedSkills = [];
        $normalizedInput = self::normalizeText($rawInput);
        $workingText = $normalizedInput;

        // 1. Scan for known catalog skills (sorted by descending length to match longest multi-word skills first)
        $catalogKeys = array_keys(self::$skillCatalog);
        usort($catalogKeys, fn($a, $b) => strlen($b) <=> strlen($a));

        foreach ($catalogKeys as $key) {
            $escaped = preg_quote($key, '~');
            if (preg_match('~(?<=[\s])' . $escaped . '(?=[\s])~i', $workingText)) {
                $canonical = self::$skillCatalog[$key];
                $detectedSkills[$canonical] = true;
                // Blank out this match in working copy so nested substrings don't create false duplicates
                $workingText = preg_replace('~(?<=[\s])' . $escaped . '(?=[\s])~i', ' ___MATCHED___ ', $workingText);
            }
        }

        // 2. Parse structured list items, bullet points, or comma-separated items
        $lines = preg_split('/[\r\n]+/', $rawInput);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Strip bullet markers (•, -, *, 1., etc.)
            $cleanLine = trim(preg_replace('/^(?:[-*•#>]|\d+[\.)])\s*/u', '', $line));

            // Split line by commas, semicolons, pipes, or "and" / "&"
            $chunks = preg_split('/[,;|]+|\s+(?:and|&)\s+/iu', $cleanLine);
            foreach ($chunks as $chunk) {
                $chunk = trim($chunk);
                if ($chunk === '') continue;

                // Strip common job description boilerplate prefixes
                $cleaned = preg_replace(
                    '/^(?:strong\s+(?:knowledge|experience)\s+(?:of|in|with)|hands-on\s+experience\s+(?:in|with)|experience\s+(?:in|with)|proficient\s+(?:in|with)|familiarity\s+with|familiar\s+with|working\s+knowledge\s+of|working\s+with|understanding\s+of|ability\s+to|responsible\s+for|must\s+have|plus\s+point|looking\s+for)\s+/i',
                    '',
                    $chunk
                );
                $cleaned = trim($cleaned);

                // Skip chunks that contain common English prepositions/conjunctions (they are sentences or clauses, not skill names)
                if (preg_match('/\b(?:of|in|with|for|to|and|the|a|an|from|by|on|at|as|is|are|experience|knowledge|understanding)\b/i', $cleaned)) {
                    continue;
                }

                // Skip if this chunk is already represented by an existing detected skill
                foreach (array_keys($detectedSkills) as $alreadyFound) {
                    if (stripos($cleaned, $alreadyFound) !== false || stripos($alreadyFound, $cleaned) !== false) {
                        continue 2;
                    }
                }

                // If cleaned chunk is a short candidate skill phrase (1-3 words, under 30 chars, no sentence ending)
                if ($cleaned !== '' && mb_strlen($cleaned) <= 30 && !preg_match('/[.!?]\s+[a-z]/i', $cleaned)) {
                    $lowerChunk = strtolower($cleaned);
                    if (isset(self::$skillCatalog[$lowerChunk])) {
                        $detectedSkills[self::$skillCatalog[$lowerChunk]] = true;
                    } elseif (preg_match('/^[a-z0-9+#.\s\/-]{2,30}$/i', $cleaned)) {
                        if (!self::isGenericFillerWord($lowerChunk)) {
                            $detectedSkills[self::toCanonicalCase($cleaned)] = true;
                        }
                    }
                }
            }
        }

        // 3. Merge position skills if provided
        foreach ($positionSkills as $posSkill) {
            $posSkill = trim($posSkill);
            if ($posSkill !== '') {
                $detectedSkills[self::toCanonicalCase($posSkill)] = true;
            }
        }

        // 4. Fallback if no skills detected at all: extract non-filler keywords
        if (empty($detectedSkills)) {
            $words = explode(' ', strtolower(self::normalizeText($rawInput)));
            foreach ($words as $word) {
                $word = trim($word);
                if ($word !== '' && strlen($word) >= 2 && !self::isGenericFillerWord($word)) {
                    $detectedSkills[self::toCanonicalCase($word)] = true;
                }
            }
        }

        return array_keys($detectedSkills);
    }

    /**
     * Matches extracted skills against the resume text completely case-insensitively.
     */
    public static function matchSkills(array $skills, string $resumeText): array
    {
        if (empty($skills)) {
            return [
                'matched' => [],
                'missing' => [],
                'score' => 0
            ];
        }

        $normalizedResume = self::normalizeText($resumeText);
        $matched = [];
        $missing = [];

        foreach ($skills as $skill) {
            $isMatched = false;
            $lowerSkill = strtolower($skill);

            // Check if skill has specific alias patterns
            if (isset(self::$skillAliases[$skill])) {
                foreach (self::$skillAliases[$skill] as $pattern) {
                    if (preg_match('~(?<=[\s])' . $pattern . '(?=[\s])~i', $normalizedResume)) {
                        $isMatched = true;
                        break;
                    }
                }
            } else {
                // Generic regex match with boundary lookaround
                $escaped = preg_quote($lowerSkill, '~');
                if (preg_match('~(?<=[\s])' . $escaped . '(?=[\s])~i', $normalizedResume)) {
                    $isMatched = true;
                }
            }

            if ($isMatched) {
                $matched[] = $skill;
            } else {
                $missing[] = $skill;
            }
        }

        $total = count($skills);
        $score = $total > 0 ? (int)round((count($matched) / $total) * 100) : 0;

        return [
            'matched' => $matched,
            'missing' => $missing,
            'score' => min(100, max(0, $score))
        ];
    }

    /**
     * Calculates an ATS (Applicant Tracking System) compatibility score.
     */
    public static function calculateAtsScore(string $resumeText, int $matchScore): int
    {
        $atsScore = 0;
        $lower = strtolower($resumeText);

        // 1. Section Headings (up to 45 pts)
        if (preg_match('/(?:experience|work\s+history|employment|work\s+experience)/i', $lower)) {
            $atsScore += 15;
        }
        if (preg_match('/(?:skills|technical\s+skills|core\s+competencies|technologies)/i', $lower)) {
            $atsScore += 15;
        }
        if (preg_match('/(?:education|academic|qualifications|degree)/i', $lower)) {
            $atsScore += 15;
        }

        // 2. Contact Information Presence (up to 15 pts)
        if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $resumeText)) {
            $atsScore += 10; // Email found
        }
        if (preg_match('/(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/', $resumeText)) {
            $atsScore += 5; // Phone number found
        }

        // 3. Adequate Length & Word Density (up to 15 pts)
        $charCount = strlen($resumeText);
        if ($charCount >= 800) {
            $atsScore += 15;
        } elseif ($charCount >= 400) {
            $atsScore += 8;
        }

        // 4. Keyword Match Alignment (up to 25 pts)
        if ($matchScore >= 80) {
            $atsScore += 25;
        } elseif ($matchScore >= 60) {
            $atsScore += 20;
        } elseif ($matchScore >= 40) {
            $atsScore += 15;
        } elseif ($matchScore >= 20) {
            $atsScore += 10;
        }

        return min(100, max(10, $atsScore));
    }

    /**
     * Checks if a word is an everyday generic English word or job posting filler.
     */
    private static function isGenericFillerWord(string $word): bool
    {
        static $filler = [
            'we', 'are', 'looking', 'for', 'a', 'an', 'the', 'and', 'or', 'to', 'in', 'on', 'at', 'with', 'by',
            'candidate', 'candidates', 'ideal', 'should', 'have', 'must', 'has', 'will', 'skills', 'skill',
            'experience', 'years', 'year', 'required', 'requirement', 'requirements', 'preferred', 'plus',
            'team', 'join', 'our', 'work', 'working', 'good', 'strong', 'excellent', 'responsibilities',
            'develop', 'developer', 'developing', 'development', 'design', 'designing', 'maintain',
            'manage', 'management', 'support', 'help', 'building', 'create', 'creating', 'solve', 'solving',
            'using', 'used', 'use', 'high', 'level', 'quality', 'fast', 'paced', 'environment', 'degree',
            'bachelor', 'master', 'proficient', 'knowledge', 'understanding', 'familiarity', 'familiar'
        ];
        return in_array($word, $filler, true);
    }

    /**
     * Converts a string into proper canonical casing.
     */
    private static function toCanonicalCase(string $skill): string
    {
        $lower = strtolower(trim($skill));
        if (isset(self::$skillCatalog[$lower])) {
            return self::$skillCatalog[$lower];
        }

        // If it's already mixed-case like "NodeJS" or "RESTful", keep it
        if (preg_match('/[a-z][A-Z]/', $skill)) {
            return trim($skill);
        }

        // Otherwise Title Case
        return ucwords($lower);
    }
}
