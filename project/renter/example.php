<!-- Contact Section -->
            <section class="mt-20 border-t border-stone-200 pt-14 max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="space-y-3">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-amber-800">Get In Touch</span>
                        <h3 class="text-xl font-serif text-stone-900">Contact Management</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">လူကြီးမင်းတို့၏ အိမ်၊ ခြံ၊ မြေ နှင့် အဆောင်အခန်းများ ငှားရမ်းခြင်းကိစ္စရပ်များအတွက် ယုံကြည်စိတ်ချစွာ ဆက်သွယ်နိုင်ပါသည်။</p>
                    </div>
                    <div class="bg-white border border-stone-200 p-6 rounded space-y-4 shadow-sm md:col-span-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="space-y-1">
                                <span class="text-stone-400 font-bold block">📍 OFFICE ADDRESS</span>
                                <p class="text-stone-700 font-medium">အမှတ် (၁၂၀)၊ ကမ္ဘာအေးဘုရားလမ်း၊ ဗဟန်းမြို့နယ်၊ ရန်ကုန်မြို့။</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 font-bold block">📞 PHONE & HOTLINE</span>
                                <p class="text-stone-700 font-mono font-medium">+95 9 123 456 789<br>+95 1 234 567</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 block font-bold">✉️ EMAIL SUPPORT</span>
                                <p class="text-blue-900 font-medium underline">support@therentalhub.com</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-stone-400 block font-bold">⏰ WORKING HOURS</span>
                                <p class="text-stone-700 font-medium">Mon - Sat | 9:00 AM - 5:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="bg-stone-900 text-stone-400 text-xs border-t border-stone-800 mt-auto">
        <div class="max-w-6xl mx-auto px-6 py-10">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-6 border-b border-stone-800 pb-8">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 bg-amber-700 flex items-center justify-center text-stone-100 font-serif font-bold text-base">R</div>
                    <span class="text-lg font-serif font-bold tracking-tight text-white">Rental<span class="text-amber-600 italic font-normal">Hub</span></span>
                </div>
                <div class="flex flex-wrap justify-center gap-6 text-[11px] font-medium tracking-wide">
                    <a href="renterhomepage.php" class="hover:text-white transition-colors">Home</a>
                    <a href="#apartmentCardSection" class="hover:text-white transition-colors">Apartments</a>
                    <a href="#hostelCardSection" class="hover:text-white transition-colors">Hostels</a>
                    <a href="../auth/login.php?redirect=homepage" class="hover:text-white transition-colors">Admin Panel</a>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 text-[11px] text-stone-500 font-serif">
                <p>&copy; <?= date('Y'); ?> The Rental Hub Co., Ltd. All rights reserved.</p>
                <p class="italic">Crafted for Quality Property Environments.</p>
            </div>
        </div>
    </footer>



    <header class="relative bg-gradient-to-b from-[#fcfbf9] to-[#f4f1ea] rounded-3xl border border-stone-300/80 py-16 sm:py-20 text-center overflow-hidden font-serif shadow-2xl transition-all duration-500">
    <div class="absolute inset-0 z-0 overflow-hidden">
        <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?q=80&w=1600&auto=format&fit=crop" 
             alt="Modern Luxury Condo Interior" 
             class="w-full h-full object-cover opacity-40 transform scale-105 transition-transform duration-1000 ease-out hover:scale-110">
        <div class="absolute inset-0 bg-gradient-to-t from-[#f4f1ea] via-[#f4f1ea]/40 to-[#fcfbf9]/80"></div>
    </div>

    <div class="relative z-10 max-w-3xl mx-auto px-6">
        <div class="inline-flex items-center gap-2.5 bg-white/90 border border-emerald-600/30 px-4 py-1.5 rounded-full mb-6 shadow-md backdrop-blur-md">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-600"></span>
            </span>
            <span class="uppercase tracking-widest text-[10px] font-sans font-bold text-emerald-950">
               Established Property Management
            </span>
        </div>

       <h1 class="text-3xl sm:text-4xl md:text-5xl font-serif font-semibold text-stone-900 max-w-2xl mx-auto leading-snug title-classic tracking-tight">
            One platform. Perfect harmony for <span class="italic font-normal text-amber-900">Renters</span> &amp; <span class="italic font-normal text-amber-900">Owners</span>.
        </h1>

        <div class="flex items-center justify-center gap-3 my-6">
            <div class="w-12 h-[1px] bg-amber-800/40"></div>
            <div class="h-1.5 w-1.5 rotate-45 border border-amber-800 bg-amber-900"></div>
            <div class="w-12 h-[1px] bg-amber-800/40"></div>
        </div>

        <div class="mt-4 inline-flex flex-wrap items-center justify-center gap-3 bg-white/95 border border-stone-300/80 backdrop-blur-xl px-7 py-3.5 rounded-2xl shadow-xl text-xs font-sans text-stone-800 transform hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center gap-1.5 text-stone-500 font-bold uppercase tracking-wider text-[10px]">
                <i class="fa-solid fa-city text-amber-900"></i>
                <span>Quick Search:</span>
            </div>
            
            <div class="h-4 w-[1px] bg-stone-300 hidden sm:block"></div>

            <button onclick="quickSearch('Yangon')" class="text-stone-900 hover:text-amber-900 font-bold underline underline-offset-4 decoration-amber-800/30 hover:decoration-amber-900 transition-all px-1.5">
                ရန်ကုန်
            </button> 
            <span class="text-stone-300">|</span>
            
            <button onclick="quickSearch('Mandalay')" class="text-stone-900 hover:text-amber-900 font-bold underline underline-offset-4 decoration-amber-800/40 hover:decoration-amber-900 transition-all px-1.5">
                မန္တလေး
            </button> 
            <span class="text-stone-300">|</span>
            
            <button onclick="quickSearch('Naypyidaw')" class="text-stone-900 hover:text-amber-900 font-bold underline underline-offset-4 decoration-amber-800/40 hover:decoration-amber-900 transition-all px-1.5">
                နေပြည်တော်
            </button> 
            <span class="text-stone-300">|</span>

            <button onclick="quickSearch('AVAILABLE')" class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 font-bold px-3 py-1 rounded-lg transition-all shadow-xs">
                <i class="fa-solid fa-building text-[10px]"></i>
                <span>Available</span>
            </button>
        </div>
    </div>
