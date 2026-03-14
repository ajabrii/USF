# 🍽️ La Table d'Or — Restaurant Booking System

Welcome to **La Table d'Or**, a sleek, modern, and fully-functional restaurant booking platform. This project blends a premium dark-themed design with powerful PHP backend logic and smooth GSAP animations.

---

## 🚀 How to Run

### Option 1: Linux/macOS (Terminal)
1. **Setup Database User**:
   ```bash
   sudo mysql < /home/ajabri/Desktop/USF/restaurant_booking/create_user.sql
   ```
2. **Start Server**:
   ```bash
   php -S localhost:8000
   ```
3. **View App**: [http://localhost:8000/restaurant_booking/public/](http://localhost:8000/restaurant_booking/public/)

### Option 2: Windows (XAMPP/MAMP)
1. **Move Project**: Copy the `restaurant_booking` folder to your server's root (e.g., `C:\xampp\htdocs\`).
2. **Start Services**: Open your control panel and start **Apache** and **MySQL**.
3. **Database Setup**:
   - Open [phpMyAdmin](http://localhost/phpmyadmin/).
   - Import `database.sql`.
   - Run the script in `create_user.sql` using the SQL tab to enable application connectivity.
4. **View App**: [http://localhost/restaurant_booking/public/](http://localhost/restaurant_booking/public/)

---

## 🔐 Test Accounts

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@test.com` | `admin123` |
| **User** | `user1@test.com` | `user123` |

---

## 🌟 Detailed Feature Guide

### 👤 Customer Experience (ROLE_USER)
*   **Dynamic Dashboard**: A personalized welcome screen showing a summary of your upcoming reservations and quick-action buttons for common tasks.
*   **Intuitive Booking Flow**:
    *   **Date Selection**: Smart calendar that prevents booking for past dates.
    *   **Slot Selection**: Automatically fetches available time slots for the chosen day.
    *   **Capacity Guard**: Real-time validation that blocks bookings exceeding the restaurant's seat limits for a specific slot.
*   **Reservation Management**:
    *   **My Reservations**: A dedicated list view categorized by status (Confirmed/Cancelled).
    *   **Easy Editing**: Need more seats or a different time? Update your existing bookings instantly.
    *   **Quick Cancellation**: Cancel reservations with a single click and clear confirmation.
*   **Confirmation Hub**: A beautifully animated confirmation page summarizing your booking details with GSAP-powered transitions.

### 🛠️ Administrative Control (ROLE_ADMIN)
*   **Global Overview**: Access to all restaurant activity. The admin dashboard features live counters for today's bookings, guest totals, and user base metrics.
*   **Reservation Master List**:
    *   **Advanced Filtering**: Sort through the entire database by date, status, or specific time slots.
    *   **Admin Override**: Permission to cancel any reservation in the system for operational needs.
*   **Time Slot Command Center**:
    *   **Full CRUD**: Create new lunch or dinner services, edit existing ones, or delete unused slots.
    *   **Service Toggling**: Instantly activate or deactivate specific time slots (e.g., closing a service for a private event) without deleting them.
    *   **Seat Management**: Fine-tune the maximum capacity for every individual service period.

### ⚡ Technical Highlights
*   **GSAP 1.11.1 Motion Engine**: 13 distinct animation zones, including staggered list reveals, form-field sequences, and animated capacity progression bars.
*   **Glassmorphism Glass UI**: A premium dark-themed interface with backdrop blurs, glow effects, and modern typography.
*   **Industrial-Grade Security**: Full CSRF protection on all forms, MySQLi prepared statements for 100% SQL injection immunity, and strict Role-Based Access Control (RBAC).

---

## 🛡️ Security First
This project follows professional security standards:
- **Prepared Statements**: Zero SQL injection risk via MySQLi prepared statements.
- **CSRF Protection**: Every state-changing form is protected by unique tokens.
- **XSS Prevention**: All user-generated content is escaped before rendering.
- **RBAC**: Role-Based Access Control ensures only admins can access sensitive areas.
- **Owner-Checks**: Users can only modify or cancel their own reservations.

---

## 🎨 Design & Motion
- **Premium Aesthetics**: A custom dark-themed design system using Inter typography.
- **GSAP 1.11.1**: 13 distinct animation zones providing micro-interactions and smooth transitions.
- **Responsive**: Fully optimized for desktop, tablet, and mobile devices.

---

## 📁 Project Structure
- `public/`: Entry points, static assets (CSS, JS).
- `src/`: Core logic, database config, and reusable helpers.
- `reservations/`: Customer-facing reservation management.
- `admin/`: Advanced administrative tools.
- `database.sql`: Full schema and demo data.