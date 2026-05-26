<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            OAuth authorization
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div>
                        <p class="text-sm text-gray-600">Application requests access:</p>
                        <p class="mt-1 text-lg font-semibold">{{ $client->name }}</p>
                    </div>

                    @if (count($scopes) > 0)
                        <ul class="list-disc list-inside text-sm text-gray-700">
                            @foreach ($scopes as $scope)
                                <li>{{ $scope->description }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-700">No additional scopes requested.</p>
                    @endif

                    <div class="flex items-center gap-3">
                        <form method="POST" action="{{ route('passport.authorizations.approve') }}">
                            @csrf
                            <input type="hidden" name="auth_token" value="{{ $authToken }}">
                            <x-primary-button>Authorize</x-primary-button>
                        </form>

                        <form method="POST" action="{{ route('passport.authorizations.deny') }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="auth_token" value="{{ $authToken }}">
                            <x-secondary-button>Deny</x-secondary-button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
