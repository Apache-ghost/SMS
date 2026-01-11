<?php include("../assets/noSessionRedirect.php"); ?>
<?php include("./verifyRoleRedirect.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Details</title>
    <link rel="shortcut icon" href="./images/logo.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .assignment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container" style="padding: 20px;">
        <a href="index.php" class="btn btn-outline-primary mb-3">
            <i class='bx bx-arrow-back'></i> Back to Dashboard
        </a>

        <div id="assignmentContent">
            <p class="text-center">Loading...</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const assignmentId = urlParams.get('id');

        if (assignmentId) {
            loadAssignment(assignmentId);
        } else {
            document.getElementById('assignmentContent').innerHTML = '<p class="text-center text-danger">Invalid assignment ID</p>';
        }

        function loadAssignment(id) {
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
                    displayAssignment(data.assignment);
                } else {
                    document.getElementById('assignmentContent').innerHTML = '<p class="text-center text-danger">Assignment not found</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('assignmentContent').innerHTML = '<p class="text-center text-danger">Error loading assignment</p>';
            });
        }

        function displayAssignment(assignment) {
            const dueDate = new Date(assignment.due_date);
            const now = new Date();
            const isOverdue = dueDate < now;

            const html = `
                <div class="assignment-header">
                    <h1>${assignment.title}</h1>
                    <p class="mb-0"><strong>Course:</strong> ${assignment.course_code || 'General'} | <strong>Type:</strong> ${assignment.assignment_type || 'Assignment'}</p>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="info-card">
                            <h4><i class='bx bx-detail'></i> Description</h4>
                            <p>${assignment.description || 'No description provided'}</p>
                        </div>

                        <div class="info-card">
                            <h4><i class='bx bx-list-ul'></i> Instructions</h4>
                            <p>${assignment.instructions || 'No instructions provided'}</p>
                        </div>

                        ${assignment.attachments && assignment.attachments.length > 0 ? `
                        <div class="info-card">
                            <h4><i class='bx bx-paperclip'></i> Reference Files</h4>
                            <ul class="list-group">
                                ${assignment.attachments.map(att => `
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class='bx bx-file'></i> ${att.file_name}</span>
                                        <a href="../assignmentUploads/${att.file_path}" class="btn btn-sm btn-primary" download>
                                            <i class='bx bx-download'></i> Download
                                        </a>
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                        ` : ''}
                    </div>

                    <div class="col-md-4">
                        <div class="info-card">
                            <h5>Assignment Info</h5>
                            <hr>
                            <p><i class='bx bx-calendar'></i> <strong>Assigned:</strong><br>${formatDate(assignment.assigned_date)}</p>
                            <p><i class='bx bx-time'></i> <strong>Due:</strong><br>
                                <span class="${isOverdue ? 'text-danger' : 'text-success'}">${formatDateTime(assignment.due_date)}</span>
                            </p>
                            <p><i class='bx bx-star'></i> <strong>Max Marks:</strong> ${assignment.max_marks || 100}</p>
                            <p><i class='bx bx-check-circle'></i> <strong>Late Submission:</strong> ${assignment.late_submission_allowed == 1 ? 'Allowed' : 'Not Allowed'}</p>
                            <hr>
                            ${!isOverdue ? `
                            <a href="submit_assignment.php?id=${assignment.assignment_id}" class="btn btn-success w-100">
                                <i class='bx bx-upload'></i> Submit Assignment
                            </a>
                            ` : '<p class="text-danger text-center">⚠ This assignment is overdue</p>'}
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('assignmentContent').innerHTML = html;
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        }

        function formatDateTime(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
</body>
</html>
