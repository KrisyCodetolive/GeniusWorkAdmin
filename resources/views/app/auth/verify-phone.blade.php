@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <div class="mb-4 text-sm text-gray-600">
            {{ __('Please verify your phone number to continue. We will send a verification code to your phone.') }}
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-4">
            <form method="POST" action="{{ route('phone.verification.send') }}">
                @csrf

                <div>
                    <x-label for="phone" :value="__('Phone Number')" />
                    <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required autofocus />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-button>
                        {{ __('Send Verification Code') }}
                    </x-button>
                </div>
            </form>
        </div>

        <div class="mt-6">
            <form method="POST" action="{{ route('phone.verification.verify') }}">
                @csrf

                <div>
                    <x-label for="otp" :value="__('Verification Code')" />
                    <x-input id="otp" class="block mt-1 w-full" type="text" name="otp" required />
                    <x-input-error :messages="$errors->get('otp')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-button>
                        {{ __('Verify') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
