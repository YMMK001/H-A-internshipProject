<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Property Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans min-h-screen">

    <div class="flex flex-col md:flex-row min-h-screen">

        <aside class="w-full md:w-64 bg-slate-900 text-white flex flex-col p-5 gap-6 shadow-xl">
            <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
                <span class="text-2xl">🏢</span>
                <div>
                    <h2 class="font-bold text-lg leading-tight">Property Admin</h2>
                    <span class="text-xs text-slate-400">Owner Panel</span>
                </div>
            </div>
            
            <nav class="flex flex-col gap-1.5 flex-1 text-sm font-medium">
                <a href="#" class="flex items-center gap-3 px-4 py-3 bg-blue-600 rounded-xl text-white transition-all shadow-md shadow-blue-600/20">
                    📊 Dashboard
                </a>
                <a href="hostellist.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                    🏠 My Properties
                </a>
                <a href="owner_contract.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                    👥 Contract
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                    👥 Renters (အိမ်ငှားများ)
                </a>
                <a href="admin_payment.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all">
                    💰 Payments (ငွေစာရင်း)
                </a>
            </nav>

            <div class="border-t border-slate-800 pt-4 flex items-center gap-3 text-sm">
                <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center font-bold text-blue-400">OP</div>
                <div>
                    <p class="font-semibold text-slate-200">ပိုင်ရှင်</p>
                    <span class="text-xs text-slate-500">Property Owner</span>
                </div>
            </div>
        </aside>

        <main class="flex-1 p-6 lg:p-8">
            
            <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">မင်္ဂလာပါ၊ ပိုင်ရှင် 👋</h1>
                    <p class="text-sm text-gray-500 mt-0.5">ယနေ့အတွက် သင့်အိမ်ခြံမြေများ၏ အခြေအနေ ခြုံငုံသုံးသပ်ချက်</p>
                </div>
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-4 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2 self-start sm:self-auto">
                    <span>➕</span> အခန်းသစ်/အဆောင်သစ် တင်ရန်
                </button>
            </header>

            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">🏠</div>
                    <div>
                        <span class="text-xs text-gray-400 block font-medium">စုစုပေါင်း အခန်း</span>
                        <span class="text-xl font-extrabold text-gray-800">12 ခန်း</span>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-xl font-bold">👥</div>
                    <div>
                        <span class="text-xs text-gray-400 block font-medium">ငှားရမ်းပြီး (Occupied)</span>
                        <span class="text-xl font-extrabold text-gray-800">9 ခန်း</span>
                        <span class="text-[10px] text-green-600 bg-green-50 px-1.5 py-0.5 rounded font-semibold ml-1">75%</span>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">🔑</div>
                    <div>
                        <span class="text-xs text-gray-400 block font-medium">လွတ်သေးသည် (Vacant)</span>
                        <span class="text-xl font-extrabold text-gray-800">3 ခန်း</span>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold">💰</div>
                    <div>
                        <span class="text-xs text-gray-400 block font-medium">ယခုလရရှိမည့်ဝင်ငွေ</span>
                        <span class="text-xl font-extrabold text-purple-600">4,500,000</span> <span class="text-xs text-gray-400 font-semibold">MMK</span>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800 text-base">📋 လက်ရှိ အခန်းများ၏ အခြေအနေပြဇယား</h3>
                    <span class="text-xs text-blue-600 bg-blue-50 font-bold px-3 py-1 rounded-full cursor-pointer hover:bg-blue-100 transition-colors">အားလုံးကြည့်ရန်</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap text-sm text-gray-600">
                        <thead class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="p-4 pl-6">အခန်း / အဆောင်</th>
                                <th class="p-4">အမျိုးအစား</th>
                                <th class="p-4">လက်ရှိအိမ်ငှား</th>
                                <th class="p-4">လစဉ်ကြေး</th>
                                <th class="p-4">ငွေပေးချေမှု</th>
                                <th class="p-4 pr-6 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-4 pl-6 font-semibold text-gray-900">
                                    Room 201 <span class="text-xs font-normal text-gray-400 block">စမ်းချောင်းတိုက်ခန်း</span>
                                </td>
                                <td class="p-4"><span class="bg-blue-50 text-blue-700 text-xs font-medium px-2 py-0.5 rounded">Apartment</span></td>
                                <td class="p-4">
                                    <div class="font-medium text-gray-800">မောင်မောင်</div>
                                    <span class="text-xs text-gray-400">📞 09-777xxxxxx</span>
                                </td>
                                <td class="p-4 font-bold text-gray-800">500,000 MMK</td>
                                <td class="p-4">
                                    <span class="text-xs text-green-700 bg-green-50 px-2.5 py-1 rounded-full font-medium">💵 ပေးပြီး</span>
                                </td>
                                <td class="p-4 pr-6 text-center">
                                    <button class="text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-xl transition-colors">ပြင်ဆင်ရန်</button>
                                </td>
                            </tr>
                            
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-4 pl-6 font-semibold text-gray-900">
                                    Room 101-A <span class="text-xs font-normal text-gray-400 block">လှည်းတန်း မမဆောင်</span>
                                </td>
                                <td class="p-4"><span class="bg-purple-50 text-purple-700 text-xs font-medium px-2 py-0.5 rounded">Hostel</span></td>
                                <td class="p-4">
                                    <div class="font-medium text-gray-800">မစုစု</div>
                                    <span class="text-xs text-gray-400">📞 09-444xxxxxx</span>
                                </td>
                                <td class="p-4 font-bold text-gray-800">150,000 MMK</td>
                                <td class="p-4">
                                    <span class="text-xs text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full font-medium">⏳ ပေးရန်ကျန်</span>
                                </td>
                                <td class="p-4 pr-6 text-center">
                                    <button class="text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-xl transition-colors">ပြင်ဆင်ရန်</button>
                                </td>
                            </tr>

                            <tr class="hover:bg-gray-50/50">
                                <td class="p-4 pl-6 font-semibold text-gray-900">
                                    Room 302 <span class="text-xs font-normal text-gray-400 block">လှိုင်ကွန်ဒို</span>
                                </td>
                                <td class="p-4"><span class="bg-blue-50 text-blue-700 text-xs font-medium px-2 py-0.5 rounded">Apartment</span></td>
                                <td class="p-4 text-gray-400 italic">မရှိပါ (လွတ်နေသည်)</td>
                                <td class="p-4 font-bold text-gray-800">650,000 MMK</td>
                                <td class="p-4">
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full font-medium">➖ N/A</span>
                                </td>
                                <td class="p-4 pr-6 text-center">
                                    <button class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl transition-colors">လူသွင်းရန်</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

</body>
</html>