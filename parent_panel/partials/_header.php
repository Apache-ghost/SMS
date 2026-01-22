<?php
session_start();
if (!isset($_SESSION['parent_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="shortcut icon" href="../images/logo.png">
    <link rel="stylesheet" href="./style.css">
    <title>Parent Portal</title>

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
            width: 320px;
            height: 320px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: -160px;
            right: -160px;
            animation-delay: 0s;
        }

        .ball-2 {
            width: 210px;
            height: 210px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            bottom: -105px;
            left: -105px;
            animation-delay: 3s;
        }

        .ball-3 {
            width: 270px;
            height: 270px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            top: 50%;
            right: -135px;
            animation-delay: 6s;
        }

        .ball-4 {
            width: 195px;
            height: 195px;
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            bottom: 22%;
            left: 9%;
            animation-delay: 9s;
        }

        .ball-5 {
            width: 235px;
            height: 235px;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            top: 32%;
            left: 4%;
            animation-delay: 12s;
        }

        .ball-6 {
            width: 185px;
            height: 185px;
            background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
            bottom: 42%;
            right: 13%;
            animation-delay: 15s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0) rotate(0deg);
            }
            25% {
                transform: translateY(-32px) translateX(22px) rotate(90deg);
            }
            50% {
                transform: translateY(-65px) translateX(-22px) rotate(180deg);
            }
            75% {
                transform: translateY(-32px) translateX(-42px) rotate(270deg);
            }
        }

        /* Parent Welcome Banner */
        .parent-welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 35px;
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

        .parent-welcome-banner h2 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }

        .parent-welcome-banner p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        /* Quick Actions */
        .parent-quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .parent-action-card {
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

        .parent-action-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            color: white;
        }

        .parent-action-icon {
            font-size: 2rem;
        }

        .parent-action-text h4 {
            margin: 0 0 5px 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .parent-action-text p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.85rem;
        }

        /* Achievement Badges */
        .parent-achievements {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .parent-achievements h3 {
            margin: 0 0 15px 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .parent-badges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
            gap: 12px;
        }

        .parent-badge-item {
            text-align: center;
            padding: 15px 8px;
            border-radius: 12px;
            background: #f5f5f5;
            transition: all 0.3s ease;
            opacity: 0.4;
            filter: grayscale(100%);
        }

        .parent-badge-item.earned {
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

        .parent-badge-item:hover {
            transform: scale(1.1) translateY(-5px);
        }

        .parent-badge-icon {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .parent-badge-name {
            font-size: 0.75rem;
            font-weight: 600;
            color: #555;
        }

        /* Timer Card */
        .parent-timer-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            text-align: center;
        }

        .parent-timer-circle {
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

        .parent-timer-text {
            font-size: 2rem;
            font-weight: 700;
            color: white;
        }

        .parent-timer-controls {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 15px;
        }

        .parent-timer-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .parent-timer-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .parent-start-btn {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .parent-pause-btn {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .parent-reset-btn {
            background: #e5e7eb;
            color: #555;
        }

        /* Dashboard Grid */
        .parent-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .parent-dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .parent-dashboard-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }

        .parent-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .parent-card-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Child Progress */
        .child-progress-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .child-progress-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        /* Confetti */
        .parent-confetti {
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

