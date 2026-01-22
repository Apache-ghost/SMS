# Admin-Parent Messaging System - Complete Guide

## Overview
A complete messaging system that enables two-way communication between parents and school administrators. Parents can send messages to the admin regarding their children, and admins can respond to these messages or initiate conversations with parents.

## 🎯 Features Implemented

### For Admin Panel:
- **View All Messages**: See all parent messages in one place
- **Filter Messages**: View all messages, unread only, or sent messages
- **Compose Messages**: Send messages to parents about specific students
- **Reply to Messages**: Respond to parent inquiries with threaded replies
- **Message Details**: View full conversation threads with timestamps
- **Real-time Updates**: See unread message indicators

### For Parent Panel:
- **Send Messages to Admin**: Contact school administration about concerns
- **View Message History**: See all sent and received messages
- **Reply to Admin**: Respond to admin messages in conversation threads
- **Message Status**: Track which messages have been read

## 📁 Files Created/Modified

### Created Files:
1. **Admin Panel:**
   - `/admin_panel/messages.php` - Main messaging interface
   - `/admin_panel/message_detail.php` - View and reply to individual messages

2. **Parent Panel:**
   - `/parent_panel/message_detail.php` - View and reply to individual messages

3. **Database:**
   - `/database/admin_parent_messaging_update.sql` - Database schema updates

### Modified Files:
1. `/assets/manageParentMessages.php` - Enhanced backend handler for admin support
2. `/admin_panel/partials/_sidebar.php` - Added Messages menu item
3. `/parent_panel/messages.php` - Updated to support admin messaging

## 🚀 Setup Instructions

### Step 1: Run Database Update
Execute the SQL update script to ensure all required fields exist:

```sql
-- Open phpMyAdmin and select your database (_sms)
-- Go to SQL tab and run:
SOURCE C:/xampp/htdocs/school/database/admin_parent_messaging_update.sql;
```

Or manually run the file contents in phpMyAdmin.

### Step 2: Verify File Permissions
Ensure the messaging uploads folder exists and has write permissions:
```
C:/xampp/htdocs/school/adminUploads/messages/
```

### Step 3: Test Admin Access
1. Login as admin: http://localhost/school/admin_panel/
2. Click "Messages" in the sidebar
3. You should see the messaging interface

### Step 4: Test Parent Access
1. Login as parent: http://localhost/school/parent_panel/
2. Click "Messages" in the sidebar
3. Test sending a message to "School Admin"

## 📝 How to Use

### As Admin:

#### Viewing Messages:
1. Navigate to **Admin Panel → Messages**
2. Use filter buttons:
   - **All Messages**: View complete inbox
   - **Unread**: Show only new messages from parents
   - **Sent**: View messages you've sent to parents

#### Composing a New Message:
1. Click **"+ New Message"** button
2. Select a student from the dropdown
3. The parent/guardian will be auto-populated
4. Enter subject and message
5. Click **Send**

#### Replying to Parent Messages:
1. Click on any message in the list
2. View the full conversation thread
3. Type your reply in the reply box at the bottom
4. Click **Send Reply**

### As Parent:

#### Sending Message to Admin:
1. Navigate to **Parent Panel → Messages**
2. Click **"+ New Message"** button
3. Select your child (optional)
4. Choose "School Admin" from the recipient dropdown
5. Enter subject and message
6. Click **Send**

#### Viewing and Replying:
1. Click on any message to view details
2. See the full conversation history
3. Type reply if the message was from admin
4. Click **Send Reply**

## 🔧 Database Schema

### parent_messages Table Structure:
```sql
- message_id (Primary Key)
- sender_id (Admin ID or Guardian ID)
- sender_type (ENUM: 'admin', 'parent', 'teacher')
- recipient_id
- recipient_type (ENUM: 'admin', 'parent', 'teacher')
- student_id (Optional - which student the message is about)
- subject (Message subject line)
- message (Original field)
- message_body (Main message content)
- parent_message_id (For threaded replies)
- message_type (general, attendance, performance, etc.)
- priority (low, normal, high, urgent)
- is_read (Boolean)
- is_archived (Boolean)
- is_starred (Boolean)
- is_replied (Boolean)
- sent_date (When message was sent)
- read_date (When message was read)
- created_at (Timestamp)
```

