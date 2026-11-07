<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Delete Account') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('This permanently removes your account.') }}</p>
    </header>

    <form method="POST" action="{{ route('profile.destroy') }}" class="mt-6 space-y-6">
        @csrf
        @method('delete')

        <div>
            <x-input-label for="password_delete" :value="__('Password')" />
            <x-text-input id="password_delete" name="password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:ring-red-500">
            {{ __('Delete Account') }}
        </x-primary-button>
    </form>
</section>
