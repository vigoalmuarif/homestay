@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Buttons" />
    <div class="space-y-5 sm:space-y-6">
        <x-common.component-card title="Button Variants">
            <x-ui-elements.buttons.variants />
        </x-common.component-card>
        <x-common.component-card title="Button Sizes">
            <x-ui-elements.buttons.sizes />
        </x-common.component-card>
        <x-common.component-card title="Button with Icon">
            <x-ui-elements.buttons.with-icon />
        </x-common.component-card>
        <x-common.component-card title="Button Group">
            <x-ui-elements.buttons.button-group />
        </x-common.component-card>
    </div>

@endsection
