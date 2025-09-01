-- Create contact_messages table for storing contact form submissions
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data (optional)
INSERT INTO `contact_messages` (`name`, `email`, `subject`, `message`, `status`) VALUES
('John Doe', 'john@example.com', 'Product Inquiry', 'I would like to know more about your makeup products.', 'unread'),
('Jane Smith', 'jane@example.com', 'Order Support', 'I have a question about my recent order.', 'unread');
