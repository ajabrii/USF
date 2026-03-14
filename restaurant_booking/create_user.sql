-- Create a dedicated user for the Restaurant Booking application
-- This avoids using the root user which often requires sudo/unix_socket

CREATE USER IF NOT EXISTS 'rb_user'@'localhost' IDENTIFIED BY 'rb_pass_123';
GRANT ALL PRIVILEGES ON restaurant_booking_db.* TO 'rb_user'@'localhost';
FLUSH PRIVILEGES;
