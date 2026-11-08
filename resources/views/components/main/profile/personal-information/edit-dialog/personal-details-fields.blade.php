<div class="space-y-4">
    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Personal Information</h3>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-ui.field x-bind:data-invalid="Boolean(errorOf('firstName'))">
            <x-ui.field.label class="mb-1.5 block">First Name <span class="text-red-500">*</span></x-ui.field.label>
            <x-ui.input type="text" x-model="form.firstName" placeholder="Enter first name" x-bind:aria-invalid="errorOf('firstName') ? 'true' : null" @blur="touch('firstName')" />
            <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('firstName')" style="display: none;" x-text="errorOf('firstName')"></p>
        </x-ui.field>

        <x-ui.field x-bind:data-invalid="Boolean(errorOf('lastName'))">
            <x-ui.field.label class="mb-1.5 block">Last Name <span class="text-red-500">*</span></x-ui.field.label>
            <x-ui.input type="text" x-model="form.lastName" placeholder="Enter last name" x-bind:aria-invalid="errorOf('lastName') ? 'true' : null" @blur="touch('lastName')" />
            <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('lastName')" style="display: none;" x-text="errorOf('lastName')"></p>
        </x-ui.field>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-ui.field x-bind:data-invalid="Boolean(errorOf('email'))">
            <x-ui.field.label class="mb-1.5 block">Email Address <span class="text-red-500">*</span></x-ui.field.label>
            <x-ui.input type="email" x-model="form.email" placeholder="info@example.com" x-bind:aria-invalid="errorOf('email') ? 'true' : null" @blur="touch('email')" />
            <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('email')" style="display: none;" x-text="errorOf('email')"></p>
        </x-ui.field>

        <x-ui.field x-bind:data-invalid="Boolean(errorOf('phone'))">
            <x-ui.field.label class="mb-1.5 block">Phone <span class="text-red-500">*</span></x-ui.field.label>
            <x-ui.input type="text" x-model="form.phone" placeholder="+09 363 398 46" x-bind:aria-invalid="errorOf('phone') ? 'true' : null" @blur="touch('phone')" />
            <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('phone')" style="display: none;" x-text="errorOf('phone')"></p>
        </x-ui.field>
    </div>

    <x-ui.field x-bind:data-invalid="Boolean(errorOf('bio'))">
        <x-ui.field.label class="mb-1.5 block">Bio</x-ui.field.label>
        <x-ui.textarea x-model="form.bio" rows="3" placeholder="Write a brief bio..." x-bind:aria-invalid="errorOf('bio') ? 'true' : null" @blur="touch('bio')" />
        <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('bio')" style="display: none;" x-text="errorOf('bio')"></p>
    </x-ui.field>
</div>
