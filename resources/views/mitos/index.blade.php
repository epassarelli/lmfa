<x-app-layout>

    <!-- Listado de noticias en cards -->
    <div class="flex flex-wrap justify-center">
        <?php foreach($mitos as $mito): ?>


        <div class="w-full sm:w-1/2 md:w-1/3 p-4">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <a href="mitos/<?php echo $mito->slug; ?>">
                    <img src="<?php echo $mito->foto; ?>" alt="<?php echo $mito->titulo; ?>" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-bold text-xl mb-2"><?php echo $mito->titulo; ?></h3>
                    </div>
                </a>
            </div>
        </div>


        <?php endforeach; ?>
    </div>

    <!-- Links del paginado -->
    <div class="flex justify-center">
        <div class="mt-8">
            {{ $mitos->links() }}
        </div>
    </div>

</x-app-layout>
