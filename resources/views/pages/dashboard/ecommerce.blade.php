@extends('layouts.app')

@section('content')
  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6 xl:col-span-7">
      <x-main.dashboard.ecommerce.metrics />
      <x-main.dashboard.ecommerce.monthly-sales-chart />
    </div>
    <div class="col-span-12 xl:col-span-5">
      <x-main.dashboard.ecommerce.monthly-target />
    </div>

    <div class="col-span-12">
      <x-main.dashboard.ecommerce.statistics-chart />
    </div>

    <div class="col-span-12 xl:col-span-5">
      <x-main.dashboard.ecommerce.demographic-card />
    </div>

    <div class="col-span-12 xl:col-span-7">
      <x-main.dashboard.ecommerce.recent-orders />
    </div>
  </div>
@endsection
