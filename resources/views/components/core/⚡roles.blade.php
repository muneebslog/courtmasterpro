<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
        <section>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Project Users</h2>
                            <p class="text-sm text-gray-500">Assign roles and manage team permissions.</p>
                        </div>
                        <button
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
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
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">Alex Thompson</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">alex.t@btms.com</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded">Admin</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            class="text-gray-400 hover:text-blue-600 font-medium text-sm mr-4">Edit</button>
                                        <button
                                            class="text-gray-400 hover:text-red-600 font-medium text-sm">Remove</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">Sarah Chen</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">s.chen@organizer.org</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded">Organizer</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            class="text-gray-400 hover:text-blue-600 font-medium text-sm mr-4">Edit</button>
                                        <button
                                            class="text-gray-400 hover:text-red-600 font-medium text-sm">Remove</button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900">Marcus Wright</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">m.wright@pro.com</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded">Umpire</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-xs font-medium text-orange-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                                            Pending
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            class="text-gray-400 hover:text-blue-600 font-medium text-sm mr-4">Edit</button>
                                        <button
                                            class="text-gray-400 hover:text-red-600 font-medium text-sm">Remove</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
</div>