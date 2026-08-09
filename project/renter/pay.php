<!DOCTYPE html>
<html lang="my">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>အိမ်ငှားခ ပေးချေရန် စာမျက်နှာ</title>
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts: Pyidaungsu & Padauk (မြန်မာစာလုံးဒီဇိုင်းအတွက်) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Padauk:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Padauk', sans-serif;
    }
  </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6">

  <!-- Payment Card Container (ကတ်တစ်ခုလုံး၏ အကျယ်အဝန်းနှင့် နောက်ခံ) -->
  <div class="w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-200/80 transition-all">
    
    <!-- ခေါင်းစဉ်ပိုင်း (Header Section) -->
    <div class="bg-indigo-600 p-6 text-white relative overflow-hidden">
      <!-- အလှဆင်အလင်းဝိုင်း (Background Decorative Blur) -->
      <div class="absolute -right-8 -top-8 w-32 h-32 bg-indigo-500/50 rounded-full blur-2xl"></div>
      
      <div class="relative z-10">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-xs font-bold tracking-wider uppercase text-indigo-200">လစဉ် အိမ်ငှားခ ပေးချေရန်</p>
            <h1 class="text-2xl font-bold mt-1">အခန်း ၄-ဘီ (Sunset Heights)</h1>
          </div>
          <!-- ရက်စွဲသတိပေးချက် -->
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-400 text-amber-950 shadow-sm">
            ၃ ရက်သာ လိုပါတော့သည်
          </span>
        </div>

        <div class="mt-6 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold tracking-tight">၁,၈၅၀,၀၀၀</span>
          <span class="text-indigo-200 text-sm font-medium">ကျပ် / လ</span>
        </div>
      </div>
    </div>

    <!-- အသေးစိတ် အချက်အလက်များ (Main Content Body) -->
    <div class="p-6 space-y-6">
      
      <!-- ကျသင့်ငွေ ခွဲခြားပြသမှု (Fee Breakdown) -->
      <div class="space-y-2 text-sm border-b border-slate-100 pb-4">
        <div class="flex justify-between text-slate-500">
          <span>မူလ အိမ်ငှားခ</span>
          <span class="font-medium text-slate-800">၁,၈၀၀,၀၀၀ ကျပ်</span>
        </div>
        <div class="flex justify-between text-slate-500">
          <span>အမှိုက်နှင့် ရေခ</span>
          <span class="font-medium text-slate-800">၅၀,၀၀၀ ကျပ်</span>
        </div>
        <div class="flex justify-between text-slate-900 font-bold pt-2 border-t border-slate-100 text-base">
          <span>စုစုပေါင်း ပေးချေရမည့်ငွေ</span>
          <span class="text-indigo-600">၁,၈၅၀,၀၀၀ ကျပ်</span>
        </div>
      </div>

      <!-- QR Code ဖြင့် ငွေပေးချေရန်နေရာ (QR Code Scanner Box) -->
      <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 flex flex-col items-center text-center">
        <p class="text-base font-bold text-slate-800">Mobile Banking ဖြင့် စကန်ဖတ်၍ ပေးချေပါ</p>
        <p class="text-xs text-slate-500 mt-0.5 mb-4">KPay, CB Pay, AYA Pay သို့မဟုတ် Bank App များဖြင့် ပေးချေနိုင်ပါသည်။</p>
        
        <!-- Scalable SVG QR Code Container -->
        <div class="bg-white p-3.5 rounded-xl shadow-sm border border-slate-200 relative group transition-transform duration-200 hover:scale-[1.02]">
          <svg class="w-44 h-44" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Background -->
            <rect width="100" height="100" fill="white"/>
            
            <!-- Top-Left Alignment Corner -->
            <rect x="5" y="5" width="25" height="25" fill="#1E293B"/>
            <rect x="9" y="9" width="17" height="17" fill="white"/>
            <rect x="13" y="13" width="9" height="9" fill="#1E293B"/>
            
            <!-- Top-Right Alignment Corner -->
            <rect x="70" y="5" width="25" height="25" fill="#1E293B"/>
            <rect x="74" y="9" width="17" height="17" fill="white"/>
            <rect x="78" y="13" width="9" height="9" fill="#1E293B"/>
            
            <!-- Bottom-Left Alignment Corner -->
            <rect x="5" y="70" width="25" height="25" fill="#1E293B"/>
            <rect x="9" y="74" width="17" height="17" fill="white"/>
            <rect x="13" y="78" width="9" height="9" fill="#1E293B"/>

            <!-- QR Data Matrix Pattern -->
            <path d="M35 5h5v10h-5zM45 5h10v5h-10zM60 5h5v5h-5zM35 20h10v5h-10zM50 15h5v10h-5z" fill="#1E293B"/>
            <path d="M5 35h10v5H5zM20 35h5v10h-5zM30 35h15v5H30zM50 35h5v5h-5zM60 35h10v5H60zM75 35h20v5H75z" fill="#1E293B"/>
            <path d="M5 45h5v10H5zM15 50h10v5H15zM35 45h5v5h-5zM45 45h10v10H45zM60 45h5v5h-5zM70 45h15v5H70zM90 45h5v10h-5z" fill="#1E293B"/>
            <path d="M10 60h15v5H10zM30 60h5v10h-5zM40 60h10v5H40zM55 60h10v5H55zM70 60h25v5H70z" fill="#1E293B"/>
            <path d="M35 75h5v15h-5zM45 70h5v10h-5zM55 70h10v5H55zM70 70h10v5H70zM85 70h10v10H85z" fill="#1E293B"/>
            <path d="M45 85h15v5H45zM65 80h10v10H65zM80 85h15v5H80zM50 90h10v5H50zM70 95h25v5H70z" fill="#1E293B"/>
          </svg>
          
          <!-- အလယ်မှ Icon ပုံရုပ် -->
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="bg-indigo-600 text-white p-2 rounded-lg shadow-md ring-4 ring-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
              </svg>
            </div>
          </div>
        </div>

        <p class="text-xs text-slate-400 mt-3 font-mono">ပြေစာအမှတ်: #RENT-2026-08-4B</p>
      </div>

      <!-- လုပ်ဆောင်ချက် ခလုတ်များ (Action Buttons) -->
      <div class="space-y-2.5">
        <button 
          onclick="alert('ဘဏ်ငွေပေးချေမှုစနစ်သို့ ချိတ်ဆက်နေပါသည်...')" 
          class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-3.5 px-4 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 text-base">
          <span>တိုက်ရိုက် ငွေပေးချေမည် (၁,၈၅၀,၀၀၀ ကျပ်)</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </button>
        
        <button 
          onclick="alert('ပြေစာ PDF ကို ဒေါင်းလုဒ်ရယူနေပါသည်...')" 
          class="w-full bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-medium py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2 text-sm">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <span>ပြေစာ PDF ရယူရန်</span>
        </button>
      </div>

    </div>

    <!-- လုံခြုံရေးဆိုင်ရာ အောက်ခြေစာကြောင်း (Security Footer) -->
    <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 text-center">
      <p class="text-xs text-slate-400 flex items-center justify-center gap-1">
        <svg class="w-3.5 h-3.5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
        </svg>
        <span>၂၅၆-ဘစ် စနစ်ဖြင့် လုံခြုံစွာ ကာကွယ်ထားပါသည်</span>
      </p>
    </div>

  </div>

</body>
</html>