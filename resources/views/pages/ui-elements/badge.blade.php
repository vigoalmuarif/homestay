@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Badges" />
    <div class="space-y-5 sm:space-y-6">
        <x-common.component-card title="With Solid Background">
            <x-ui-elements.badges.solid-variant />
        </x-common.component-card>
        <x-common.component-card title="With Light Background">
            <x-ui-elements.badges.light-variant />
        </x-common.component-card>
        <x-common.component-card title="Ghost Variant">
            <x-ui-elements.badges.ghost-variant />
        </x-common.component-card>
        <x-common.component-card title="Solid Background with Leading Icon">
            <x-ui-elements.badges.solid-variant-with-leading-icon />
        </x-common.component-card>
        <x-common.component-card title="Light Background with Leading Icon">
            <x-ui-elements.badges.light-variant-with-leading-icon />
        </x-common.component-card>
        <x-common.component-card title="Ghost Variant with Leading Icon">
            <x-ui-elements.badges.ghost-variant-with-leading-icon />
        </x-common.component-card>
        <x-common.component-card title="Solid Background with Trailing Icon">
            <x-ui-elements.badges.solid-variant-with-trailing-icon />
        </x-common.component-card>
        <x-common.component-card title="Light Background with Trailing Icon">
            <x-ui-elements.badges.light-variant-with-trailing-icon />
        </x-common.component-card>
        <x-common.component-card title="Ghost Variant with Trailing Icon">
            <x-ui-elements.badges.ghost-variant-with-trailing-icon />
        </x-common.component-card>
    </div>

@endsection
