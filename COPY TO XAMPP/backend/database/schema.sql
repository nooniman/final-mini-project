-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 06, 2025 at 08:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wmsu_grading`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `subject_id`, `semester_id`, `date`, `status`, `remarks`, `created_at`) VALUES
(1, 1, 3, 2, '2025-01-06', 'present', NULL, '2025-11-27 13:56:06'),
(2, 1, 3, 2, '2025-01-08', 'present', NULL, '2025-11-27 13:56:06'),
(3, 1, 3, 2, '2025-01-10', 'present', NULL, '2025-11-27 13:56:06'),
(4, 1, 3, 2, '2025-01-13', 'present', NULL, '2025-11-27 13:56:06'),
(5, 1, 3, 2, '2025-01-15', 'late', 'Arrived 10 mins late', '2025-11-27 13:56:06'),
(6, 1, 3, 2, '2025-01-17', 'present', NULL, '2025-11-27 13:56:06'),
(7, 1, 3, 2, '2025-01-20', 'absent', 'Sick', '2025-11-27 13:56:06'),
(8, 1, 3, 2, '2025-01-22', 'excused', 'Medical appointment', '2025-11-27 13:56:06'),
(9, 1, 3, 2, '2025-01-24', 'present', NULL, '2025-11-27 13:56:06'),
(10, 1, 4, 2, '2025-01-06', 'present', NULL, '2025-11-27 13:56:06'),
(11, 1, 4, 2, '2025-01-08', 'present', NULL, '2025-11-27 13:56:06'),
(12, 1, 4, 2, '2025-01-10', 'present', NULL, '2025-11-27 13:56:06'),
(13, 1, 4, 2, '2025-01-13', 'present', NULL, '2025-11-27 13:56:06'),
(14, 1, 4, 2, '2025-01-15', 'present', NULL, '2025-11-27 13:56:06'),
(15, 3, 1, 2, '2025-11-27', 'present', '', '2025-11-27 18:53:54'),
(16, 3, 3, 2, '2025-01-06', 'present', NULL, '2025-11-27 18:59:33'),
(17, 3, 3, 2, '2025-01-08', 'present', NULL, '2025-11-27 18:59:33'),
(18, 3, 3, 2, '2025-01-10', 'present', NULL, '2025-11-27 18:59:33'),
(19, 3, 3, 2, '2025-01-13', 'late', 'Traffic', '2025-11-27 18:59:33'),
(20, 3, 3, 2, '2025-01-15', 'present', NULL, '2025-11-27 18:59:33'),
(21, 3, 4, 2, '2025-01-06', 'present', NULL, '2025-11-27 18:59:33'),
(22, 3, 4, 2, '2025-01-08', 'present', NULL, '2025-11-27 18:59:33'),
(23, 3, 4, 2, '2025-01-10', 'excused', 'Medical checkup', '2025-11-27 18:59:33'),
(24, 3, 4, 2, '2025-01-13', 'present', NULL, '2025-11-27 18:59:33'),
(25, 3, 4, 2, '2025-01-15', 'present', NULL, '2025-11-27 18:59:33'),
(26, 2, 2, 2, '2025-01-06', 'present', NULL, '2025-11-27 18:59:33'),
(27, 2, 2, 2, '2025-01-08', 'present', NULL, '2025-11-27 18:59:33'),
(28, 2, 2, 2, '2025-01-10', 'absent', 'No excuse', '2025-11-27 18:59:33'),
(29, 2, 2, 2, '2025-01-13', 'present', NULL, '2025-11-27 18:59:33'),
(30, 2, 2, 2, '2025-01-15', 'present', NULL, '2025-11-27 18:59:33'),
(31, 2, 3, 2, '2025-01-06', 'present', NULL, '2025-11-27 18:59:33'),
(32, 2, 3, 2, '2025-01-08', 'late', 'Arrived 15 mins late', '2025-11-27 18:59:33'),
(33, 2, 3, 2, '2025-01-10', 'present', NULL, '2025-11-27 18:59:33'),
(34, 2, 3, 2, '2025-01-13', 'present', NULL, '2025-11-27 18:59:33'),
(35, 2, 3, 2, '2025-01-15', 'present', NULL, '2025-11-27 18:59:33');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `code`, `name`, `department`, `created_at`) VALUES
(1, 'BSCS', 'Bachelor of Science in Computer Science', 'College of Computing Studies', '2025-11-27 13:56:06'),
(2, 'BSIT', 'Bachelor of Science in Information Technology', 'College of Computing Studies', '2025-11-27 13:56:06'),
(3, 'BSIS', 'Bachelor of Science in Information Systems', 'College of Computing Studies', '2025-11-27 13:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `status` enum('enrolled','dropped','completed') DEFAULT 'enrolled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `subject_id`, `semester_id`, `status`, `created_at`) VALUES
(1, 1, 3, 2, 'enrolled', '2025-11-27 13:56:06'),
(2, 1, 4, 2, 'enrolled', '2025-11-27 13:56:06'),
(3, 1, 5, 2, 'enrolled', '2025-11-27 13:56:06'),
(4, 1, 6, 2, 'enrolled', '2025-11-27 13:56:06'),
(5, 1, 7, 2, 'enrolled', '2025-11-27 13:56:06'),
(6, 2, 2, 2, 'enrolled', '2025-11-27 16:27:59'),
(7, 2, 3, 2, 'enrolled', '2025-11-27 16:41:18'),
(8, 2, 1, 1, 'completed', '2025-11-27 18:59:30'),
(9, 2, 5, 1, 'completed', '2025-11-27 18:59:30'),
(10, 2, 7, 1, 'completed', '2025-11-27 18:59:30'),
(11, 2, 8, 1, 'completed', '2025-11-27 18:59:30'),
(12, 2, 1, 4, 'completed', '2025-11-27 18:59:32'),
(13, 2, 8, 4, 'completed', '2025-11-27 18:59:32'),
(14, 2, 7, 3, 'completed', '2025-11-27 18:59:32'),
(15, 2, 8, 3, 'completed', '2025-11-27 18:59:32'),
(16, 3, 3, 2, 'enrolled', '2025-11-27 18:59:32'),
(17, 3, 4, 2, 'enrolled', '2025-11-27 18:59:32'),
(18, 3, 5, 2, 'enrolled', '2025-11-27 18:59:32'),
(19, 3, 6, 2, 'enrolled', '2025-11-27 18:59:32'),
(20, 3, 7, 2, 'enrolled', '2025-11-27 18:59:32'),
(21, 3, 1, 1, 'completed', '2025-11-27 18:59:32'),
(22, 3, 2, 1, 'completed', '2025-11-27 18:59:32'),
(23, 3, 8, 1, 'completed', '2025-11-27 18:59:32'),
(24, 3, 7, 4, 'completed', '2025-11-27 18:59:32'),
(25, 3, 8, 4, 'completed', '2025-11-27 18:59:32'),
(26, 1, 1, 1, 'completed', '2025-11-27 18:59:32'),
(27, 1, 2, 1, 'completed', '2025-11-27 18:59:32'),
(28, 1, 8, 1, 'completed', '2025-11-27 18:59:32');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `prelim_grade` decimal(3,2) DEFAULT NULL,
  `midterm_grade` decimal(3,2) DEFAULT NULL,
  `prefinal_grade` decimal(3,2) DEFAULT NULL,
  `final_grade` decimal(3,2) DEFAULT NULL,
  `remarks` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `subject_id`, `semester_id`, `prelim_grade`, `midterm_grade`, `prefinal_grade`, `final_grade`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 2, 1.50, 1.75, 1.50, 1.50, 'Passed', '2025-11-27 13:56:06', '2025-11-27 13:56:06'),
(2, 1, 4, 2, 1.25, 1.50, 1.25, 1.25, 'Passed', '2025-11-27 13:56:06', '2025-11-27 13:56:06'),
(3, 1, 5, 2, 2.00, 1.75, 1.75, 1.75, 'Passed', '2025-11-27 13:56:06', '2025-11-27 13:56:06'),
(4, 1, 6, 2, 1.75, 2.00, 1.75, 1.75, 'Passed', '2025-11-27 13:56:06', '2025-11-27 17:15:50'),
(5, 1, 7, 2, 2.25, 2.00, NULL, NULL, NULL, '2025-11-27 13:56:06', '2025-11-27 13:56:06'),
(6, 2, 2, 2, NULL, NULL, NULL, NULL, NULL, '2025-11-27 17:08:08', '2025-11-27 17:08:08'),
(7, 2, 3, 2, 2.00, 1.50, 1.25, 2.00, 'Passed', '2025-11-27 17:08:08', '2025-11-27 17:14:10'),
(8, 2, 1, 1, 1.75, 2.00, 1.75, 1.75, 'Passed', '2025-11-27 18:59:32', '2025-11-27 18:59:32'),
(9, 2, 5, 1, 2.00, 2.25, 2.00, 2.00, 'Passed', '2025-11-27 18:59:32', '2025-11-27 18:59:32'),
(10, 2, 7, 1, 2.50, 2.75, 2.50, 2.50, 'Passed', '2025-11-27 18:59:32', '2025-11-27 18:59:32'),
(11, 2, 8, 1, 1.50, 1.75, 1.50, 1.50, 'Passed', '2025-11-27 18:59:32', '2025-11-27 18:59:32'),
(12, 2, 1, 4, 2.00, 2.25, 2.00, 2.00, 'Passed', '2025-11-27 18:59:32', '2025-11-27 18:59:32'),
(13, 2, 8, 4, 1.75, 1.50, 1.75, 1.75, 'Passed', '2025-11-27 18:59:32', '2025-11-27 18:59:32'),
(14, 2, 7, 3, 2.75, 3.00, 2.75, 2.75, 'Passed', '2025-11-27 18:59:32', '2025-11-27 18:59:32'),
(15, 2, 8, 3, 2.00, 1.75, 2.00, 2.00, 'Passed', '2025-11-27 18:59:32', '2025-11-27 18:59:32'),
(16, 3, 3, 2, 1.25, 1.50, NULL, NULL, 'In Progress', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(17, 3, 4, 2, 1.50, 1.75, NULL, NULL, 'In Progress', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(18, 3, 5, 2, 1.75, 1.50, NULL, NULL, 'In Progress', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(19, 3, 6, 2, 2.00, 1.75, NULL, NULL, 'In Progress', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(20, 3, 7, 2, 2.25, 2.50, NULL, NULL, 'In Progress', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(21, 3, 1, 1, 1.00, 1.25, 1.25, 1.25, 'Passed', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(22, 3, 2, 1, 1.25, 1.50, 1.25, 1.25, 'Passed', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(23, 3, 8, 1, 1.50, 1.25, 1.50, 1.50, 'Passed', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(24, 3, 7, 4, 1.75, 2.00, 1.75, 1.75, 'Passed', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(25, 3, 8, 4, 1.25, 1.50, 1.25, 1.25, 'Passed', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(26, 1, 1, 1, 1.50, 1.75, 1.50, 1.50, 'Passed', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(27, 1, 2, 1, 1.75, 1.50, 1.75, 1.75, 'Passed', '2025-11-27 18:59:33', '2025-11-27 18:59:33'),
(28, 1, 8, 1, 2.00, 1.75, 2.00, 2.00, 'Passed', '2025-11-27 18:59:33', '2025-11-27 18:59:33');

-- --------------------------------------------------------

--
-- Table structure for table `grade_components`
--

CREATE TABLE `grade_components` (
  `id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `component_type` enum('quiz','assignment','exam','project','recitation','attendance','other') NOT NULL,
  `name` varchar(100) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) NOT NULL,
  `weight` decimal(5,2) DEFAULT 0.00,
  `period` enum('prelim','midterm','prefinal','final') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grade_components`
--

INSERT INTO `grade_components` (`id`, `grade_id`, `component_type`, `name`, `score`, `max_score`, `weight`, `period`, `created_at`) VALUES
(1, 1, 'quiz', 'Quiz 1', 45.00, 50.00, 10.00, 'prelim', '2025-11-27 13:56:06'),
(2, 1, 'quiz', 'Quiz 2', 48.00, 50.00, 10.00, 'prelim', '2025-11-27 13:56:06'),
(3, 1, 'assignment', 'Assignment 1', 95.00, 100.00, 10.00, 'prelim', '2025-11-27 13:56:06'),
(4, 1, 'exam', 'Prelim Exam', 88.00, 100.00, 70.00, 'prelim', '2025-11-27 13:56:06'),
(5, 1, 'quiz', 'Quiz 3', 42.00, 50.00, 10.00, 'midterm', '2025-11-27 13:56:06'),
(6, 1, 'quiz', 'Quiz 4', 46.00, 50.00, 10.00, 'midterm', '2025-11-27 13:56:06'),
(7, 1, 'project', 'Mini Project', 92.00, 100.00, 20.00, 'midterm', '2025-11-27 13:56:06'),
(8, 1, 'exam', 'Midterm Exam', 85.00, 100.00, 60.00, 'midterm', '2025-11-27 13:56:06'),
(9, 16, 'quiz', 'Quiz 1', 48.00, 50.00, 10.00, 'prelim', '2025-11-27 18:59:33'),
(10, 16, 'quiz', 'Quiz 2', 47.00, 50.00, 10.00, 'prelim', '2025-11-27 18:59:33'),
(11, 16, 'assignment', 'Assignment 1', 98.00, 100.00, 10.00, 'prelim', '2025-11-27 18:59:33'),
(12, 16, 'exam', 'Prelim Exam', 92.00, 100.00, 70.00, 'prelim', '2025-11-27 18:59:33'),
(13, 16, 'quiz', 'Quiz 3', 45.00, 50.00, 10.00, 'midterm', '2025-11-27 18:59:33'),
(14, 16, 'project', 'Mini Project', 95.00, 100.00, 20.00, 'midterm', '2025-11-27 18:59:33'),
(15, 16, 'exam', 'Midterm Exam', 88.00, 100.00, 60.00, 'midterm', '2025-11-27 18:59:33'),
(16, 7, 'quiz', 'Quiz 1', 40.00, 50.00, 10.00, 'prelim', '2025-11-27 18:59:33'),
(17, 7, 'quiz', 'Quiz 2', 42.00, 50.00, 10.00, 'prelim', '2025-11-27 18:59:33'),
(18, 7, 'assignment', 'Assignment 1', 85.00, 100.00, 10.00, 'prelim', '2025-11-27 18:59:33'),
(19, 7, 'exam', 'Prelim Exam', 80.00, 100.00, 70.00, 'prelim', '2025-11-27 18:59:33');

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `office` varchar(255) DEFAULT NULL,
  `consultation_hours` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`id`, `employee_id`, `email`, `first_name`, `last_name`, `department`, `office`, `consultation_hours`, `created_at`) VALUES
(1, 'EMP001', 'rey.saavedra@wmsu.edu.ph', 'Rey', 'Saavedra', 'Computer Science Department', 'CCS Building, Room 102', 'MWF 2:00 PM - 4:00 PM', '2025-11-27 13:56:06'),
(2, 'EMP002', 'jp.arip@wmsu.edu.ph', 'Jhon Paul', 'Arip', 'Information Technology Department', 'CCS Building, Room 101', 'TTh 1:00 PM - 3:00 PM', '2025-11-27 13:56:06'),
(3, 'EMP003', 'ceed.jezreel@wmsu.edu.ph', 'Ceed Jezreel', 'Lorenzo', 'Information Technology Department', 'CCS Building, Room 101', 'MWF 10:00 AM - 12:00 PM', '2025-11-27 13:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('grade','announcement','reminder','alert','system') DEFAULT 'system',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `student_id`, `title`, `message`, `type`, `read_at`, `created_at`) VALUES
(1, 1, 'Grade Posted', 'Your CS 201 prelim grade has been posted.', 'grade', '2025-11-27 20:00:41', '2025-01-20 02:00:00'),
(2, 1, 'Grade Posted', 'Your CS 202 midterm grade has been posted.', 'grade', '2025-11-27 20:00:38', '2025-01-22 06:30:00'),
(3, 1, 'Class Announcement', 'CS 201 class is cancelled on Friday due to faculty meeting.', 'announcement', '2025-11-27 20:00:37', '2025-01-23 01:00:00'),
(4, 1, 'Enrollment Reminder', 'Mid-year enrollment starts next week. Please prepare your requirements.', 'reminder', '2025-11-27 20:00:42', '2025-01-24 00:00:00'),
(5, 1, 'System Update', 'The grading system will undergo maintenance on Saturday 8PM-10PM.', 'system', '2025-11-27 20:00:43', '2025-01-25 08:00:00'),
(6, 2, 'Welcome to WMSU', 'Welcome to Western Mindanao State University Grading System!', 'system', NULL, '2025-01-05 00:00:00'),
(7, 2, 'Grade Posted', 'Your CS 201 prelim grade has been posted.', 'grade', NULL, '2025-01-20 02:00:00'),
(8, 2, 'Grade Posted', 'Your CS 202 midterm grade has been posted.', 'grade', NULL, '2025-02-15 06:30:00'),
(9, 2, 'Enrollment Reminder', 'Please check your enrollment status for the current semester.', 'reminder', NULL, '2025-01-10 01:00:00'),
(10, 3, 'Welcome to WMSU', 'Welcome to Western Mindanao State University Grading System!', 'system', NULL, '2025-01-05 00:00:00'),
(11, 3, 'Grade Posted', 'Your CS 201 prelim grade has been posted.', 'grade', NULL, '2025-01-22 03:00:00'),
(12, 3, 'Grade Posted', 'Your CS 202 prelim grade has been posted.', 'grade', NULL, '2025-01-22 03:05:00'),
(13, 3, 'Class Announcement', 'IT 101 laboratory session moved to Room IT-Lab2 this week.', 'announcement', NULL, '2025-01-25 00:00:00'),
(14, 3, 'Grade Posted', 'Your midterm grades have been posted for multiple subjects.', 'grade', NULL, '2025-02-20 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room` varchar(50) DEFAULT NULL,
  `type` enum('Lecture','Laboratory','Tutorial') DEFAULT 'Lecture'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `subject_id`, `semester_id`, `day`, `start_time`, `end_time`, `room`, `type`) VALUES
