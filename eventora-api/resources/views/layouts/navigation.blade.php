<nav x-data="{ open: false }" class="bg-white border-b border-[#E6DCCF]">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#B08968] to-[#9C6644] flex items-center justify-center shadow">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(request()->routeIs('super-admin.*'))
                        <x-nav-link :href="route('super-admin.dashboard')" :active="request()->routeIs('super-admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('super-admin.tenants.index')" :active="request()->routeIs('super-admin.tenants.*')">
                            {{ __('Tenants') }}
                        </x-nav-link>
                        <x-nav-link :href="route('super-admin.categories.index')" :active="request()->routeIs('super-admin.categories.*')">
                            {{ __('Categories') }}
                        </x-nav-link>
                        <x-nav-link :href="route('super-admin.payouts.index')" :active="request()->routeIs('super-admin.payouts.*')">
                            {{ __('Payouts') }}
                        </x-nav-link>
                        <x-nav-link :href="route('super-admin.settings.index')" :active="request()->routeIs('super-admin.settings.*')">
                            {{ __('Settings') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                            {{ __('Events') }}
                        </x-nav-link>
                        <x-nav-link :href="route('coupons.index')" :active="request()->routeIs('coupons.*')">
                            {{ __('Coupons') }}
                        </x-nav-link>
                        <x-nav-link :href="route('organization.edit')" :active="request()->routeIs('organization.edit')">
                            {{ __('Organization') }}
                        </x-nav-link>
                        <x-nav-link :href="route('finance.index')" :active="request()->routeIs('finance.*')">
                            {{ __('Finance') }}
                        </x-nav-link>
                        <x-nav-link :href="route('team.index')" :active="request()->routeIs('team.*')">
                            {{ __('Team') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-[#6D4C41] bg-white hover:text-[#9C6644] focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if (Auth::user()->is_super_admin)
                            <div class="border-t border-gray-100"></div>
                            <x-dropdown-link :href="route('super-admin.dashboard')" class="text-indigo-600 font-semibold">
                                {{ __('Super Admin Console') }}
                            </x-dropdown-link>
                            <div class="border-t border-gray-100"></div>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-[#6D4C41] hover:text-[#9C6644] hover:bg-[#F5F0E6] focus:outline-none focus:bg-[#F5F0E6] focus:text-[#9C6644] transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-[#FDFBF7]">
        <div class="pt-2 pb-3 space-y-1">
            @if(request()->routeIs('super-admin.*'))
                <x-responsive-nav-link :href="route('super-admin.dashboard')" :active="request()->routeIs('super-admin.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('super-admin.tenants.index')" :active="request()->routeIs('super-admin.tenants.*')">
                    {{ __('Tenants') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('super-admin.categories.index')" :active="request()->routeIs('super-admin.categories.*')">
                    {{ __('Categories') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('super-admin.payouts.index')" :active="request()->routeIs('super-admin.payouts.*')">
                    {{ __('Payouts') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('super-admin.settings.index')" :active="request()->routeIs('super-admin.settings.*')">
                    {{ __('Settings') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                    {{ __('Events') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('coupons.index')" :active="request()->routeIs('coupons.*')">
                    {{ __('Coupons') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('organization.edit')" :active="request()->routeIs('organization.edit')">
                    {{ __('Organization') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('finance.index')" :active="request()->routeIs('finance.*')">
                    {{ __('Finance') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('team.index')" :active="request()->routeIs('team.*')">
                    {{ __('Team') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-[#E6DCCF]">
            <div class="px-4">
                <div class="font-medium text-base text-[#3E2723]">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-[#6D4C41]">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
