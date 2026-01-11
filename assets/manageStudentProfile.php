<?php
// REQ-ACD-001: Comprehensive Student Profile Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Fetch comprehensive student profile
    if ($action == "fetch_complete_profile") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        
        // Get student basic info
        $sqlStudent = "SELECT * FROM students WHERE id = ?";
        $stmtStudent = mysqli_prepare($conn, $sqlStudent);
        mysqli_stmt_bind_param($stmtStudent, "s", $studentId);
        mysqli_stmt_execute($stmtStudent);
        $resultStudent = mysqli_stmt_get_result($stmtStudent);
        $student = mysqli_fetch_assoc($resultStudent);
        
        if (!$student) {
            echo json_encode(['status' => 'error', 'message' => 'Student not found']);
            exit;
        }
        
        // Get guardian info
        $sqlGuardian = "SELECT * FROM student_guardian WHERE id = ?";
        $stmtGuardian = mysqli_prepare($conn, $sqlGuardian);
        mysqli_stmt_bind_param($stmtGuardian, "s", $studentId);
        mysqli_stmt_execute($stmtGuardian);
        $resultGuardian = mysqli_stmt_get_result($stmtGuardian);
        $guardian = mysqli_fetch_assoc($resultGuardian);
        
        // Get admission info
        $sqlAdmission = "SELECT * FROM student_admissions WHERE student_id = ? ORDER BY application_date DESC LIMIT 1";
        $stmtAdmission = mysqli_prepare($conn, $sqlAdmission);
        mysqli_stmt_bind_param($stmtAdmission, "s", $studentId);
        mysqli_stmt_execute($stmtAdmission);
        $resultAdmission = mysqli_stmt_get_result($stmtAdmission);
        $admission = mysqli_fetch_assoc($resultAdmission);
        
        // Get current enrollment
        $sqlEnrollment = "SELECT * FROM student_enrollments WHERE student_id = ? AND enrollment_status = 'active' ORDER BY enrollment_date DESC LIMIT 1";
        $stmtEnrollment = mysqli_prepare($conn, $sqlEnrollment);
        mysqli_stmt_bind_param($stmtEnrollment, "s", $studentId);
        mysqli_stmt_execute($stmtEnrollment);
        $resultEnrollment = mysqli_stmt_get_result($stmtEnrollment);
        $enrollment = mysqli_fetch_assoc($resultEnrollment);
        
        // Get enrollment history
        $sqlEnrollmentHistory = "SELECT * FROM student_enrollments WHERE student_id = ? ORDER BY academic_year DESC";
        $stmtEnrollmentHistory = mysqli_prepare($conn, $sqlEnrollmentHistory);
        mysqli_stmt_bind_param($stmtEnrollmentHistory, "s", $studentId);
        mysqli_stmt_execute($stmtEnrollmentHistory);
        $resultEnrollmentHistory = mysqli_stmt_get_result($stmtEnrollmentHistory);
        $enrollmentHistory = [];
        while ($row = mysqli_fetch_assoc($resultEnrollmentHistory)) {
            $enrollmentHistory[] = $row;
        }
        
        // Get promotion history
        $sqlPromotions = "SELECT * FROM student_promotions WHERE student_id = ? ORDER BY academic_year_from DESC";
        $stmtPromotions = mysqli_prepare($conn, $sqlPromotions);
        mysqli_stmt_bind_param($stmtPromotions, "s", $studentId);
        mysqli_stmt_execute($stmtPromotions);
        $resultPromotions = mysqli_stmt_get_result($stmtPromotions);
        $promotions = [];
        while ($row = mysqli_fetch_assoc($resultPromotions)) {
            $promotions[] = $row;
        }
        
        // Get transfer history
        $sqlTransfers = "SELECT * FROM student_transfers WHERE student_id = ? ORDER BY transfer_date DESC";
        $stmtTransfers = mysqli_prepare($conn, $sqlTransfers);
        mysqli_stmt_bind_param($stmtTransfers, "s", $studentId);
        mysqli_stmt_execute($stmtTransfers);
        $resultTransfers = mysqli_stmt_get_result($stmtTransfers);
        $transfers = [];
        while ($row = mysqli_fetch_assoc($resultTransfers)) {
            $transfers[] = $row;
        }
        
        // Get documents
        $sqlDocuments = "SELECT * FROM student_documents WHERE student_id = ? ORDER BY uploaded_date DESC";
        $stmtDocuments = mysqli_prepare($conn, $sqlDocuments);
        mysqli_stmt_bind_param($stmtDocuments, "s", $studentId);
        mysqli_stmt_execute($stmtDocuments);
        $resultDocuments = mysqli_stmt_get_result($stmtDocuments);
        $documents = [];
        while ($row = mysqli_fetch_assoc($resultDocuments)) {
            $documents[] = $row;
        }
        
        // Get academic history
        $sqlAcademic = "SELECT * FROM student_academic_history WHERE student_id = ? ORDER BY academic_year DESC";
        $stmtAcademic = mysqli_prepare($conn, $sqlAcademic);
        mysqli_stmt_bind_param($stmtAcademic, "s", $studentId);
        mysqli_stmt_execute($stmtAcademic);
        $resultAcademic = mysqli_stmt_get_result($stmtAcademic);
        $academicHistory = [];
        while ($row = mysqli_fetch_assoc($resultAcademic)) {
            $academicHistory[] = $row;
        }
        
        // Get parent-teacher meetings
        $sqlMeetings = "SELECT * FROM parent_teacher_meetings WHERE student_id = ? ORDER BY meeting_date DESC LIMIT 10";
        $stmtMeetings = mysqli_prepare($conn, $sqlMeetings);
        mysqli_stmt_bind_param($stmtMeetings, "s", $studentId);
        mysqli_stmt_execute($stmtMeetings);
        $resultMeetings = mysqli_stmt_get_result($stmtMeetings);
        $meetings = [];
        while ($row = mysqli_fetch_assoc($resultMeetings)) {
            $meetings[] = $row;
        }
        
        // Compile complete profile
        $completeProfile = [
            'student' => $student,
            'guardian' => $guardian,
            'admission' => $admission,
            'current_enrollment' => $enrollment,
            'enrollment_history' => $enrollmentHistory,
            'promotions' => $promotions,
            'transfers' => $transfers,
            'documents' => $documents,
            'academic_history' => $academicHistory,
            'meetings' => $meetings
        ];
        
        echo json_encode(['status' => 'success', 'data' => $completeProfile]);
    }
    
    // Update extended student profile
    else if ($action == "update_extended_profile") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $bloodGroup = mysqli_real_escape_string($conn, $_POST["blood_group"] ?? '');
        $nationality = mysqli_real_escape_string($conn, $_POST["nationality"] ?? 'Indian');
        $religion = mysqli_real_escape_string($conn, $_POST["religion"] ?? '');
        $category = mysqli_real_escape_string($conn, $_POST["category"] ?? 'General');
        $motherTongue = mysqli_real_escape_string($conn, $_POST["mother_tongue"] ?? '');
        $aadharNo = mysqli_real_escape_string($conn, $_POST["aadhar_no"] ?? '');
        $medicalConditions = mysqli_real_escape_string($conn, $_POST["medical_conditions"] ?? '');
        $allergies = mysqli_real_escape_string($conn, $_POST["allergies"] ?? '');
        $emergencyContact = mysqli_real_escape_string($conn, $_POST["emergency_contact"] ?? '');
        $hostelRequired = $_POST["hostel_required"] ?? 0;
        $transportRequired = $_POST["transport_required"] ?? 0;
        $specialNeeds = mysqli_real_escape_string($conn, $_POST["special_needs"] ?? '');
        $siblingId = mysqli_real_escape_string($conn, $_POST["sibling_id"] ?? '');
        
        $sql = "UPDATE students SET 
                blood_group = ?,
                nationality = ?,
                religion = ?,
                category = ?,
                mother_tongue = ?,
                aadhar_no = ?,
                medical_conditions = ?,
                allergies = ?,
                emergency_contact = ?,
                hostel_required = ?,
                transport_required = ?,
                special_needs = ?,
                sibling_id = ?
                WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssssiisss", 
            $bloodGroup, $nationality, $religion, $category, $motherTongue, 
            $aadharNo, $medicalConditions, $allergies, $emergencyContact,
            $hostelRequired, $transportRequired, $specialNeeds, $siblingId, $studentId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Extended profile updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating profile: ' . mysqli_error($conn)]);
        }
    }
    
    // Update guardian extended info
    else if ($action == "update_guardian_extended") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $motherName = mysqli_real_escape_string($conn, $_POST["mother_name"] ?? '');
        $motherPhone = mysqli_real_escape_string($conn, $_POST["mother_phone"] ?? '');
        $motherOccupation = mysqli_real_escape_string($conn, $_POST["mother_occupation"] ?? '');
        $motherIncome = $_POST["mother_income"] ?? 0;
        $fatherOccupation = mysqli_real_escape_string($conn, $_POST["father_occupation"] ?? '');
        $fatherIncome = $_POST["father_income"] ?? 0;
        $guardianEmail = mysqli_real_escape_string($conn, $_POST["guardian_email"] ?? '');
        $guardianOccupation = mysqli_real_escape_string($conn, $_POST["guardian_occupation"] ?? '');
        $guardianIncome = $_POST["guardian_income"] ?? 0;
        $totalFamilyIncome = $_POST["total_family_income"] ?? 0;
        $emergencyContactName = mysqli_real_escape_string($conn, $_POST["emergency_contact_name"] ?? '');
        $emergencyContactPhone = mysqli_real_escape_string($conn, $_POST["emergency_contact_phone"] ?? '');
        $emergencyContactRelation = mysqli_real_escape_string($conn, $_POST["emergency_contact_relation"] ?? '');
        
        $sql = "UPDATE student_guardian SET 
                mother_name = ?,
                mother_phone = ?,
                mother_occupation = ?,
                mother_income = ?,
                father_occupation = ?,
                father_income = ?,
                guardian_email = ?,
                guardian_occupation = ?,
                guardian_income = ?,
                total_family_income = ?,
                emergency_contact_name = ?,
                emergency_contact_phone = ?,
                emergency_contact_relation = ?
                WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssdsdsdddssss", 
            $motherName, $motherPhone, $motherOccupation, $motherIncome,
            $fatherOccupation, $fatherIncome, $guardianEmail, $guardianOccupation,
            $guardianIncome, $totalFamilyIncome, $emergencyContactName,
            $emergencyContactPhone, $emergencyContactRelation, $studentId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Guardian info updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating guardian info: ' . mysqli_error($conn)]);
        }
    }
    
    // Add student document
    else if ($action == "add_document") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $documentType = mysqli_real_escape_string($conn, $_POST["document_type"]);
        $documentName = mysqli_real_escape_string($conn, $_POST["document_name"]);
        $expiryDate = $_POST["expiry_date"] ?? null;
        $remarks = mysqli_real_escape_string($conn, $_POST["remarks"] ?? '');
        $uploadedDate = date('Y-m-d');
        
        // Handle file upload
        if (isset($_FILES["document"]) && $_FILES["document"]["error"] == 0) {
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $fileExtension = strtolower(pathinfo($_FILES["document"]["name"], PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid file format!']);
                exit;
            }
            
            $fileName = $studentId . "_" . time() . "." . $fileExtension;
            $uploadPath = "../studentUploads/documents/" . $fileName;
            
            if (move_uploaded_file($_FILES["document"]["tmp_name"], $uploadPath)) {
                $fileSize = $_FILES["document"]["size"];
                
                $sql = "INSERT INTO student_documents 
                        (student_id, document_type, document_name, document_path, 
                         document_size, uploaded_date, expiry_date, remarks) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssisss", 
                    $studentId, $documentType, $documentName, $uploadPath,
                    $fileSize, $uploadedDate, $expiryDate, $remarks);
                
                if (mysqli_stmt_execute($stmt)) {
                    echo json_encode(['status' => 'success', 'message' => 'Document uploaded successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error saving document: ' . mysqli_error($conn)]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error uploading file!']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No file uploaded!']);
        }
    }
    
    // Verify document
    else if ($action == "verify_document") {
        $documentId = mysqli_real_escape_string($conn, $_POST["document_id"]);
        $verifiedBy = mysqli_real_escape_string($conn, $_POST["verified_by"]);
        $verifiedDate = date('Y-m-d');
        
        $sql = "UPDATE student_documents SET 
                verified = 1,
                verified_by = ?,
                verified_date = ?
                WHERE document_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $verifiedBy, $verifiedDate, $documentId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Document verified successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error verifying document: ' . mysqli_error($conn)]);
        }
    }
    
    // Add parent-teacher meeting
    else if ($action == "add_meeting") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $teacherId = mysqli_real_escape_string($conn, $_POST["teacher_id"] ?? '');
        $meetingDate = mysqli_real_escape_string($conn, $_POST["meeting_date"]);
        $meetingTime = mysqli_real_escape_string($conn, $_POST["meeting_time"]);
        $meetingType = mysqli_real_escape_string($conn, $_POST["meeting_type"] ?? 'scheduled');
        $attendees = mysqli_real_escape_string($conn, $_POST["attendees"] ?? '');
        $topicsDiscussed = mysqli_real_escape_string($conn, $_POST["topics_discussed"] ?? '');
        $concernsRaised = mysqli_real_escape_string($conn, $_POST["concerns_raised"] ?? '');
        $actionItems = mysqli_real_escape_string($conn, $_POST["action_items"] ?? '');
        $followUpRequired = $_POST["follow_up_required"] ?? 0;
        $followUpDate = $_POST["follow_up_date"] ?? null;
        $meetingNotes = mysqli_real_escape_string($conn, $_POST["meeting_notes"] ?? '');
        
        $sql = "INSERT INTO parent_teacher_meetings 
                (student_id, teacher_id, meeting_date, meeting_time, meeting_type, 
                 attendees, topics_discussed, concerns_raised, action_items, 
                 follow_up_required, follow_up_date, meeting_notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssssss", 
            $studentId, $teacherId, $meetingDate, $meetingTime, $meetingType,
            $attendees, $topicsDiscussed, $concernsRaised, $actionItems,
            $followUpRequired, $followUpDate, $meetingNotes);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Meeting record added successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error adding meeting: ' . mysqli_error($conn)]);
        }
    }
    
    // Get student siblings
    else if ($action == "fetch_siblings") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        
        // Get student's sibling_id field
        $getSibling = "SELECT sibling_id FROM students WHERE id = ?";
        $stmtGet = mysqli_prepare($conn, $getSibling);
        mysqli_stmt_bind_param($stmtGet, "s", $studentId);
        mysqli_stmt_execute($stmtGet);
        $resultGet = mysqli_stmt_get_result($stmtGet);
        $studentData = mysqli_fetch_assoc($resultGet);
        
        if ($studentData && !empty($studentData['sibling_id'])) {
            // Get sibling info
            $sql = "SELECT id, fname, lname, class, section, enrollment_status 
                    FROM students 
                    WHERE id = ?";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $studentData['sibling_id']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $siblings = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $siblings[] = $row;
            }
            
            echo json_encode(['status' => 'success', 'data' => $siblings]);
        } else {
            echo json_encode(['status' => 'success', 'data' => []]);
        }
    }
}

mysqli_close($conn);
?>
