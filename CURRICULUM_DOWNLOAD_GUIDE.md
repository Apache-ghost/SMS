# Curriculum Download System - Implementation Guide

## ✅ Features Implemented

### Admin Side (curriculum.php)
1. **Upload Curriculum File**: Admins can upload PDF/DOC files when creating curriculum
2. **File Indicator**: Shows "File attached" badge for curriculum with files
3. **Download Button**: Green download button in admin list for quick access
4. **All existing features** (add subjects, view, edit, delete)

### Student Side (index.php dashboard)
1. **Download Button**: Large download button appears when curriculum has a file
2. **Auto-display**: Shows for student's class
3. **Visual Design**: Prominent button with download icon

## 📁 Files Modified

1. **admin_panel/curriculum.php**
   - Added file upload input field
   - Added file indicator and download button in list
   
2. **assets/addCurriculum.php**
   - Added file upload handling
   - Stores files in /curriculumUploads/
   - Saves file path to database

3. **student_panel/index.php**
   - Added download button for curriculum files
   - Shows only when file exists

4. **database/add_curriculum_file_column.sql**
   - SQL to add file_path column to curriculum_master

## 🚀 Setup Instructions

### Step 1: Add Database Column

Run this SQL in phpMyAdmin:

```sql
ALTER TABLE `curriculum_master` 
ADD COLUMN `file_path` VARCHAR(500) NULL AFTER `total_credits`;
```

Or import the file: `database/add_curriculum_file_column.sql`

### Step 2: Verify Folder Permissions

The folder `curriculumUploads/` has been created. Ensure it has write permissions:
- Location: `C:\xampp\htdocs\school\curriculumUploads\`

### Step 3: Test the System

**As Admin:**
1. Go to: `http://localhost/school/admin_panel/curriculum.php`
2. Click "Add Curriculum"
3. Fill in all required fields
4. Upload a PDF file (optional)
5. Add subjects
6. Click "Save Curriculum"

**As Student:**
1. Login to student dashboard
2. Scroll to "My Curriculum" section
3. If curriculum has a file, you'll see "Download Curriculum" button
4. Click to download

## 💾 Database Structure

### curriculum_master table
```sql
- curriculum_id (INT, PK)
- curriculum_name (VARCHAR)
- academic_year (VARCHAR)
- class (INT)
- department_code (VARCHAR)
- total_credits (INT)
- file_path (VARCHAR) -- NEW: stores filename
- status (ENUM)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### File Storage
- Location: `/curriculumUploads/`
- Naming: `curriculum_{timestamp}_{unique_id}.{ext}`
- Supported: PDF, DOC, DOCX

## 🎨 Visual Features

### Admin Panel
- **Upload field**: File input with helper text
- **File badge**: Green "File attached" indicator
- **Download button**: Green button with download icon
- **Responsive design**: Works on all screen sizes

### Student Dashboard  
- **Download button**: Large, prominent purple button
- **Icon**: Download icon for clarity
- **Placement**: Next to curriculum header
- **Only shows**: When file exists

## 🔧 Testing Checklist

- [ ] Database column `file_path` added
- [ ] Folder `curriculumUploads/` exists and is writable
- [ ] Admin can upload file when creating curriculum
- [ ] File appears in admin curriculum list
- [ ] Admin can download file from list
- [ ] Student sees download button on dashboard
- [ ] Student can download curriculum file
- [ ] File downloads with correct name

## 📊 Example Curriculum Creation

```
Curriculum Name: Science Stream 2026
Class: 10
Academic Year: 2026-2027
Department: Science
Description: Complete science curriculum for Class 10
File: [Upload curriculum_science_class10.pdf]

Subjects:
- Mathematics (Theory: 6h, Practical: 2h)
- Physics (Theory: 5h, Practical: 3h)
- Chemistry (Theory: 5h, Practical: 3h)
- Biology (Theory: 5h, Practical: 3h)
```

## 🐛 Troubleshooting

### File not uploading
- Check folder permissions: `curriculumUploads/` must be writable
- Check PHP upload limits in `php.ini`:
  - `upload_max_filesize = 10M`
  - `post_max_size = 10M`

### Download button not showing
- Verify file_path is not empty in database
- Check file actually exists in curriculumUploads folder
- Verify student's class matches curriculum class

### File path in database but file missing
- File may have been deleted from folder
- Re-upload using edit function (when implemented)

## 🔐 Security Features

1. **File type validation**: Only PDF, DOC, DOCX allowed
2. **Unique filenames**: Prevents overwrites with timestamp + unique ID
3. **Upload directory**: Separate from main code
4. **Database storage**: Only stores filename, not full path

## 📝 Future Enhancements (Optional)

- [ ] Edit curriculum to update file
- [ ] Delete old file when uploading new one
- [ ] Preview curriculum in browser
- [ ] Multiple file uploads per curriculum
- [ ] File size limits and validation
- [ ] Download statistics tracking

## ✨ Success Indicators

When working correctly:
- ✅ Admin uploads file successfully
- ✅ File appears in admin list with indicator
- ✅ Admin can download from list
- ✅ Student sees download button
- ✅ Student can download file
- ✅ File opens correctly after download
