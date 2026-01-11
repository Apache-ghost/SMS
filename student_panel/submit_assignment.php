<?php 
include("../assets/noSessionRedirect.php"); 
include("./verifyRoleRedirect.php");
include("../assets/config.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Assignment</title>
    <link rel="shortcut icon" href="./images/logo.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .submit-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .assignment-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="submit-container">
            <a href="index.php" class="btn btn-outline-primary mb-3">
                <i class='bx bx-arrow-back'></i> Back to Dashboard
            </a>

            <div id="assignmentInfo" class="assignment-info">
                <h3>Loading...</h3>
            </div>

            <form id="submitAssignmentForm" enctype="multipart/form-data">
                <input type="hidden" name="assignment_id" id="assignmentId">
                
                <div class="mb-4">
                    <label class="form-label">
                        <i class='bx bx-text'></i> Your Submission Text
                    </label>
                    <textarea class="form-control" name="submission_text" rows="8" 
                              placeholder="Type your answer or explanation here..." required></textarea>
                    <small class="text-muted">Provide detailed answers or explanations for your assignment</small>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class='bx bx-upload'></i> Attach File (Optional)
                    </label>
                    <input type="file" class="form-control" name="attachment" id="fileInput" 
                           accept=".pdf,.doc,.docx,.txt,.zip">
                    <small class="text-muted">Supported formats: PDF, DOC, DOCX, TXT, ZIP (Max 10MB)</small>
                </div>

                <div class="alert alert-info">
                    <i class='bx bx-info-circle'></i> 
                    <strong>Note:</strong> Make sure to review your submission before clicking submit. 
                    Some assignments may not allow resubmission.
                </div>

                <button type="submit" class="btn btn-success btn-lg w-100" id="submitBtn">
                    <i class='bx bx-send'></i> Submit Assignment
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const assignmentId = urlParams.get('id');

        if (assignmentId) {
            document.getElementById('assignmentId').value = assignmentId;
            loadAssignmentInfo(assignmentId);
        } else {
            window.location.href = 'index.php';
        }

        function loadAssignmentInfo(id) {
            const formData = new FormData();
            formData.append('action', 'get_assignment_details');
            formData.append('assignment_id', id);

            fetch('../assets/manageAssignments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.assignment) {
                    const assignment = data.assignment;
                    document.getElementById('assignmentInfo').innerHTML = `
                        <h4>${assignment.title}</h4>
                        <p class="mb-0">
                            <i class='bx bx-calendar'></i> Due: ${formatDateTime(assignment.due_date)} | 
                            <i class='bx bx-star'></i> Max Marks: ${assignment.max_marks}
                        </p>
                    `;
                }
            })
            .catch(error => console.error('Error:', error));
        }

        document.getElementById('submitAssignmentForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'submit_assignment');

            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Submitting...';

            fetch('../assets/manageAssignments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('✓ Assignment submitted successfully!');
                    window.location.href = 'index.php';
                } else {
                    alert('Error: ' + (data.message || 'Failed to submit assignment'));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bx bx-send"></i> Submit Assignment';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to submit assignment. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-send"></i> Submit Assignment';
            });
        });

        function formatDateTime(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString('en-US', { 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
</body>
</html>
