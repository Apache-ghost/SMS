# 🎨 Quick Animation Reference Guide

## Adding Animations to Your Pages

### Step 1: Include the Animation CSS
Make sure to include the animations.css file in your header:
```html
<link rel="stylesheet" href="../css/animations.css" />
```

### Step 2: Choose Your Animation

#### **Entrance Animations** (Elements appear when page loads)
```html
<div class="animate-fade-in-up">Fade in from bottom</div>
<div class="animate-fade-in-down">Fade in from top</div>
<div class="animate-fade-in-left">Fade in from left</div>
<div class="animate-fade-in-right">Fade in from right</div>
<div class="animate-bounce-in">Bounce in with scale</div>
<div class="animate-slide-in-up">Slide up from bottom</div>
<div class="animate-rotate-in">Rotate while appearing</div>
```

#### **Add Delays** (Stagger animations)
```html
<div class="animate-fade-in-up animate-delay-1">Appears first</div>
<div class="animate-fade-in-up animate-delay-2">Appears second</div>
<div class="animate-fade-in-up animate-delay-3">Appears third</div>
<!-- Continue up to animate-delay-6 -->
```

#### **Hover Effects** (Interactive animations)
```html
<div class="hover-lift">Lifts up on hover</div>
<div class="hover-scale">Scales up on hover</div>
<div class="hover-glow">Glows on hover</div>
<div class="hover-rotate">Rotates on hover</div>
<div class="hover-pulse">Pulses on hover</div>
<div class="hover-ripple">Ripple effect on hover</div>
<div class="hover-shine">Shine sweep on hover</div>
```

#### **Card Enhancements**
```html
<div class="card card-modern hover-lift shadow-soft">
  <!-- Your card content -->
</div>
```

#### **Button Enhancements**
```html
<button class="btn btn-modern">Click Me</button>
```

#### **Icon Animations**
```html
<i class="fas fa-star icon-bounce"></i>
<i class="fas fa-cog icon-spin"></i>
```

#### **Shadow Effects**
```html
<div class="shadow-soft">Soft shadow</div>
<div class="shadow-strong">Strong shadow</div>
<div class="shadow-colored">Colored shadow</div>
```

## Common Combinations

### Animated Card
```html
<div class="card card-modern hover-lift shadow-soft animate-fade-in-up animate-delay-1">
  <div class="card-body">
    <h3>
      <i class="fas fa-icon icon-bounce"></i>
      Card Title
    </h3>
    <p>Card content here</p>
  </div>
</div>
```

### Hero Section
```html
<div class="hero-section">
  <div class="animate-fade-in-left">
    <h1 class="text-gradient">Main Heading</h1>
    <p class="text-shimmer">Subheading with shimmer</p>
  </div>
  <div class="animate-fade-in-right">
    <img src="image.jpg" class="img-hover-zoom" alt="Hero Image">
  </div>
</div>
```

### Button Group
```html
<div class="btn-group">
  <button class="btn btn-modern animate-bounce-in animate-delay-1">Button 1</button>
  <button class="btn btn-modern animate-bounce-in animate-delay-2">Button 2</button>
  <button class="btn btn-modern animate-bounce-in animate-delay-3">Button 3</button>
</div>
```

### Feature Cards Grid
```html
<div class="row">
  <div class="col-md-4 animate-fade-in-up animate-delay-1">
    <div class="card card-modern hover-lift">
      <i class="fas fa-star icon-bounce"></i>
      <h3>Feature 1</h3>
    </div>
  </div>
  <div class="col-md-4 animate-fade-in-up animate-delay-2">
    <div class="card card-modern hover-lift">
      <i class="fas fa-rocket icon-bounce"></i>
      <h3>Feature 2</h3>
    </div>
  </div>
  <div class="col-md-4 animate-fade-in-up animate-delay-3">
    <div class="card card-modern hover-lift">
      <i class="fas fa-heart icon-bounce"></i>
      <h3>Feature 3</h3>
    </div>
  </div>
</div>
```

## Adding Decorative Shapes

Add these inside your main wrapper:
```html
<div class="big-wrapper">
  <!-- Decorative shapes -->
  <div class="decorative-shape decorative-shape-1"></div>
  <div class="decorative-shape decorative-shape-2"></div>
  <div class="decorative-shape decorative-shape-3"></div>
  
  <!-- Your content -->
</div>
```

## Special Text Effects

### Gradient Text
```html
<h1 class="text-gradient">Colorful Text</h1>
```

### Shimmer Text
```html
<h2 class="text-shimmer">Shimmering Text</h2>
```

### Neon Text
```html
<h3 class="neon-text">Neon Glow Text</h3>
```

## JavaScript Auto-Animations

These work automatically if you include `shared/app.js`:

1. **Scroll Reveal**: Add class `scroll-reveal` to any element
2. **Card 3D Tilt**: All `.card` elements get automatic tilt
3. **Button Ripple**: All `.btn` elements get ripple effect
4. **Smooth Scroll**: All anchor links scroll smoothly
5. **Parallax**: Elements with `.person` or `.shape` get parallax
6. **Navbar Effect**: Header animates automatically on scroll

## Performance Tips

1. Don't animate too many elements at once
2. Use delays strategically (animate-delay-1 through animate-delay-6)
3. Prefer CSS animations over JavaScript when possible
4. Test on mobile devices
5. Use `prefers-reduced-motion` for accessibility

## Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ⚠️ IE11 (limited support)

## Examples by Page Type

### Admin Dashboard
```html
<div class="dashboard">
  <div class="row">
    <div class="col-md-3 animate-fade-in-up animate-delay-1">
      <div class="stat-card card-modern hover-lift shadow-colored">
        <i class="fas fa-users icon-bounce"></i>
        <h3>1,234</h3>
        <p>Total Students</p>
      </div>
    </div>
    <!-- More cards with delay-2, delay-3, etc. -->
  </div>
</div>
```

### Login Page
```html
<div class="login-container animate-bounce-in">
  <form class="login-form card-modern shadow-strong">
    <h2 class="text-gradient">Welcome Back</h2>
    <input type="text" class="form-control">
    <button class="btn btn-modern hover-glow">Login</button>
  </form>
</div>
```

### Table/List View
```html
<table class="table animate-fade-in-up">
  <tr class="animate-fade-in-left animate-delay-1">...</tr>
  <tr class="animate-fade-in-left animate-delay-2">...</tr>
  <tr class="animate-fade-in-left animate-delay-3">...</tr>
</table>
```

## Quick Copy-Paste Templates

### Modern Card
```html
<div class="card border-0 shadow-soft card-modern hover-lift animate-fade-in-up">
  <div class="card-body p-4">
    <h3 class="mb-3">
      <span class="icon-bounce"><i class="fas fa-icon"></i></span>
      Card Title
    </h3>
    <p>Card content goes here</p>
  </div>
</div>
```

### CTA Button
```html
<a href="#" class="btn btn-modern hover-glow animate-bounce-in">
  Get Started
</a>
```

### Info Section
```html
<section class="info-section animate-fade-in-up">
  <div class="container">
    <h2 class="text-gradient mb-4">Section Title</h2>
    <p class="lead">Section description</p>
  </div>
</section>
```

---

**Happy Animating! 🎉**
