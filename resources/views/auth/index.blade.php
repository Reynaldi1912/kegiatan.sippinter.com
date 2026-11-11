<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />

  <title>Login to system | Kegiatan SIPPINTER</title>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('sneats/assets/img/favicon/favicon.ico') }}" />

  <!-- Fonts: Plus Jakarta Sans / Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet" />

  <!-- Boxicons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />

  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="{{ asset('sneats/assets/vendor/css/core.css') }}" />

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
      background: radial-gradient(ellipse at center, #424f92 0%, #35407d 55%, #252e5c 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      color: #ffffff;
      overflow-x: hidden;
    }

    /* Outer Container */
    .login-container-card {
      width: 100%;
      max-width: 980px;
      min-height: 560px;
      background: #0d1326;
      border-radius: 28px;
      box-shadow: 
        0 35px 80px -15px rgba(8, 12, 30, 0.75),
        0 15px 35px -10px rgba(0, 0, 0, 0.5),
        0 0 0 1px rgba(255, 255, 255, 0.04);
      display: flex;
      overflow: hidden;
      position: relative;
    }

    /* Left Form Side */
    .login-form-side {
      flex: 1 1 46%;
      max-width: 46%;
      background: #0d1326;
      padding: 3.5rem 3.5rem 3.25rem 3.5rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      z-index: 5;
    }

    /* Top Back Arrow */
    .btn-back-nav {
      width: 32px;
      height: 32px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #8b9bb4;
      text-decoration: none;
      font-size: 1.4rem;
      transition: all 0.2s ease;
      margin-bottom: 2rem;
      cursor: pointer;
    }

    .btn-back-nav:hover {
      color: #ffffff;
      transform: translateX(-3px);
    }

    /* Brand Pill (Subtle BOK identifier) */
    .bok-brand-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: #38bdf8;
      background: rgba(56, 189, 248, 0.1);
      border: 1px solid rgba(56, 189, 248, 0.2);
      padding: 0.28rem 0.75rem;
      border-radius: 20px;
      margin-bottom: 1rem;
      width: fit-content;
    }

    /* Heading & Description */
    .login-title {
      font-size: 2.05rem;
      font-weight: 700;
      color: #ffffff;
      line-height: 1.2;
      letter-spacing: -0.025em;
      margin-bottom: 0.65rem;
    }

    .login-subtitle {
      color: #8c9cb6;
      font-size: 0.88rem;
      line-height: 1.5;
      margin-bottom: 2.25rem;
      font-weight: 400;
    }

    .login-subtitle a {
      color: #38bdf8;
      text-decoration: none;
      transition: color 0.2s ease;
      font-weight: 500;
    }

    .login-subtitle a:hover {
      color: #7dd3fc;
      text-decoration: underline;
    }

    /* Minimalist Underline Input Form */
    .form-group-minimal {
      margin-bottom: 1.75rem;
      position: relative;
    }

    .form-group-minimal label {
      display: block;
      color: #8c9cb6;
      font-size: 0.88rem;
      font-weight: 400;
      margin-bottom: 0.2rem;
      transition: color 0.2s ease;
    }

    .input-line-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .form-control-minimal {
      width: 100%;
      background-color: transparent !important;
      border: none !important;
      border-bottom: 1.5px solid #28334e !important;
      border-radius: 0 !important;
      color: #ffffff !important;
      -webkit-text-fill-color: #ffffff !important;
      caret-color: #38bdf8 !important;
      font-size: 0.95rem;
      font-weight: 500;
      padding: 0.5rem 0;
      outline: none !important;
      box-shadow: none !important;
      transition: border-color 0.25s ease, box-shadow 0.25s ease;
    }

    .form-control-minimal::placeholder {
      color: #4b5876 !important;
      -webkit-text-fill-color: #4b5876 !important;
      font-size: 0.88rem;
    }

    .form-control-minimal:focus,
    .form-control-minimal:hover,
    .form-control-minimal:active {
      background-color: transparent !important;
      color: #ffffff !important;
      -webkit-text-fill-color: #ffffff !important;
      border-bottom-color: #6366f1 !important;
      box-shadow: 0 1px 0 0 #6366f1 !important;
    }

    .form-control-minimal:-webkit-autofill,
    .form-control-minimal:-webkit-autofill:hover,
    .form-control-minimal:-webkit-autofill:focus,
    .form-control-minimal:-webkit-autofill:active {
      -webkit-text-fill-color: #ffffff !important;
      color: #ffffff !important;
      caret-color: #38bdf8 !important;
      -webkit-box-shadow: 0 0 0 1000px #0d1326 inset, 0 1px 0 0 #6366f1 !important;
      box-shadow: 0 0 0 1000px #0d1326 inset, 0 1px 0 0 #6366f1 !important;
      transition: background-color 9999s ease-in-out 0s;
    }

    .form-control-minimal::selection {
      background: rgba(56, 189, 248, 0.35);
      color: #ffffff;
      -webkit-text-fill-color: #ffffff;
    }

    .form-group-minimal:focus-within label {
      color: #38bdf8;
    }

    /* Toggle Password Icon */
    .btn-toggle-eye {
      position: absolute;
      right: 0;
      bottom: 8px;
      background: transparent;
      border: none;
      color: #6b7a99;
      cursor: pointer;
      font-size: 1.25rem;
      padding: 0;
      display: flex;
      align-items: center;
      transition: color 0.2s ease;
    }

    .btn-toggle-eye:hover {
      color: #ffffff;
    }

    /* Remember Me Checkbox */
    .remember-wrapper {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      margin-top: 1.5rem;
      margin-bottom: 2.25rem;
      user-select: none;
    }

    .custom-checkbox-dark {
      appearance: none;
      -webkit-appearance: none;
      width: 16px;
      height: 16px;
      background: transparent;
      border: 1.5px solid #4a597a;
      border-radius: 3px;
      cursor: pointer;
      position: relative;
      outline: none;
      margin: 0;
      transition: all 0.2s ease;
    }

    .custom-checkbox-dark:checked {
      background: #6366f1;
      border-color: #6366f1;
    }

    .custom-checkbox-dark:checked::after {
      content: '';
      position: absolute;
      width: 4px;
      height: 8px;
      border: solid white;
      border-width: 0 2px 2px 0;
      top: 1px;
      left: 4.5px;
      transform: rotate(45deg);
    }

    .remember-label {
      color: #8c9cb6;
      font-size: 0.86rem;
      cursor: pointer;
      font-weight: 400;
    }

    /* Pill Gradient Button (Exact match) */
    .btn-pill-submit {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      min-width: 145px;
      padding: 0.68rem 2.25rem;
      background: linear-gradient(90deg, #5b35ee 0%, #703bf7 50%, #8734f7 100%);
      color: #ffffff !important;
      border: none;
      border-radius: 50px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 6px 20px -3px rgba(112, 59, 247, 0.45);
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      text-decoration: none;
    }

    .btn-pill-submit:hover {
      background: linear-gradient(90deg, #502be3 0%, #6630f0 50%, #7e26f3 100%);
      box-shadow: 0 10px 28px -3px rgba(112, 59, 247, 0.65);
      transform: translateY(-1.5px);
    }

    .btn-pill-submit:active {
      transform: translateY(0);
    }

    .btn-pill-submit:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }

    /* =========================================================================
       RIGHT SIDE: EXACT WAVE SHAPE WITH LIQUID FLOWING ANIMATION
       RIGHT SIDE: 100% SHARP HD IMAGE WITH HARDWARE-ACCELERATED FLOWING MOTION
       NO BLUR FILTER - 100% CRYSTAL CLEAR DETAIL & CRISP EDGES
       ========================================================================= */
    .login-wave-side {
      flex: 1 1 54%;
      max-width: 54%;
      background: #090e1f;
      position: relative;
      overflow: hidden;
      perspective: 1000px;
    }

    /* 1. HD Sharp Image with Gentle Organic Silk Undulation */
    .wave-image-flow,
    .wave-image-sharp {
      position: absolute;
      top: -15px;
      left: -15px;
      right: -15px;
      bottom: -15px;
      background-image: url("{{ asset('assets/img/auth-wave-bg.jpg') }}");
      background-size: cover;
      background-position: center center;
      background-repeat: no-repeat;
      filter: url(#fluidWaveDisplacement);
      transform-origin: center center;
      animation: silkUndulate 12s cubic-bezier(0.45, 0.05, 0.55, 0.95) infinite alternate;
      will-change: transform;
      z-index: 1;
    }

    @keyframes silkUndulate {
      0% {
        transform: scale(1.02) translate3d(0px, 0px, 0px) rotate(0deg);
      }
      35% {
        transform: scale(1.05) translate3d(-10px, -6px, 0px) rotate(0.4deg);
      }
      70% {
        transform: scale(1.04) translate3d(8px, 5px, 0px) rotate(-0.35deg);
      }
      100% {
        transform: scale(1.03) translate3d(-4px, 4px, 0px) rotate(0.2deg);
      }
    }

    /* 2. Flowing Neon Energy Beam Traveling Along the Curves */
    .wave-shimmer-flow,
    .wave-energy-stream {
      position: absolute;
      inset: -50px;
      background: linear-gradient(
        115deg,
        transparent 20%,
        rgba(56, 189, 248, 0.0) 35%,
        rgba(56, 189, 248, 0.28) 46%,
        rgba(236, 72, 153, 0.38) 52%,
        rgba(168, 85, 247, 0.28) 58%,
        transparent 75%
      );
      background-size: 300% 300%;
      mix-blend-mode: screen;
      animation: streamFlow 6s ease-in-out infinite alternate;
      pointer-events: none;
      z-index: 2;
    }

    @keyframes streamFlow {
      0% {
        background-position: 100% 0%;
        opacity: 0.35;
      }
      50% {
        opacity: 0.85;
      }
      100% {
        background-position: 0% 100%;
        opacity: 0.45;
      }
    }

    /* 3. Traveling Caustic Luminous Wave Glow */
    .wave-pulse-flow,
    .wave-glow-caustic {
      position: absolute;
      inset: 0;
      background: radial-gradient(
        ellipse at 55% 48%,
        rgba(168, 85, 247, 0.3) 0%,
        rgba(56, 189, 248, 0.18) 35%,
        transparent 65%
      );
      mix-blend-mode: color-dodge;
      animation: causticPulse 4.5s ease-in-out infinite alternate;
      pointer-events: none;
      z-index: 3;
    }

    @keyframes causticPulse {
      0% {
        transform: scale(0.92) translate3d(-5px, -3px, 0);
        opacity: 0.5;
      }
      100% {
        transform: scale(1.1) translate3d(5px, 3px, 0);
        opacity: 0.9;
      }
    }

    /* 4. Canvas Floating Stardust / Glowing Sparkles */
    #stardustCanvas {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 3;
      z-index: 4;
    }

    /* 5. Left Seamless Blend Mask */
    .wave-left-blend {
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      width: 35%;
      background: linear-gradient(to right, #0d1326 0%, rgba(13, 19, 38, 0.7) 30%, transparent 100%);
      width: 32%;
      background: linear-gradient(to right, #0d1326 0%, rgba(13, 19, 38, 0.6) 35%, transparent 100%);
      pointer-events: none;
      z-index: 4;
      z-index: 5;
    }

    /* Error alert */
    .alert-dark-custom {
      background: rgba(239, 68, 68, 0.12);
      border: 1px solid rgba(239, 68, 68, 0.3);
      color: #fca5a5;
      border-radius: 10px;
      font-size: 0.82rem;
      padding: 0.65rem 0.9rem;
      margin-bottom: 1.25rem;
    }

    .alert-success-custom {
      background: rgba(16, 185, 129, 0.12);
      border: 1px solid rgba(16, 185, 129, 0.3);
      color: #6ee7b7;
      border-radius: 10px;
      font-size: 0.82rem;
      padding: 0.65rem 0.9rem;
      margin-bottom: 1.25rem;
    }

    /* Responsive */
    @media (max-width: 900px) {
      .login-container-card {
        flex-direction: column;
        max-width: 480px;
        min-height: auto;
      }
      .login-form-side {
        max-width: 100%;
        flex: 1 1 100%;
        padding: 2.5rem 2rem;
      }
      .login-wave-side {
        display: none;
      }
    }
  </style>
</head>

<body>

  <!-- SVG Fluid Wave Displacement Filter Definition -->
  <svg width="0" height="0" style="position: absolute; pointer-events: none;">
    <defs>
      <filter id="fluidWaveDisplacement" x="-10%" y="-10%" width="120%" height="120%">
        <!-- Animated fluid turbulence with continuous wave offset -->
        <feTurbulence
          id="waveTurbulence"
          type="fractalNoise"
          baseFrequency="0.012 0.008"
          numOctaves="2"
          result="waveNoise"
          seed="2" />
        <feDisplacementMap
          id="waveDisplacement"
          in="SourceGraphic"
          in2="waveNoise"
          scale="16"
          xChannelSelector="R"
          yChannelSelector="G" />
      </filter>
    </defs>
  </svg>

  <div class="login-container-card">
    <!-- Left Side: Login Form -->
    <div class="login-form-side">
      <div>
        <!-- Top Back Icon Navigation -->
        <a href="{{ url('/') }}" class="btn-back-nav" title="Kembali">
          <i class="bx bx-chevron-left"></i>
        </a>

        <!-- Subtle KEGIATAN SIPPINTER Badge -->
        <div>
          <span class="bok-brand-badge">
            <i class="bx bxs-shield-plus"></i> KEGIATAN • SIPPINTER
          </span>
        </div>

        <!-- Main Heading (Exact Match) -->
        <h1 class="login-title">Login to system</h1>

        <!-- Subtitle & Registration Link (Exact Match) -->
        <p class="login-subtitle">
          Please enter your login information<br />
          or <a href="{{ Route::has('register') ? route('register') : 'javascript:void(0);' }}" id="linkRegister">click here</a> to registration
        </p>

        <!-- Flash Alert -->
        @if(session('error'))
          <div class="alert-dark-custom">
            <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
          </div>
        @endif

        @if(session('success'))
          <div class="alert-success-custom">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
          </div>
        @endif

        @if($errors->any())
          <div class="alert-dark-custom">
            <i class="bx bx-shield-x me-1"></i> {{ $errors->first() }}
          </div>
        @endif

        <!-- Form -->
        <form id="formAuthentication" method="POST" action="{{ route('login') }}">
          @csrf

          <!-- Username Input (Exact Match Underline Style) -->
          <div class="form-group-minimal">
            <label for="username">Username</label>
            <div class="input-line-wrapper">
              <input
                type="text"
                class="form-control-minimal"
                id="username"
                name="username"
                value="{{ old('username') ?? old('email') }}"
                autocomplete="username"
                spellcheck="false"
                autofocus
                required />
            </div>
          </div>

          <!-- Password Input (Exact Match Underline Style) -->
          <div class="form-group-minimal">
            <label for="password">Password</label>
            <div class="input-line-wrapper">
              <input
                type="password"
                class="form-control-minimal"
                id="password"
                name="password"
                autocomplete="current-password"
                required />
              <button type="button" class="btn-toggle-eye" id="btnTogglePassword" title="Tampilkan kata sandi">
                <i class="bx bx-hide" id="passwordEyeIcon"></i>
              </button>
            </div>
          </div>

          <!-- Remember Me (Exact Match) -->
          <div class="remember-wrapper">
            <input
              type="checkbox"
              id="remember"
              name="remember"
              class="custom-checkbox-dark"
              {{ old('remember') ? 'checked' : '' }} />
            <label for="remember" class="remember-label">Remember me</label>
          </div>

          <!-- Button (Exact Match Rounded Pill Gradient) -->
          <div>
            <button type="submit" class="btn-pill-submit" id="btnSubmit">
              <span class="spinner-border spinner-border-sm d-none" id="btnSpinner" role="status" aria-hidden="true"></span>
              <span id="btnText">Log In</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Right Side: Exact Shape Animated Flowing Waves -->
    <!-- Right Side: 100% Crisp Sharp Wave with Fluid Light Flow -->
    <div class="login-wave-side" id="waveSideContainer">
      <!-- 1. The original wave image with live liquid displacement -->
      <div class="wave-image-flow" id="waveImageFlow"></div>
      <!-- 1. Crystal Sharp HD Image with 3D Undulation (Zero blur) -->
      <div class="wave-image-sharp" id="waveImageSharp"></div>

      <!-- 2. Light stream shimmer traveling along the ribbons -->
      <div class="wave-shimmer-flow"></div>
      <!-- 2. Traveling Luminous Neon Energy Stream -->
      <div class="wave-energy-stream"></div>

      <!-- 3. Ambient neon glow pulsation -->
      <div class="wave-pulse-flow"></div>
      <!-- 3. Caustic Pulse Glow -->
      <div class="wave-glow-caustic"></div>

      <!-- 4. Floating stardust particles -->
      <!-- 4. Floating Stardust Sparkles -->
      <canvas id="stardustCanvas"></canvas>

      <!-- 5. Seamless left blend -->
      <!-- 5. Seamless Left Blend -->
      <div class="wave-left-blend"></div>
    </div>
  </div>

  <!-- jQuery & Helpers -->
  <script src="{{ asset('sneats/assets/vendor/libs/jquery/jquery.js') }}"></script>
  <script>
    $(document).ready(function () {
      // Toggle Password Visibility
      $('#btnTogglePassword').on('click', function (e) {
        e.preventDefault();
        const input = $('#password');
        const icon = $('#passwordEyeIcon');

        if (input.attr('type') === 'password') {
          input.attr('type', 'text');
          icon.removeClass('bx-hide').addClass('bx-show text-info');
        } else {
          input.attr('type', 'password');
          icon.removeClass('bx-show text-info').addClass('bx-hide');
        }
      });

      // Submit State
      $('#formAuthentication').on('submit', function () {
        $('#btnSubmit').prop('disabled', true);
        $('#btnSpinner').removeClass('d-none');
        $('#btnText').text('Signing in...');
      });
    });

    (function () {
      const turb = document.getElementById('waveTurbulence');
      const disp = document.getElementById('waveDisplacement');
      const waveSide = document.getElementById('waveSideContainer');
      const waveImg = document.getElementById('waveImageSharp') || document.getElementById('waveImageFlow');
      const canvas = document.getElementById('stardustCanvas');

      let time = 0;
      let mouseX = 0, mouseY = 0;
      let targetMouseX = 0, targetMouseY = 0;

      if (waveSide) {
        waveSide.addEventListener('mousemove', (e) => {
          const rect = waveSide.getBoundingClientRect();
          targetMouseX = ((e.clientX - rect.left) / rect.width - 0.5) * 16;
          targetMouseY = ((e.clientY - rect.top) / rect.height - 0.5) * 12;
        });

        waveSide.addEventListener('mouseleave', () => {
          targetMouseX = 0;
          targetMouseY = 0;
        });
      }

      let ctx, cw, ch;
      const particles = [];
      const particleCount = 40;

      if (canvas && waveSide) {
        ctx = canvas.getContext('2d');
        function resizeCanvas() {
          const dpr = Math.min(window.devicePixelRatio || 1, 2);
          cw = waveSide.clientWidth;
          ch = waveSide.clientHeight;
          canvas.width = cw * dpr;
          canvas.height = ch * dpr;
          ctx.scale(dpr, dpr);
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        for (let i = 0; i < particleCount; i++) {
          particles.push({
            x: Math.random(),
            y: Math.random(),
            radius: Math.random() * 1.6 + 0.6,
            speedY: -(Math.random() * 0.0006 + 0.0002),
            speedX: (Math.random() - 0.5) * 0.0003,
            alpha: Math.random() * 0.7 + 0.2,
            pulseSpeed: Math.random() * 0.04 + 0.02,
            phase: Math.random() * Math.PI * 2,
            color: ['#38bdf8', '#f43f5e', '#c084fc', '#ffffff'][Math.floor(Math.random() * 4)]
          });
        }
      }

      function animate() {
        time += 0.01;

        // 1. Fluid Wave Undulation on the exact image via SVG Filter
        if (turb) {
          // Flowing horizontal and vertical frequencies create continuous organic silk waves
          const freqX = 0.010 + Math.sin(time * 0.8) * 0.003;
          const freqY = 0.007 + Math.cos(time * 0.6) * 0.002;
          turb.setAttribute('baseFrequency', `${freqX.toFixed(5)} ${freqY.toFixed(5)}`);
        }

        if (disp) {
          // Subtle breathing amplitude
          const scale = 14 + Math.sin(time * 1.2) * 5;
          disp.setAttribute('scale', scale.toFixed(2));
        }

        // 2. Parallax Response on the Image Layer
        // Smooth Parallax tilt on image (Hardware-accelerated translate3d, completely sharp)
        if (waveImg) {
          mouseX += (targetMouseX - mouseX) * 0.06;
          mouseY += (targetMouseY - mouseY) * 0.06;
          waveImg.style.transform = `scale(1.04) translate(${mouseX.toFixed(2)}px, ${mouseY.toFixed(2)}px)`;
          mouseX += (targetMouseX - mouseX) * 0.05;
          mouseY += (targetMouseY - mouseY) * 0.05;
          waveImg.style.marginLeft = `${mouseX.toFixed(2)}px`;
          waveImg.style.marginTop = `${mouseY.toFixed(2)}px`;
        }

        // 3. Stardust particles floating loop
        // Stardust particles loop
        if (ctx && cw && ch) {
          ctx.clearRect(0, 0, cw, ch);

          for (let i = 0; i < particles.length; i++) {
            const p = particles[i];
            p.y += p.speedY;
            p.x += p.speedX;

            if (p.y < -0.05) p.y = 1.05;
            if (p.x < 0) p.x = 1;
            if (p.x > 1) p.x = 0;

            const px = p.x * cw;
            const py = p.y * ch;
            const currentAlpha = Math.max(0, p.alpha + Math.sin(time * 8 * p.pulseSpeed + p.phase) * 0.25);
            const currentAlpha = Math.max(0, p.alpha + Math.sin(time * 6 * p.pulseSpeed + p.phase) * 0.25);

            ctx.save();
            ctx.beginPath();
            ctx.arc(px, py, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = p.color;
            ctx.globalAlpha = currentAlpha;
            ctx.shadowColor = p.color;
            ctx.shadowBlur = 8;
            ctx.shadowBlur = 6;
            ctx.fill();
            ctx.restore();
          }
        }

        requestAnimationFrame(animate);
      }

      requestAnimationFrame(animate);
    })();
  </script>
</body>
</html>
