# 🎉 Wedding Registration & Payment System - SECURED

A **SECURE** wedding registration and payment system with QR code functionality and admin dashboard. **All pages now require login!**

## 🔐 Security Features

- **Login Required**: All pages now require authentication
- **Session Management**: Secure session handling
- **Redirect Protection**: Unauthorized access redirected to login
- **Secure Routes**: All content protected behind authentication

## 📁 Secure Project Structure

```
hamisi/
├── index.php                 # 🔒 Redirects to login
├── index.html               # 🔒 Redirects to login (legacy)
├── .htaccess                # 🔒 Security rules & redirects
├── install.php              # 🔒 Installation script
├── README.md                # 📖 This file
├── login/                   # 🔐 Authentication
│   ├── index.html          # Login form
│   ├── login.php           # Login handler
│   └── logout.php          # Logout handler
├── public/                  # 🔐 SECURED CONTENT (requires login)
│   ├── index.php           # Main page (secured)
│   ├── rsvp.php            # RSVP form (secured)
│   ├── payment.php         # Payment form (secured)
│   ├── gallery.php         # Photo gallery (secured)
│   ├── gifts.php           # Gift info (secured)
│   ├── directions.php      # Venue directions (secured)
│   ├── about.php           # About couple (secured)
│   └── info.php            # General info (secured)
├── admin/                   # 👑 Admin interface (admin only)
│   └── dashboard.php       # Admin dashboard
├── config/                  # ⚙️ Configuration
│   ├── database.php        # Database connection
│   └── session_check.php   # Session security
├── database/                # 🗄️ Database files
│   └── schema.sql          # Database schema
├── modules/                 # 🔧 Core functionality
│   ├── qr/                 # QR code generation
│   ├── payment/            # Payment processing
│   └── registration/       # Guest registration
├── vendor/                  # 📚 Third-party libraries
│   └── phpqrcode/          # QR code library
├── temp/                    # 📁 Temporary files
│   └── qr_codes/           # Generated QR codes
├── image/                   # 🖼️ Your image files
├── music/                   # 🎵 Your music files
└── video/                   # 🎬 Your video files
```

## 🚀 How It Works Now

1. **User visits any page** → Automatically redirected to login
2. **User logs in** → Redirected to secure main page
3. **All content protected** → Requires valid session
4. **Logout** → Session destroyed, redirected to login

## 🛠️ Installation

1. **Run the installer**:
   ```bash
   php install.php
   ```

2. **Access the system**:
   - **Login**: `login/index.html`
   - **Main site**: `public/index.php` (after login)
   - **Admin**: `admin/dashboard.php` (admin users only)

## 🔐 Login Credentials

- **Email**: `admin@harusi.com`
- **Password**: `admin123`

## 🔒 Security Features

- **Session-based authentication**
- **Automatic redirects to login**
- **Protected file access**
- **Secure headers (XSS, CSRF protection)**
- **Input validation and sanitization**
- **Prepared statements for database queries**
- **CORS configuration for ngrok deployment**

## 📱 Usage Flow

1. **Guest visits site** → Redirected to login
2. **Guest logs in** → Access to all wedding content
3. **Guest can**: Register RSVP, make payments, view gallery
4. **Admin can**: Access dashboard, view all registrations
5. **Logout** → Returns to login page

## 🎨 Customization

- **Design**: Modify HTML/CSS in `public/` files
- **Functionality**: Adjust PHP modules
- **Security**: Update `config/session_check.php`
- **Media**: Add files to `image/`, `music/`, `video/` folders

## 🔧 Configuration

- **Database**: `config/database.php`
- **Security**: `config/session_check.php`
- **Server**: `.htaccess`
- **Sessions**: Automatic management

## 📞 Support

For questions or issues, check the code comments or modify the relevant files directly.

## ⚠️ Important Notes

- **All HTML files now redirect to login**
- **Use `public/` directory for secure content**
- **Login required for any page access**
- **Sessions automatically managed**
- **Logout destroys all access**
