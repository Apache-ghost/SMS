<?php
// Student Transfers Management
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? '';

    // Create new transfer
    if ($action == "create_transfer") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $transferType = mysqli_real_escape_string($conn, $_POST["transfer_type"]);
        $fromSchool = mysqli_real_escape_string($conn, $_POST["from_school"] ?? '');
        $toSchool = mysqli_real_escape_string($conn, $_POST["to_school"] ?? '');
        $transferReason = mysqli_real_escape_string($conn, $_POST["transfer_reason"] ?? '');
        $requestedBy = mysqli_real_escape_string($conn, $_POST["requested_by"]);
        
        $requestDate = date('Y-m-d');
        $transferDate = $_POST["transfer_date"] ?? date('Y-m-d');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get current student details
            $getStudent = "SELECT class, section FROM students WHERE id = ?";
            $stmt = mysqli_prepare($conn, $getStudent);
            mysqli_stmt_bind_param($stmt, "s", $studentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $student = mysqli_fetch_assoc($result);
            
            if (!$student) {
                throw new Exception("Student not found");
            }
            
            // Insert transfer record
            $sql = "INSERT INTO student_transfers (student_id, transfer_type, from_school, to_school, from_class, from_section, transfer_date, request_date, transfer_status, transfer_reason, requested_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'requested', ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssssss", 
                $studentId, $transferType, $fromSchool, $toSchool, 
                $student['class'], $student['section'], $transferDate, $requestDate, 
                $transferReason, $requestedBy);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating transfer: " . mysqli_error($conn));
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Transfer request created successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Approve transfer
    else if ($action == "approve_transfer") {
        $transferId = mysqli_real_escape_string($conn, $_POST["transfer_id"]);
        $approvedBy = mysqli_real_escape_string($conn, $_POST["approved_by"]);
        $tcNumber = mysqli_real_escape_string($conn, $_POST["tc_number"] ?? '');
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get transfer details
            $getTransfer = "SELECT * FROM student_transfers WHERE transfer_id = ?";
            $stmt = mysqli_prepare($conn, $getTransfer);
            mysqli_stmt_bind_param($stmt, "i", $transferId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $transfer = mysqli_fetch_assoc($result);
            
            if (!$transfer) {
                throw new Exception("Transfer not found");
            }
            
            // Update transfer status
            $tcIssueDate = date('Y-m-d');
            $updateTransfer = "UPDATE student_transfers 
                              SET transfer_status = 'approved', 
                                  approved_by = ?, 
                                  tc_issued = 1, 
                                  tc_issue_date = ?,
                                  tc_number = ?
                              WHERE transfer_id = ?";
            $stmtUpdate = mysqli_prepare($conn, $updateTransfer);
            mysqli_stmt_bind_param($stmtUpdate, "sssi", $approvedBy, $tcIssueDate, $tcNumber, $transferId);
            
            if (!mysqli_stmt_execute($stmtUpdate)) {
                throw new Exception("Error updating transfer: " . mysqli_error($conn));
            }
            
            // If outgoing transfer, mark student as transferred
            if ($transfer['transfer_type'] == 'outgoing') {
                $updateStudent = "UPDATE students SET request = 'transferred' WHERE id = ?";
                $stmtStudent = mysqli_prepare($conn, $updateStudent);
                mysqli_stmt_bind_param($stmtStudent, "s", $transfer['student_id']);
                mysqli_stmt_execute($stmtStudent);
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Transfer approved successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    // Issue TC
    else if ($action == "issue_tc") {
        $transferId = mysqli_real_escape_string($conn, $_POST["transfer_id"]);
        $tcNumber = mysqli_real_escape_string($conn, $_POST["tc_number"]);
        
        $tcIssueDate = date('Y-m-d');
        
        $sql = "UPDATE student_transfers 
                SET tc_issued = 1, 
                    tc_issue_date = ?, 
                    tc_number = ? 
                WHERE transfer_id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $tcIssueDate, $tcNumber, $transferId);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success', 'message' => 'TC issued successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error issuing TC: ' . mysqli_error($conn)]);
        }
    }
    
    // Fetch all transfers
    else if ($action == "fetch_transfers") {
        $type = $_POST["type"] ?? '';
        
        $sql = "SELECT t.*, s.fname, s.lname, s.email, s.phone 
                FROM student_transfers t 
                JOIN students s ON t.student_id = s.id";
        
        if ($type) {
            $sql .= " WHERE t.transfer_type = '" . mysqli_real_escape_string($conn, $type) . "'";
        }
        
        $sql .= " ORDER BY t.request_date DESC";
        
        $result = mysqli_query($conn, $sql);
        $transfers = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $transfers[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $transfers]);
    }
    
    // Internal transfer (section change)
    else if ($action == "internal_transfer") {
        $studentId = mysqli_real_escape_string($conn, $_POST["student_id"]);
        $toClass = mysqli_real_escape_string($conn, $_POST["to_class"]);
        $toSection = mysqli_real_escape_string($conn, $_POST["to_section"]);
        $reason = mysqli_real_escape_string($conn, $_POST["reason"] ?? '');
        $requestedBy = mysqli_real_escape_string($conn, $_POST["requested_by"]);
        
        mysqli_begin_transaction($conn);
        
        try {
            // Get current student details
            $getStudent = "SELECT class, section FROM students WHERE id = ?";
            $stmt = mysqli_prepare($conn, $getStudent);
            mysqli_stmt_bind_param($stmt, "s", $studentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $student = mysqli_fetch_assoc($result);
            
            if (!$student) {
                throw new Exception("Student not found");
            }
            
            $transferDate = date('Y-m-d');
            
            // Create transfer record
            $insertTransfer = "INSERT INTO student_transfers (student_id, transfer_type, from_class, from_section, to_class, to_section, transfer_date, request_date, transfer_status, transfer_reason, requested_by, approved_by) 
                              VALUES (?, 'internal', ?, ?, ?, ?, ?, ?, 'approved', ?, ?, ?)";
            
            $stmtTransfer = mysqli_prepare($conn, $insertTransfer);
            mysqli_stmt_bind_param($stmtTransfer, "ssssssssss", 
                $studentId, $student['class'], $student['section'], 
                $toClass, $toSection, $transferDate, $transferDate, 
                $reason, $requestedBy, $requestedBy);
            
            if (!mysqli_stmt_execute($stmtTransfer)) {
                throw new Exception("Error creating transfer record: " . mysqli_error($conn));
            }
            
            // Update student class and section
            $updateStudent = "UPDATE students SET class = ?, section = ? WHERE id = ?";
            $stmtUpdate = mysqli_prepare($conn, $updateStudent);
            mysqli_stmt_bind_param($stmtUpdate, "sss", $toClass, $toSection, $studentId);
            
            if (!mysqli_stmt_execute($stmtUpdate)) {
                throw new Exception("Error updating student: " . mysqli_error($conn));
            }
            
            mysqli_commit($conn);
            echo json_encode(['status' => 'success', 'message' => 'Internal transfer completed successfully!']);
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

mysqli_close($conn);
?>
