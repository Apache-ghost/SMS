
<?php include("../assets/noSessionRedirect.php"); ?>
<?php include("./verifyRoleRedirect.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <title>School Management</title>
    <link rel="icon" type="image/x-icon" href="../images/1.png">
    
    <link rel="stylesheet" href="css/bootstrap.css">

    <link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/date-picker.css" />
    <link rel="stylesheet" href="../css/oranbyte-google-translator.css">
    <link rel="stylesheet" href="settings-style.css">
   
    <style>
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
            opacity: 0.12;
            animation: float 20s infinite ease-in-out;
        }

        .ball-1 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: -180px;
            right: -180px;
            animation-delay: 0s;
        }

        .ball-2 {
            width: 220px;
            height: 220px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            bottom: -110px;
            left: -110px;
            animation-delay: 3s;
        }

        .ball-3 {
            width: 280px;
            height: 280px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            top: 50%;
            right: -140px;
            animation-delay: 6s;
        }

        .ball-4 {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            bottom: 25%;
            left: 8%;
            animation-delay: 9s;
        }

        .ball-5 {
            width: 240px;
            height: 240px;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            top: 35%;
            left: 3%;
            animation-delay: 12s;
        }

        .ball-6 {
            width: 190px;
            height: 190px;
            background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
            bottom: 45%;
            right: 12%;
            animation-delay: 15s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0) rotate(0deg);
            }
            25% {
                transform: translateY(-35px) translateX(25px) rotate(90deg);
            }
            50% {
                transform: translateY(-70px) translateX(-25px) rotate(180deg);
            }
            75% {
                transform: translateY(-35px) translateX(-45px) rotate(270deg);
            }
        }

        /* Welcome Banner */
        .admin-welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 25px;
            color: white;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.4);
            animation: fadeInDown 0.8s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .admin-welcome-banner h2 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }

        .admin-welcome-banner p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        /* Quick Actions Grid */
        .admin-quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .admin-action-card {
            padding: 20px;
            border-radius: 15px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .admin-action-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            color: white;
        }

        .admin-action-icon {
            font-size: 2rem;
        }

        .admin-action-text h4 {
            margin: 0 0 5px 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .admin-action-text p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.85rem;
        }

        /* Achievement Badges */
        .admin-achievements {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .admin-achievements h3 {
            margin: 0 0 15px 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-badges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
            gap: 12px;
        }

        .admin-badge-item {
            text-align: center;
            padding: 15px 8px;
            border-radius: 12px;
            background: #f5f5f5;
            transition: all 0.3s ease;
            opacity: 0.4;
            filter: grayscale(100%);
        }

        .admin-badge-item.earned {
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

        .admin-badge-item:hover {
            transform: scale(1.1) translateY(-5px);
        }

        .admin-badge-icon {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .admin-badge-name {
            font-size: 0.75rem;
            font-weight: 600;
            color: #555;
        }

        /* Admin Timer */
        .admin-timer-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            text-align: center;
        }

        .admin-timer-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .admin-timer-text {
            font-size: 2rem;
            font-weight: 700;
            color: white;
        }

        .admin-timer-controls {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 15px;
        }

        .admin-timer-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .admin-timer-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .admin-start-btn {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .admin-pause-btn {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .admin-reset-btn {
            background: #e5e7eb;
            color: #555;
        }

        /* Dashboard Grid */
        .admin-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .admin-dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .admin-dashboard-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }

        .admin-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .admin-card-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Activity List */
        .admin-activity-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .admin-activity-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        /* Confetti */
        .admin-confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            animation: confetti-fall 3s linear forwards;
            pointer-events: none;
            z-index: 9999;
        }

        @keyframes confetti-fall {
            to {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<?php 
 
    
    $theme = "light";
   
    $uid = $_SESSION['uid'];
    $query = "SELECT theme FROM users WHERE id='$uid'";
    $result = mysqli_query($conn, $query);
    if(mysqli_num_rows($result) > 0){
      $row = mysqli_fetch_array($result);
   
      $theme = $row['theme'];
    }
?>
<body class='<?php echo $theme; ?>'>
    <!-- Floating Background -->
    <div class="floating-background">
        <div class="floating-ball ball-1"></div>
        <div class="floating-ball ball-2"></div>
        <div class="floating-ball ball-3"></div>
        <div class="floating-ball ball-4"></div>
        <div class="floating-ball ball-5"></div>
        <div class="floating-ball ball-6"></div>
    </div>

 
<div class='toast-container position-fixed text-success bottom-0 end-0 p-3' style="z-index: 9000;">
    <div id='liveToast' class='toast' role='alert' aria-live='assertive' aria-atomic='true' style="color:black;">
    <div class='d-flex'>
      <div class='toast-body' id="toast-alert-message">
        
      </div>
      <button type='button' class='btn-close me-2 m-auto text-danger' data-bs-dismiss='toast' aria-label='Close'></button>
    </div>
    </div>
  </div>




