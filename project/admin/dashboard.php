<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentalHub - Classic Property Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fbfaf7] text-stone-900 antialiased font-sans">

  <!-- Navbar -->
  <nav class="bg-white border-b-2 border-stone-200">
    <div class="max-w-6xl mx-auto px-6">
      <div class="flex justify-between h-20 items-center">
        <!-- Logo -->
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 bg-blue-900 border border-amber-600 flex items-center justify-center text-amber-100 font-serif font-bold text-xl">R</div>
          <span class="text-2xl font-serif font-bold tracking-tight text-stone-900">Rental<span class="text-blue-900 italic font-normal">Hub</span></span>
        </div>
        <!-- Navigation Link/Buttons -->
        <div class="flex items-center gap-6">
          <a href="register.php" class="text-sm font-medium text-stone-600 hover:text-blue-900 hover:underline transition-all">Create an Account</a>
          <a href="login.php" class="px-5 py-2 text-sm font-serif font-medium text-amber-100 bg-blue-900 hover:bg-blue-950 border border-amber-700 shadow-sm transition-all">Sign In</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <header class="bg-white border-b border-stone-200 py-20 text-center">
    <div class="max-w-4xl mx-auto px-6">
      <span class="inline-block uppercase tracking-widest text-xs font-semibold text-amber-800 border-b border-amber-800 pb-1 mb-6">Established Property Management</span>
      <h1 class="text-4xl sm:text-5xl font-serif font-normal text-stone-900 max-w-3xl mx-auto leading-tight">
        One platform. Perfect harmony for <span class="italic text-blue-900">Renters</span> & <span class="italic text-blue-900">Owners</span>.
      </h1>
      <div class="w-16 h-0.5 bg-amber-700 mx-auto mt-6 mb-6"></div>
      <p class="text-base font-serif italic text-stone-600 max-w-2xl mx-auto leading-relaxed">
        Whether you are searching for your next exceptional residence or managing a distinguished portfolio of rental properties, we provide the ultimate framework.
      </p>
    </div>
  </header>

  <!-- Dual Dashboard Portals -->
  <main id="portal" class="max-w-6xl mx-auto px-6 py-16">
    <div class="grid md:grid-cols-2 gap-10">
      
      <!-- Renter Portal Card -->
      <div class="bg-white border border-stone-300 p-8 flex flex-col justify-between shadow-sm">
        <div>
          <div class="flex items-center justify-between border-b border-stone-200 pb-4 mb-6">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-stone-100 text-blue-900 border border-stone-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
              </div>
              <h2 class="text-xl font-serif font-bold text-stone-900">The Tenant Portal</h2>
            </div>
            <span class="text-xs uppercase tracking-wider text-stone-500 font-medium">For Residents</span>
          </div>
          <p class="text-stone-600 text-sm leading-relaxed mb-6">
            Browse refined listings, submit formal leasing applications, schedule automated rent payments, and log maintenance requests directly to your property manager.
          </p>
        </div>
        <a href="login.php?role=renter" class="block text-center px-4 py-2.5 text-sm font-serif font-medium border border-blue-900 text-blue-900 hover:bg-blue-900 hover:text-white transition-all">
          Enter Renter Portal &rarr;
        </a>
      </div>

      <!-- Owner Portal Card -->
      <div class="bg-white border border-stone-300 p-8 flex flex-col justify-between shadow-sm">
        <div>
          <div class="flex items-center justify-between border-b border-stone-200 pb-4 mb-6">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-stone-100 text-amber-800 border border-stone-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <h2 class="text-xl font-serif font-bold text-stone-900">The Landlord Portal</h2>
            </div>
            <span class="text-xs uppercase tracking-wider text-stone-500 font-medium">For Proprietors</span>
          </div>
          <p class="text-stone-600 text-sm leading-relaxed mb-6">
            Oversee property portfolios, track vacancy metrics, access detailed balance sheets, distribute leases, and review legal documentation seamlessly.
          </p>
        </div>
        <a href="login.php?role=owner" class="block text-center px-4 py-2.5 text-sm font-serif font-medium bg-stone-900 text-amber-100 border border-stone-900 hover:bg-stone-950 transition-all">
          Enter Owner Portal &rarr;
        </a>
      </div>

    </div>
  </main>

  <!-- Quick Stats Footer Row -->
  <footer class="bg-stone-900 text-stone-300 border-t-2 border-amber-700 py-12">
    <div class="max-w-6xl mx-auto px-6">
      <div class="grid grid-cols-3 gap-6 text-center">
        <div>
          <p class="text-2xl sm:text-3xl font-serif font-normal text-amber-100">45,000+</p>
          <p class="text-xs uppercase tracking-widest text-stone-400 mt-1">Active Listings</p>
        </div>
        <div class="border-x border-stone-700">
          <p class="text-2xl sm:text-3xl font-serif font-normal text-amber-100">99.4%</p>
          <p class="text-xs uppercase tracking-widest text-stone-400 mt-1">On-Time Settlements</p>
        </div>
        <div>
          <p class="text-2xl sm:text-3xl font-serif font-normal text-amber-100">&lt; 2 Hours</p>
          <p class="text-xs uppercase tracking-widest text-stone-400 mt-1">Avg. Dispatch Resolution</p>
        </div>
      </div>
      <div class="text-center text-xs text-stone-500 mt-10 pt-6 border-t border-stone-800">
        &copy; 2026 RentalHub Platforms Inc. All structural rights reserved.
      </div>
    </div>
  </footer>

</body>
</html>