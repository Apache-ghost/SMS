

    <script src="../assets/js/logout.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="js/bootstrap.bundle.js"></script>
    
    <script src="script.js"></script>
    <script src="../js/oranbyte-google-translator.js"></script>
    <script>
     
      function switchToTab(tabId) {
        if (event) {
        event.preventDefault();
      }
    
        var tab = new bootstrap.Tab(document.getElementById(tabId));
        tab.show();
       
      }
    
      
     
      function isTabAvailable(tabId) {
        return document.getElementById(tabId) !== null;
      }
      
      document.addEventListener('DOMContentLoaded', function () {
        var hash = window.location.hash;
        if (hash) {
          var tabId = hash.substring(1);
          if (isTabAvailable(tabId)) {
            switchToTab(tabId);
          } else {
            window.location.href = window.location.href.split('#')[0];
          }
        }
      });
     
    </script>

    <!-- Admin Dashboard Enhancements -->
    <script>
        // Admin Timer Variables
        let adminTimerInterval;
        let adminTimerSeconds = 0;
        let adminIsRunning = false;

        function startAdminTimer() {
            if (!adminIsRunning) {
                adminIsRunning = true;
                document.getElementById('adminStartBtn').style.display = 'none';
                document.getElementById('adminPauseBtn').style.display = 'inline-block';
                
                adminTimerInterval = setInterval(() => {
                    adminTimerSeconds++;
                    updateAdminTimerDisplay();
                    saveAdminWorkTime();
                }, 1000);
            }
        }

        function pauseAdminTimer() {
            adminIsRunning = false;
            clearInterval(adminTimerInterval);
            document.getElementById('adminStartBtn').style.display = 'inline-block';
            document.getElementById('adminPauseBtn').style.display = 'none';
        }

        function resetAdminTimer() {
            pauseAdminTimer();
            adminTimerSeconds = 0;
            updateAdminTimerDisplay();
        }

        function updateAdminTimerDisplay() {
            const minutes = Math.floor(adminTimerSeconds / 60);
            const seconds = adminTimerSeconds % 60;
            document.getElementById('adminTimerDisplay').textContent = 
                `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        function saveAdminWorkTime() {
            const today = new Date().toDateString();
            let workData = JSON.parse(localStorage.getItem('adminWorkTime') || '{}');
            
            if (!workData[today]) {
                workData[today] = 0;
            }
            workData[today]++;
            
            localStorage.setItem('adminWorkTime', JSON.stringify(workData));
            updateAdminWorkStats();
        }

        function updateAdminWorkStats() {
            const today = new Date().toDateString();
            let workData = JSON.parse(localStorage.getItem('adminWorkTime') || '{}');
            
            const todaySeconds = workData[today] || 0;
            const todayHours = Math.floor(todaySeconds / 3600);
            const todayMinutes = Math.floor((todaySeconds % 3600) / 60);
            if(document.getElementById('adminTodayTime')) {
                document.getElementById('adminTodayTime').textContent = `${todayHours}h ${todayMinutes}m`;
            }
        }

        // Create Confetti Effect
        function createAdminConfetti() {
            const colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe', '#43e97b'];
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'admin-confetti';
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
        const adminStyle = document.createElement('style');
        adminStyle.textContent = `
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
        document.head.appendChild(adminStyle);

        // Initialize Admin Dashboard
        window.addEventListener('load', () => {
            setTimeout(() => {
                createAdminConfetti();
            }, 500);
            
            updateAdminWorkStats();
            loadAdminAchievements();
        });

        // Load Admin Achievements
        function loadAdminAchievements() {
            // Check student count for Manager badge
            const studentCountEl = document.getElementById('studentCount');
            if (studentCountEl) {
                const checkStudentCount = setInterval(() => {
                    const count = parseInt(studentCountEl.textContent);
                    if (!isNaN(count) && count > 0) {
                        if (count >= 100) {
                            unlockAdminBadge(1); // Manager badge
                        }
                        clearInterval(checkStudentCount);
                    }
                }, 1000);
            }

            // Check notice count for Communicator badge
            const noticeCountEl = document.getElementById('noticeCount');
            if (noticeCountEl) {
                const checkNoticeCount = setInterval(() => {
                    const count = parseInt(noticeCountEl.textContent);
                    if (!isNaN(count) && count > 0) {
                        if (count >= 100) {
                            unlockAdminBadge(3); // Communicator badge
                        }
                        clearInterval(checkNoticeCount);
                    }
                }, 1000);
            }
        }

        function unlockAdminBadge(index) {
            const badges = document.querySelectorAll('.admin-badge-item');
            if (badges[index] && !badges[index].classList.contains('earned')) {
                badges[index].classList.add('earned');
                
                // Celebration effect
                setTimeout(() => {
                    createAdminConfetti();
                }, 100);
            }
        }

        // Add hover effects to insight cards
        document.querySelectorAll('.insights li').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.03)';
                this.style.transition = 'all 0.3s ease';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        console.log('🎉 Admin Dashboard Enhanced! Enjoy the amazing features!');
    </script>

   
</body>

</html>

