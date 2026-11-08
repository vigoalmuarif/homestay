@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Profile" />
        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 lg:mb-7 dark:text-white/90">Profile</h3>
            <div class="space-y-6">
                <x-main.profile.personal-information />
                <x-main.profile.address />
                <x-main.profile.security />
                <x-main.profile.danger />
            </div>
        </div>
    </div>
@endsection
