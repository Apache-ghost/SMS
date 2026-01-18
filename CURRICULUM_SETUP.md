# 🎓 Curriculum Download System - Quick Setup

## ✅ What Was Done

I've made the curriculum system fully functional with file download capabilities for students!

### Changes Made:

1. **Created folder**: `curriculumUploads/` for storing curriculum files
2. **Updated admin panel**: Added file upload field to curriculum form
3. **Updated backend**: Modified `addCurriculum.php` to handle file uploads
4. **Updated student dashboard**: Added download button when curriculum has a file
5. **Created test page**: For easy testing and verification

## 🚀 QUICK START (3 Steps)

### Step 1: Add Database Column (REQUIRED!)

**Option A - Run SQL file:**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database: `_sms`
3. Click "Import"
4. Choose: `C:\xampp\htdocs\school\database\add_curriculum_file_column.sql`
5. Click "Go"

**Option B - Run SQL directly:**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database: `_sms`
3. Click "SQL" tab
4. Run this:
```sql
ALTER TABLE `curriculum_master` 
ADD COLUMN `file_path` VARCHAR(500) NULL AFTER `total_credits`;
```

### Step 2: Test the System

Visit: `http://localhost/school/admin_panel/test_curriculum_system.php`

All tests should show ✅ PASS

### Step 3: Create Your First Curriculum

1. Go to: `http://localhost/school/admin_panel/curriculum.php`
2. Click "Add Curriculum"
3. Fill in:
   - Curriculum Name: "Science Stream 2026"
   - Class: Select a class (e.g., 10)
   - Academic Year: "2026-2027"
   - Department: "Science"
   - **Upload File**: Choose a PDF file
   - Add subjects using the dropdown
4. Click "Save Curriculum"

### Step 4: Test Student View

1. Login as a student of the class you created curriculum for
2. Go to dashboard
3. Scroll to "My Curriculum" section
4. You should see a **"Download Curriculum"** button
5. Click to download the file

## 📁 File Locations

- **Upload folder**: `C:\xampp\htdocs\school\curriculumUploads\`
- **Admin page**: `admin_panel/curriculum.php`
- **Student dashboard**: `student_panel/index.php`
- **Backend API**: `assets/addCurriculum.php`
- **Test page**: `admin_panel/test_curriculum_system.php`
- **SQL file**: `database/add_curriculum_file_column.sql`

## 🎯 Features

### Admin Can:
- ✅ Upload PDF/DOC files when creating curriculum
- ✅ See "File attached" indicator in curriculum list
- ✅ Download curriculum files directly from admin panel
- ✅ All existing features (add, edit, delete, view subjects)

### Student Can:
- ✅ See curriculum for their class
- ✅ Download curriculum file if available
- ✅ View all subjects with hours
- ✅ See department and academic year

## 🐛 Troubleshooting

### Problem: "Column doesn't exist" error
**Solution**: Run Step 1 above to add the database column

### Problem: File upload fails
**Solution**: Check folder permissions
```bash
# In PowerShell:
icacls "C:\xampp\htdocs\school\curriculumUploads" /grant Everyone:(OI)(CI)F
```

### Problem: Download button not showing for student
**Checklist**:
- [ ] File was uploaded (check admin panel)
- [ ] Student's class matches curriculum class
- [ ] Curriculum status is "active"
- [ ] File exists in curriculumUploads folder

### Problem: File downloads but won't open
**Causes**:
- File got corrupted during upload
- Wrong file type was uploaded
- Try re-uploading the file

## 📊 Supported File Types

- PDF (`.pdf`) - Recommended
- Word Document (`.doc`)
- Word Document (`.docx`)

## 🔒 Security

- ✅ Unique filenames prevent overwrites
- ✅ File type validation
- ✅ Files stored outside web-accessible code
- ✅ Only authenticated users can access

## 📝 Example Use Case

**Scenario**: School wants to share Class 10 Science curriculum

1. **Admin Action**:
   - Creates curriculum: "Class 10 Science - 2026"
   - Uploads: "science_curriculum_2026.pdf"
   - Adds subjects: Math, Physics, Chemistry, Biology
   - Saves

2. **Student Experience**:
   - Logs into dashboard
   - Sees "My Curriculum" section
   - Sees curriculum info and download button
   - Downloads PDF and views on computer

## ✨ Success!

When working correctly you'll see:
- ✅ "File attached" badge in admin curriculum list
- ✅ Green download button in admin panel
- ✅ Purple "Download Curriculum" button on student dashboard
- ✅ File downloads when clicked
- ✅ PDF opens correctly

## 🎓 Next Steps

The curriculum system is now fully functional! You can:
1. Create curriculum for each class
2. Upload detailed curriculum PDFs
3. Students can download and study
4. Update curriculum files as needed

## 📞 Need Help?

1. Check test page: `admin_panel/test_curriculum_system.php`
2. Look at browser console (F12) for errors
3. Check: `CURRICULUM_DOWNLOAD_GUIDE.md` for detailed docs
