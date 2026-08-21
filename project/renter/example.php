<!DOCTYPE html>
<html lang="my">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Receipt Mockup</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white w-full max-w-sm rounded-lg shadow-md p-6 m-4 font-sans text-gray-800">
    <!-- Success Icon -->
    <div class="flex justify-center mb-4">
      <div class="bg-blue-600 rounded-full p-3">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
    </div>

    <!-- Title & Amount -->
    <div class="text-center mb-6">
      <p class="text-base text-gray-600 font-medium">လုပ်ဆောင်မှုအောင်မြင်ပါသည်</p>
      <h1 class="text-3xl font-bold text-gray-900 mt-2">-720,000.00 <span class="text-lg font-normal">(Ks)</span></h1>
    </div>

    <!-- Details Table -->
    <div class="space-y-4 text-sm border-t pt-4 border-gray-100">
      <div class="flex justify-between">
        <span class="text-gray-500">လုပ်ဆောင်သော အချိန်</span>
        <span class="font-medium text-gray-800">21/08/2026 12:24:00</span>
      </div>

      <div class="flex justify-between">
        <span class="text-gray-500">လုပ်ဆောင်မှုအမှတ်</span>
        <span class="font-medium text-gray-800">01004245081404645048</span>
      </div>

      <div class="flex justify-between">
        <span class="text-gray-500">လုပ်ဆောင်မှုအမျိုးအစား</span>
        <span class="font-medium text-gray-800">ငွေလွှဲ</span>
      </div>

      <div class="flex justify-between">
        <span class="text-gray-500">ငွေလွှဲမည် သို့</span>
        <span class="font-medium text-gray-800">Yin Min Min Kyawt (******6947)</span>
      </div>

      <div class="flex justify-between">
        <span class="text-gray-500">ငွေပမာဏ</span>
        <span class="font-medium text-gray-800">-720,000.00 Ks</span>
      </div>

      <div class="flex justify-between">
        <span class="text-gray-500">မှတ်ချက်</span>
        <span class="font-medium text-gray-800">ငွေပေးချေခြင်း</span>
      </div>
    </div>
  </div>

</body>
</html>