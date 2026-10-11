-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2026 at 07:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resume_analyzer`
--

-- --------------------------------------------------------

--
-- Table structure for table `analysis_results`
--

CREATE TABLE `analysis_results` (
  `id` int(11) NOT NULL,
  `resume_id` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `ats_score` int(11) DEFAULT NULL,
  `matched_keywords` text DEFAULT NULL,
  `missing_keywords` text DEFAULT NULL,
  `sections_detected` text DEFAULT NULL,
  `suggestions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `analysis_results`
--

INSERT INTO `analysis_results` (`id`, `resume_id`, `score`, `ats_score`, `matched_keywords`, `missing_keywords`, `sections_detected`, `suggestions`, `created_at`) VALUES
(12, 6, 8, 20, 'performance,knowledge,skills,good,work,communication,teamwork,collaboration,problem,solving', 'we,are,looking,skilled,web,developer,database,administrator,join,our,team,ideal,candidate,should,have,strong,experience,php,mysql,javascript,api,development,responsibilities,design,develop,maintain,scalable,applications,optimize,using,sql,indexing,techniques,apis,integrate,third,party,services,troubleshoot,debug,improve,collaborate,cross,functional,teams,including,designers,developers,requirements,laravel,or,postgresql,databases,familiarity,html,css,modern,frameworks,understanding,normalization,query,optimization,version,control,systems,like,git,preferred,react,frontend,cloud,deployment,security,best,practices,automation,scripting,soft,time,management,attention,detail', NULL, 'Your resume does not match job keywords well. Add more relevant skills. | Your resume is not ATS optimized. Improve structure and keyword usage. | Missing important technical skills like PHP, MySQL, or APIs. | Add a clear experience section.', '2026-03-23 23:48:38'),
(13, 5, 0, 0, '', 'we,are,looking,skilled,web,developer,database,administrator,join,our,team,ideal,candidate,should,have,strong,experience,php,mysql,javascript,api,development,responsibilities,design,develop,maintain,scalable,applications,optimize,performance,using,sql,indexing,techniques,work,apis,integrate,third,party,services,troubleshoot,debug,improve,collaborate,cross,functional,teams,including,designers,developers,requirements,knowledge,laravel,or,postgresql,databases,familiarity,html,css,modern,frameworks,understanding,normalization,query,optimization,version,control,systems,like,git,good,communication,teamwork,skills,preferred,react,frontend,cloud,deployment,security,best,practices,automation,scripting,soft,problem,solving,collaboration,time,management,attention,detail', NULL, 'Your resume does not match job keywords well. Add more relevant skills. | Your resume is not ATS optimized. Improve structure and keyword usage. | Missing important technical skills like PHP, MySQL, or APIs. | Add a clear experience section. | Add a skills section.', '2026-03-23 23:49:04'),
(14, 5, 0, 0, '', 'we,are,looking,skilled,web,developer,database,administrator,join,our,team,ideal,candidate,should,have,strong,experience,php,mysql,javascript,api,development,responsibilities,design,develop,maintain,scalable,applications,optimize,performance,using,sql,indexing,techniques,work,apis,integrate,third,party,services,troubleshoot,debug,improve,collaborate,cross,functional,teams,including,designers,developers,requirements,knowledge,laravel,or,postgresql,databases,familiarity,html,css,modern,frameworks,understanding,normalization,query,optimization,version,control,systems,like,git,good,communication,teamwork,skills,preferred,react,frontend,cloud,deployment,security,best,practices,automation,scripting,soft,problem,solving,collaboration,time,management,attention,detail', NULL, 'Your resume does not match job keywords well. Add more relevant skills. | Your resume is not ATS optimized. Improve structure and keyword usage. | Missing important technical skills like PHP, MySQL, or APIs. | Add a clear experience section. | Add a skills section.', '2026-03-23 23:49:19'),
(28, 6, 0, 20, '', 'php,sql,c', NULL, 'Add more relevant skills from job description. | Improve resume structure for ATS. | Add experience section.', '2026-03-24 20:51:53'),
(29, 6, 0, 20, '', 'nedd,it,expert', NULL, 'Add more relevant skills from job description. | Improve resume structure for ATS. | Add experience section.', '2026-03-24 22:19:22'),
(30, 9, 100, 100, 'php', '', NULL, '', '2026-06-23 19:33:33'),
(31, 9, 33, 80, 'php', 'nodejs,docker', NULL, 'Add more relevant skills from job description.', '2026-06-23 19:34:12'),
(32, 10, 0, 0, '', 'html,css,js', NULL, 'Add more relevant skills from job description. | Improve resume structure for ATS. | Add experience section. | Add skills section.', '2026-08-26 12:12:52'),
(33, 10, 0, 0, '', 'good,communication,skills', NULL, 'Add more relevant skills from job description. | Improve resume structure for ATS. | Add experience section. | Add skills section.', '2026-08-26 12:22:06'),
(34, 10, 0, 0, '', 'php', NULL, 'Populate the resume content immediately, as the current submission is completely blank, resulting in a zero match score and preventing ATS parsing. | Integrate the missing core technical keyword php prominently throughout the summary, skills, and professional experience sections to satisfy ATS filters. | Structure the resume into standard, machine-readable sections including Professional Summary, Technical Skills, Professional Experience, and Education. | Add quantifiable metrics to every job responsibility, such as percentage increases in application performance, team sizes managed, or revenue generated by PHP projects. | Include a dedicated Technical Skills section listing PHP alongside related backend technologies, frameworks, and databases like MySQL, Laravel, and Apache to capture broader keyword matches.', '2026-10-10 09:09:13'),
(35, 10, 0, 0, '', 'require,web,developer,strong,knowledge,front,back,end,development', NULL, 'Populate the resume content immediately, as the current submission is completely blank and resulted in a 0 percent match score across all Applicant Tracking Systems. | Integrate critical missing keywords from the job description directly into the resume text, including Web Developer, front-end development, and back-end development. | Add a dedicated Technical Skills section listing core proficiencies such as HTML, CSS, JavaScript, and relevant front-end and back-end frameworks to pass keyword filters. | Quantify all professional accomplishments in the experience section by including metrics, percentages, team sizes, or revenue impact for web development projects. | Use a clean, standard ATS-compatible structural layout with clear section headings like Summary, Skills, Experience, and Education while avoiding tables, columns, and graphics.', '2026-10-10 10:28:13'),
(36, 10, 0, 0, '', 'phphtmlcss', NULL, 'Incorporate critical missing keywords (phphtmlcss) into your skills and work history. | Align your project descriptions and job summaries more closely with the target position\'s requirements. | Optimize resume structure for ATS scanners using standard section titles and clean single-column formatting. | Add a clearly defined \'Professional Experience\' section detailing your past roles and achievements. | Add a dedicated \'Technical & Core Skills\' section categorized by languages, frameworks, and tools.', '2026-10-10 10:38:38'),
(37, 10, 0, 0, '', 'php,html,css', NULL, 'Incorporate critical missing keywords (php, html, css) into your skills and work history. | Align your project descriptions and job summaries more closely with the target position\'s requirements. | Optimize resume structure for ATS scanners using standard section titles and clean single-column formatting. | Add a clearly defined \'Professional Experience\' section detailing your past roles and achievements. | Add a dedicated \'Technical & Core Skills\' section categorized by languages, frameworks, and tools.', '2026-10-10 10:38:55'),
(38, 10, 100, 100, 'PHP,HTML,CSS', '', NULL, 'Quantify your achievements using measurable metrics (e.g., \'Improved performance by 25%\' or \'Managed 5+ projects\').', '2026-10-10 10:50:57'),
(39, 10, 0, 80, '', 'Good communication skills,Fluent English', NULL, 'Incorporate critical missing keywords (Good communication skills, Fluent English) into your skills and work history. | Align your project descriptions and job summaries more closely with the target position\'s requirements. | Quantify your achievements using measurable metrics (e.g., \'Improved performance by 25%\' or \'Managed 5+ projects\').', '2026-10-10 10:55:58'),
(41, 9, 100, 100, 'PHP,HTML,CSS', '', NULL, 'Quantify your achievements using measurable metrics (e.g., \'Improved performance by 25%\' or \'Managed 5+ projects\').', '2026-10-10 15:21:57'),
(42, 9, 0, 80, '', 'nedd,it,expert', NULL, 'Incorporate critical missing keywords (nedd, it, expert) into your skills and work history. | Align your project descriptions and job summaries more closely with the target position\'s requirements. | Quantify your achievements using measurable metrics (e.g., \'Improved performance by 25%\' or \'Managed 5+ projects\').', '2026-10-10 15:25:13'),
(43, 9, 50, 80, 'PHP,HTML,CSS', 'Node js,Rust,Ruby', NULL, 'Incorporate critical missing keywords (Node js, Rust, Ruby) into your skills and work history. | Quantify your achievements using measurable metrics (e.g., \'Improved performance by 25%\' or \'Managed 5+ projects\').', '2026-10-10 17:12:05');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` int(11) DEFAULT NULL,
  `actor_name` varchar(150) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(80) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_id`, `actor_name`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Admin', 'user.deleted', 'user', 7, 'Account deleted: test (test@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:13:52'),
(2, 1, 'Admin', 'user.updated', 'user', 4, 'Account details updated for Muhammad Awais (awais@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:30:58'),
(3, 1, 'Admin', 'user.locked', 'user', 4, 'Account locked: Muhammad Awais (awais@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:31:45'),
(4, NULL, NULL, 'auth.login_blocked', 'user', 4, 'Sign-in blocked because the account is locked.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:32:14'),
(5, 1, 'Admin', 'user.unlocked', 'user', 4, 'Account unlocked: Muhammad Awais (awais@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:32:24'),
(6, 1, 'Admin', 'user.updated', 'user', 4, 'Account details updated for Muhammad Awais (awais@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:32:43'),
(7, 1, 'Admin', 'user.created_by_admin', 'user', 9, 'Account created by an administrator: Muhammad Ali (ali@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:33:21'),
(8, 1, 'Admin', 'user.locked', 'user', 9, 'Account locked: Muhammad Ali (ali@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:36:04'),
(9, 1, 'Admin', 'user.unlocked', 'user', 9, 'Account unlocked: Muhammad Ali (ali@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:36:10'),
(10, 1, 'Admin', 'user.updated', 'user', 9, 'Account details updated for Muhammad Ali (mali@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:36:36'),
(11, 1, 'Admin', 'auth.logout', 'user', 1, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:55:56'),
(12, 1, 'Admin', 'auth.login', 'user', 1, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:56:12'),
(13, 1, 'Admin', 'auth.logout', 'user', 1, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 11:58:51'),
(14, 1, 'Admin', 'auth.login', 'user', 1, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 12:14:43'),
(15, 1, 'Admin', 'user.updated', 'user', 4, 'Account details updated for Muhammad Awais (awais.khaur34@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 12:15:08'),
(16, 1, 'Admin', 'auth.logout', 'user', 1, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 12:15:14'),
(17, 1, 'Admin', 'auth.login', 'user', 1, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:03:34'),
(18, 1, 'Admin', 'auth.login', 'user', 1, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:03:37'),
(19, 1, 'Admin', 'auth.logout', 'user', 1, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:11:11'),
(20, 1, 'Admin', 'auth.login', 'user', 1, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:11:16'),
(21, 1, 'Admin', 'auth.logout', 'user', 1, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:11:29'),
(22, NULL, 'Awais', 'auth.login_failed', 'user', 8, 'Sign-in failed because the password did not match.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:11:34'),
(23, 1, 'Admin', 'auth.login', 'user', 1, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:11:39'),
(24, 1, 'Admin', 'user.updated', 'user', 8, 'Account details updated for Awais (awaisk@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:11:58'),
(25, 1, 'Admin', 'auth.logout', 'user', 1, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:12:04'),
(26, NULL, 'Awais', 'auth.login_failed', 'user', 8, 'Sign-in failed because the password did not match.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:12:09'),
(27, 8, 'Awais', 'auth.login', 'user', 8, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:12:20'),
(28, 8, 'Awais', 'resume.viewed', 'resume', 9, 'Resume opened: 1782243206_Muhammad Awais.pdf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:23:52'),
(29, 8, 'Awais', 'user.updated', 'user', 4, 'Account details updated for Muhammad Awais (awais.khaur34@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:45:08'),
(30, 8, 'Awais', 'auth.logout', 'user', 8, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:45:14'),
(31, NULL, 'Muhammad Awais', 'auth.login', 'user', 4, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:45:34'),
(32, NULL, 'Muhammad Awais', 'resume.analyzed', 'analysis', 40, 'Resume analyzed with a match score of 67% and ATS score of 100%.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 13:46:33'),
(33, NULL, 'Muhammad Awais', 'auth.logout', 'user', 4, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 14:00:48'),
(34, NULL, 'Muhammad Awais', 'account.registered', 'user', 10, 'A new account was registered.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 14:42:25'),
(35, NULL, 'Muhammad Awais', 'auth.logout', 'user', 10, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 14:42:31'),
(36, 1, 'Admin', 'auth.login', 'user', 1, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 14:45:36'),
(37, NULL, 'Muhammad Ali', 'auth.login_failed', 'user', 9, 'Sign-in failed because the password did not match.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 14:56:59'),
(38, NULL, 'Muhammad Ali', 'auth.login_failed', 'user', 9, 'Sign-in failed because the password did not match.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 14:57:15'),
(39, NULL, 'Muhammad Ali', 'auth.login_failed', 'user', 9, 'Sign-in failed because the password did not match.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 14:57:29'),
(40, NULL, 'Muhammad Ali', 'auth.login_failed', 'user', 9, 'Sign-in failed because the password did not match.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 14:57:36'),
(41, NULL, 'Muhammad Ali', 'auth.login_failed', 'user', 9, 'Sign-in failed because the password did not match.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 14:57:44'),
(42, 1, 'Admin', 'user.locked', 'user', 10, 'Account locked: Muhammad Awais (awais.ali@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 14:59:40'),
(43, 1, 'Admin', 'user.unlocked', 'user', 10, 'Account unlocked: Muhammad Awais (awais.ali@gmail.com).', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 14:59:45'),
(44, 1, 'Admin', 'auth.logout', 'user', 1, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 15:02:57'),
(45, NULL, 'Awais', 'auth.login_failed', 'user', 8, 'Sign-in failed because the password did not match.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 15:17:32'),
(46, 8, 'Awais', 'auth.login', 'user', 8, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 15:17:43'),
(47, 8, 'Awais', 'resume.analyzed', 'analysis', 41, 'Resume analyzed with a match score of 100% and ATS score of 100%.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 15:21:57'),
(48, 8, 'Awais', 'resume.analyzed', 'analysis', 42, 'Resume analyzed with a match score of 0% and ATS score of 80%.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 15:25:13'),
(49, 8, 'Awais', 'auth.logout', 'user', 8, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 15:25:52'),
(50, 8, 'Awais', 'auth.login', 'user', 8, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 16:48:38'),
(51, 8, 'Awais', 'auth.logout', 'user', 8, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 16:49:27'),
(52, 8, 'Awais', 'auth.login', 'user', 8, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 16:49:38'),
(53, 8, 'Awais', 'auth.logout', 'user', 8, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 16:49:41'),
(54, NULL, 'Muhammad Awais', 'auth.login', 'user', 4, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 16:49:46'),
(55, NULL, 'Muhammad Awais', 'auth.logout', 'user', 4, 'User signed out.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 16:49:59'),
(56, 8, 'Awais', 'auth.login', 'user', 8, 'User signed in.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 16:59:42'),
(57, 8, 'Awais', 'analysis.report_downloaded', 'analysis', 41, 'PDF report downloaded for resume: 1782243206_Muhammad Awais.pdf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 17:03:01'),
(58, 8, 'Awais', 'analysis.report_downloaded', 'analysis', 41, 'PDF report downloaded for resume: 1782243206_Muhammad Awais.pdf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 17:03:02'),
(59, 8, 'Awais', 'analysis.report_downloaded', 'analysis', 41, 'PDF report downloaded for resume: 1782243206_Muhammad Awais.pdf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 17:10:16'),
(60, 8, 'Awais', 'analysis.report_downloaded', 'analysis', 41, 'PDF report downloaded for resume: 1782243206_Muhammad Awais.pdf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 17:10:17'),
(61, 8, 'Awais', 'position.updated', 'position', 3, 'Position updated: Web Developer', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 17:11:52'),
(62, 8, 'Awais', 'resume.analyzed', 'analysis', 43, 'Resume analyzed with a match score of 50% and ATS score of 80%.', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 17:12:05'),
(63, 8, 'Awais', 'analysis.report_downloaded', 'analysis', 43, 'PDF report downloaded for resume: 1782243206_Muhammad Awais.pdf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 17:12:22'),
(64, 8, 'Awais', 'analysis.report_downloaded', 'analysis', 43, 'PDF report downloaded for resume: 1782243206_Muhammad Awais.pdf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 17:12:22'),
(65, 8, 'Awais', 'analysis.report_downloaded', 'analysis', 43, 'PDF report downloaded for resume: 1782243206_Muhammad Awais.pdf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 17:13:59'),
(66, 8, 'Awais', 'analysis.report_downloaded', 'analysis', 43, 'PDF report downloaded for resume: 1782243206_Muhammad Awais.pdf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:157.0) Gecko/20100101 Firefox/157.0', '2026-10-10 17:13:59');

-- --------------------------------------------------------

--
-- Table structure for table `auth_rate_limits`
--

CREATE TABLE `auth_rate_limits` (
  `bucket_key` char(64) NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `window_started_at` datetime NOT NULL,
  `blocked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_rate_limits`
--

INSERT INTO `auth_rate_limits` (`bucket_key`, `attempts`, `window_started_at`, `blocked_until`) VALUES
('f6fd441e6544c705aff550abab65f1096a915b61c9007326b29937391d68da11', 1, '2026-10-10 20:17:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otps`
--

CREATE TABLE `password_reset_otps` (
  `user_id` int(11) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `required_skills` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `name`, `description`, `required_skills`, `created_by`, `created_at`) VALUES
(1, 'IT', 'Nedd It expert', NULL, NULL, '2026-03-24 20:34:40'),
(2, 'HR', '<ul><li>Good communication skills&nbsp;</li><li><strong>Fluent English</strong></li></ul>', NULL, NULL, '2026-03-24 22:37:42'),
(3, 'Web Developer', '<p>PHP</p><p>HTML</p><p>CSS</p>', 'PHP, HTML, CSS, Node js, Rust, Ruby', 1, '2026-10-10 10:27:06');

-- --------------------------------------------------------

--
-- Table structure for table `resumes`
--

CREATE TABLE `resumes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resumes`
--

INSERT INTO `resumes` (`id`, `user_id`, `position`, `file_name`, `file_path`, `file_size`, `created_at`) VALUES
(5, 2, NULL, '1774302890_Muhammad Awais.pdf', 'uploads/1774302890_Muhammad Awais.pdf', NULL, '2026-03-23 21:54:50'),
(6, 2, 1, '1774302915_appraisal_3 (3).pdf', 'uploads/1774302915_appraisal_3 (3).pdf', NULL, '2026-03-23 21:55:15'),
(9, 8, 3, '1782243206_Muhammad Awais.pdf', 'uploads/1782243206_Muhammad Awais.pdf', NULL, '2026-06-23 19:33:26'),
(10, 1, 2, '1787746345_Muhammad Awais.pdf', 'uploads/1787746345_Muhammad Awais.pdf', NULL, '2026-08-26 12:12:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `failed_login_count` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `failed_login_until` datetime DEFAULT NULL,
  `lock_reason` varchar(32) DEFAULT NULL,
  `locked_by_name` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`, `failed_login_count`, `failed_login_until`, `lock_reason`, `locked_by_name`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2a$12$kLHgm38GILDyr8arvNR3me70IsLC6f49zEX9OVwX2L8NX0utEpRS2', 'admin', 1, '2026-03-23 18:26:22', 0, NULL, NULL, NULL),
(2, 'Emily Davis', 'edavis@gmail.com', '$2y$10$cvTq4sfKOUQInK1ckjsw4..Xn8wCYdZhg1FEx6mFe8at4ZS4F5lMm', 'user', 1, '2026-03-23 18:37:25', 0, NULL, NULL, NULL),
(8, 'Awais', 'awaisk@gmail.com', '$2y$10$k2Av6zrKuQCllD1.OWI2pOo3s1hJB4CvrOK8IgepzD5ms9SncxtNq', 'admin', 1, '2026-03-24 22:39:45', 0, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analysis_results`
--
ALTER TABLE `analysis_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resume_id` (`resume_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_logs_created_at` (`created_at`),
  ADD KEY `idx_audit_logs_actor_id` (`actor_id`),
  ADD KEY `idx_audit_logs_action` (`action`),
  ADD KEY `idx_audit_logs_entity` (`entity_type`,`entity_id`);

--
-- Indexes for table `auth_rate_limits`
--
ALTER TABLE `auth_rate_limits`
  ADD PRIMARY KEY (`bucket_key`),
  ADD KEY `idx_auth_rate_limits_window` (`window_started_at`);

--
-- Indexes for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `fk_created_by_user` (`created_by`);

--
-- Indexes for table `resumes`
--
ALTER TABLE `resumes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `fk_resume_position` (`position`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analysis_results`
--
ALTER TABLE `analysis_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `resumes`
--
ALTER TABLE `resumes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analysis_results`
--
ALTER TABLE `analysis_results`
  ADD CONSTRAINT `analysis_results_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD CONSTRAINT `fk_password_reset_otps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `fk_created_by_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `resumes`
--
ALTER TABLE `resumes`
  ADD CONSTRAINT `fk_resume_position` FOREIGN KEY (`position`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
