<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'App' }} - Traffic Tracker</title>

  <!-- Tailwind + DaisyUI (CDN) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />

  <!-- HTMX + Alpine -->
  <script src="https://unpkg.com/htmx.org@1.9.12"></script>
  <script src="https://unpkg.com/alpinejs@3.x.x" defer></script>

  <!-- Chart.js for analytics -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Theme changer -->
  <script src="https://cdn.jsdelivr.net/npm/theme-change@2.0.2/index.js"></script>

  <style>
    /* Show moon icon in light theme, hide in dark theme */
    [data-theme="light"] .dark-icon { display: block; }
    [data-theme="dark"] .dark-icon { display: none; }
    
    /* Show sun icon in dark theme, hide in light theme */
    [data-theme="light"] .light-icon { display: none; }
    [data-theme="dark"] .light-icon { display: block; }
    
    /* Default state (no theme set) - show moon icon */
    html:not([data-theme]) .dark-icon { display: block; }
    html:not([data-theme]) .light-icon { display: none; }
    
    /* Mobile improvements */
    @media (max-width: 640px) {
      .container {
        max-width: 100%;
        padding-left: 1rem;
        padding-right: 1rem;
      }
      
      /* Prevent horizontal scroll */
      body {
        overflow-x: hidden;
      }
      
      /* Better touch targets */
      .btn {
        min-height: 44px;
      }
      
      /* Responsive text */
      .text-xl {
        font-size: 1.125rem;
      }
    }
    
    /* Smooth transitions for responsive elements */
    .navbar, .btn, .dropdown-content {
      transition: all 0.2s ease-in-out;
    }
  </style>
</head>
<body class="bg-base-200 min-h-screen">
  <header class="navbar bg-base-100 shadow items-center px-4">
    @if(isset($user) && $user)
    <!-- Mobile Navigation Menu (Left Side) -->
    <div class="dropdown dropdown-start lg:hidden">
      <div tabindex="0" role="button" class="btn btn-ghost btn-square">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </div>
      <ul tabindex="0" class="menu dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow-lg border border-base-300">
        <li><a href="/" class="flex items-center gap-3 py-3 px-4 min-h-[48px]">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          Home
        </a></li>
        <li><a href="/dashboard" class="flex items-center gap-3 py-3 px-4 min-h-[48px]">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          Dashboard
        </a></li>
        <li><a href="/websites" class="flex items-center gap-3 py-3 px-4 min-h-[48px]">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9-9a9 9 0 00-9 9m0 0a9 9 0 919-9"></path>
          </svg>
          Websites
        </a></li>
      </ul>
    </div>
    @endif

    <!-- Title and Desktop Navigation (Left Side) -->
    <div class="flex-1 flex items-center gap-2">
      <a href="/" class="btn btn-ghost text-lg sm:text-xl normal-case flex items-center gap-2">
        <svg class="w-6 h-6 hidden sm:block text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        Traffic Tracker
      </a>
      
      @if(isset($user) && $user)
      <!-- Desktop Navigation -->
      <div class="hidden lg:flex gap-2 ml-4">
        <a href="/" class="btn btn-ghost">Home</a>
        <a href="/dashboard" class="btn btn-ghost">Dashboard</a>
        <a href="/websites" class="btn btn-ghost">Websites</a>
      </div>
      @endif
    </div>
    
    <div class="flex-none gap-2">
      @if(isset($user) && $user)
        <div class="dropdown dropdown-end">
          <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar hover:bg-base-200 transition-colors">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-primary text-primary-content flex items-center justify-center shadow-lg">
              <span class="text-xs sm:text-sm font-bold leading-none flex items-center justify-center h-full">{{ strtoupper(substr($user['name'], 0, 1)) }}</span>
            </div>
          </div>
          <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-2xl z-[1] mt-3 w-64 p-3 shadow-2xl border border-base-300 right-0">
            <li class="px-3 py-2 mb-2">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-primary text-primary-content flex items-center justify-center">
                  <span class="text-lg font-bold leading-none flex items-center justify-center h-full">{{ strtoupper(substr($user['name'], 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-semibold text-base-content truncate">{{ $user['name'] }}</div>
                  <div class="text-xs text-base-content/70 truncate">{{ $user['email'] }}</div>
                </div>
              </div>
            </li>
            <li><hr class="my-2 border-base-300"></li>
            <li>
              <a href="/dashboard" class="flex items-center gap-3 px-3 py-2 hover:bg-base-200 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span>Dashboard</span>
              </a>
            </li>
            <li>
              <a href="/websites" class="flex items-center gap-3 px-3 py-2 hover:bg-base-200 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9-9a9 9 0 00-9 9m0 0a9 9 0 019-9"></path>
                </svg>
                <span>My Websites</span>
              </a>
            </li>
            <li><hr class="my-2 border-base-300"></li>
            <li>
              <a href="/logout" hx-post="/logout" class="flex items-center gap-3 px-3 py-2 hover:bg-error hover:text-error-content rounded-lg transition-colors text-error">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span>Logout</span>
              </a>
            </li>
          </ul>
        </div>
      @endif
      
      <button class="btn btn-ghost btn-circle btn-sm sm:btn-md" data-toggle-theme="dark,light">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 dark-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
        </svg>
        <svg class="w-5 h-5 sm:w-6 sm:h-6 light-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
      </button>
    </div>
  </header>

  <main id="app" class="container mx-auto p-4 sm:p-6">
    @yield('content')
  </main>

  <div id="flash"></div>

  <script>
  // Global form loading functionality
  document.addEventListener('DOMContentLoaded', function() {
    // Add loading state to all forms with hx-post, hx-put, etc.
    document.addEventListener('htmx:beforeRequest', function(event) {
      const form = event.target.closest('form');
      if (form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
          // Store original text
          submitBtn.dataset.originalText = submitBtn.innerHTML;
          // Add loading state
          submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Loading...';
          submitBtn.disabled = true;
        }
      }
    });

    // Reset button state after request
    document.addEventListener('htmx:afterRequest', function(event) {
      const form = event.target.closest('form');
      if (form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn && submitBtn.dataset.originalText) {
          // Restore original text
          submitBtn.innerHTML = submitBtn.dataset.originalText;
          submitBtn.disabled = false;
        }
      }
    });

    // Also handle regular form submissions (non-HTMX)
    document.addEventListener('submit', function(event) {
      const form = event.target;
      if (form && !form.hasAttribute('hx-post') && !form.hasAttribute('hx-put') && !form.hasAttribute('hx-patch')) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
          setTimeout(() => {
            submitBtn.dataset.originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Loading...';
            submitBtn.disabled = true;
          }, 10);
        }
      }
    });
  });
  </script>
  <script src="//traffic-tracker-t18u.onrender.com/api/tracking-script?key=tk_18e501c80b98cf747262d380321c10c22918e923203a5817f05cd86d"></script>
</body>
</html>
