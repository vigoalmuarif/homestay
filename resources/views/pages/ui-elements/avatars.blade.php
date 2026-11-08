@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Avatar" />
    <div class="space-y-5 sm:space-y-6">
        <x-common.component-card title="Default Avatar">
            <x-ui-elements.avatars.default-avatar />
        </x-common.component-card>
        <x-common.component-card title="Avatar Group">
            <x-ui-elements.avatars.avatar-group />
        </x-common.component-card>
        <x-common.component-card title="Avatar with Online Indicator">
            <x-ui-elements.avatars.online-indicator />
        </x-common.component-card>
        <x-common.component-card title="Avatar with Offline Indicator">
            <x-ui-elements.avatars.offline-indicator />
        </x-common.component-card>
        <x-common.component-card title="Avatar with Busy Indicator">
            <x-ui-elements.avatars.busy-indicator />
        </x-common.component-card>
    </div>

@endsection
