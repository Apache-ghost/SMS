<?php include("../assets/noSessionRedirect.php"); ?>

<?php include("./verifyRoleRedirect.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="shortcut icon" href="./images/logo.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/oranbyte-google-translator.css">

    <style type="text/css">
        /* Floating Background Balls */
        .floating-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
        }

        .floating-ball {
            position: absolute;
            border-radius: 50%;
            opacity: 0.15;
            animation: float 20s infinite ease-in-out;
        }

        .ball-1 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: -150px;
            right: -150px;
            animation-delay: 0s;
        }

        .ball-2 {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            bottom: -100px;
            left: -100px;
            animation-delay: 3s;
        }

        .ball-3 {
            width: 250px;
            height: 250px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            top: 50%;
            right: -125px;
            animation-delay: 6s;
        }

        .ball-4 {
            width: 180px;
            height: 180px;
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            bottom: 20%;
            left: 10%;
            animation-delay: 9s;
        }

        .ball-5 {
            width: 220px;
            height: 220px;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            top: 30%;
            left: 5%;
            animation-delay: 12s;
        }

        .ball-6 {
            width: 160px;
            height: 160px;
            background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
            bottom: 40%;
            right: 15%;
            animation-delay: 15s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0) rotate(0deg);
            }
            25% {
                transform: translateY(-30px) translateX(20px) rotate(90deg);
            }
            50% {
                transform: translateY(-60px) translateX(-20px) rotate(180deg);
            }
            75% {
                transform: translateY(-30px) translateX(-40px) rotate(270deg);
            }
        }

        /* Welcome Banner with Animation */
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '🎓';
            position: absolute;
            font-size: 200px;
            opacity: 0.1;
            right: -50px;
            top: -50px;
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .welcome-text {
            position: relative;
            z-index: 1;
        }

        .welcome-text h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            animation: slideInLeft 0.8s ease;
        }

        .welcome-text p {
            font-size: 1.2rem;
            opacity: 0.9;
            animation: slideInLeft 1s ease;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Quick Stats Cards */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .stat-card.purple .stat-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card.green .stat-icon {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-card.orange .stat-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-card.blue .stat-icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin: 10px 0 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Motivational Quote Card */
        .quote-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            color: white;
            text-align: center;
            box-shadow: 0 10px 40px rgba(240, 147, 251, 0.4);
            animation: fadeIn 1.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .quote-card .quote-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .quote-card .quote-text {
            font-size: 1.3rem;
            font-style: italic;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .quote-card .quote-author {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Progress Rings */
        .progress-ring-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin: 30px 0;
        }

        .progress-ring-item {
            text-align: center;
        }

        .progress-ring {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#667eea 0deg, #667eea var(--progress), #e5e7eb var(--progress), #e5e7eb 360deg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            position: relative;
            animation: rotateIn 1s ease;
        }

        @keyframes rotateIn {
            from {
                transform: rotate(-180deg);
                opacity: 0;
            }
            to {
                transform: rotate(0deg);
                opacity: 1;
            }
        }

        .progress-ring::before {
            content: '';
            position: absolute;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: white;
        }

        .progress-value {
            position: relative;
            z-index: 1;
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
        }

        /* Enhanced Cards */
        .enhanced-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .enhanced-card:hover {
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            transform: translateY(-5px);
        }

        .enhanced-card h2 {
            color: #667eea;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Confetti Effect */
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #667eea;
            position: absolute;
            animation: confetti-fall 3s linear forwards;
            pointer-events: none;
        }

        @keyframes confetti-fall {
            to {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        .container main .subjects .eg #piechart {
            width: 600px;
            height: 350px;
            padding-right: 0%;
            position: relative;
            border-radius: 20px;
        }

        .container main .subjects .eg {
            border-radius: 20px;
        }

        @media screen and (max-width: 700px) {
            .container main .subjects .eg #piechart {
                width: 250px;
                height: 200px;
                padding-left: 0%;
                padding-right: 0%;

            }

            .container main .subjects {
                margin-left: 4%;
            }

            .leaves {
                width: 106%;
                /*margin-left: 5%;*/
                font-size: 10px;
                padding-right: 0;
            }

        }

        #myInput {
            background-image: url('search.svg');
            /* Add a search icon to input */
            background-position: 5px 2px;
            /* Position the search icon */
            background-repeat: no-repeat;
            /* Do not repeat the icon image */
            width: 80%;
            /* Full-width */
            font-size: 16px;
            /* Increase font-size */
            padding: 12px 20px 12px 40px;
            /* Add some padding */
            border: 1px solid #ddd;
            /* Add a grey border */
            margin-bottom: 12px;
            /* Add some space below the input */
            border-radius: 40px;
            position: relative;
        }

        #myTable {
            width: 80%;
            /* Full-width */
            border: 1px solid #ddd;
            /* Add a grey border */
            font-size: 15px;
            /* Increase font-size */
            border-radius: 40px;
            position: relative;
        }

        #myTable th {
            background-color: #A9A9A9;
            color: white;
        }

        #myTable th,
        #myTable td {
            text-align: center;
            /* Left-align text */
            padding: 12px;
            /* Add padding */
            border-radius: 16px;
        }


        #myTable tr {
            /* Add a bottom border to all table rows */
            border-bottom: 1px solid #ddd;
            border-radius: 40px;
            text-align: center;
        }

        #myTable tr.header,
        #myTable tr:hover {
            /* Add a grey background color to the table header and on hover */
            background-color: #f1f1f1;
        }

        @media only screen and (max-width: 768px) {
            #myTable {
                width: 95%;
                margin: 0%;
                font-size: 12.5px;
            }

            #myInput {
                width: 95%;
                margin: 0%;
            }
        }

        .link-btn {
            display: block;
            border: 1px solid rgb(214 183 255);
            background-color: rgb(212 196 255 / 77%);
            color: #000000;
            padding: 8px 10px;
            border-radius: 5px;
            max-width: 100px;
            text-align: center;
        }

        /* Quick Actions Grid */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-card {
            padding: 25px;
            border-radius: 15px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            color: white;
        }

        .action-icon {
            font-size: 2.5rem;
            opacity: 0.9;
        }

        .action-text h4 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .action-text p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-header h3 {
            margin: 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
        }

        .view-all-link {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .view-all-link:hover {
            color: #764ba2;
        }

        /* Progress Section */
        .progress-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .progress-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            font-weight: 600;
            color: #555;
        }

        .progress-bar-container {
            width: 100%;
            height: 12px;
            background: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            transition: width 1s ease;
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            to {
                left: 100%;
            }
        }

        /* Recent Grades */
        .grade-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .grade-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .grade-info h5 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 1rem;
        }

        .grade-info p {
            margin: 0;
            color: #777;
            font-size: 0.85rem;
        }

        .grade-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1rem;
            color: white;
        }

        .grade-A { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .grade-B { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .grade-C { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .grade-D { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .grade-F { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); }

        .loading-text {
            text-align: center;
            color: #999;
            padding: 20px;
        }

        @media screen and (max-width: 768px) {
            .quick-actions-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Achievement Badges */
        .achievements-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .achievements-section h3 {
            margin: 0 0 20px 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 15px;
        }

        .badge-item {
            text-align: center;
            padding: 20px 10px;
            border-radius: 12px;
            background: #f5f5f5;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            opacity: 0.4;
            filter: grayscale(100%);
        }

        .badge-item.earned {
            opacity: 1;
            filter: grayscale(0%);
            background: linear-gradient(135deg, #fff7ed 0%, #fef3c7 100%);
            animation: badge-glow 2s infinite;
        }

        @keyframes badge-glow {
            0%, 100% {
                box-shadow: 0 0 10px rgba(251, 191, 36, 0.3);
            }
            50% {
                box-shadow: 0 0 20px rgba(251, 191, 36, 0.6);
            }
        }

        .badge-item:hover {
            transform: scale(1.1) translateY(-5px);
        }

        .badge-icon {
            font-size: 2.5rem;
            margin-bottom: 8px;
        }

        .badge-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #555;
        }

        /* Study Timer */
        .timer-display {
            text-align: center;
            padding: 20px;
        }

        .timer-circle {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            position: relative;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .timer-text {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .timer-controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .timer-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .start-btn {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .pause-btn {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .reset-btn {
            background: #e5e7eb;
            color: #555;
        }

        .timer-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .study-stats {
            display: flex;
            justify-content: space-around;
            gap: 20px;
        }

        .stat-mini {
            text-align: center;
        }

        .stat-mini span {
            display: block;
            color: #777;
            font-size: 0.85rem;
            margin-bottom: 5px;
        }

        .stat-mini strong {
            display: block;
            color: #667eea;
            font-size: 1.2rem;
        }

        /* Upcoming Deadlines */
        .deadline-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #f093fb;
            transition: all 0.3s ease;
        }

        .deadline-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .deadline-item.urgent {
            border-left-color: #ff0000;
            background: #fff5f5;
        }

        .deadline-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .deadline-time {
            color: #777;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .deadline-time.urgent {
            color: #ff0000;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <!-- Floating Background -->
    <div class="floating-background">
        <div class="floating-ball ball-1"></div>
        <div class="floating-ball ball-2"></div>
        <div class="floating-ball ball-3"></div>
        <div class="floating-ball ball-4"></div>
        <div class="floating-ball ball-5"></div>
        <div class="floating-ball ball-6"></div>
    </div>

    <header>
        <div class="logo" title="University Management System">
            <img src="./images/logo.png" alt="">
            <h2>E<span class="danger">R</span>P</h2>
        </div>
        <div class="navbar">
            <a href="index.php">
                <span class="material-icons-sharp">home</span>
                <h3>Home</h3>
            </a>
            <a href="timetable.php" onclick="timeTableAll()">
                <span class="material-icons-sharp">today</span>
                <h3>Time Table</h3>
            </a>
            <a href="exams.php">
                <span class="material-icons-sharp">grid_view</span>
                <h3>Examination</h3>
            </a>
            <a href="workspace.php">
                <span class="material-icons-sharp">description</span>
                <h3>Workspace</h3>
            </a>
            <a href="password.php">
                <span class="material-icons-sharp">password</span>
                <h3>Change Password</h3>
            </a>
            <a href="logout.php">
                <span class="material-icons-sharp" onclick="">logout</span>
                <h3>Logout</h3>
            </a>
        </div>
        <div id="profile-btn">
            <span class="material-icons-sharp">person</span>
        </div>
        <div class="theme-toggler">
            <span class="material-icons-sharp active">light_mode</span>
            <span class="material-icons-sharp">dark_mode</span>
        </div>

    </header>
    <div class="container">
        <aside>
            <div class="profile">
                <div class="top">
                    <?php
                    // Ensure session is started and user ID is available
                    if(!isset($_SESSION['uid'])) {
                        header("Location: ../login.php");
                        exit();
                    }
                    
                    $id = $_SESSION['uid'];
                    // Use prepared statement for security
                    $query_sql = "SELECT * FROM students WHERE id=?";
                    $stmt = mysqli_prepare($conn, $query_sql);
                    mysqli_stmt_bind_param($stmt, "s", $id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $row = mysqli_fetch_assoc($result);
                    
                    // Default image if none exists
                    $studentImage = isset($row['image']) && !empty($row['image']) ? $row['image'] : 'default-avatar.png';
                    echo "<div class='profile-photo'>
                        <img src='../studentUploads/" . htmlspecialchars($studentImage) . "' alt='Student Profile'>
                    </div>";
                    mysqli_stmt_close($stmt);
                    ?>

                    <div class="info">
                        <?php
                        // Use the same ID from session with prepared statement
                        $id = $_SESSION['uid'];
                        $query = "SELECT * FROM students WHERE id=?";
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "s", $id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        if ($row = mysqli_fetch_assoc($result)) {
                            echo "<p>Hey, <b>" . htmlspecialchars($row["fname"]) . "</b> </p>
                        <small class='text-muted'><b>ID&nbsp;:&nbsp;</b>" . htmlspecialchars($row["id"]) . "</small>";
                        }
                        mysqli_stmt_close($stmt);
                        ?>

                    </div>
                </div>
                <br>
                <div id="oranbyte-google-translator" 
                        data-default-lang="en"
                        data-lang-root-style="code-flag"
                        data-lang-list-style="code-flag"
                        ></div>
                <div class="about">
                    <?php
                    $id = $_SESSION['uid'];
                    $query = "SELECT * FROM students WHERE id=?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "s", $id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if ($row = mysqli_fetch_assoc($result)) {
                        echo "<p><h5>Class : " . htmlspecialchars($row["class"]) . "</h5></p>
                    <p>Section " . htmlspecialchars($row["section"]) . "</p>
                    <h5>DOB</h5>
                    <p>" . $row["dob"] . "</p>
                    <h5>Contact</h5>
                    <p>" . $row["phone"] . "</p>
                    <h5>Email</h5>
                    <p>" . $row["email"] . "</p>
                    <h5>Address</h5>
                    <p>" . $row["address"] . "</p>";
                    }
                    mysqli_stmt_close($stmt);

                    ?><br>

                    <div style="display: inline;">
                  
                    <b><a href="calendar.php" class="link-btn">📅 Calendar</a></b><br>
                    <b><a href="buspanel.php" class="link-btn">Bus Panel</a></b><br>
                    <b><a href="fee-payment.php" class="link-btn">Pay-Fee</a></b>
                    
                    </div>
                </div>
            </div>
        </aside>

        <main>
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="welcome-text">
                    <h1>👋 Welcome Back, <?php 
                        $id = $_SESSION['uid'];
                        $query = "SELECT fname FROM students WHERE id=?";
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "s", $id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        if ($row = mysqli_fetch_assoc($result)) {
                            echo htmlspecialchars($row["fname"]);
                        }
                        mysqli_stmt_close($stmt);
                    ?>!</h1>
                    <p>✨ Ready to achieve great things today? Let's make it awesome!</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-card orange">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value" id="totalExams">0</div>
                    <div class="stat-label">Exams Taken</div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-value" id="averageGrade">-</div>
                    <div class="stat-label">Avg Grade</div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-value" id="pendingAssignments">0</div>
                    <div class="stat-label">Pending Tasks</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value" id="completedAssignments">0</div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions-grid">
                <a href="exams.php" class="action-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="action-icon">📊</div>
                    <div class="action-text">
                        <h4>View Results</h4>
                        <p>Check your exam scores</p>
                    </div>
                </a>
                <a href="submit_assignment.php" class="action-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="action-icon">📤</div>
                    <div class="action-text">
                        <h4>Submit Assignment</h4>
                        <p>Upload your work</p>
                    </div>
                </a>
                <a href="timetable.php" class="action-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="action-icon">📅</div>
                    <div class="action-text">
                        <h4>My Schedule</h4>
                        <p>View timetable</p>
                    </div>
                </a>
                <a href="calendar.php" class="action-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <div class="action-icon">🗓️</div>
                    <div class="action-text">
                        <h4>Calendar</h4>
                        <p>Upcoming events</p>
                    </div>
                </a>
            </div>

            <!-- Achievement Badges -->
            <div class="achievements-section">
                <h3><i class='bx bx-trophy'></i> Achievements</h3>
                <div class="badges-grid" id="achievementBadges">
                    <div class="badge-item earned" data-achievement="first-login" title="Welcome to the platform!">
                        <div class="badge-icon">🎓</div>
                        <div class="badge-name">Scholar</div>
                    </div>
                    <div class="badge-item" data-achievement="perfect-attendance" title="100% attendance for a month">
                        <div class="badge-icon">✨</div>
                        <div class="badge-name">Perfect</div>
                    </div>
                    <div class="badge-item" data-achievement="top-performer" title="Grade A+ in all exams">
                        <div class="badge-icon">🏆</div>
                        <div class="badge-name">Top Star</div>
                    </div>
                    <div class="badge-item" data-achievement="task-master" title="Complete 10 assignments">
                        <div class="badge-icon">📝</div>
                        <div class="badge-name">Task Master</div>
                    </div>
                    <div class="badge-item" data-achievement="early-bird" title="Submit 5 assignments early">
                        <div class="badge-icon">🌅</div>
                        <div class="badge-name">Early Bird</div>
                    </div>
                </div>
            </div>

            <!-- Study Timer & Upcoming Deadlines -->
            <div class="dashboard-grid" style="margin-top: 20px;">
                <div class="dashboard-card timer-card">
                    <div class="card-header">
                        <h3><i class='bx bx-timer'></i> Study Timer</h3>
                    </div>
                    <div class="timer-display">
                        <div class="timer-circle">
                            <div class="timer-text" id="timerDisplay">00:00</div>
                        </div>
                        <div class="timer-controls">
                            <button class="timer-btn start-btn" id="startTimerBtn" onclick="startTimer()">
                                <i class='bx bx-play'></i> Start
                            </button>
                            <button class="timer-btn pause-btn" id="pauseTimerBtn" onclick="pauseTimer()" style="display:none;">
                                <i class='bx bx-pause'></i> Pause
                            </button>
                            <button class="timer-btn reset-btn" onclick="resetTimer()">
                                <i class='bx bx-reset'></i> Reset
                            </button>
                        </div>
                        <div class="study-stats">
                            <div class="stat-mini">
                                <span>Today</span>
                                <strong id="todayStudyTime">0h 0m</strong>
                            </div>
                            <div class="stat-mini">
                                <span>Total</span>
                                <strong id="totalStudyTime">0h 0m</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class='bx bx-alarm'></i> Upcoming Deadlines</h3>
                    </div>
                    <div id="upcomingDeadlines">
                        <p class="loading-text">Loading deadlines...</p>
                    </div>
                </div>
            </div>

            <!-- Recent Grades & Performance -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class='bx bx-trophy'></i> Recent Grades</h3>
                        <a href="exams.php" class="view-all-link">View All →</a>
                    </div>
                    <div id="recentGradesContainer">
                        <p class="loading-text">Loading recent grades...</p>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class='bx bx-line-chart'></i> Performance Tracker</h3>
                    </div>
                    <div class="progress-section">
                        <div class="progress-item">
                            <div class="progress-label">
                                <span>Overall Progress</span>
                                <span id="overallProgress">0%</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" id="overallProgressBar" style="width: 0%;"></div>
                            </div>
                        </div>
                        <div class="progress-item">
                            <div class="progress-label">
                                <span>Assignments</span>
                                <span id="assignmentProgress">0%</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" id="assignmentProgressBar" style="width: 0%; background: #f093fb;"></div>
                            </div>
                        </div>
                        <div class="progress-item">
                            <div class="progress-label">
                                <span>Exams</span>
                                <span id="examProgress">0%</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" id="examProgressBar" style="width: 0%; background: #4facfe;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motivational Quote -->
            <div class="quote-card">
                <div class="quote-icon">💡</div>
                <div class="quote-text" id="dailyQuote">
                    "Education is the most powerful weapon which you can use to change the world."
                </div>
                <div class="quote-author">— Nelson Mandela</div>
            </div>

            <h1>Dashboard</h1>
            
            <!-- Daily Attendance Card -->
            <div class="attendance-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 15px; margin-bottom: 20px; color: white; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div>
                        <h2 style="margin: 0; font-size: 24px;">📅 Daily Attendance</h2>
                        <p style="margin: 10px 0 0 0; opacity: 0.9;" id="attendanceDate"><?php echo date('l, F j, Y'); ?></p>
                    </div>
                    <div id="attendanceButtonContainer">
                        <button id="markPresentBtn" class="btn btn-light btn-lg" style="padding: 15px 30px; font-size: 18px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                            <i class='bx bx-check-circle'></i> Mark Present
                        </button>
                    </div>
                </div>
                <div id="attendanceStatus" style="margin-top: 15px; padding: 15px; background: rgba(255,255,255,0.2); border-radius: 10px; display: none;">
                    <p style="margin: 0; font-size: 16px;"></p>
                </div>
            </div>

            <!-- Attendance Statistics -->
            <h2 style="margin-top: 30px;">Attendance Statistics</h2>
            <div class="subjects">
                <div class="eg">
                    <div id="piechart"></div>

                </div>
            </div>


            <div class="leaves " style="margin-top: 20px;">
                <h2>Syllabus</h2>
                <?php
                $id = $_SESSION['uid'];
                $query_sql = "SELECT class FROM students WHERE id=?";
                $stmt = mysqli_prepare($conn, $query_sql);
                mysqli_stmt_bind_param($stmt, "s", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                $class = $row['class'];
                mysqli_stmt_close($stmt);

                $sql2 = "SELECT * FROM syllabus WHERE class=?";
                $stmt2 = mysqli_prepare($conn, $sql2);
                mysqli_stmt_bind_param($stmt2, "s", $class);
                mysqli_stmt_execute($stmt2);
                $result2 = mysqli_stmt_get_result($stmt2);
                if (mysqli_num_rows($result2) > 0) {
                    while ($row2 = mysqli_fetch_assoc($result2)) {
                        echo "<div class='teacher'>
                    <div class='profile-photo'>
                    <a href='../syllabusUploads/" . htmlspecialchars($row2['file']) . "'>
                    <img src='./images/profile-2.png' alt=''></div>
                    <div class='info'>
                        <h3>" . htmlspecialchars($row2['subject']) . "</h3>
                        <small class='text-muted'>Download or View</small>
                        </a>
                    </div>
                </div>";
                    }
                    mysqli_stmt_close($stmt2);
                } else {
                    echo '<p style="padding-left: 20px;margin-top: 10px;">Syllabus not uploaded yet!</p>';
                }
                ?>


            </div>
            
            <!-- Announcements Section -->
            <div class="leaves" style="margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2>📢 Announcements</h2>
                    <span id="unreadCount" style="background: #ff0000; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px; display: none;"></span>
                </div>
                <div id="announcementsList" style="padding: 10px;">
                    <p class="text-center">Loading announcements...</p>
                </div>
            </div>
            
            <!-- Assignments Section -->
            <div class="leaves" style="margin-top: 20px;">
                <h2>📝 My Assignments</h2>
                <div id="assignmentsList" style="padding: 10px;">
                    <p class="text-center">Loading assignments...</p>
                </div>
            </div>
            
            <!-- Curriculum Section -->
            <div class="leaves" style="margin-top: 20px;">
                <h2>My Curriculum</h2>
                <?php
                $id = $_SESSION['uid'];
                $query_sql = "SELECT class FROM students WHERE id=?";
                $stmt = mysqli_prepare($conn, $query_sql);
                mysqli_stmt_bind_param($stmt, "s", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                $class = $row['class'];
                mysqli_stmt_close($stmt);

                // Fetch curriculum for student's class
                $currSql = "SELECT cm.*, 
                            (SELECT COUNT(*) FROM curriculum_subjects cs WHERE cs.curriculum_id = cm.curriculum_id) as subject_count
                            FROM curriculum_master cm 
                            WHERE cm.class=? AND cm.status='active' 
                            ORDER BY cm.created_at DESC LIMIT 1";
                $currStmt = mysqli_prepare($conn, $currSql);
                $classNum = intval($class);
                mysqli_stmt_bind_param($currStmt, "i", $classNum);
                mysqli_stmt_execute($currStmt);
                $currResult = mysqli_stmt_get_result($currStmt);
                
                if ($currRow = mysqli_fetch_assoc($currResult)) {
                    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 15px;'>";
                    echo "<div style='display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;'>";
                    echo "<div style='flex: 1;'>";
                    echo "<h4 style='color: #667eea; margin: 0 0 10px 0;'>" . htmlspecialchars($currRow['curriculum_name']) . "</h4>";
                    echo "<p style='margin: 5px 0;'><strong>Academic Year:</strong> " . htmlspecialchars($currRow['academic_year']) . "</p>";
                    if (!empty($currRow['department_code'])) {
                        echo "<p style='margin: 5px 0;'><strong>Department:</strong> " . htmlspecialchars($currRow['department_code']) . "</p>";
                    }
                    echo "<p style='margin: 5px 0;'><strong>Total Subjects:</strong> " . $currRow['subject_count'] . "</p>";
                    echo "</div>";
                    
                    // Show download button if curriculum file exists
                    if (!empty($currRow['file_path'])) {
                        echo "<div>";
                        echo "<a href='../curriculumUploads/" . htmlspecialchars($currRow['file_path']) . "' class='link-btn' download style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-flex; align-items: center; gap: 5px;'>";
                        echo "<i class='bx bx-download' style='font-size: 20px;'></i> Download Curriculum";
                        echo "</a>";
                        echo "</div>";
                    }
                    echo "</div>";
                    echo "</div>";
                    
                    // Fetch and display subjects with subject names from subjects table
                    $subSql = "SELECT cs.*, s.subject_name 
                              FROM curriculum_subjects cs 
                              LEFT JOIN subjects s ON cs.course_code = s.subject_id 
                              WHERE cs.curriculum_id=? 
                              ORDER BY s.subject_name";
                    $subStmt = mysqli_prepare($conn, $subSql);
                    mysqli_stmt_bind_param($subStmt, "i", $currRow['curriculum_id']);
                    mysqli_stmt_execute($subStmt);
                    $subResult = mysqli_stmt_get_result($subStmt);
                    
                    echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;'>";
                    while ($subRow = mysqli_fetch_assoc($subResult)) {
                        $totalHours = intval($subRow['theory_hours']) + intval($subRow['practical_hours']);
                        $subjectName = $subRow['subject_name'] ?? 'Subject ' . $subRow['course_code'];
                        echo "<div style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;'>";
                        echo "<h5 style='margin: 0 0 5px 0; color: #333;'>" . htmlspecialchars($subjectName) . "</h5>";
                        echo "<p style='margin: 0; color: #666; font-size: 13px;'>Code: " . htmlspecialchars($subRow['course_code']) . "</p>";
                        echo "<p style='margin: 5px 0 0 0; color: #667eea; font-size: 12px;'>" . $totalHours . " hours/week</p>";
                        if ($subRow['is_mandatory']) {
                            echo "<span style='background: #10b981; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px;'>Mandatory</span>";
                        }
                        echo "</div>";
                    }
                    echo "</div>";
                    mysqli_stmt_close($subStmt);
                } else {
                    echo '<p style="padding-left: 20px;margin-top: 10px;">Curriculum not available yet!</p>';
                }
                mysqli_stmt_close($currStmt);
                ?>
            </div>
            
            <div class="timetable" id="timetable">
                <h2>Monthly Attendance</h2>
                <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search for Date...">

                <table id="myTable">
                    <tr class="header">
                        <th style="width:60%;">Date</th>
                        <th style="width:40%;">Attendence</th>
                    </tr>
                    <tbody id="attendence_table">

                    </tbody>
                </table>
                <br><br>
            </div>
        </main>

        <div class="right">
            <div class="announcements">
                <h2>Notice</h2>
                <div class="updates">
                    <div class="message">
                        <?php
                        $id = $_SESSION['uid'];
                        $query_sql2 = "SELECT class FROM students WHERE id=?";
                        $stmt = mysqli_prepare($conn, $query_sql2);
                        mysqli_stmt_bind_param($stmt, "s", $id);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $row = mysqli_fetch_assoc($result);
                        $class = $row['class'];
                        mysqli_stmt_close($stmt);

                        $sql_query = "SELECT * FROM notice WHERE (role = 'student' AND class=?) OR (role = 'all' OR role='') ORDER BY s_no DESC LIMIT 3";
                        $stmt2 = mysqli_prepare($conn, $sql_query);
                        mysqli_stmt_bind_param($stmt2, "s", $class);
                        mysqli_stmt_execute($stmt2);
                        $result = mysqli_stmt_get_result($stmt2);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<p> <b>" . htmlspecialchars($row['title']) . "</b> <br>" . htmlspecialchars($row['body']) . "<br></p>";
                                if ($row['file'] != null) {
                                    echo "<a href='../noticeUploads/" . htmlspecialchars($row['file']) . "'><img src='file.svg' height='30px' width='30px'><p style='color:red;'>View Notice</p></a>";
                                }
                                echo "<small class='text-muted'><b>" . htmlspecialchars($row['timestamp']) . "</b></small><hr><br>";
                            }
                            mysqli_stmt_close($stmt2);
                        }
                        ?>



                    </div>

                </div>
            </div>

            <div class="leaves">
                <h2>Feedbacks</h2>
                <?php
                $id = $_SESSION['uid'];

                $sql2 = "SELECT * FROM `feedback` WHERE `receiver_id`='$id' LIMIT 5";
                $result2 = mysqli_query($conn, $sql2);
                if ($result2->num_rows > 0) {
                    while ($row2 = $result2->fetch_assoc()) {
                        $timestamp = $row2['timestamp'];
                        $formattedDate = date('d M, Y', strtotime($timestamp));

                        $senderId = $row2['sender_id'];
                        $tableName = ($senderId >= 1000) ? 'admins' : 'teachers';
                        $sql = "SELECT `fname`, `lname` FROM `$tableName` WHERE id = '$senderId' LIMIT 1";

                        $result = mysqli_query($conn, $sql);
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $sender = ucfirst(strtolower($row['fname'])) . " " . strtolower($row['lname']);
                        } else {
                            $sender = "REMOVED";
                        }

                        echo "<div class='teacher'>
                            <div class='info' style='width: 100%;'>
                                <p class='text-muted para-text'>
                                <i class='bx bxs-chat' ></i>
                                " . $row2['msg'] . "</p>
                                <div class='flexbox' style='margin-top: 8px;'>
                                    <small>" . $formattedDate . "</small>
                                    <small style='margin-left: auto;'>" .  $sender . "</small>
                                </div>
                            </div>
                        </div>";
                    }
                } else {
                    echo "<div class='teacher'>
                    <div class='info' style='width: 100%;'>
                        <p class='text-muted para-text'>
                        <i class='bx bxs-chat' ></i>
                       No Feedbacks</p>
                       
                    </div>
                </div>";
                }
                ?>


            </div>

        </div>
    </div>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        let presentPer = 30;
        let absentPer = 70;


        document.addEventListener("DOMContentLoaded", function() {
            fetch("fetchAttendencePercentage.php", {
                    method: "POST",
                })
                .then(response => response.json())
                .then(data => {


                    if (data['status'] === "success") {
                        presentPer = parseFloat(data['present']);
                        absentPer = parseFloat(data['absent']);



                        google.charts.load("current", {
                            packages: ["corechart"]
                        });
                        google.charts.setOnLoadCallback(drawChart);

                    } else {
                        alert("Something went wrong!");
                    }
                })
                .catch(error => {
                    console.error("error" + error)
                })
        });


        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Attandence', 'percentage'],
                ['preset', presentPer],
                ['Absent', absentPer],
            ]);

            var options = {
                legend: 'none',
                pieSliceText: 'label',
                title: 'Student Attendence All time',
                pieStartAngle: 100,
            };

            var chart = new google.visualization.PieChart(document.getElementById('piechart'));
            chart.draw(data, options);
        }
    </script>

    <!-- Student Assignments Script -->
    <script>
        // Load student assignments
        function loadStudentAssignments() {
            const formData = new FormData();
            formData.append('action', 'get_student_assignments');

            fetch('../assets/manageAssignments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('assignmentsList');
                
                if (data.status === 'success' && data.assignments && data.assignments.length > 0) {
                    let html = '';
                    
                    data.assignments.forEach(assignment => {
                        const dueDate = new Date(assignment.due_date);
                        const now = new Date();
                        const isOverdue = dueDate < now && !assignment.submission_id;
                        const hasSubmitted = assignment.submission_id ? true : false;
                        
                        let statusBadge = '';
                        let statusClass = '';
                        
                        if (hasSubmitted) {
                            if (assignment.marks_obtained !== null) {
                                statusBadge = `<span class="badge bg-success">✓ Graded: ${assignment.marks_obtained}/${assignment.max_marks}</span>`;
                                statusClass = 'border-success';
                            } else {
                                statusBadge = '<span class="badge bg-info">✓ Submitted</span>';
                                statusClass = 'border-info';
                            }
                        } else if (isOverdue) {
                            statusBadge = '<span class="badge bg-danger">⚠ Overdue</span>';
                            statusClass = 'border-danger';
                        } else {
                            statusBadge = '<span class="badge bg-warning">⏰ Pending</span>';
                            statusClass = 'border-warning';
                        }
                        
                        html += `
                            <div class="card mb-3 ${statusClass}" style="border-left: 4px solid;">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div>
                                            <h5 class="card-title mb-1">${assignment.title}</h5>
                                            <p class="text-muted mb-2">
                                                <small>${assignment.assignment_type || 'Assignment'} | ${assignment.course_code || 'General'}</small>
                                            </p>
                                            <p class="card-text mb-2">${assignment.description || ''}</p>
                                            <p class="mb-1">
                                                <i class='bx bx-calendar'></i> <strong>Due:</strong> 
                                                <span class="${isOverdue ? 'text-danger' : ''}">${formatDateTime(assignment.due_date)}</span>
                                            </p>
                                            <p class="mb-0">
                                                <i class='bx bx-star'></i> <strong>Max Marks:</strong> ${assignment.max_marks || 100}
                                            </p>
                                        </div>
                                        <div>
                                            ${statusBadge}
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <a href="assignment_details.php?id=${assignment.assignment_id}" class="btn btn-sm btn-primary">
                                            <i class='bx bx-show'></i> View Details
                                        </a>
                                        ${!hasSubmitted && !isOverdue ? `
                                        <a href="submit_assignment.php?id=${assignment.assignment_id}" class="btn btn-sm btn-success">
                                            <i class='bx bx-upload'></i> Submit Now
                                        </a>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<p class="text-center text-muted" style="padding: 20px;">📚 No assignments available at the moment.</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('assignmentsList').innerHTML = '<p class="text-center text-danger">Error loading assignments</p>';
            });
        }

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

        // Load assignments when page loads
        if (document.getElementById('assignmentsList')) {
            loadStudentAssignments();
        }
        
        // Load announcements when page loads
        if (document.getElementById('announcementsList')) {
            loadStudentAnnouncements();
        }
        
        // Function to load announcements
        function loadStudentAnnouncements() {
            fetch('../assets/fetchStudentAnnouncements.php')
            .then(response => response.text())
            .then(text => {
                console.log('Announcements response:', text);
                try {
                    const data = JSON.parse(text);
                    const container = document.getElementById('announcementsList');
                    
                    if (data.status === 'success' && data.data.length > 0) {
                        const announcements = data.data;
                        
                        // Count unread announcements
                        const unreadCount = announcements.filter(a => !a.is_read).length;
                        const unreadBadge = document.getElementById('unreadCount');
                        if (unreadCount > 0) {
                            unreadBadge.textContent = `${unreadCount} New`;
                            unreadBadge.style.display = 'inline-block';
                        }
                    
                    let html = '';
                    announcements.forEach(announcement => {
                        const isPinned = announcement.is_pinned == 1;
                        const isRead = announcement.is_read == 1;
                        const priority = announcement.priority;
                        
                        let priorityColor = '#667eea';
                        if (priority === 'urgent') priorityColor = '#ff0000';
                        else if (priority === 'high') priorityColor = '#ff9800';
                        else if (priority === 'normal') priorityColor = '#2196f3';
                        
                        let typeIcon = '📢';
                        if (announcement.announcement_type === 'academic') typeIcon = '📚';
                        else if (announcement.announcement_type === 'event') typeIcon = '🎉';
                        else if (announcement.announcement_type === 'exam') typeIcon = '📝';
                        else if (announcement.announcement_type === 'holiday') typeIcon = '🏖️';
                        else if (announcement.announcement_type === 'emergency') typeIcon = '🚨';
                        else if (announcement.announcement_type === 'sports') typeIcon = '⚽';
                        
                        html += `
                            <div class="announcement-card" style="
                                background: ${isRead ? '#f8f9fa' : '#ffffff'};
                                border-left: 4px solid ${priorityColor};
                                border-radius: 10px;
                                padding: 15px;
                                margin-bottom: 15px;
                                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                                cursor: pointer;
                                transition: transform 0.2s;
                                ${!isRead ? 'border: 2px solid ' + priorityColor + ';' : ''}
                            " onclick="showAnnouncementDetails(${announcement.announcement_id})">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                            <span style="font-size: 24px;">${typeIcon}</span>
                                            ${isPinned ? '<i class="bx bx-pin" style="color: #ff0000; font-size: 20px;" title="Pinned"></i>' : ''}
                                            ${!isRead ? '<span style="background: #ff0000; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold;">NEW</span>' : ''}
                                        </div>
                                        <h4 style="margin: 5px 0; color: #333;">${announcement.title}</h4>
                                        <p style="color: #666; font-size: 14px; margin: 8px 0; line-height: 1.5;">
                                            ${announcement.content.substring(0, 150)}${announcement.content.length > 150 ? '...' : ''}
                                        </p>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                        <span style="font-size: 12px; color: #888;">
                                            <i class='bx bx-user'></i> ${announcement.publisher_name}
                                        </span>
                                        <span style="font-size: 12px; color: #888;">
                                            <i class='bx bx-calendar'></i> ${formatDateTime(announcement.published_date)}
                                        </span>
                                        <span style="font-size: 12px; padding: 2px 8px; background: ${priorityColor}; color: white; border-radius: 5px;">
                                            ${announcement.priority.toUpperCase()}
                                        </span>
                                    </div>
                                    <div>
                                        <i class='bx bx-chevron-right' style="font-size: 24px; color: ${priorityColor};"></i>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    container.innerHTML = html;
                } else if (data.status === 'success') {
                    container.innerHTML = '<p class="text-center text-muted" style="padding: 20px;">📢 No announcements at this time.</p>';
                } else {
                    container.innerHTML = '<p class="text-center text-danger">Error: ' + (data.message || 'Unknown error') + '</p>';
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                document.getElementById('announcementsList').innerHTML = '<p class="text-center text-danger">Error: Invalid response from server. Check console for details.</p>';
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('announcementsList').innerHTML = '<p class="text-center text-danger">Error loading announcements: ' + error.message + '</p>';
        });
    }
        
        // Show announcement details in modal
        function showAnnouncementDetails(announcementId) {
            // Fetch full announcement details
            const formData = new FormData();
            formData.append('action', 'get_announcement_details');
            formData.append('announcement_id', announcementId);
            
            fetch('../assets/manageAnnouncements.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displayAnnouncementModal(data.data);
                    // Mark as read
                    markAnnouncementAsRead(announcementId);
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        function displayAnnouncementModal(announcement) {
            let typeIcon = '📢';
            if (announcement.announcement_type === 'academic') typeIcon = '📚';
            else if (announcement.announcement_type === 'event') typeIcon = '🎉';
            else if (announcement.announcement_type === 'exam') typeIcon = '📝';
            else if (announcement.announcement_type === 'holiday') typeIcon = '🏖️';
            else if (announcement.announcement_type === 'emergency') typeIcon = '🚨';
            else if (announcement.announcement_type === 'sports') typeIcon = '⚽';
            
            const modalHTML = `
                <div id="announcementModal" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                " onclick="closeAnnouncementModal(event)">
                    <div style="
                        background: white;
                        border-radius: 15px;
                        padding: 30px;
                        max-width: 600px;
                        max-height: 80vh;
                        overflow-y: auto;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                    " onclick="event.stopPropagation()">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <span style="font-size: 40px;">${typeIcon}</span>
                                <div>
                                    <h2 style="margin: 0; color: #333;">${announcement.title}</h2>
                                    <p style="margin: 5px 0 0 0; color: #888; font-size: 14px;">
                                        <i class='bx bx-user'></i> ${announcement.publisher_name} • 
                                        <i class='bx bx-calendar'></i> ${formatDateTime(announcement.published_date)}
                                    </p>
                                </div>
                            </div>
                            <button onclick="closeAnnouncementModal()" style="
                                background: none;
                                border: none;
                                font-size: 30px;
                                cursor: pointer;
                                color: #999;
                            ">&times;</button>
                        </div>
                        
                        <div style="margin: 20px 0;">
                            <span style="background: #667eea; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; text-transform: uppercase;">
                                ${announcement.announcement_type}
                            </span>
                            <span style="background: #ff9800; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; text-transform: uppercase; margin-left: 10px;">
                                ${announcement.priority}
                            </span>
                        </div>
                        
                        <div style="
                            background: #f8f9fa;
                            padding: 20px;
                            border-radius: 10px;
                            margin: 20px 0;
                            line-height: 1.6;
                            color: #333;
                        ">
                            ${announcement.content.replace(/\n/g, '<br>')}
                        </div>
                        
                        ${announcement.external_link ? `
                            <div style="margin: 20px 0;">
                                <a href="${announcement.external_link}" target="_blank" style="
                                    display: inline-block;
                                    background: #667eea;
                                    color: white;
                                    padding: 10px 20px;
                                    border-radius: 8px;
                                    text-decoration: none;
                                ">
                                    <i class='bx bx-link-external'></i> View Link
                                </a>
                            </div>
                        ` : ''}
                        
                        <div style="border-top: 1px solid #ddd; padding-top: 15px; margin-top: 20px;">
                            <p style="color: #888; font-size: 12px; margin: 0;">
                                <i class='bx bx-time'></i> Valid until: ${formatDateTime(announcement.display_until)}
                            </p>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
        
        function closeAnnouncementModal(event) {
            const modal = document.getElementById('announcementModal');
            if (modal && (!event || event.target === modal)) {
                modal.remove();
                // Reload announcements to update read status
                loadStudentAnnouncements();
            }
        }
        
        function markAnnouncementAsRead(announcementId) {
            const formData = new FormData();
            formData.append('action', 'mark_announcement_read');
            formData.append('announcement_id', announcementId);
            formData.append('user_id', '<?php echo $_SESSION['uid']; ?>');
            formData.append('user_type', 'student');
            
            fetch('../assets/manageAnnouncements.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .catch(error => console.error('Error marking as read:', error));
        }
    </script>

    <!-- Student Self-Attendance Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            checkTodayAttendance();
            
            const markPresentBtn = document.getElementById('markPresentBtn');
            if (markPresentBtn) {
                markPresentBtn.addEventListener('click', markPresent);
            }
        });

        function checkTodayAttendance() {
            const formData = new FormData();
            formData.append('action', 'check_student_attendance');

            fetch('../assets/manageAttendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.marked) {
                    showAlreadyMarked(data.attendance_status, data.time);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function markPresent() {
            const btn = document.getElementById('markPresentBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Marking...';

            const formData = new FormData();
            formData.append('action', 'student_mark_present');

            fetch('../assets/manageAttendance.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showSuccess(data.message);
                    // Reload page to update attendance chart
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showError(data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bx bx-check-circle"></i> Mark Present';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to mark attendance');
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-check-circle"></i> Mark Present';
            });
        }

        function showAlreadyMarked(status, time) {
            const container = document.getElementById('attendanceButtonContainer');
            const statusDiv = document.getElementById('attendanceStatus');
            
            container.innerHTML = `
                <div style="text-align: center; padding: 15px;">
                    <i class='bx bx-check-circle' style="font-size: 48px; color: #4CAF50;"></i>
                    <p style="margin: 10px 0 0 0; font-size: 18px; font-weight: bold;">Attendance Already Marked</p>
                </div>
            `;
            
            statusDiv.style.display = 'block';
            statusDiv.querySelector('p').innerHTML = `
                <i class='bx bx-check'></i> You marked yourself <strong>${status}</strong> today at ${time}
            `;
        }

        function showSuccess(message) {
            const statusDiv = document.getElementById('attendanceStatus');
            statusDiv.style.display = 'block';
            statusDiv.style.background = 'rgba(76, 175, 80, 0.3)';
            statusDiv.querySelector('p').innerHTML = `
                <i class='bx bx-check-circle'></i> ${message}
            `;
        }

        function showError(message) {
            const statusDiv = document.getElementById('attendanceStatus');
            statusDiv.style.display = 'block';
            statusDiv.style.background = 'rgba(244, 67, 54, 0.3)';
            statusDiv.querySelector('p').innerHTML = `
                <i class='bx bx-error-circle'></i> ${message}
            `;
        }
    </script>

    <!-- Enhanced Dashboard Features -->
    <script>
        // Motivational Quotes Array
        const quotes = [
            { text: "Education is the most powerful weapon which you can use to change the world.", author: "Nelson Mandela" },
            { text: "The beautiful thing about learning is that nobody can take it away from you.", author: "B.B. King" },
            { text: "Live as if you were to die tomorrow. Learn as if you were to live forever.", author: "Mahatma Gandhi" },
            { text: "The more that you read, the more things you will know.", author: "Dr. Seuss" },
            { text: "Education is not preparation for life; education is life itself.", author: "John Dewey" },
            { text: "Success is the sum of small efforts, repeated day in and day out.", author: "Robert Collier" },
            { text: "Don't let what you cannot do interfere with what you can do.", author: "John Wooden" },
            { text: "The expert in anything was once a beginner.", author: "Helen Hayes" }
        ];

        // Display Random Quote
        function displayRandomQuote() {
            const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];
            document.getElementById('dailyQuote').textContent = `"${randomQuote.text}"`;
            document.querySelector('.quote-author').textContent = `— ${randomQuote.author}`;
        }

        // Load Quick Stats
        function loadQuickStats() {
            const studentId = '<?php echo $_SESSION['uid']; ?>';
            
            // Fetch exam results count and average grade
            fetch('../assets/manageExams.php?action=get_my_stats')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('totalExams').textContent = data.stats.results_published || '0';
                        document.getElementById('averageGrade').textContent = data.stats.average || '0%';
                    }
                })
                .catch(error => console.error('Error loading exam stats:', error));

            // Load assignment counts
            loadAssignmentCounts();
            
            // Load recent grades
            loadRecentGrades();
            
            // Load performance tracker
            loadPerformanceTracker();
        }

        // Load Assignment Counts from existing assignments data
        function loadAssignmentCounts() {
            const formData = new FormData();
            formData.append('action', 'get_my_assignments');
            
            fetch('../assets/manageAssignments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.assignments) {
                    const now = new Date();
                    const pending = data.assignments.filter(a => !a.submission_id && new Date(a.due_date) > now).length;
                    const completed = data.assignments.filter(a => a.submission_id).length;
                    
                    document.getElementById('pendingAssignments').textContent = pending;
                    document.getElementById('completedAssignments').textContent = completed;
                    
                    // Update progress tracker
                    const total = data.assignments.length;
                    if (total > 0) {
                        const assignmentProgress = Math.round((completed / total) * 100);
                        updateProgressBar('assignmentProgress', 'assignmentProgressBar', assignmentProgress);
                    }
                }
            })
            .catch(error => {
                console.error('Error loading assignments:', error);
                // Set to 0 if error
                document.getElementById('pendingAssignments').textContent = '0';
                document.getElementById('completedAssignments').textContent = '0';
            });
        }

        // Load Recent Grades
        function loadRecentGrades() {
            fetch('../assets/manageExams.php?action=get_my_results&limit=5')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recentGradesContainer');
                    
                    if (data.status === 'success' && data.results && data.results.length > 0) {
                        let html = '';
                        data.results.forEach(result => {
                            const gradeClass = result.grade.includes('A') ? 'grade-A' :
                                             result.grade.includes('B') ? 'grade-B' :
                                             result.grade.includes('C') ? 'grade-C' :
                                             result.grade.includes('D') ? 'grade-D' : 'grade-F';
                            
                            html += `
                                <div class="grade-item">
                                    <div class="grade-info">
                                        <h5>${result.exam_name}</h5>
                                        <p><i class='bx bx-calendar'></i> ${formatDate(result.exam_date)}</p>
                                    </div>
                                    <div class="grade-badge ${gradeClass}">
                                        ${result.grade} - ${result.percentage}%
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p class="loading-text">No grades available yet</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading recent grades:', error);
                    document.getElementById('recentGradesContainer').innerHTML = 
                        '<p class="loading-text">Error loading grades</p>';
                });
        }

        // Load Performance Tracker with real data
        function loadPerformanceTracker() {
            // Get exam stats
            fetch('../assets/manageExams.php?action=get_my_stats')
                .then(response => response.json())
                .then(examData => {
                    if (examData.status === 'success') {
                        // Calculate exam progress from average
                        const examProgress = parseInt(examData.stats.average) || 0;
                        
                        // Overall progress is the exam average
                        const overallProgress = examProgress;
                        
                        // Update progress bars with animation
                        setTimeout(() => {
                            updateProgressBar('overallProgress', 'overallProgressBar', overallProgress);
                            updateProgressBar('examProgress', 'examProgressBar', examProgress);
                        }, 300);
                    } else {
                        // No exam data, set to 0
                        updateProgressBar('overallProgress', 'overallProgressBar', 0);
                        updateProgressBar('examProgress', 'examProgressBar', 0);
                    }
                })
                .catch(error => {
                    console.error('Error loading exam performance:', error);
                    updateProgressBar('overallProgress', 'overallProgressBar', 0);
                    updateProgressBar('examProgress', 'examProgressBar', 0);
                });
        }

        // Update Progress Bar
        function updateProgressBar(labelId, barId, percentage) {
            document.getElementById(labelId).textContent = percentage + '%';
            document.getElementById(barId).style.width = percentage + '%';
        }

        // Format Date Helper
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        // Create Confetti Effect
        function createConfetti() {
            const colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe', '#43e97b'];
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * window.innerWidth + 'px';
                    confetti.style.top = '-10px';
                    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDelay = Math.random() * 2 + 's';
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => confetti.remove(), 3000);
                }, i * 30);
            }
        }

        // Particle Effect on Click
        document.addEventListener('click', (e) => {
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: fixed;
                width: 10px;
                height: 10px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                border-radius: 50%;
                pointer-events: none;
                left: ${e.clientX}px;
                top: ${e.clientY}px;
                animation: particle-burst 0.6s ease-out forwards;
                z-index: 9999;
            `;
            document.body.appendChild(particle);
            setTimeout(() => particle.remove(), 600);
        });

        // Add particle burst animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes particle-burst {
                0% {
                    transform: scale(1) translate(0, 0);
                    opacity: 1;
                }
                100% {
                    transform: scale(0) translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Smooth Scroll for Internal Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Add Hover Effect to Cards
        document.querySelectorAll('.stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Welcome Animation on Load
        window.addEventListener('load', () => {
            setTimeout(() => {
                createConfetti();
            }, 500);
            
            displayRandomQuote();
            loadQuickStats();
            loadUpcomingDeadlines();
            initializeAchievements();
        });

        // Study Timer Variables
        let timerInterval;
        let timerSeconds = 0;
        let isRunning = false;

        function startTimer() {
            if (!isRunning) {
                isRunning = true;
                document.getElementById('startTimerBtn').style.display = 'none';
                document.getElementById('pauseTimerBtn').style.display = 'inline-flex';
                
                timerInterval = setInterval(() => {
                    timerSeconds++;
                    updateTimerDisplay();
                    saveStudyTime();
                }, 1000);
            }
        }

        function pauseTimer() {
            isRunning = false;
            clearInterval(timerInterval);
            document.getElementById('startTimerBtn').style.display = 'inline-flex';
            document.getElementById('pauseTimerBtn').style.display = 'none';
        }

        function resetTimer() {
            pauseTimer();
            timerSeconds = 0;
            updateTimerDisplay();
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timerSeconds / 60);
            const seconds = timerSeconds % 60;
            document.getElementById('timerDisplay').textContent = 
                `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        function saveStudyTime() {
            const today = new Date().toDateString();
            let studyData = JSON.parse(localStorage.getItem('studyTime') || '{}');
            
            if (!studyData[today]) {
                studyData[today] = 0;
            }
            studyData[today]++;
            
            localStorage.setItem('studyTime', JSON.stringify(studyData));
            updateStudyStats();
        }

        function updateStudyStats() {
            const today = new Date().toDateString();
            let studyData = JSON.parse(localStorage.getItem('studyTime') || '{}');
            
            // Today's study time
            const todaySeconds = studyData[today] || 0;
            const todayHours = Math.floor(todaySeconds / 3600);
            const todayMinutes = Math.floor((todaySeconds % 3600) / 60);
            document.getElementById('todayStudyTime').textContent = `${todayHours}h ${todayMinutes}m`;
            
            // Total study time
            let totalSeconds = Object.values(studyData).reduce((a, b) => a + b, 0);
            const totalHours = Math.floor(totalSeconds / 3600);
            const totalMinutes = Math.floor((totalSeconds % 3600) / 60);
            document.getElementById('totalStudyTime').textContent = `${totalHours}h ${totalMinutes}m`;
        }

        // Load study stats on page load
        updateStudyStats();

        // Load Upcoming Deadlines
        function loadUpcomingDeadlines() {
            const formData = new FormData();
            formData.append('action', 'get_my_assignments');
            
            fetch('../assets/manageAssignments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('upcomingDeadlines');
                
                if (data.status === 'success' && data.assignments) {
                    const now = new Date();
                    const upcoming = data.assignments
                        .filter(a => !a.submission_id && new Date(a.due_date) > now)
                        .sort((a, b) => new Date(a.due_date) - new Date(b.due_date))
                        .slice(0, 5);
                    
                    if (upcoming.length > 0) {
                        let html = '';
                        upcoming.forEach(assignment => {
                            const dueDate = new Date(assignment.due_date);
                            const timeLeft = dueDate - now;
                            const daysLeft = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                            const hoursLeft = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            
                            const isUrgent = daysLeft < 2;
                            let timeDisplay = '';
                            if (daysLeft > 0) {
                                timeDisplay = `${daysLeft} day${daysLeft > 1 ? 's' : ''} ${hoursLeft}h`;
                            } else {
                                timeDisplay = `${hoursLeft} hours`;
                            }
                            
                            html += `
                                <div class="deadline-item ${isUrgent ? 'urgent' : ''}">
                                    <div class="deadline-title">${assignment.title}</div>
                                    <div class="deadline-time ${isUrgent ? 'urgent' : ''}">
                                        <i class='bx bx-time-five'></i>
                                        Due in ${timeDisplay}
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p class="loading-text">🎉 No pending deadlines!</p>';
                    }
                } else {
                    container.innerHTML = '<p class="loading-text">No assignments found</p>';
                }
            })
            .catch(error => {
                console.error('Error loading deadlines:', error);
                document.getElementById('upcomingDeadlines').innerHTML = 
                    '<p class="loading-text">Error loading deadlines</p>';
            });
        }

        // Initialize Achievements
        function initializeAchievements() {
            // Check for achievements based on performance
            fetch('../assets/manageExams.php?action=get_my_stats')
                .then(response => response.json())
                .then(examData => {
                    if (examData.status === 'success') {
                        // Top Performer badge: average >= 90%
                        if (parseInt(examData.stats.average) >= 90) {
                            unlockBadge('top-performer');
                        }
                    }
                });
            
            // Check assignments for Task Master and Early Bird
            const formData = new FormData();
            formData.append('action', 'get_my_assignments');
            
            fetch('../assets/manageAssignments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.assignments) {
                    const completed = data.assignments.filter(a => a.submission_id).length;
                    
                    // Task Master badge: 10 completed assignments
                    if (completed >= 10) {
                        unlockBadge('task-master');
                    }
                    
                    // Early Bird badge: 5 early submissions
                    const earlySubmissions = data.assignments.filter(a => {
                        if (a.submission_id && a.submitted_at && a.due_date) {
                            return new Date(a.submitted_at) < new Date(a.due_date);
                        }
                        return false;
                    }).length;
                    
                    if (earlySubmissions >= 5) {
                        unlockBadge('early-bird');
                    }
                }
            });
        }

        function unlockBadge(badgeName) {
            const badge = document.querySelector(`[data-achievement="${badgeName}"]`);
            if (badge && !badge.classList.contains('earned')) {
                badge.classList.add('earned');
                
                // Show celebration effect
                const icon = badge.querySelector('.badge-icon');
                icon.style.animation = 'none';
                setTimeout(() => {
                    icon.style.animation = 'badge-glow 2s infinite';
                }, 10);
            }
        }

        // Welcome Animation on Load
        window.addEventListener('load', () => {
            setTimeout(() => {
                createConfetti();
            }, 500);
            
            displayRandomQuote();
            loadQuickStats();
        });

        // Refresh quote every 30 seconds
        setInterval(displayRandomQuote, 30000);

        // Add gradient animation to welcome banner
        let hue = 260;
        setInterval(() => {
            hue = (hue + 1) % 360;
            const banner = document.querySelector('.welcome-banner');
            if (banner) {
                banner.style.background = `linear-gradient(135deg, hsl(${hue}, 70%, 65%) 0%, hsl(${(hue + 20) % 360}, 70%, 60%) 100%)`;
            }
        }, 100);

        // Add typing effect to welcome text
        function typeWriter(element, text, speed = 50) {
            let i = 0;
            element.textContent = '';
            function type() {
                if (i < text.length) {
                    element.textContent += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                }
            }
            type();
        }

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards
        document.querySelectorAll('.stat-card, .enhanced-card, .leaves, .timetable').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });

        console.log('🎓 Enhanced Dashboard Loaded! Enjoy your learning journey! 🚀');
    </script>

    <script type="text/javascript" src="app.js"></script>
    <!-- <script type="text/javascript" src="timeTable.js"></script> -->
    <script type="text/javascript" src="index.js"></script>
    <script src="../js/oranbyte-google-translator.js"></script>
</body>

</html>