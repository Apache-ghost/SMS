<?php
// REQ-ACD-001: Student Admission Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create new admission application
    if ($action == "create_admission") {
        $applicationNo = "APP" . time();
        $fname = mysqli_real_escape_string($conn, $_POST["fname"]);
        $lname = mysqli_real_escape_string($conn, $_POST["lname"]);
        $father = mysqli_real_escape_string($conn, $_POST["father"]);
        $mother = mysqli_real_escape_string($conn, $_POST["mother"] ?? '');
        
        $dobString = $_POST["dob"];
        $timestamp = strtotime($dobString);
        $dob = date('d-m-Y', $timestamp);
        
        $gender = mysqli_real_escape_string($conn, $_POST["gender"]);
        $admissionClass = mysqli_real_escape_string($conn, $_POST["admission_class"]);
        $admissionSection = mysqli_real_escape_string($conn, $_POST["admission_section"] ?? '');
        $applicationDate = date('Y-m-d');
        
        $phone = mysqli_real_escape_string($conn, $_POST["phone"]);
        $email = mysqli_real_escape_string($conn, $_POST["email"]);
        $address = mysqli_real_escape_string($conn, $_POST["address"]);
        $city = mysqli_real_escape_string($conn, $_POST["city"]);
        $zip = mysqli_real_escape_string($conn, $_POST["zip"]);
        $state = mysqli_real_escape_string($conn, $_POST["state"]);
        
        $previousSchool = mysqli_real_escape_string($conn, $_POST["previous_school"] ?? '');
        $tcNumber = mysqli_real_escape_string($conn, $_POST["tc_number"] ?? '');
        $bloodGroup = mysqli_real_escape_string($conn, $_POST["blood_group"] ?? '');
        $nationality = mysqli_real_escape_string($conn, $_POST["nationality"] ?? 'Indian');
        $religion = mysqli_real_escape_string($conn, $_POST["religion"] ?? '');
        $category = mysqli_real_escape_string($conn, $_POST["category"] ?? 'General');
        
        // Guardian information
        $guardian = mysqli_real_escape_string($conn, $_POST["guardian"]);
        $gphone = mysqli_real_escape_string($conn, $_POST["gphone"]);
        $gaddress = mysqli_real_escape_string($conn, $_POST["gaddress"]);
        $gcity = mysqli_real_escape_string($conn, $_POST["gcity"]);
        $gzip = mysqli_real_escape_string($conn, $_POST["gzip"]);
        $relation = mysqli_real_escape_string($conn, $_POST["relation"]);
        
        // Check if email already exists
        $checkEmail = "SELECT * FROM users WHERE email=?";
        $stmt = mysqli_prepare($conn, $checkEmail);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists!']);
            exit;
        }
        
        // Generate unique student ID
        $studentId = "S" . time();
        $applicationNo = "APP" . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        mysqli_begin_transaction($conn);
        
        try {
            // Insert into students table (using current schema)
            $sqlStudent = "INSERT INTO students (id, fname, lname, father, gender, class, section, dob, phone, email, address, city, zip, state) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmtStudent = mysqli_prepare($conn, $sqlStudent);
            mysqli_stmt_bind_param($stmtStudent, "ssssssssssssss", 
                $studentId, $fname, $lname, $father, $gender, 
                $admissionClass, $admissionSection, $dob, $phone, $email, $address, $city, 
                $zip, $state);
            
            if (!mysqli_stmt_execute($stmtStudent)) {
                throw new Exception("Error inserting student: " . mysqli_error($conn));
            }
            
            // Insert into student_guardian table
            $sqlGuardian = "INSERT INTO student_guardian (id, gname, gphone, gaddress, gcity, gzip, relation) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmtGuardian = mysqli_prepare($conn, $sqlGuardian);
            mysqli_stmt_bind_param($stmtGuardian, "sssssss", 
                $studentId, $guardian, $gphone, $gaddress, $gcity, $gzip, $relation);
            
            if (!mysqli_stmt_execute($stmtGuardian)) {
                throw new Exception("Error inserting guardian: " . mysqli_error($conn));
            }
            
            // Check if student_admissions table exists, create if not
            $tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'student_admissions'");
            if (mysqli_num_rows($tableCheck) > 0) {
                // Insert into student_admissions table
                $sqlAdmission = "INSERT INTO student_admissions (student_id, application_no, application_date, admission_for_class, admission_for_section, admission_status) 
                                VALUES (?, ?, ?, ?, ?, 'pending')";
                
                $stmtAdmission = mysqli_prepare($conn, $sqlAdmission);
                $applicationDate = date('Y-m-d');
                mysqli_stmt_bind_param($stmtAdmission, "sssss", 
                    $studentId, $applicationNo, $applicationDate, $admissionClass, $admissionSection);
                
                if (!mysqli_stmt_execute($stmtAdmission)) {
                    throw new Exception("Error creating admission record: " . mysqli_error($conn));
                }
            }
            
            mysqli_commit($conn);
            echo json_encode([
                'status' => 'success', 
                'message' => 'Student admission created successfully!',
                'application_no' => $applicationNo,
                'student_id' => $studentId
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Update admission status
    else if ($action == "update_admission_status") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $admissionStatus = mysqli_real_escape_string($conn, $_POST["admission_status"]);
        $interviewDate = $_POST["interview_date"] ?? null;
        $interviewNotes = mysqli_real_escape_string($conn, $_POST["interview_notes"] ?? '');
        $entranceScore = $_POST["entrance_score"] ?? null;
        $approvedBy = mysqli_real_escape_string($conn, $_POST["approved_by"] ?? '');
        $rejectionReason = mysqli_real_escape_string($conn, $_POST["rejection_reason"] ?? '');
        $remarks = mysqli_real_escape_string($conn, $_POST["remarks"] ?? '');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Update admission record
            $sql = "UPDATE student_admissions SET 
                    admission_status = ?,
                    interview_date = ?,
                    interview_notes = ?,
                    entrance_test_score = ?,
                    approved_by = ?,
                    rejection_reason = ?,
                    remarks = ?,
                    approved_date = " . ($admissionStatus == 'approved' ? "CURDATE()" : "NULL") . "
                    WHERE student_id = ?";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssdsss", 
                $admissionStatus, $interviewDate, $interviewNotes, $entranceScore, 
                $approvedBy, $rejectionReason, $remarks, $studentId);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error updating admission: " . mysqli_error($conn));
            }
            
            // If approved, update student enrollment status to active
            if ($admissionStatus == 'approved') {
                $updateStudent = "UPDATE students SET enrollment_status = 'active' WHERE id = ?";
                $stmtStudent = mysqli_prepare($conn, $updateStudent);
                mysqli_stmt_bind_param($stmtStudent, "s", $studentId);
                
                if (!mysqli_stmt_execute($stmtStudent)) {
                    throw new Exception("Error updating student status: " . mysqli_error($conn));
                }
                
                // Create enrollment record
                $academicYear = date('Y') . "-" . (date('Y') + 1);
                $getAdmission = "SELECT admission_for_class, admission_for_section FROM student_admissions WHERE student_id = ?";
                $stmtGet = mysqli_prepare($conn, $getAdmission);
                mysqli_stmt_bind_param($stmtGet, "s", $studentId);
                mysqli_stmt_execute($stmtGet);
                $resultGet = mysqli_stmt_get_result($stmtGet);
                $admissionData = mysqli_fetch_assoc($resultGet);
                
                $enrollmentDate = date('Y-m-d');
                $insertEnrollment = "INSERT INTO student_enrollments (student_id, academic_year, class, section, enrollment_date, enrollment_status) 
                                    VALUES (?, ?, ?, ?, ?, 'active')";
                $stmtEnroll = mysqli_prepare($conn, $insertEnrollment);
                mysqli_stmt_bind_param($stmtEnroll, "sssss", 
                    $studentId, $academicYear, $admissionData['admission_for_class'], 
                    $admissionData['admission_for_section'], $enrollmentDate);
                
                if (!mysqli_stmt_execute($stmtEnroll)) {
                    throw new Exception("Error creating enrollment: " . mysqli_error($conn));
                }
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Admission status updated successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Fetch admission details
    else if ($action == "fetch_admission") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        
        $sql = "SELECT sa.*, s.fname, s.lname, s.father, s.email, s.phone, s.class, s.section 
                FROM student_admissions sa 
                JOIN students s ON sa.student_id = s.id 
                WHERE sa.student_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Admission not found']);
        }
    }
    
    // Fetch all pending admissions
    else if ($action == "fetch_pending_admissions") {
        // Check if student_admissions table exists
        $tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'student_admissions'");
        
        if (mysqli_num_rows($tableCheck) > 0) {
            $sql = "SELECT sa.*, s.fname, s.lname, s.father, s.email, s.phone, s.dob 
                    FROM student_admissions sa 
                    JOIN students s ON sa.student_id = s.id 
                    WHERE sa.admission_status = 'pending' 
                    ORDER BY sa.application_date DESC";
            
            $result = mysqli_query($conn, $sql);
            $admissions = [];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $admissions[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'data' => $admissions]);
        } else {
            // Return empty array if table doesn't exist yet
            echo json_encode(['status' => 'success', 'data' => [], 'message' => 'No admissions table found']);
        }
    }
}

mysqli_close($conn);
?>
