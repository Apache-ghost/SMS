<?php
// REQ-ACD-001: Student Transfer Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create transfer request
    if ($action == "create_transfer") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $transferType = mysqli_real_escape_string($conn, $_POST["transfer_type"]);
        $fromSchool = mysqli_real_escape_string($conn, $_POST["from_school"] ?? '');
        $toSchool = mysqli_real_escape_string($conn, $_POST["to_school"] ?? '');
        $fromClass = mysqli_real_escape_string($conn, $_POST["from_class"] ?? '');
        $fromSection = mysqli_real_escape_string($conn, $_POST["from_section"] ?? '');
        $toClass = mysqli_real_escape_string($conn, $_POST["to_class"] ?? '');
        $toSection = mysqli_real_escape_string($conn, $_POST["to_section"] ?? '');
        $transferDate = mysqli_real_escape_string($conn, $_POST["transfer_date"]);
        $transferReason = mysqli_real_escape_string($conn, $_POST["transfer_reason"]);
        $requestedBy = mysqli_real_escape_string($conn, $_POST["requested_by"] ?? '');
        $remarks = mysqli_real_escape_string($conn, $_POST["remarks"] ?? '');
        $requestDate = date('Y-m-d');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Check if there's already a pending transfer
            $checkSql = "SELECT * FROM student_transfers 
                        WHERE student_id = ? AND transfer_status IN ('requested', 'approved')";
            $stmtCheck = mysqli_prepare($conn, $checkSql);
            mysqli_stmt_bind_param($stmtCheck, "s", $studentId);
            mysqli_stmt_execute($stmtCheck);
            $resultCheck = mysqli_stmt_get_result($stmtCheck);
            
            if (mysqli_num_rows($resultCheck) > 0) {
                throw new Exception("Student already has a pending transfer request!");
            }
            
            // For internal transfer, get current class/section
            if ($transferType == 'internal') {
                $getStudent = "SELECT class, section FROM students WHERE id = ?";
                $stmtGet = mysqli_prepare($conn, $getStudent);
                mysqli_stmt_bind_param($stmtGet, "s", $studentId);
                mysqli_stmt_execute($stmtGet);
                $resultGet = mysqli_stmt_get_result($stmtGet);
                $studentData = mysqli_fetch_assoc($resultGet);
                $fromClass = $studentData['class'];
                $fromSection = $studentData['section'];
            }
            
            // Insert transfer request
            $sql = "INSERT INTO student_transfers 
                    (student_id, transfer_type, from_school, to_school, from_class, from_section, 
                     to_class, to_section, transfer_date, request_date, transfer_status, 
                     transfer_reason, requested_by, remarks) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'requested', ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssssssss", 
                $studentId, $transferType, $fromSchool, $toSchool, $fromClass, $fromSection,
                $toClass, $toSection, $transferDate, $requestDate, $transferReason, 
                $requestedBy, $remarks);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating transfer request: " . mysqli_error($conn));
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Transfer request created successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Approve transfer request
    else if ($action == "approve_transfer") {
        $transferId = mysqli_real_escape_string($conn, $_POST["transfer_id"]);
        $approvedBy = mysqli_real_escape_string($conn, $_POST["approved_by"]);
        
        $sql = "UPDATE student_transfers SET 
                transfer_status = 'approved',
                approved_by = ?
                WHERE transfer_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $approvedBy, $transferId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Transfer request approved!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error approving transfer: ' . mysqli_error($conn)]);
        }
    }
    
    // Reject transfer request
    else if ($action == "reject_transfer") {
        $transferId = mysqli_real_escape_string($conn, $_POST["transfer_id"]);
        $remarks = mysqli_real_escape_string($conn, $_POST["remarks"] ?? '');
        
        $sql = "UPDATE student_transfers SET 
                transfer_status = 'rejected',
                remarks = ?
                WHERE transfer_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $remarks, $transferId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Transfer request rejected!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error rejecting transfer: ' . mysqli_error($conn)]);
        }
    }
    
    // Complete transfer (after all clearances)
    else if ($action == "complete_transfer") {
        $transferId = mysqli_real_escape_string($conn, $_POST["transfer_id"]);
        $tcIssued = $_POST["tc_issued"] ?? 0;
        $tcNumber = mysqli_real_escape_string($conn, $_POST["tc_number"] ?? '');
        $documentsCleared = $_POST["documents_cleared"] ?? 0;
        $feeCleared = $_POST["fee_cleared"] ?? 0;
        $libraryCleared = $_POST["library_cleared"] ?? 0;
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get transfer details
            $getSql = "SELECT * FROM student_transfers WHERE transfer_id = ?";
            $stmtGet = mysqli_prepare($conn, $getSql);
            mysqli_stmt_bind_param($stmtGet, "i", $transferId);
            mysqli_stmt_execute($stmtGet);
            $resultGet = mysqli_stmt_get_result($stmtGet);
            $transfer = mysqli_fetch_assoc($resultGet);
            
            if (!$transfer) {
                throw new Exception("Transfer not found!");
            }
            
            if ($transfer['transfer_status'] != 'approved') {
                throw new Exception("Transfer must be approved before completion!");
            }
            
            // Check if all clearances are done for outgoing transfer
            if ($transfer['transfer_type'] == 'outgoing') {
                if (!$tcIssued || !$documentsCleared || !$feeCleared || !$libraryCleared) {
                    throw new Exception("All clearances must be completed for outgoing transfer!");
                }
            }
            
            // Update transfer record
            $tcIssueDate = $tcIssued ? date('Y-m-d') : null;
            $updateTransfer = "UPDATE student_transfers SET 
                              transfer_status = 'completed',
                              tc_issued = ?,
                              tc_issue_date = ?,
                              tc_number = ?,
                              documents_cleared = ?,
                              fee_cleared = ?,
                              library_cleared = ?
                              WHERE transfer_id = ?";
            
            $stmtUpdate = mysqli_prepare($conn, $updateTransfer);
            mysqli_stmt_bind_param($stmtUpdate, "issiiis", 
                $tcIssued, $tcIssueDate, $tcNumber, $documentsCleared, 
                $feeCleared, $libraryCleared, $transferId);
            
            if (!mysqli_stmt_execute($stmtUpdate)) {
                throw new Exception("Error completing transfer: " . mysqli_error($conn));
            }
            
            // Update student status based on transfer type
            if ($transfer['transfer_type'] == 'outgoing') {
                $updateStudent = "UPDATE students SET 
                                 enrollment_status = 'transferred',
                                 tc_number = ?
                                 WHERE id = ?";
                $stmtStudent = mysqli_prepare($conn, $updateStudent);
                mysqli_stmt_bind_param($stmtStudent, "ss", $tcNumber, $transfer['student_id']);
                
                // Discontinue current enrollment
                $updateEnrollment = "UPDATE student_enrollments SET 
                                    enrollment_status = 'discontinued'
                                    WHERE student_id = ? AND enrollment_status = 'active'";
                $stmtEnroll = mysqli_prepare($conn, $updateEnrollment);
                mysqli_stmt_bind_param($stmtEnroll, "s", $transfer['student_id']);
                mysqli_stmt_execute($stmtEnroll);
                
            } else if ($transfer['transfer_type'] == 'internal') {
                // Update student's class and section
                $updateStudent = "UPDATE students SET 
                                 class = ?,
                                 section = ?
                                 WHERE id = ?";
                $stmtStudent = mysqli_prepare($conn, $updateStudent);
                mysqli_stmt_bind_param($stmtStudent, "sss", 
                    $transfer['to_class'], $transfer['to_section'], $transfer['student_id']);
                
                // Update current enrollment
                $updateEnrollment = "UPDATE student_enrollments SET 
                                    class = ?,
                                    section = ?
                                    WHERE student_id = ? AND enrollment_status = 'active'";
                $stmtEnroll = mysqli_prepare($conn, $updateEnrollment);
                mysqli_stmt_bind_param($stmtEnroll, "sss", 
                    $transfer['to_class'], $transfer['to_section'], $transfer['student_id']);
                mysqli_stmt_execute($stmtEnroll);
                
            } else if ($transfer['transfer_type'] == 'incoming') {
                // Activate student enrollment
                $updateStudent = "UPDATE students SET 
                                 enrollment_status = 'active',
                                 class = ?,
                                 section = ?
                                 WHERE id = ?";
                $stmtStudent = mysqli_prepare($conn, $updateStudent);
                mysqli_stmt_bind_param($stmtStudent, "sss", 
                    $transfer['to_class'], $transfer['to_section'], $transfer['student_id']);
            }
            
            if (!mysqli_stmt_execute($stmtStudent)) {
                throw new Exception("Error updating student: " . mysqli_error($conn));
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Transfer completed successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Issue Transfer Certificate
    else if ($action == "issue_tc") {
        $transferId = mysqli_real_escape_string($conn, $_POST["transfer_id"]);
        $tcNumber = mysqli_real_escape_string($conn, $_POST["tc_number"]);
        
        $tcIssueDate = date('Y-m-d');
        
        $sql = "UPDATE student_transfers SET 
                tc_issued = 1,
                tc_issue_date = ?,
                tc_number = ?
                WHERE transfer_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $tcIssueDate, $tcNumber, $transferId);
        
        if (mysqli_stmt_execute($stmt)) {
            // Also update student's TC number
            $getSql = "SELECT student_id FROM student_transfers WHERE transfer_id = ?";
            $stmtGet = mysqli_prepare($conn, $getSql);
            mysqli_stmt_bind_param($stmtGet, "i", $transferId);
            mysqli_stmt_execute($stmtGet);
            $resultGet = mysqli_stmt_get_result($stmtGet);
            $transfer = mysqli_fetch_assoc($resultGet);
            
            $updateStudent = "UPDATE students SET tc_number = ? WHERE id = ?";
            $stmtStudent = mysqli_prepare($conn, $updateStudent);
            mysqli_stmt_bind_param($stmtStudent, "ss", $tcNumber, $transfer['student_id']);
            mysqli_stmt_execute($stmtStudent);
            
            echo json_encode(['status' => 'success', 'message' => 'Transfer Certificate issued successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error issuing TC: ' . mysqli_error($conn)]);
        }
    }
    
    // Update clearance status
    else if ($action == "update_clearance") {
        $transferId = mysqli_real_escape_string($conn, $_POST["transfer_id"]);
        $clearanceType = mysqli_real_escape_string($conn, $_POST["clearance_type"]);
        $status = $_POST["status"] ?? 0;
        
        $sql = "UPDATE student_transfers SET 
                $clearanceType = ?
                WHERE transfer_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $status, $transferId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'Clearance updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating clearance: ' . mysqli_error($conn)]);
        }
    }
    
    // Fetch student transfer history
    else if ($action == "fetch_transfer_history") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        
        $sql = "SELECT * FROM student_transfers 
                WHERE student_id = ? 
                ORDER BY transfer_date DESC";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $studentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $transfers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $transfers[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $transfers]);
    }
    
    // Fetch pending transfers
    else if ($action == "fetch_pending_transfers") {
        $transferType = mysqli_real_escape_string($conn, $_POST["transfer_type"] ?? '');
        
        $sql = "SELECT st.*, s.fname, s.lname, s.father, s.email, s.class, s.section 
                FROM student_transfers st 
                JOIN students s ON st.student_id = s.id 
                WHERE st.transfer_status IN ('requested', 'approved')";
        
        if (!empty($transferType)) {
            $sql .= " AND st.transfer_type = ?";
        }
        
        $sql .= " ORDER BY st.request_date DESC";
        
        if (!empty($transferType)) {
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $transferType);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_query($conn, $sql);
        }
        
        $transfers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $transfers[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $transfers]);
    }
    
    // Fetch transfer details
    else if ($action == "fetch_transfer_details") {
        $transferId = mysqli_real_escape_string($conn, $_POST["transfer_id"]);
        
        $sql = "SELECT st.*, s.fname, s.lname, s.father, s.email, s.phone, s.class, s.section,
                sg.gname, sg.gphone, sg.relation
                FROM student_transfers st 
                JOIN students s ON st.student_id = s.id 
                LEFT JOIN student_guardian sg ON s.id = sg.id
                WHERE st.transfer_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $transferId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode(['status' => 'success', 'data' => $row]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Transfer not found']);
        }
    }
}

mysqli_close($conn);
?>