</header>




 <section class="max-w-6xl mx-auto px-6 w-full pt-10 pb-12">
            <div class="bg-[#f5f2eb] rounded-xl shadow-sm overflow-hidden border border-stone-300/60 text-stone-800">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 p-6 lg:p-8 items-center">
                    
                    <div class="lg:col-span-5 space-y-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[9px] uppercase font-bold tracking-wider bg-amber-700/10 text-amber-800 border border-amber-700/20">
                            <i class="fa-solid fa-feather-pointed mr-1 text-amber-700"></i> Elite Core Premium
                        </span>
                        <h2 class="text-2xl lg:text-3xl font-normal leading-tight text-stone-900 title-classic">
                            ယုံကြည်စိတ်ချရသော <br>
                            <span class="text-blue-900 font-semibold italic">အိမ်ရာစီမံခန့်ခွဲမှုဗဟို</span>
                        </h2>
                        <p class="text-stone-600 text-xs leading-relaxed font-light">
                            အဆောင်နှင့် တိုက်ခန်းငှားရမ်းခြင်းလုပ်ငန်းများကို ခေတ်မီစနစ်များဖြင့် ဒစ်ဂျစ်တယ်စနစ်သို့ ပြောင်းလဲလိုက်ပါ။ ပိုင်ရှင်နှင့် အိမ်ငှားကြား စာရွက်စာတမ်းရှုပ်ထွေးမှုများကို ဘေးကင်းလုံခြုံစွာ ဖြေရှင်းပေးပါသည်။
                        </p>
                        
                        <div class="space-y-3 pt-3 border-t border-stone-300/60">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 bg-amber-700/10 text-amber-800 p-1 rounded border border-amber-700/20 text-[9px] shrink-0">
                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[11px] text-stone-800">Auto-Sync Digital Contracts</h4>
                                    <p class="text-[10px] text-stone-500">စာချုပ်သက်တမ်းကုန်ဆုံးပါက အခန်းများကို Available အဖြစ် အလိုအလျောက်ပြောင်းလဲပေးခြင်း။</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 bg-amber-700/10 text-amber-800 p-1 rounded border border-amber-700/20 text-[9px] shrink-0">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[11px] text-stone-800">Polymorphic Core Tracking</h4>
                                    <p class="text-[10px] text-stone-500">တိုက်ခန်းများနှင့် အဆောင်ဒေတာများကို ပေါင်းစည်းထားသော ရလဒ်ထွက်စနစ်ဖြင့် စနစ်တကျပြသပေးခြင်း။</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7 grid grid-cols-12 gap-3 h-[220px] lg:h-[260px]">
                        <div class="col-span-7 relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=80" 
                                 alt="Modern Room Architecture" 
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                            <span class="absolute bottom-2.5 left-2.5 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-2 py-0.5 rounded-sm text-[9px] uppercase tracking-wider font-bold text-amber-300">
                                Premium Spaces
                            </span>
                        </div>
                        <div class="col-span-5 grid grid-rows-2 gap-3">
                            <div class="relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=400&q=80" 
                                     alt="Cozy Interior View" 
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 left-2 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-1.5 py-0.5 rounded-sm text-[8px] uppercase tracking-wider text-stone-200">
                                    Cozy Hostels
                                </span>
                            </div>
                            <div class="relative rounded-lg overflow-hidden border border-stone-300/60 group shadow-sm">
                                <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=400&q=80" 
                                     alt="Verified Apartment" 
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-all duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 via-transparent to-transparent"></div>
                                <span class="absolute bottom-2 left-2 bg-stone-900/80 border border-stone-700 backdrop-blur-md px-1.5 py-0.5 rounded-sm text-[8px] uppercase tracking-wider text-stone-200">
                                    Verified Units
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>