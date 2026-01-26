<?php

use Livewire\Component;

new class extends Component {
    //
};
?>

<div class="">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <section class="bg-gray-50 p-8">

        <div class="max-w-7xl mx-auto space-y-10">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-[12px] border border-gray-100 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tournaments</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-2xl font-semibold text-gray-900">24</span>
                        <div class="text-blue-500 bg-blue-50 p-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="浸8 13h8m-8-4h8m-8 8h8M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[12px] border border-gray-100 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Events</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-2xl font-semibold text-gray-900">112</span>
                        <div class="text-gray-400 bg-gray-50 p-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[12px] border border-gray-100 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Live Matches</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-2xl font-semibold text-gray-900">8</span>
                        <div class="flex items-center justify-center">
                            <span class="relative flex h-3 w-3 mr-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[12px] border border-gray-100 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Players</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-2xl font-semibold text-gray-900">1,240</span>
                        <div class="text-gray-400 bg-gray-50 p-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <section>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Tournaments</h2>
                    <p class="text-sm text-gray-500">Manage your active and upcoming badminton events.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <button
                        class="border-2 border-dashed border-gray-200 rounded-[12px] p-6 flex flex-col items-center justify-center text-gray-500 hover:border-blue-400 hover:text-blue-500 transition-colors group">
                        <svg class="w-8 h-8 mb-2 group-hover:scale-110 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="font-medium">Add New Tournament</span>
                    </button>

                    <div
                        class="bg-white rounded-[12px] border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Live</span>
                                <span class="text-xs text-gray-400 font-medium italic">Ends in 2 days</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">National Open 2024</h3>
                            <p class="text-sm text-gray-500 mb-4">Wembley Arena, London</p>
                            <div class="grid grid-cols-3 gap-2 border-t border-gray-50 pt-4">
                                <div class="text-center">
                                    <p class="text-[10px] uppercase text-gray-400 font-bold">Events</p>
                                    <p class="text-sm font-semibold text-gray-700">5</p>
                                </div>
                                <div class="text-center border-x border-gray-50">
                                    <p class="text-[10px] uppercase text-gray-400 font-bold">Matches</p>
                                    <p class="text-sm font-semibold text-gray-700">120</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] uppercase text-gray-400 font-bold">Players</p>
                                    <p class="text-sm font-semibold text-gray-700">240</p>
                                </div>
                            </div>
                        </div>
                        <button
                            class="mt-6 w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">View
                            Details</button>
                    </div>

                    <div
                        class="bg-white rounded-[12px] border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Upcoming</span>
                                <span class="text-xs text-gray-400 font-medium">Starts Oct 12</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Junior Masters Cup</h3>
                            <p class="text-sm text-gray-500 mb-4">Stadium Juara, KL</p>
                            <div class="grid grid-cols-3 gap-2 border-t border-gray-50 pt-4">
                                <div class="text-center">
                                    <p class="text-[10px] uppercase text-gray-400 font-bold">Events</p>
                                    <p class="text-sm font-semibold text-gray-700">3</p>
                                </div>
                                <div class="text-center border-x border-gray-50">
                                    <p class="text-[10px] uppercase text-gray-400 font-bold">Matches</p>
                                    <p class="text-sm font-semibold text-gray-700">45</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] uppercase text-gray-400 font-bold">Players</p>
                                    <p class="text-sm font-semibold text-gray-700">86</p>
                                </div>
                            </div>
                        </div>
                        <button
                            class="mt-6 w-full py-2 px-4 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg text-sm font-medium border border-gray-200 transition-colors">Manage</button>
                    </div>
                </div>
            </section>

            <section>
                <div class="bg-white rounded-[12px] border border-gray-100 shadow-sm overflow-hidden">
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

    </section>
</div>