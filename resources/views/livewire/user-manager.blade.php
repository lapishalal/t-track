<div class="bg-white rounded-lg shadow p-6 space-y-6">
    <h3 class="text-lg font-semibold text-gray-800">Manajemen User</h3>

    {{-- Flash Messages --}}
    @if(session('success_users'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-sm">
            {{ session('success_users') }}
        </div>
    @endif
    @if(session('error_users'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm">
            {{ session('error_users') }}
        </div>
    @endif

    {{-- Form Tambah/Edit User --}}
    <form wire:submit="createUser" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" wire:model="name"
                   class="w-full rounded border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
            <input type="email" wire:model="email"
                   class="w-full rounded border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                Password @if($editingUserId)
                    <span class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span>
                @endif
            </label>
            <input type="password" wire:model="password"
                   class="w-full rounded border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Role</label>
            <select wire:model="role"
                    class="w-full rounded border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="operator">Operator (Analytics + Upload)</option>
                <option value="uploader">Uploader (Upload saja)</option>
            </select>
            @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="flex gap-2">
            <button type="submit"
                    class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition">
                @if($editingUserId) Simpan @else + Tambah @endif
            </button>
            @if($editingUserId)
                <button type="button" wire:click="cancelEdit"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-md transition">
                    Batal
                </button>
            @endif
        </div>
    </form>

    {{-- Daftar User --}}
    <div>
        <h4 class="text-sm font-semibold text-gray-700 mb-2">Daftar User ({{ $users->count() }})</h4>
        <table class="w-full text-xs table-fixed">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="text-left px-3 py-2 w-8">#</th>
                    <th class="text-left px-3 py-2">Nama</th>
                    <th class="text-left px-3 py-2">Email</th>
                    <th class="text-left px-3 py-2 w-24">Role</th>
                    <th class="text-left px-3 py-2 w-32">Dibuat</th>
                    <th class="text-center px-3 py-2 w-20">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $i => $user)
                <tr class="border-b hover:bg-gray-50 @if($editingUserId == $user->id) bg-indigo-50 @endif">
                    <td class="px-3 py-2 text-gray-500">{{ $i+1 }}</td>
                    <td class="px-3 py-2">{{ $user->name }}</td>
                    <td class="px-3 py-2 text-gray-600">{{ $user->email }}</td>
                    <td class="px-3 py-2">
                        @if($user->isOwner())
                            <span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full text-[10px] font-semibold">Owner</span>
                        @elseif($user->isUploader())
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full text-[10px] font-semibold">Uploader</span>
                        @else
                            <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-[10px] font-semibold">Operator</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-gray-500 text-[10px]">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-3 py-2 text-center">
                        @if(!$user->isOwner())
                        <div class="flex items-center justify-center gap-1">
                            <button wire:click="editUser({{ $user->id }})"
                                    class="text-indigo-500 hover:text-indigo-700 text-xs font-bold px-1"
                                    title="Edit user">
                                E
                            </button>
                            <button wire:click="deleteUser({{ $user->id }})"
                                    wire:confirm="Hapus user {{ $user->name }}?"
                                    class="text-red-500 hover:text-red-700 text-xs font-bold px-1"
                                    title="Hapus user">
                                X
                            </button>
                        </div>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
