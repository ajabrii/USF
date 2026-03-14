# 🍽️ La Table d'Or — Restaurant Booking System

Welcome to **La Table d'Or**, a sleek, modern, and fully-functional restaurant booking platform. This project blends a premium dark-themed design with powerful PHP backend logic and smooth GSAP animations.

---

1. **Setup Database User**
   Run this command to create the dedicated user (required for connection):
   ```bash
   sudo mysql < /home/ajabri/Desktop/USF/restaurant_booking/create_user.sql
   ```

2. **Start the PHP Development Server**
   In the root directory, run:
   ```bash
   php -S localhost:8000
   ```

2. **Access the Application**
   Open your browser and navigate to:
   [http://localhost:8000/restaurant_booking/public/](http://localhost:8000/restaurant_booking/public/)

---

## 🔐 Test Accounts

Use these credentials to explore the different roles:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@test.com` | `admin123` |
| **User 1** | `user1@test.com` | `user123` |
| **User 2** | `user2@test.com` | `user123` |

---

## ✨ Features

### 👤 For Customers
- **Dashboard**: View upcoming reservations at a glance.
- **Easy Booking**: Intuitive form to choose dates, time slots, and guest counts.
- **Smart Validation**: Real-time capacity checks prevent overbooking.
- **Manage Bookings**: Edit or cancel your reservations with ease.
- **Live Feedback**: GSAP-powered animations for a premium feel.

### 🛠️ For Administrators
- **Reservation Overview**: Filter and manage all restaurant bookings.
- **Slot Management**: Full CRUD (Create, Read, Update, Delete) for time slots.
- **Dynamic Capacity**: Toggle slots active/inactive or adjust seat limits on the fly.
- **Live Stats**: Key metrics visible on the admin dashboard.

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