(1, 3, 2, 'Monday', '08:00:00', '09:30:00', 'CL-301', 'Lecture'),
(2, 3, 2, 'Wednesday', '08:00:00', '09:30:00', 'CL-301', 'Lecture'),
(3, 3, 2, 'Friday', '08:00:00', '11:00:00', 'CL-Lab1', 'Laboratory'),
(4, 4, 2, 'Monday', '10:00:00', '11:30:00', 'CL-302', 'Lecture'),
(5, 4, 2, 'Wednesday', '10:00:00', '11:30:00', 'CL-302', 'Lecture'),
(6, 4, 2, 'Friday', '13:00:00', '16:00:00', 'CL-Lab2', 'Laboratory'),
(7, 5, 2, 'Tuesday', '08:00:00', '09:30:00', 'IT-201', 'Lecture'),
(8, 5, 2, 'Thursday', '08:00:00', '09:30:00', 'IT-201', 'Lecture'),
(9, 5, 2, 'Saturday', '08:00:00', '11:00:00', 'IT-Lab1', 'Laboratory'),
(10, 6, 2, 'Tuesday', '10:00:00', '11:30:00', 'IT-202', 'Lecture'),
(11, 6, 2, 'Thursday', '10:00:00', '11:30:00', 'IT-202', 'Lecture'),
(12, 7, 2, 'Monday', '13:00:00', '14:30:00', 'Room-101', 'Lecture'),
(13, 7, 2, 'Wednesday', '13:00:00', '14:30:00', 'Room-101', 'Lecture'),
(14, 7, 2, 'Friday', '13:00:00', '14:30:00', 'Room-101', 'Lecture');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester_number` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `name`, `academic_year`, `semester_number`, `start_date`, `end_date`, `is_current`, `created_at`) VALUES
(1, 'First Semester', '2024-2025', 1, '2024-08-01', '2024-12-15', 0, '2025-11-27 13:56:06'),
(2, 'Second Semester', '2024-2025', 2, '2025-01-06', '2025-05-30', 1, '2025-11-27 13:56:06'),
(3, 'First Semester', '2023-2024', 1, '2023-08-01', '2023-12-15', 0, '2025-11-27 13:56:06'),
(4, 'Second Semester', '2023-2024', 2, '2024-01-08', '2024-05-31', 0, '2025-11-27 13:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `course` varchar(20) DEFAULT NULL,
  `year_level` int(11) DEFAULT 1,
  `section` varchar(20) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive','graduated','dropped') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `email`, `password`, `first_name`, `last_name`, `middle_name`, `course`, `year_level`, `section`, `contact_number`, `address`, `profile_image`, `status`, `created_at`, `updated_at`) VALUES
(1, '2024-00001', 'student@wmsu.edu.ph', '$2y$10$nP6cdoVAx/rE2S5an9QWTOb2rG0zuTnuYYZ1LHMvaYl0adX4arvLC', 'Juan', 'Dela Cruz', 'Santos', 'BSIT', 3, 'C', '09123456789', 'Zamboanga City', NULL, 'active', '2025-11-27 13:56:06', '2025-11-27 16:01:35'),
(2, '2021-05289', 'qb202105289@wmsu.edu.ph', '$2y$10$rr20MZg59trfvcs379pKFuRRCT8QXykAKLm47eO6dnc2BarKYpbN2', 'Anas Mohammad', 'Demonteverde', 'Embay', 'BSIT', 5, 'A', NULL, NULL, NULL, 'active', '2025-11-27 16:06:25', '2025-11-27 16:06:25'),
(3, '2021-00700', 'qb202100700@wmsu.edu.ph', '$2y$10$nmRGaIaJ7GroxREJern4K.jTzrigusIHpDYjWsPTFPtueHSqg/i6W', 'Sophia', 'Tolosa', 'Espinosa', 'BSIT', 5, 'A', NULL, NULL, NULL, 'active', '2025-11-27 18:40:48', '2025-11-27 18:40:48');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `units` int(11) NOT NULL DEFAULT 3,
  `lecture_hours` int(11) DEFAULT 0,
  `lab_hours` int(11) DEFAULT 0,
  `instructor_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `description`, `units`, `lecture_hours`, `lab_hours`, `instructor_id`, `created_at`) VALUES
