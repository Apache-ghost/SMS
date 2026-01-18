# Announcement System Troubleshooting Guide

## Common Errors and Solutions

### Error: "Error creating announcement"

**Possible Causes:**
1. Database table `announcements` doesn't exist
2. Missing session data (uid)
3. Invalid date format
4. Missing required fields

**Solutions:**

#### 1. Check if database tables exist
Visit: `http://localhost/school/admin_panel/test_announcements_db.php`

If the table doesn't exist, you need to run the SQL migration:

```sql
-- Run this in phpMyAdmin or MySQL console
SOURCE C:/xampp/htdocs/school/database/school_enhancement_part3.sql;
```

Or manually create the table by running the SQL from:
`database/school_enhancement_part3.sql` (lines 332-362)

#### 2. Check browser console for detailed errors
1. Open browser Developer Tools (F12)
2. Go to Console tab
3. Try creating an announcement
4. Look for the error message - it will show the exact SQL error

#### 3. Verify session is active
Make sure you're logged in as admin. Check if you can see your session UID in the page source.

#### 4. Check date format
Make sure you're using the datetime-local input properly:
- Display From: Must be a valid datetime (YYYY-MM-DDTHH:MM)
- Display Until: Must be after Display From

### Error: "Error loading announcements"

**Possible Causes:**
1. Database connection issue
2. Session not started
3. Student not found in database
4. SQL query error

**Solutions:**

#### 1. Check browser console
Press F12 and look at the Console tab for detailed error messages

#### 2. Check if student is logged in
Make sure you're logged in as a student and can see your profile

#### 3. Verify database connection
Check `assets/config.php` has correct database credentials

#### 4. Check SQL query
The query needs the announcements table to exist

## Quick Test Steps

### Test 1: Check Database
1. Go to: `http://localhost/school/admin_panel/test_announcements_db.php`
2. Should see: `"table_exists": true`
3. If false, run the SQL migration

### Test 2: Create Announcement
1. Login as admin
2. Go to: `http://localhost/school/admin_panel/announcements.php`
3. Click "Create Announcement"
4. Fill in:
   - Title: "Test Announcement"
   - Content: "This is a test"
   - Type: General
   - Priority: Normal
   - Target Audience: Students
   - Display From: Today's date and time
   - Display Until: Tomorrow's date and time
   - Status: Publish Now
5. Click "Create Announcement"
6. Open browser console (F12) to see detailed response
7. Should see success message

### Test 3: View on Student Dashboard
1. Login as student
2. Go to: `http://localhost/school/student_panel/index.php`
3. Scroll to "Announcements" section
4. Should see the test announcement
5. Open browser console (F12) to see fetch response

## Debug Mode

### Enable PHP Error Display
Add this to the top of the PHP files for debugging:

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Check PHP Error Log
Location: `C:\xampp\apache\logs\error.log`

Look for any PHP errors related to the announcement files

## Common SQL Issues

### Missing Table Error
```
Table 'school.announcements' doesn't exist
```

**Solution:** Run the SQL migration file

### Column Doesn't Exist Error
```
Unknown column 'xyz' in 'field list'
```

**Solution:** Your table structure is outdated. Drop and recreate the table

### Foreign Key Constraint Error
```
Cannot add or update a child row: a foreign key constraint fails
```

**Solution:** 
- Make sure the `published_by` user ID exists in admins or teachers table
- Or set published_by to NULL-able in the table

## File Checklist

Verify these files exist and are correct:

- ✅ `assets/manageAnnouncements.php` - Main backend API
- ✅ `assets/fetchStudentAnnouncements.php` - Student fetch API
- ✅ `admin_panel/announcements.php` - Admin interface
- ✅ `student_panel/index.php` - Student dashboard (modified)
- ✅ Database table: `announcements`
- ✅ Database table: `announcement_recipients`

## Browser Console Commands

Open browser console and run these to debug:

```javascript
// Test fetch student announcements
fetch('../assets/fetchStudentAnnouncements.php')
  .then(r => r.text())
  .then(console.log);

// Test create announcement
const fd = new FormData();
fd.append('action', 'create_announcement');
fd.append('title', 'Test');
fd.append('content', 'Test content');
fd.append('announcement_type', 'general');
fd.append('priority', 'normal');
fd.append('target_audience', 'students');
fd.append('display_from', '2026-01-17 10:00:00');
fd.append('display_until', '2026-01-18 10:00:00');
fd.append('status', 'published');
fd.append('published_by', '1001');
fetch('../assets/manageAnnouncements.php', {method: 'POST', body: fd})
  .then(r => r.text())
  .then(console.log);
```

## Contact Points

If still having issues, check:
1. Browser console for JavaScript errors
2. Network tab for API responses
3. PHP error log for server errors
4. Database for table existence

## Expected Behavior

**Admin Side:**
1. Can create announcements with all fields
2. See list of all announcements
3. Can publish, view, delete announcements
4. See statistics (total, published, views, pinned)

**Student Side:**
1. See all published announcements targeted to them
2. See unread count badge
3. Click to view full details in modal
4. Announcement marks as read automatically
5. See priority colors and type icons
