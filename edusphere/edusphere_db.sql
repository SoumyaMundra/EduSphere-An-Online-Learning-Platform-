-- Database: `edusphere_db`
CREATE DATABASE IF NOT EXISTS `edusphere_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `edusphere_db`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `courses`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `teacher_id` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `lectures`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lectures` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT,
  `video_url` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Seed Data
-- Default passwords for seeded accounts:
-- Admin: admin@edusphere.com / admin1234
-- Teacher: teacher@edusphere.com / teacher1234
-- Student: student@edusphere.com / student1234
-- --------------------------------------------------------

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `role`) VALUES
(1, 'System Admin', 'admin@edusphere.com', '$2y$10$h5dpyA.urzEU2ZO.wtlO6OsNJmX0I0iMpgWpinkzH.sTNVFheD10O', 'admin'),
(2, 'Professor John Doe', 'teacher@edusphere.com', '$2y$10$ORw/ovC.Ku2gWgIT5Osim.qwxoYJGgAwDQEk5tIfba4Fne5sXEATm', 'teacher'),
(3, 'Alex Smith', 'student@edusphere.com', '$2y$10$Wb80lGmeLo.Z6ZJFTzT5QuSRpWmrjMep2XpqHPJN.IHADTD9y2hxK', 'student')
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `courses` (`id`, `title`, `description`, `teacher_id`) VALUES
(1, 'Web Development Fundamentals', 'Learn core concepts of HTML, CSS, JavaScript, and PHP backend development.', 2),
(2, 'Mastering MySQL & Relational Databases', 'Comprehensive guide to database design, indexing, SQL queries, and PDO in PHP.', 2)
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `lectures` (`id`, `course_id`, `title`, `content`, `video_url`) VALUES
(1, 1, 'Introduction to HTML5 & Semantic Tags', 'In this lecture, we explore structural HTML5 tags like header, nav, section, and article.', 'https://www.youtube.com/watch?v=kUMe1FH4CHE'),
(2, 1, 'CSS Grid & Flexbox Masterclass', 'Deep dive into responsive layout techniques using CSS Flexbox and Grid layout systems.', 'https://www.youtube.com/watch?v=3YL4S7x561g'),
(3, 2, 'Database Normalization & Foreign Keys', 'Learn how to structure database tables using 1NF, 2NF, and 3NF rules to avoid redundancy.', 'https://www.youtube.com/watch?v=GFQaEYEc8_8')
ON DUPLICATE KEY UPDATE `id`=`id`;
