<div>
    @if ($errors->any())
        <div class="bg-red-500 text-white rounded-lg px-4 py-2 mb-4">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="flex">
        <div class="w-2/3 p-4">
            <div class="mb-4">
                <input wire:model="firstname" class="border rounded-lg p-2 w-full" type="text" placeholder="First Name">
            </div>
            <div class="mb-4">
                <input wire:model="lastname" class="border rounded-lg p-2 w-full" type="text" placeholder="Last Name">
            </div>
            <div class="mb-4">
                <input wire:model="email" class="border rounded-lg p-2 w-full" type="email" placeholder="Email">
            </div>
            <div class="mb-4">
                <textarea wire:model="description" class="border rounded-lg p-2 w-full" placeholder="Description"></textarea>
            </div>
            <div class="mb-4">
                <input wire:model="image" class="border rounded-lg p-2 w-full" type="file" accept="image/*">
            </div>
            <div class="flex justify-end
            @if ($selectedUser) <div class="mb-4">
                    <button wire:click="saveUser" class="bg-blue-500 text-white rounded-lg px-4 py-2">Guardar</button>
                    <button wire:click="deleteUser" class="bg-red-500 text-white rounded-lg px-4 py-2 ml-4">Eliminar</button>
                </div> @endif
        </div>
        <div class="w-1/3
                p-4">
                <ul>
                    @foreach ($users as $user)
                        <li>
                            <a href="#" wire:click="selectUser({{ $user->id }})"
                                class="block hover:bg-gray-200 p-2 rounded-lg {{ $selectedUser && $selectedUser->id == $user->id ? 'bg-gray-200' : '' }}">{{ $user->firstname }}
                                {{ $user->lastname }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
