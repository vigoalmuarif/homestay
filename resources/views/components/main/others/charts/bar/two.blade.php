<div class="flex flex-col gap-6">
  <!-- Legend Indicators -->
  <div class="flex items-center justify-end gap-5">
    <div class="flex items-center gap-1.5">
      <span class="size-2.5 rounded-full bg-brand-600"></span>
      <span class="text-sm font-normal text-gray-700 dark:text-gray-300"> Income </span>
    </div>
    <div class="flex items-center gap-1.5">
      <span class="size-2.5 rounded-full bg-[#7CD4FD]"></span>
      <span class="text-sm font-normal text-gray-700 dark:text-gray-300"> Expense </span>
    </div>
  </div>

  <x-ui.scroll-area class="max-w-full">
    <div class="min-w-162.5 xl:min-w-full">
      <div data-others-chart="bar-two"></div>
    </div>
  </x-ui.scroll-area>
</div>
