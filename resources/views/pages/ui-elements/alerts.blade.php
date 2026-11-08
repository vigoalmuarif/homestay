@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Alerts" />
    <div class="space-y-5 sm:space-y-6">
        <x-common.component-card title="Default Alert">
            <x-ui-elements.alerts.default-alert />
        </x-common.component-card>
        <x-common.component-card title="Success Alert">
            <x-ui-elements.alerts.success-alert />
        </x-common.component-card>
        <x-common.component-card title="Warning Alert">
            <x-ui-elements.alerts.warning-alert />
        </x-common.component-card>
        <x-common.component-card title="Error Alert">
            <x-ui-elements.alerts.error-alert />
        </x-common.component-card>
        <x-common.component-card title="Info Alert">
            <x-ui-elements.alerts.info-alert />
        </x-common.component-card>
    </div>

@endsection
