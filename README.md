# PHP Login & Registration System

A complete, secure login and registration system built with PHP and MySQL.

## Features

- User Registration and Login
- Password Hashing for Security
- User Dashboard
- View All Registered Users
- Logout Functionality
- Glass-morphism UI Design
- Mobile Responsive
- Real-time Form Validation
- Toast Notifications
- Show/Hide Password Toggle
- Automatic Database Table Creation

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP / WAMP / MAMP

## Installation

1. Copy `login.php` to your htdocs folder (XAMPP) or www folder (WAMP)
2. Start Apache and MySQL services
3. Create a database named `github_db` in phpMyAdmin
4. Open browser and go to `http://localhost/login.php`
5. The system will automatically create the required table

## Default Test Account

```
Email: test@example.com
Password: password
```

Add this test user by running SQL in phpMyAdmin:

```sql
INSERT INTO users (email, password) 
VALUES ('test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
```

## How to Use

1. **Sign Up** - Click "Sign Up" tab, enter email and password (min 6 characters)
2. **Login** - Click "Login" tab, enter your credentials
3. **Dashboard** - View welcome page after login
4. **View Users** - Click "View Users" to see all registered accounts
5. **Logout** - Click "Logout" to end session

## Database Structure

**Table:** users

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key, Auto Increment |
| email | VARCHAR(255) | Unique email address |
| password | VARCHAR(255) | Hashed password |
| created_at | TIMESTAMP | Account creation date |

## Security

- Passwords are hashed using `password_hash()`
- SQL injection protection using `mysqli_real_escape_string()`
- Session-based authentication
- No plain text passwords stored

## Customization

### Change Database Settings

```php
$conn = mysqli_connect("localhost", "root", "", "github_db");
```

### Change Theme Colors

Search for `#D4AF37` in the CSS to change the gold theme color.

## Troubleshooting

**Database Connection Failed**
- Ensure MySQL is running in XAMPP
- Create `github_db` database in phpMyAdmin

**Login Not Working**
- Verify user exists in database
- Clear browser cookies and cache
- Check if password meets minimum length

## Browser Support

- Chrome, Firefox, Safari, Edge (Latest versions)
- Mobile responsive

## Limitations

- Single user role (no admin/user distinction)
- No password reset feature
- No email verification
- No remember me functionality

## Future Improvements

- Email verification
- Password reset
- Admin and user roles
- Remember me feature
- Login attempt rate limiting
- OAuth login (Google, Facebook)

## License

Open source - available for personal and commercial use.

## Credits

- PHP and MySQL
- Glass-morphism UI design
- Font Awesome icons
- Google Fonts (Inter)

---

**Note:** This is a development application. For production use, add SSL, CSRF protection, and proper error handling.
