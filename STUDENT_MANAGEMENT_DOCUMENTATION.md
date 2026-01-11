# REQ-ACD-001: Student Management System
## Comprehensive Student Management Implementation

### Overview
This implementation provides a complete student management system covering all aspects from admission to graduation, including enrollment tracking, promotions, transfers, and comprehensive profile management.

---

## Features Implemented

### 1. Student Admission Management
**Location:** `assets/manageStudentAdmission.php`

#### Features:
- Create new admission applications with complete student and guardian information
- Track application status (pending, approved, rejected, waitlisted)
- Interview scheduling and notes
- Entrance test score recording
- Admission fee tracking
- Document submission tracking
- Approval/rejection workflow

#### Key Functions:
- `create_admission` - Create new admission application
- `update_admission_status` - Approve/reject admission
- `fetch_pending_admissions` - View all pending applications
- `fetch_admission` - Get specific admission details

#### Database Table: `student_admissions`

---

### 2. Student Enrollment Management
**Location:** `assets/manageStudentEnrollment.php`

#### Features:
- Manage yearly enrollments for students
- Track academic year wise enrollment
- Roll number assignment
- Subject selection and tracking
- Fee category management
- Scholarship tracking
- Enrollment status management (active, completed, discontinued)
- Class-wise enrollment reports
- Enrollment statistics

#### Key Functions:
- `create_enrollment` - Create new academic year enrollment
- `update_enrollment` - Update enrollment details
- `fetch_class_enrollments` - Get all students in a class
- `complete_enrollment` - Mark enrollment as completed
- `discontinue_enrollment` - Discontinue student enrollment
- `get_enrollment_stats` - Get enrollment statistics

#### Database Table: `student_enrollments`

---

### 3. Student Promotion System
**Location:** `assets/manageStudentPromotion.php`

#### Features:
- Individual student promotions
- Bulk class promotion
- Promotion types (regular, skip grade, detention)
- Performance grade tracking
- Attendance percentage in promotion
- Conduct remarks
- Promotion approval workflow
- Automatic enrollment creation on promotion
- Promotion history tracking

#### Key Functions:
- `create_promotion` - Create promotion record
- `approve_promotion` - Approve and execute promotion
- `reject_promotion` - Reject promotion request
- `bulk_promote_class` - Promote entire class
- `fetch_promotion_history` - Get student promotion history
- `fetch_pending_promotions` - View pending promotions

#### Database Table: `student_promotions`

---

### 4. Student Transfer Management
**Location:** `assets/manageStudentTransfer.php`

#### Features:
- Transfer types:
  - **Incoming** - Students joining from other schools
  - **Outgoing** - Students leaving to other schools
  - **Internal** - Section/class transfers within school
- Transfer clearance tracking:
  - Fee clearance
  - Library clearance
  - Documents clearance
- Transfer Certificate (TC) issuance
- TC number tracking
- Transfer approval workflow
- Transfer reason documentation

#### Key Functions:
- `create_transfer` - Create transfer request
- `approve_transfer` - Approve transfer
- `complete_transfer` - Complete transfer with clearances
- `issue_tc` - Issue Transfer Certificate
- `update_clearance` - Update clearance status
- `fetch_transfer_history` - Get transfer history
- `fetch_pending_transfers` - View pending transfers

#### Database Table: `student_transfers`

---

### 5. Comprehensive Student Profile
**Location:** `assets/manageStudentProfile.php`

#### Features:
- **Extended Student Information:**
  - Blood group, nationality, religion, category
  - Mother tongue, Aadhar number
  - Medical conditions and allergies
  - Emergency contacts
  - Hostel and transport requirements
  - Special needs documentation
  - Sibling information

- **Guardian Information:**
  - Father and mother details separately
  - Father/Mother occupation and income
  - Guardian details (if different)
  - Total family income
  - Emergency contact persons

- **Document Management:**
  - Upload and track important documents
  - Document verification system
  - Expiry date tracking for documents
  - Document types tracking

- **Academic History:**
  - Year-wise academic performance
  - Marks, percentage, grades
  - Class rank tracking
  - Result status

- **Parent-Teacher Meetings:**
  - Meeting scheduling and tracking
  - Topics discussed
  - Concerns and action items
  - Follow-up tracking

#### Key Functions:
- `fetch_complete_profile` - Get all student information
- `update_extended_profile` - Update extended student info
- `update_guardian_extended` - Update guardian details
- `add_document` - Upload student document
- `verify_document` - Verify uploaded document
- `add_meeting` - Record parent-teacher meeting
- `fetch_siblings` - Get sibling information

#### Database Tables:
- `students` (enhanced)
- `student_guardian` (enhanced)
- `student_documents`
- `student_academic_history`
- `parent_teacher_meetings`

---

### 6. Admin Panel UI
**Location:** `admin_panel/student_management.php`

#### Features:
- Tabbed interface for different management sections
- **Admissions Tab:**
  - View and manage admission applications
  - Approve/reject applications
  - Create new admission applications

