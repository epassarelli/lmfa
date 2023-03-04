<x-app-layout>


    <div class="flex flex-wrap  px-4 py-4">
        @foreach ($interpretes as $interprete)
            <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 px-4 mb-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <a href="{{ route('interpretes.show', $interprete->slug) }}">
                        <img src="{{ asset('storage/interpretes/' . $interprete->foto) }}"
                            alt="{{ $interprete->interprete }}" class="w-full h-64 object-cover">
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2">{{ $interprete->interprete }}</h3>
                        </div>
                    </a>
                </div>
            </div>
        @endforeach

        <div class="mt-8 content-center">
            {{ $interpretes->links() }}
        </div>
    </div>

</x-app-layout>
