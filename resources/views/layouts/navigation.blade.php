<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <span class="text-xl font-extrabold text-blue-700 dark:text-blue-400 tracking-tight">STIFLOW</span>
                    </a>
                </div>

                <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                        @if(auth()->user()->isStaffOrAbove())
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-nav-link>
                            <x-nav-link :href="route('admin.contacts.index')" :active="request()->routeIs('admin.contacts.*')">CRM</x-nav-link>
                            <x-dropdown>
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        Affiliate
                                        <svg class="ml-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('admin.payouts.index')">Payout Komisi</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.promotors.index')">Kelola Promotor</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                            <x-dropdown>
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        LMS
                                        <svg class="ml-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('admin.courses.index')">Kursus & Lesson</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.products-catalog.index')">Katalog Produk</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.stifin-results.index')">Hasil STIFIN</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                            <x-dropdown>
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        Komunikasi
                                        <svg class="ml-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('admin.campaigns.index')">Campaign</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.templates.index')">Message Templates</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.pipelines.index')">Pipeline CRM</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                            <x-nav-link :href="route('admin.integrations.index')" :active="request()->routeIs('admin.integrations.*')">Integrations</x-nav-link>
                            <x-dropdown>
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        Lainnya
                                        <svg class="ml-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('admin.orders.index')">Orders</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.reconciliation.index')">Rekonsiliasi</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.points-ledger.index')">Points Ledger</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.staff.permissions')">Staff Permissions</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.audit.index')">Audit Logs</x-dropdown-link>
                                    <x-dropdown-link :href="route('admin.branch-settings.edit')">Pengaturan</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        @elseif(auth()->user()->isPromotor())
                            <x-nav-link :href="route('promotor.dashboard')" :active="request()->routeIs('promotor.dashboard')">Dashboard</x-nav-link>
                            <x-nav-link :href="route('promotor.checkout.index')" :active="request()->routeIs('promotor.checkout.*')">Beli Voucher</x-nav-link>
                            <x-nav-link :href="route('promotor.orders.index')" :active="request()->routeIs('promotor.orders.*')">Order Saya</x-nav-link>
                            <x-nav-link :href="route('promotor.crm.index')" :active="request()->routeIs('promotor.crm.*')">CRM</x-nav-link>
                            <x-dropdown>
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        Bisnis
                                        <svg class="ml-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('promotor.affiliate.tree')">Pohon Sponsor</x-dropdown-link>
                                    <x-dropdown-link :href="route('promotor.komisi.index')">Komisi</x-dropdown-link>
                                    <x-dropdown-link :href="route('promotor.referral-links.index')">Referral Links</x-dropdown-link>
                                    <x-dropdown-link :href="route('promotor.points.index')">Poin Reward</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                            <x-dropdown>
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        Member Area
                                        <svg class="ml-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('promotor.kelas-saya.index')">Kelas Saya</x-dropdown-link>
                                    <x-dropdown-link :href="route('promotor.hasil-stifin.index')">Hasil STIFIN</x-dropdown-link>
                                    <x-dropdown-link :href="route('promotor.unduhan.index')">Unduhan</x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        @else
                            <x-nav-link :href="route('member.dashboard')" :active="request()->routeIs('member.*')">Member</x-nav-link>
                            <x-nav-link :href="route('member.orders.index')" :active="request()->routeIs('member.orders.*')">Pesanan</x-nav-link>
                            <x-nav-link :href="route('member.kelas.index')" :active="request()->routeIs('member.kelas.*')">Kelas</x-nav-link>
                            <x-nav-link :href="route('member.lisensi.index')" :active="request()->routeIs('member.lisensi.*')">Lisensi</x-nav-link>
                            <x-nav-link :href="route('member.poin.index')" :active="request()->routeIs('member.poin.*')">Poin</x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-2 inline-flex items-center gap-1">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-100 text-gray-700 uppercase">
                                    {{ auth()->user()->isAdmin() ? 'Admin' : (auth()->user()->isStaff() ? 'Staff' : 'Promotor') }}
                                </span>
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                @if(auth()->user()->isStaffOrAbove())
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.contacts.index')" :active="request()->routeIs('admin.contacts.*')">CRM</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.pipelines.index')" :active="request()->routeIs('admin.pipelines.*')">Pipeline</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.campaigns.index')" :active="request()->routeIs('admin.campaigns.*')">Campaign</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.templates.index')" :active="request()->routeIs('admin.templates.*')">Templates</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.courses.index')" :active="request()->routeIs('admin.courses.*')">Kursus (LMS)</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.products-catalog.index')" :active="request()->routeIs('admin.products-catalog.*')">Katalog Produk</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.stifin-results.index')" :active="request()->routeIs('admin.stifin-results.*')">Hasil STIFIN</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.payouts.index')" :active="request()->routeIs('admin.payouts.*')">Payout Komisi</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.promotors.index')" :active="request()->routeIs('admin.promotors.*')">Promotor</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.integrations.index')" :active="request()->routeIs('admin.integrations.*')">Integrations</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.points-ledger.index')" :active="request()->routeIs('admin.points-ledger.*')">Points Ledger</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.staff.permissions')" :active="request()->routeIs('admin.staff.*')">Staff Permissions</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.audit.index')" :active="request()->routeIs('admin.audit.*')">Audit Logs</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">Orders</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.reconciliation.index')" :active="request()->routeIs('admin.reconciliation.*')">Rekonsiliasi</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')">Produk Voucher</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.branch-settings.edit')" :active="request()->routeIs('admin.branch-settings.*')">Pengaturan</x-responsive-nav-link>
                @elseif(auth()->user()->isPromotor())
                    <x-responsive-nav-link :href="route('promotor.dashboard')" :active="request()->routeIs('promotor.dashboard')">Dashboard</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('promotor.checkout.index')" :active="request()->routeIs('promotor.checkout.*')">Beli Voucher</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('promotor.orders.index')" :active="request()->routeIs('promotor.orders.*')">Order Saya</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('promotor.crm.index')" :active="request()->routeIs('promotor.crm.*')">CRM Saya</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('promotor.crm.boards')" :active="request()->routeIs('promotor.crm.boards*')">CRM Board</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('promotor.affiliate.tree')" :active="request()->routeIs('promotor.affiliate.*')">Pohon Sponsor</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('promotor.komisi.index')" :active="request()->routeIs('promotor.komisi.*')">Komisi</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('promotor.referral-links.index')" :active="request()->routeIs('promotor.referral-links.*')">Referral Links</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('promotor.points.index')" :active="request()->routeIs('promotor.points.*')">Poin Reward</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('promotor.kelas-saya.index')" :active="request()->routeIs('promotor.kelas-saya.*')">Kelas Saya</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('promotor.hasil-stifin.index')" :active="request()->routeIs('promotor.hasil-stifin.*')">Hasil STIFIN</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('promotor.unduhan.index')" :active="request()->routeIs('promotor.unduhan.*')">Unduhan</x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('member.dashboard')" :active="request()->routeIs('member.dashboard')">Dashboard</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('member.orders.index')" :active="request()->routeIs('member.orders.*')">Pesanan</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('member.kelas.index')" :active="request()->routeIs('member.kelas.*')">Kelas</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('member.hasil-stifin.index')" :active="request()->routeIs('member.hasil-stifin.*')">Hasil STIFIN</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('member.unduhan.index')" :active="request()->routeIs('member.unduhan.*')">Unduhan</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('member.lisensi.index')" :active="request()->routeIs('member.lisensi.*')">Lisensi</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('member.poin.index')" :active="request()->routeIs('member.poin.*')">Poin</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('member.profil.index')" :active="request()->routeIs('member.profil.*')">Profil</x-responsive-nav-link>
                @endif
            @endauth
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
