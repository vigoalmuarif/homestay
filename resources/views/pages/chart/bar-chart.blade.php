@extends('layouts.app')

@section('content')
    <div>
      <x-common.page-breadcrumb pageTitle="Bar Chart" />
      <div class="space-y-6 overflow-x-hidden">
        <x-common.component-card title="Bar Chart 1">
          <x-main.others.charts.bar.one />
        </x-common.component-card>
        <x-common.component-card title="Bar Chart 2">
          <x-main.others.charts.bar.two />
        </x-common.component-card>
        <x-common.component-card title="Bar Chart 3">
          <x-main.others.charts.bar.three />
        </x-common.component-card>
    
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <x-common.component-card title="Bar Chart 4">
            <x-main.others.charts.bar.four />
          </x-common.component-card>
          <x-common.component-card title="Bar Chart 5">
            <x-main.others.charts.bar.five />
          </x-common.component-card>
          <x-common.component-card title="Bar Chart 6">
            <x-main.others.charts.bar.six />
          </x-common.component-card>
          <x-common.component-card title="Bar Chart 7">
            <x-main.others.charts.bar.seven />
          </x-common.component-card>
        </div>
      </div>
    </div>
@endsection
