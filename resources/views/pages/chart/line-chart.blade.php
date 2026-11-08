@extends('layouts.app')

@section('content')
    <div>
      <x-common.page-breadcrumb pageTitle="Line Chart" />
      <div class="space-y-6 overflow-x-hidden">
        <x-common.component-card title="Line Chart 1">
          <x-main.others.charts.line.one />
        </x-common.component-card>
        <x-common.component-card title="Line Chart 2">
          <x-main.others.charts.line.two />
        </x-common.component-card>
        <x-common.component-card title="Line Chart 3">
          <x-main.others.charts.line.three />
        </x-common.component-card>
        <x-common.component-card title="Line Chart 4">
          <x-main.others.charts.line.four />
        </x-common.component-card>
        <x-common.component-card title="Line Chart 5">
          <x-main.others.charts.line.five />
        </x-common.component-card>
      </div>
    </div>
@endsection
