<?php include('shared/_header.php');?>

  <main>
    <div class="big-wrapper light">
      <img src="./images/shape.png" alt="" class="shape" />
      
      <!-- Premium Decorative Elements -->
      <div class="floating-bubbles">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
      </div>
      
      <div class="gradient-orb gradient-orb-1"></div>
      <div class="gradient-orb gradient-orb-2"></div>
      <div class="gradient-orb gradient-orb-3"></div>
      
      <!-- Decorative Animated Shapes -->
      <div class="decorative-shape decorative-shape-1"></div>
      <div class="decorative-shape decorative-shape-2"></div>
      <div class="decorative-shape decorative-shape-3"></div>

     <?php include('shared/_navbar.php'); ?>
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-md-6 d-flex justify-content-center get-started animate-fade-in-left" style="height: 550px;">
            <div class=" d-flex justify-content-center align-items-center">
              <div>
                <div class="big-title">
                  <h1>Future is here,</h1>
                  <h1>Start Exploring now.</h1>
                </div>
                <p class="text">
                  streamline processes, manage resources, track student data, facilitate
                  communication, and enhance administrative tasks effectively.
                </p>
                <div class="cta animate-bounce-in animate-delay-3">
                  <a href="login.php" class="btn btn-modern">Get started</a>
                </div>


              </div>
            </div>
          </div>

          <div class="col-12 col-md-6 image-box animate-fade-in-right">

            <img src="./images/children.png" alt="Person Image" class="person" />
          </div>
        </div>
      </div>


  <?php include('shared/feature-cards.php'); ?>
      

      <div class="container mt-3">
        <hr>
      </div>

      <div class="container mt-3 carousel-box animate-fade-in-up">

        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="images/carousel1.jpg" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
              <img src="images/carousel2.jpg" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
              <img src="images/carousel3.jpg" class="d-block w-100" alt="...">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>

      </div>
    </div>


  </main>




  <?php include('shared/_footer.php'); ?>
