# � 91CLUB - Color Prediction Game

A complete color prediction game website built with **Core PHP**, **MySQL**, **Vue.js**, and **Bootstrap 5**. Features multiple games including Color Prediction, Dice, Big/Small, and more.

## 🚀 Features

### � **User Panel**
- **Registration/Login** with auto-generated UID (91CLUB + 5 digits)
- **Real-time Dashboard** with Vue.js reactivity
- **Color Prediction Game** with RED/GREEN/VIOLET betting
- **Multiple Games**: Dice, Big/Small, Head/Tail, Mines
- **60-second rounds** with live countdown timer
- **Deposit System** with UPI transaction ID
- **Withdraw System** with bank details validation
- **Game History** and transaction records
- **Live Notifications** with auto-refresh
- **Mobile-responsive** bottom navigation
- **Real-time balance** updates

### � **Admin Panel**
- **Secure Admin Login** with encrypted passwords
- **Complete Dashboard** with statistics
- **User Management** with balance editing
- **Deposit/Withdraw** approval system
- **Game Control** with manual result setting
- **Round History** and bet monitoring
- **Notification Management**
- **Real-time monitoring** of all activities

### ⚙️ **Technical Features**
- **Auto Result Generation** via cron job
- **Real-time API** endpoints
- **Secure Authentication** with PHP sessions
- **Database Transactions** for bet consistency
- **Mobile-first Design** with Tailwind CSS
- **Vue.js Components** for interactive UI
- **SweetAlert2** for beautiful notifications
- **Bootstrap 5** for responsive layouts

## 📁 Project Structure

```
├── admin/                  # Admin panel
│   ├── login.php          # Admin login
│   ├── dashboard.php      # Admin dashboard
│   └── logout.php         # Admin logout
├── api/                   # API endpoints
│   ├── place_bet.php      # Bet placement
│   ├── get_game_data.php  # Game data fetching
│   ├── get_notification.php # Notifications
│   └── admin/             # Admin APIs
│       ├── get_users.php  # User management
│       ├── get_rounds.php # Round history
│       └── set_result.php # Manual result setting
├── config/                # Configuration
│   ├── db.php            # Database connection
│   └── functions.php     # Helper functions
├── cron/                 # Cron jobs
│   └── auto_result.php   # Auto result generation
├── games/                # Game components
│   └── dice.php          # Dice game
├── user/                 # User panel
│   ├── login.php         # User login
│   ├── register.php      # User registration
│   ├── dashboard.php     # Main dashboard
│   └── deposit.php       # Deposit page
├── color_prediction_database.sql # Database schema
└── README.md             # This file
```

## �️ Database Tables

- **users** - User accounts with UID, balance, credentials
- **deposits** - Deposit requests with UPI transaction IDs
- **withdraws** - Withdrawal requests with bank details
- **predictions** - User bets with game type and amounts
- **rounds** - Game rounds with results and timing
- **notifications** - System-wide notifications
- **admin_users** - Admin accounts for management

## 🛠️ Installation & Setup

### **Local Development (XAMPP/WAMP)**

1. **Download & Extract**
   ```bash
   # Download the project files
   # Extract to htdocs/color-prediction-game/
   ```

2. **Database Setup**
   ```sql
   # Import the database
   mysql -u root -p < color_prediction_database.sql
   ```

3. **Configure Database**
   ```php
   // Edit config/db.php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'color_prediction_game');
   ```

4. **Setup Cron Job**
   ```bash
   # Add to crontab (Linux/Mac)
   * * * * * /usr/bin/php /path/to/project/cron/auto_result.php
   
   # Windows: Use Task Scheduler to run every minute
   ```

5. **Access the Application**
   - **User Panel**: `http://localhost/color-prediction-game/user/login.php`
   - **Admin Panel**: `http://localhost/color-prediction-game/admin/login.php`

### **cPanel Hosting**

1. **Upload Files**
   ```bash
   # Upload all files to public_html/
   # Or create subdirectory: public_html/game/
   ```

2. **Create Database**
   ```sql
   # Use cPanel MySQL Database Wizard
   # Import color_prediction_database.sql
   ```

3. **Update Configuration**
   ```php
   // Update config/db.php with cPanel database details
   define('DB_HOST', 'localhost');
   define('DB_USER', 'cpanel_username_dbuser');
   define('DB_PASS', 'your_db_password');
   define('DB_NAME', 'cpanel_username_dbname');
   ```