- **Enrollments Tab:**
  - Filter by academic year and class
  - View enrollment statistics
  - Manage enrollments

- **Promotions Tab:**
  - Individual and bulk promotions
  - View pending promotions
  - Approve/reject promotions

- **Transfers Tab:**
  - Manage incoming, outgoing, internal transfers
  - Track TC issuance
  - Clearance management

- **Complete Profiles Tab:**
  - View comprehensive student profiles
  - Timeline view of student journey
  - Edit extended information

---

## Database Schema

### Enhanced Tables

#### students table (enhanced with new fields):
```sql
- admission_no (VARCHAR) - Unique admission number
- admission_date (DATE) - Date of admission
- enrollment_status (ENUM) - Current enrollment status
- previous_school (VARCHAR) - Previous school name
- tc_number (VARCHAR) - Transfer certificate number
- blood_group (VARCHAR)
- nationality (VARCHAR)
- religion (VARCHAR)
- category (ENUM) - General, OBC, SC, ST, Other
- mother_tongue (VARCHAR)
- aadhar_no (VARCHAR)
- medical_conditions (TEXT)
- allergies (TEXT)
- emergency_contact (VARCHAR)
- hostel_required (BOOLEAN)
- transport_required (BOOLEAN)
- special_needs (TEXT)
- sibling_id (VARCHAR) - Link to sibling
```

#### student_guardian table (enhanced):
```sql
- mother_name (VARCHAR)
- mother_phone (VARCHAR)
- mother_occupation (VARCHAR)
- mother_income (DECIMAL)
- father_occupation (VARCHAR)
- father_income (DECIMAL)
- guardian_email (VARCHAR)
- guardian_occupation (VARCHAR)
- guardian_income (DECIMAL)
- total_family_income (DECIMAL)
- emergency_contact_name (VARCHAR)
- emergency_contact_phone (VARCHAR)
- emergency_contact_relation (VARCHAR)
```

### New Tables Created

1. **student_admissions** - Admission tracking
2. **student_enrollments** - Academic year enrollments
3. **student_promotions** - Promotion history
4. **student_transfers** - Transfer records
5. **student_documents** - Document management
6. **student_academic_history** - Academic performance history
7. **parent_teacher_meetings** - Meeting records

---

## Installation Steps

### 1. Database Setup
Run the SQL script to create enhanced schema:
```bash
# Navigate to database directory
cd database

# Import the enhancement SQL file
mysql -u username -p database_name < student_management_enhancement.sql
```

### 2. File Upload Directory
Create directory for document uploads:
```bash
mkdir -p studentUploads/documents
chmod 755 studentUploads/documents
```

### 3. Access the System
Navigate to: `admin_panel/student_management.php`

---

## Usage Guide

### Creating New Admission
1. Go to Admissions tab
2. Click "New Admission" button
3. Fill in all required information:
   - Student details
   - Contact information
   - Parent/Guardian information
   - Previous school details
4. Submit application
5. System generates unique Application Number and Admission Number

### Approving Admission
1. View pending admissions in Admissions tab
2. Click "Approve" button
3. Student status changes to "active"
4. Enrollment record is automatically created

### Managing Enrollments
1. Go to Enrollments tab
2. Select academic year and class
3. View enrolled students
4. Update enrollment details as needed
5. View enrollment statistics

### Promoting Students
**Individual Promotion:**
1. Go to Promotions tab
2. Click "New Promotion"
3. Select student and new class
4. Enter performance details
5. Submit for approval

**Bulk Promotion:**
1. Click "Bulk Promotion"
2. Select from class/section
3. Select to class/section
4. System promotes all active students
5. Creates new enrollments automatically

### Managing Transfers
**Outgoing Transfer:**
1. Create transfer request
2. Mark clearances (fee, library, documents)
3. Issue TC
4. Complete transfer
5. Student status changes to "transferred"

**Internal Transfer:**
1. Create internal transfer
2. Select new class/section
3. Approve and complete
4. Student moves to new class

---

## API Reference

### Common Parameters
- `action` - Action to perform
- `student_id` - Student unique ID

### Response Format
```json
{
  "status": "success" | "error",
  "message": "Response message",
  "data": {} // Response data (if applicable)
}
```

---

## Security Features
- SQL injection prevention using prepared statements
- Input validation and sanitization
- Transaction management for data integrity
- File upload validation
- Session-based authentication required

---

## Future Enhancements
- Email notifications for admission status
- SMS alerts for parents
- Online admission form
- Document scanning and verification
- Automated promotion based on criteria
- Transfer certificate PDF generation
- Mobile app integration
- Parent portal access

---

## Support
For issues or questions, contact the development team or refer to the main documentation.

---

## Version History
- v1.0 (Dec 2025) - Initial implementation
  - Admission management
  - Enrollment tracking
  - Promotion system
  - Transfer management
  - Comprehensive profiles
