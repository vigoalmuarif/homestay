@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Form Elements" />
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="space-y-6">
                <x-main.forms.form-elements.default-inputs />
                <x-main.forms.form-elements.select-inputs />
                <x-main.forms.form-elements.textarea-input />
                <x-main.forms.form-elements.input-states />
            </div>
            <div class="space-y-6">
                <x-main.forms.form-elements.input-group />
                <x-main.forms.form-elements.file-input />
                <x-main.forms.form-elements.checkbox-inputs />
                <x-main.forms.form-elements.radio-buttons />
                <x-main.forms.form-elements.toggle-switch />
                <x-main.forms.form-elements.dropzone />
            </div>
        </div>
    </div>
@endsection
