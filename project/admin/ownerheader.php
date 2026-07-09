<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management System - Navigation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans min-h-screen overflow-hidden text-slate-900">

    <div class="flex flex-col md:flex-row h-screen overflow-hidden">

        <aside class="fixed inset-y-0 left-0 w-64 md:relative md:w-64 bg-slate-950 text-slate-200 flex flex-col p-4 border-r border-slate-800 z-50 flex-shrink-0 h-full font-sans transform -translate-x-full transition-transform duration-300 ease-in-out md:translate-x-0">
            
            <div class="flex items-center justify-between md:justify-start gap-3 border-b-2 border-slate-800 pb-5 pt-2">
                <div class="flex items-center gap-3">
                    
                    <button onclick="toggleMobileMenu()" class="md:hidden text-slate-400 hover:text-white text-lg font-sans mr-1 select-none cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                    <div class="w-2.5 h-6 bg-blue-500 hidden md:block"></div>
                    <div>
                        <h2 class="font-bold text-base tracking-wider uppercase text-white font-serif">PROPERTY ADMIN</h2>
                        <span class="text-[11px] uppercase tracking-widest text-slate-400 block font-sans">Management Portal</span>    
                    </div>
                </div>
            </div>
            
            <nav class="flex flex-col gap-1 mt-6 flex-1 text-xs font-semibold uppercase tracking-wider overflow-y-auto">
                
                <a href="owner_dashboard.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-900  text-white rounded-none transition-all">
                    <i class="fa-solid fa-tachometer-alt text-slate-500 w-4 text-center text-sm"></i> 
                    <span>Dashboard</span>
                </a>
                
                <a href="hostellist.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-900 border-l-4 border-transparent rounded-none transition-all">
                    <i class="fa-solid fa-building text-slate-500 w-4 text-center text-sm"></i> 
                    <span>My Properties</span>
                </a>
                
                <a href="owner_contract.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-900 border-l-4 border-transparent rounded-none transition-all">
                    <i class="fa-solid fa-file-contract text-slate-500 w-4 text-center text-sm"></i> 
                    <span>Contract</span>
                </a>
                
                <a href="view_renter.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-900 border-l-4 border-transparent rounded-none transition-all">
                    <i class="fa-solid fa-users text-slate-500 w-4 text-center text-sm"></i> 
                    <span>Renters (အိမ်ငှားများ)</span>
                </a>
                
                <a href="admin_payment.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-900 border-l-4 border-transparent rounded-none transition-all">
                    <i class="fa-solid fa-credit-card text-slate-500 w-4 text-center text-sm"></i> 
                    <span>Payments (ငွေစာရင်း)</span>
                </a>
                
                <a href="add_paymentmethod.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-900 border-l-4 border-transparent rounded-none transition-all">
                    <i class="fa-solid fa-sliders text-slate-500 w-4 text-center text-sm"></i> 
                    <span>Payment Setup</span>
                </a>
            </nav>

            <div class="border-t border-slate-800 pt-4 items-center justify-between text-xs tracking-wider font-semibold uppercase mt-auto">
                <a href="../renter/renterhomepage.php" class="w-full flex items-center justify-center gap-2 text-rose-400 hover:text-white hover:bg-rose-950/40 border border-slate-800 hover:border-rose-900 py-2 transition-all">
                    <i class="fa-solid fa-sign-out-alt"></i> Log Out
                </a>
            </div>
           
        </aside>

        <main class="flex-1 bg-slate-50 overflow-y-auto">
            </main>

    </div>
        
</body>
</html>