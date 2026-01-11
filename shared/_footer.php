<footer>
    <div class="container">
      <div class="row g-4">
        <!-- About Section -->
        <div class="col-lg-4 col-md-6 footer-section">
          <div class="footer-logo-section">
            <img src="images/company-logo.jpg" alt="School Logo" class="footer-logo">
            <h4 class="footer-title">School Management System</h4>
          </div>
          <p class="footer-description">
            Empowering education through innovative management solutions. 
            Building tomorrow's leaders today.
          </p>
          <div class="footer-info-item">
            <i class="fas fa-map-marker-alt"></i>
            <span>Q9P3+75H, My Town, My City, My Country</span>
          </div>
        </div>
        
        <!-- Quick Links Section -->
        <div class="col-lg-3 col-md-6 footer-section">
          <h5 class="footer-heading">Quick Links</h5>
          <ul class="footer-links-list">
            <li><a href="./index.php"><i class="fas fa-angle-right"></i> Home</a></li>
            <li><a href="./about-us.php"><i class="fas fa-angle-right"></i> About Us</a></li>
            <li><a href="./login.php"><i class="fas fa-angle-right"></i> Login</a></li>
            <li><a href="#"><i class="fas fa-angle-right"></i> Contact</a></li>
          </ul>
        </div>
        
        <!-- Contact Info Section -->
        <div class="col-lg-2 col-md-6 footer-section">
          <h5 class="footer-heading">Contact</h5>
          <ul class="footer-contact-list">
            <li>
              <i class="fas fa-phone"></i>
              <span>+91 1234567890</span>
            </li>
            <li>
              <i class="fas fa-envelope"></i>
              <span>info@school.edu</span>
            </li>
            <li>
              <i class="fas fa-clock"></i>
              <span>Mon - Fri: 9AM - 5PM</span>
            </li>
          </ul>
        </div>
        
        <!-- Social Media Section -->
        <div class="col-lg-3 col-md-6 footer-section">
          <h5 class="footer-heading">Follow Us</h5>
          <p class="footer-social-text">Stay connected with us on social media</p>
          <div class="social-icons">
            <a href="#" class="social-link" title="Facebook">
              <i class="fab fa-facebook-f facebook"></i>
            </a>
            <a href="#" class="social-link" title="Twitter">
              <i class="fa-brands fa-x-twitter twitter"></i>
            </a>
            <a href="#" class="social-link" title="Instagram">
              <i class="fab fa-instagram instagram"></i>
            </a>
            <a href="#" class="social-link" title="LinkedIn">
              <i class="fab fa-linkedin-in linked-in"></i>
            </a>
          </div>
          <div class="footer-timezone">
            <i class="fas fa-globe"></i>
            <?php
              date_default_timezone_set('Asia/Kolkata');
              $current_time = date('D, M d Y');
              echo "<span>$current_time</span>";
            ?>
          </div>
        </div>
      </div>
      
      <!-- Footer Bottom -->
      <div class="footer-bottom">
        <div class="row">
          <div class="col-md-12">
            <p class="footer-copyright">
              &copy; <?php echo date('Y'); ?> School Management System. 
              Developed by <a href="https://www.github.com/ProjectsAndPrograms" target="_blank" class="footer-link">ProjectsAndPrograms</a>. 
              All rights reserved.
            </p>
          </div>
        </div>
      </div>
    </div>
  </footer>



  <script src="https://kit.fontawesome.com/a81368914c.js"></script>
  <script src="js/bootstrap.bundle.js"></script>
  <script src="./shared/app.js"></script>
</body>

</html>
