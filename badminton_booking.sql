-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.14.0.7165
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for badminton_booking
CREATE DATABASE IF NOT EXISTS `badminton_booking` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `badminton_booking`;

-- Dumping structure for table badminton_booking.bookings
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'หน่วยเป็นนาที',
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reminder_sent` tinyint(1) DEFAULT 0,
  `end_notified` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `field_id` (`field_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `fields` (`field_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table badminton_booking.bookings: ~0 rows (approximately)

-- Dumping structure for function badminton_booking.count_user_bookings
DELIMITER //
CREATE FUNCTION `count_user_bookings`(p_user_id INT) RETURNS int(11)
    DETERMINISTIC
BEGIN
    DECLARE v_total INT;
    
    SELECT COUNT(*) INTO v_total
    FROM bookings
    WHERE user_id = p_user_id AND status = 'confirmed';
    
    RETURN v_total;
END//
DELIMITER ;

-- Dumping structure for table badminton_booking.fields
CREATE TABLE IF NOT EXISTS `fields` (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_name` varchar(50) NOT NULL,
  `status` enum('available','maintenance') DEFAULT 'available',
  PRIMARY KEY (`field_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table badminton_booking.fields: ~8 rows (approximately)
INSERT INTO `fields` (`field_id`, `field_name`, `status`) VALUES
	(1, 'สนามแบดมินตัน 1', 'available'),
	(2, 'สนามแบดมินตัน 2', 'available'),
	(3, 'สนามแบดมินตัน 3', 'available'),
	(4, 'สนามแบดมินตัน 4', 'available'),
	(5, 'สนามแบดมินตัน 5', 'available'),
	(6, 'สนามแบดมินตัน 6', 'available'),
	(7, 'สนามแบดมินตัน 7', 'available'),
	(8, 'สนามแบดมินตัน 8', 'available');

-- Dumping structure for table badminton_booking.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `date_time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table badminton_booking.notifications: ~0 rows (approximately)

-- Dumping structure for procedure badminton_booking.sp_cancel_booking
DELIMITER //
CREATE PROCEDURE `sp_cancel_booking`(IN p_booking_id INT)
BEGIN
    -- ปรับปรุงสถานะการจองเป็น cancelled
    UPDATE bookings 
    SET status = 'cancelled' 
    WHERE booking_id = p_booking_id;
    
END//
DELIMITER ;

-- Dumping structure for table badminton_booking.user_tokens
CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `selector` char(12) NOT NULL,
  `hashed_validator` char(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expiry` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table badminton_booking.user_tokens: ~0 rows (approximately)

-- Dumping structure for table badminton_booking.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) DEFAULT NULL COMMENT 'คำนำหน้า: นาย/นาง/นางสาว',
  `name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `student_id` varchar(13) DEFAULT NULL COMMENT 'รหัสนักศึกษา/รหัสประจำตัว',
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','staff','admin') DEFAULT 'student',
  `register_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `line_user_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `student_id` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table badminton_booking.users: ~2 rows (approximately)
INSERT INTO `users` (`user_id`, `title`, `name`, `surname`, `student_id`, `email`, `password`, `role`, `register_date`, `line_user_id`) VALUES
	(1, 'นาย', 'ผู้ดูแล', 'ระบบ', NULL, 'admin@mail.com', '$2y$10$5dSvq3wlcesx3iSOV8b8yeBJGe.xpJIeAAV0sZgIoh1EchRTXtRsS', 'admin', '2026-03-14 08:49:58', NULL),
	(2, 'นาย', 'กิตติพัฒ', '้บุญรอด', '6650110001', '0953509308a@gamil.com', '$2y$10$5dSvq3wlcesx3iSOV8b8yeBJGe.xpJIeAAV0sZgIoh1EchRTXtRsS', 'student', '2026-03-14 09:45:48', NULL);

-- Dumping structure for view badminton_booking.view_booking_details
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `view_booking_details` (
	`booking_id` INT(11) NOT NULL,
	`student_id` VARCHAR(1) NULL COMMENT 'รหัสนักศึกษา/รหัสประจำตัว' COLLATE 'utf8mb4_unicode_ci',
	`user_full_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`field_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`booking_date` DATE NOT NULL,
	`start_time` TIME NOT NULL,
	`end_time` TIME NOT NULL,
	`duration` INT(11) NULL COMMENT 'หน่วยเป็นนาที',
	`status` ENUM('pending','confirmed','cancelled') NULL COLLATE 'utf8mb4_general_ci'
);

-- Dumping structure for trigger badminton_booking.before_booking_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER before_booking_insert
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
    -- คำนวณส่วนต่างระหว่างเวลาเรี่มและเวลาสิ้นสุดเป็นนาที
    SET NEW.duration = TIMESTAMPDIFF(MINUTE, NEW.start_time, NEW.end_time);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `view_booking_details`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `view_booking_details` AS SELECT 
    b.booking_id,
    u.student_id,
    CONCAT(u.name, ' ', u.surname) AS user_full_name,
    f.field_name,
    b.booking_date,
    b.start_time,
    b.end_time,
    b.duration,
    b.status
FROM bookings b
JOIN users u ON b.user_id = u.user_id
JOIN fields f ON b.field_id = f.field_id 
;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
