@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Images" />
    <div class="space-y-5 sm:space-y-6">
        <x-common.component-card title="Responsive image">
            <x-ui-elements.images.responsive />
        </x-common.component-card>
        <x-common.component-card title="Image in 2 Grid">
            <x-ui-elements.images.two-column-grid />
        </x-common.component-card>
        <x-common.component-card title="Image in 3 Grid">
            <x-ui-elements.images.three-column-greed />
        </x-common.component-card>
    </div>

@endsection
