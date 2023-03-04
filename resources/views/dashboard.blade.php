<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>


    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px6 lg:px-8">
            {{-- <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4"> --}}





            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <!-- Card 1 -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-semibold mb-2">Total de Ventas</h3>
                    <p class="text-3xl font-bold">$10,000</p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-semibold mb-2">Clientes Nuevos</h3>
                    <p class="text-3xl font-bold">50</p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-semibold mb-2">Pedidos Pendientes</h3>
                    <p class="text-3xl font-bold">10</p>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-semibold mb-2">Ganancias</h3>
                    <p class="text-3xl font-bold">$5,000</p>
                </div>

                <!-- Graph 1 -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-semibold mb-2">Ventas Diarias</h3>
                    <canvas id="salesChart"></canvas>
                </div>

                <!-- Graph 2 -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-semibold mb-2">Top Productos</h3>
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>









        </div>
        <div class="max-w-7xl mx-auto sm:px6 lg:px-8 py-8">









            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <!-- Card 1 -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="flex items-center">
                        <div class="rounded-full bg-green-500 text-white p-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold mb-2">Total de Ventas</h3>
                            <p class="text-3xl font-bold">$10,000</p>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="flex items-center">
                        <div class="rounded-full bg-blue-500 text-white p-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold mb-2">Clientes Nuevos</h3>
                            <p class="text-3xl font-bold">50</p>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="flex items-center">
                        <div class="rounded-full bg-yellow-500 text-white p-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold mb-2">Pedidos Pendientes</h3>
                            <p class="text-3xl font-bold">10</p>
                        </div>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="flex items-center">
                        <div class="rounded-full bg-red-500 text-white p-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold mb-2">Ingresos Mensuales</h3>
                            <p class="text-3xl font-bold">$5,000</p>
                        </div>
                    </div>
                </div>
                <!-- Gráfico de barras -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-semibold mb-2">Ventas por Categoría</h3>
                    <canvas id="myChart"></canvas>
                </div>
            </div>










            {{-- </div> --}}
        </div>
    </div>

</x-app-layout>