### message_attachments Table:
```sql
- attachment_id (Primary Key)
- message_id (Foreign Key)
- file_name
- file_path
- file_type
- file_size
- uploaded_at
```

## 🎨 UI Features

### Admin Messages Page:
- **Card-based layout** with clear visual hierarchy
- **Color-coded indicators**: Unread messages highlighted
- **Badge system**: Shows sender type (parent/admin)
- **Quick filters**: Toggle between all, unread, and sent
- **Responsive design**: Works on desktop and tablet

### Message Detail Page:
- **Thread view**: See entire conversation in chronological order
- **Reply form**: Easy-to-use inline reply interface
- **Metadata display**: Timestamps, student info, read status
- **Back navigation**: Quick return to inbox

## 🔍 Testing Checklist

### Admin Panel Testing:
- [ ] Login as admin
- [ ] Navigate to Messages page
- [ ] Verify empty state shows correctly
- [ ] Click "New Message" button
- [ ] Select a student with guardian
- [ ] Send a test message
- [ ] Verify message appears in "Sent" filter
- [ ] Receive a message from parent (test from parent panel)
- [ ] Verify unread indicator shows
- [ ] Click message to view details
- [ ] Verify message is marked as read
- [ ] Reply to the message
- [ ] Verify reply appears in thread

### Parent Panel Testing:
- [ ] Login as parent
- [ ] Navigate to Messages page
- [ ] Click "New Message"
- [ ] Select "School Admin" as recipient
- [ ] Send test message
- [ ] Verify message appears in list
- [ ] Receive reply from admin
- [ ] Verify unread indicator
- [ ] Click to view message detail
- [ ] Reply to admin
- [ ] Verify threaded conversation

## 🐛 Troubleshooting

### Issue: "No messages" always shows
**Solution**: Check database table exists and has correct structure. Run update SQL script.

### Issue: Cannot send messages
**Solution**: 
1. Check session is active (user is logged in)
2. Verify database connection in config.php
3. Check browser console for JavaScript errors
4. Verify fetchStudents.php and fetchStudentInfo.php exist

### Issue: Messages sent but not appearing
**Solution**:
1. Check sender_id and recipient_id are correctly set
2. Verify sender_type and recipient_type match database enum values
3. Check SQL query filters in manageParentMessages.php

### Issue: Reply doesn't work
**Solution**:
1. Ensure parent_message_id is being sent correctly
2. Check foreign key constraint exists in database
3. Verify message ownership for reply permission

## 🔐 Security Features

1. **Session-based authentication**: Only logged-in users can access
2. **SQL injection protection**: All inputs sanitized with mysqli_real_escape_string
3. **Prepared statements**: Used for all database queries
4. **XSS protection**: HTML escaping in JavaScript display functions
5. **Access control**: Users only see their own messages

## 📊 Future Enhancements (Optional)

- [ ] Email notifications when new message received
- [ ] File attachments for messages
- [ ] Message search functionality
- [ ] Bulk messaging to multiple parents
- [ ] SMS integration
- [ ] Message templates for common responses
- [ ] Archive/delete functionality
- [ ] Message priority levels with visual indicators

## 📞 Support

If you encounter any issues:
1. Check browser console for JavaScript errors
2. Check PHP error logs in `/xampp/logs/php_error_log`
3. Verify database structure matches schema
4. Ensure all files have correct permissions

## ✅ Summary

The messaging system is now fully functional with:
- ✅ Admin can view all parent messages
- ✅ Admin can reply to parent messages
- ✅ Admin can initiate messages to parents
- ✅ Parents can send messages to admin
- ✅ Parents can reply to admin messages
- ✅ Threaded conversation view
- ✅ Read/unread status tracking
- ✅ Clean, intuitive interface
- ✅ Mobile-responsive design

---

**Version**: 1.0  
**Date**: January 21, 2026  
**Status**: Complete and Ready for Testing
