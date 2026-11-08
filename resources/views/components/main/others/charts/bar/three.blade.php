<div class="flex flex-col gap-6">
  <!-- Legend Indicators -->
  <div class="flex items-center gap-5">
    <div class="flex items-center gap-1.5">
      <span class="size-2.5 rounded-full bg-[#7592FF]"></span>
      <span class="text-sm font-normal text-gray-700 dark:text-gray-300"> Shipment </span>
    </div>
    <div class="flex items-center gap-1.5">
      <span class="size-2.5 rounded-full bg-[#BDB4FE]"></span>
      <span class="text-sm font-normal text-gray-700 dark:text-gray-300"> In Transit </span>
    </div>
    <div class="flex items-center gap-1.5">
      <span class="size-2.5 rounded-full bg-[#7CD4FD]"></span>
      <span class="text-sm font-normal text-gray-700 dark:text-gray-300"> Delivery </span>
    </div>
  </div>

  <x-ui.scroll-area class="max-w-full">
    <div class="min-w-162.5 xl:min-w-full">
      <div data-others-chart="bar-three"></div>
    </div>
  </x-ui.scroll-area>
</div>