(1, 'CS 101', 'Introduction to Computing', 'Basic concepts of computing and programming fundamentals', 3, 2, 3, 1, '2025-11-27 13:56:06'),
(2, 'CS 102', 'Computer Programming 1', 'Introduction to programming using Python', 3, 2, 3, 1, '2025-11-27 13:56:06'),
(3, 'CS 201', 'Data Structures and Algorithms', 'Fundamental data structures and algorithm analysis', 3, 2, 3, 1, '2025-11-27 13:56:06'),
(4, 'CS 202', 'Object-Oriented Programming', 'OOP concepts using Java', 3, 2, 3, 2, '2025-11-27 13:56:06'),
(5, 'IT 101', 'Web Development', 'HTML, CSS, and JavaScript fundamentals', 3, 2, 3, 3, '2025-11-27 13:56:06'),
(6, 'IT 102', 'Database Management Systems', 'SQL and database design', 3, 2, 3, 3, '2025-11-27 13:56:06'),
(7, 'MATH 101', 'Calculus I', 'Differential calculus', 3, 3, 0, NULL, '2025-11-27 13:56:06'),
(8, 'ENGL 101', 'Communication Skills', 'English communication fundamentals', 3, 3, 0, NULL, '2025-11-27 13:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `subject_prerequisites`
--

CREATE TABLE `subject_prerequisites` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `prerequisite_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`student_id`,`subject_id`,`date`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `idx_attendance_student` (`student_id`),
  ADD KEY `idx_attendance_date` (`date`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`subject_id`,`semester_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `idx_enrollments_student` (`student_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`subject_id`,`semester_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_grades_student` (`student_id`),
  ADD KEY `idx_grades_semester` (`semester_id`);

--
-- Indexes for table `grade_components`
--
ALTER TABLE `grade_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grade_id` (`grade_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_student` (`student_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `course` (`course`),
  ADD KEY `idx_students_student_id` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `subject_prerequisites`
--
ALTER TABLE `subject_prerequisites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `prerequisite_id` (`prerequisite_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `grade_components`
--
ALTER TABLE `grade_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subject_prerequisites`
--
ALTER TABLE `subject_prerequisites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grade_components`
--
ALTER TABLE `grade_components`
  ADD CONSTRAINT `grade_components_ibfk_1` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`course`) REFERENCES `courses` (`code`) ON DELETE SET NULL;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `subject_prerequisites`
--
ALTER TABLE `subject_prerequisites`
  ADD CONSTRAINT `subject_prerequisites_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_prerequisites_ibfk_2` FOREIGN KEY (`prerequisite_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
