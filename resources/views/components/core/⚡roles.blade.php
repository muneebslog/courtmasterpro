<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public Project $project;

    // Modals
    public bool $showAddUserModal = false;
    public bool $showEditUserModal = false;
    public bool $showDeleteUserModal = false;

    // Form fields
    public ?int $editingUserId = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'viewer';
    public bool $is_active = true;


    public function mount()
    {
        $project = auth()->user()->ownedProject;

        if (!$project) {
            abort(403, 'You do not own a project.');
        }

        $this->project = $project->load('users');
    }




    /* ---------------- ADD USER ---------------- */

    public function openAddUser()
    {
        $this->resetForm();
        $this->showAddUserModal = true;
    }

    public function addUser()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,referee,umpire,viewer',
        ]);

        DB::transaction(function () {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            $this->project->users()->attach($user->id, [
                'role' => $this->role,
                'is_active' => $this->is_active,
            ]);
        });

        $this->closeAndRefresh();
    }

    /* ---------------- EDIT USER ---------------- */

    public function openEditUser(int $userId)
    {
        $pivot = $this->project->users()->where('users.id', $userId)->first()->pivot;

        $user = User::findOrFail($userId);

        $this->editingUserId = $userId;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $pivot->role;
        $this->is_active = (bool) $pivot->is_active;

        $this->showEditUserModal = true;
    }

    public function updateUser()
    {
        $this->validate([
            'role' => 'required|in:admin,referee,umpire,viewer',
        ]);

        $this->project->users()->updateExistingPivot(
            $this->editingUserId,
            [
                'role' => $this->role,
                'is_active' => $this->is_active,
            ]
        );

        $this->closeAndRefresh();
    }

    /* ---------------- DELETE USER ---------------- */

    public function confirmDeleteUser(int $userId)
    {
        $this->editingUserId = $userId;
        $this->showDeleteUserModal = true;
    }

    public function deleteUser()
    {
        $this->project->users()->detach($this->editingUserId);

        $this->closeAndRefresh();
    }

    /* ---------------- HELPERS ---------------- */

    protected function resetForm()
    {
        $this->reset([
            'editingUserId',
            'name',
            'email',
            'password',
            'role',
            'is_active',
        ]);
    }

    protected function closeAndRefresh()
    {
        $this->resetForm();

        $this->showAddUserModal = false;
        $this->showEditUserModal = false;
        $this->showDeleteUserModal = false;

        $this->project->refresh();
    }
};

?>

<div class="mt-4">
    <section>
        <div class=" bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Project Users</h2>
                    <p class="text-sm text-gray-500">Assign roles and manage team permissions.</p>
                </div>
                <button wire:click="openAddUser"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                    Add User
                </button>

            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                Name</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                Email</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                Role</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-right">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($project->users as $user)
                            <tr class="hover:bg-gray-50/30 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-medium bg-gray-100 px-2 py-1 rounded">
                                        {{ ucfirst($user->pivot->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium
                                                        {{ $user->pivot->is_active ? 'text-green-700' : 'text-red-600' }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full
                                                            {{ $user->pivot->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        {{ $user->pivot->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button wire:click="openEditUser({{ $user->id }})"
                                        class="text-gray-400 hover:text-blue-600 text-sm mr-4">
                                        Edit
                                    </button>
                                    <button wire:click="confirmDeleteUser({{ $user->id }})"
                                        class="text-gray-400 hover:text-red-600 text-sm">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <flux:modal wire:model.self="showAddUserModal" class="md:w-[420px]">
        <form wire:submit.prevent="addUser" class="space-y-6">
            <flux:heading size="lg">Add User</flux:heading>

            <flux:input label="Name" wire:model.defer="name" />
            <flux:input label="Email" wire:model.defer="email" />
            <flux:input label="Password" type="password" wire:model.defer="password" />

            <flux:select label="Role" wire:model.defer="role">
                <option value="admin">Admin</option>
                <option value="referee">Referee</option>
                <option value="umpire">Umpire</option>
                <option value="viewer">Viewer</option>
            </flux:select>

            <flux:field variant="inline">
                <flux:checkbox wire:model.defer="is_active" />
                <flux:label>Active</flux:label>
            </flux:field>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">Add User</flux:button>
            </div>
        </form>
    </flux:modal>
    <flux:modal wire:model.self="showEditUserModal" class="md:w-[420px]">
        <form wire:submit.prevent="updateUser" class="space-y-6">
            <flux:heading size="lg">Edit User</flux:heading>

            <flux:select label="Role" wire:model.defer="role">
                <option value="admin">Admin</option>
                <option value="referee">Referee</option>
                <option value="umpire">Umpire</option>
                <option value="viewer">Viewer</option>
            </flux:select>

            <flux:field variant="inline">
                <flux:checkbox wire:model.defer="is_active" />
                <flux:label>Active</flux:label>
            </flux:field>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">Save Changes</flux:button>
            </div>
        </form>
    </flux:modal>
    <flux:modal wire:model.self="showDeleteUserModal" class="md:w-[420px]">
        <div class="space-y-6">
            <flux:heading size="lg" class="text-red-600">Remove User</flux:heading>

            <p class="text-sm text-gray-600">
                This user will lose access to this project.
            </p>

            <div class="flex justify-end gap-3">
                <flux:button variant="outline" wire:click="$set('showDeleteUserModal', false)">
                    Cancel
                </flux:button>

                <flux:button variant="danger" wire:click="deleteUser">
                    Remove
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>