4. **Setup Cron Job**
   ```bash
   # In cPanel Cron Jobs, add:
   * * * * * /usr/local/bin/php /home/username/public_html/cron/auto_result.php
   ```

## 🎮 Default Login Credentials

### **Admin Access**
- **Username**: `admin`
- **Password**: `admin123`

### **Test User**
- Create new account via registration
- UID will be auto-generated (e.g., 91CLUB12345)

## 🎯 Game Rules

### **Color Prediction**
- **RED/GREEN**: 1.5x payout (50% profit)
- **VIOLET**: 5x payout (400% profit)
- **Duration**: 60 seconds per round

### **Dice Game**
- **BIG (4-6)**: 1.8x payout
- **SMALL (1-3)**: 1.8x payout
- **EXACT NUMBER**: 5x payout

### **Big/Small**
- Similar to dice but with different mechanics
- **Duration**: 60 seconds per round

## 🔧 API Endpoints

### **User APIs**
- `POST /api/place_bet.php` - Place a bet
- `GET /api/get_game_data.php` - Get current game data
- `GET /api/get_time_remaining.php` - Get round timer
- `GET /api/get_notification.php` - Get latest notification

### **Admin APIs**
- `GET /api/admin/get_users.php` - Get all users
- `GET /api/admin/get_rounds.php` - Get round history
- `POST /api/admin/set_result.php` - Set manual result
- `POST /api/admin/update_balance.php` - Update user balance

## 💰 Payment Integration

### **Deposit Process**
1. User enters amount and UPI transaction ID
2. Admin approves/rejects in admin panel
3. Balance automatically updated on approval

### **Withdraw Process**
1. User enters bank details and amount
2. System validates balance and password
3. Admin processes the withdrawal
4. Balance deducted after approval

## 🚀 Advanced Features

### **Real-time Updates**
- Live balance updates
- Auto-refreshing game data
- Real-time notifications
- Live round countdown

### **Security Features**
- Password hashing with PHP `password_hash()`
- SQL injection prevention with prepared statements
- Session-based authentication
- Input sanitization and validation

### **Mobile Optimization**
- Responsive design with Tailwind CSS
- Touch-friendly interface
- Bottom navigation like 91Club
- Optimized for all screen sizes

## 🔄 Cron Job Details

The `auto_result.php` script should run every minute to:
- Check for expired rounds (60+ seconds)
- Generate random results
- Calculate winnings and update balances
- Create new rounds automatically
- Clean up old round data

## 🎨 Customization

### **Colors & Branding**
- Edit CSS variables in each file
- Update gradient backgrounds
- Change logo and app name
- Modify color schemes

### **Game Logic**
- Adjust payout rates in `config/functions.php`
- Modify round duration (currently 60 seconds)
- Add new game types in `/games/` folder
- Customize betting amounts

### **UI/UX**
- All styles use Tailwind CSS classes
- Vue.js components for interactivity
- Bootstrap 5 for responsive layouts
- SweetAlert2 for notifications

## 🐛 Troubleshooting

### **Common Issues**

1. **Database Connection Failed**
   ```php
   // Check config/db.php credentials
   // Verify MySQL service is running
   // Check database permissions
   ```

2. **Cron Job Not Working**
   ```bash
   # Test manually first:
   php /path/to/cron/auto_result.php
   
   # Check cron logs:
   tail -f /var/log/cron
   ```

3. **Session Issues**
   ```php
   // Check PHP session configuration
   // Verify session.save_path is writable
   // Clear browser cookies
   ```

4. **API Errors**
   ```javascript
   // Check browser console for errors
   // Verify API endpoints are accessible
   // Check PHP error logs
   ```

## 📞 Support

For technical support or customization:
- Check PHP error logs: `/var/log/php_errors.log`
- Review browser console for JavaScript errors
- Verify database connectivity and permissions
- Test API endpoints individually

## 📄 License

This project is open-source and available for educational and commercial use.

## 🔮 Future Enhancements

- **Payment Gateway Integration** (Razorpay, PayU)
- **Mobile App** with React Native
- **Advanced Analytics** and reporting
- **Multi-language Support**
- **Social Features** and leaderboards
- **Additional Games** (Roulette, Blackjack)
- **Referral System** with rewards
- **KYC Verification** system

---

## 🚀 **Get Started Now!**

1. **Setup Database** → Import SQL file
2. **Configure Settings** → Update config/db.php
3. **Setup Cron Job** → Auto result generation
4. **Login as Admin** → admin/admin123
5. **Create Test Account** → Register new user
6. **Start Playing!** → Place your first bet

**Happy Gaming! 🎮**