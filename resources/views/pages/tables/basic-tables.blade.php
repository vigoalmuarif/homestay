@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Basic Tables" />
    <div class="space-y-6">
        <x-common.component-card title="Basic Table 1">
            <x-main.tables.basic-tables.table-one />
        </x-common.component-card>
        <x-common.component-card title="Basic Table 2">
            <x-main.tables.basic-tables.table-two />
        </x-common.component-card>
        <x-common.component-card title="Basic Table 3">
            <x-main.tables.basic-tables.table-three />
        </x-common.component-card>
        <x-common.component-card title="Basic Table 4">
            <x-main.tables.basic-tables.table-four />
        </x-common.component-card>
        <x-common.component-card title="Basic Table 5">
            <x-main.tables.basic-tables.table-five />
        </x-common.component-card>
    </div>
@endsection
