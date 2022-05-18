<?php
?>
<nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark <?php echo ($current_menu == 'admin')?'sidebar-link active':'sidebar-link' ?>"
                                href="https://dev.jtindonesia.com/admin/admin" aria-expanded="false"><i class="mdi me-2 mdi-account-key"></i><span
                                    class="hide-menu">Admin</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark <?php echo ($current_menu == 'user')?'sidebar-link active':'sidebar-link' ?>"
                                href="https://dev.jtindonesia.com/admin/user" aria-expanded="false"><i class="mdi me-2 mdi-account"></i><span
                                    class="hide-menu">Pengguna Free</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark <?php echo ($current_menu == 'premium_user')?'sidebar-link active':'sidebar-link' ?>"
                                href="https://dev.jtindonesia.com/admin/premiumuser" aria-expanded="false"><i class="mdi me-2 mdi-account"></i><span
                                    class="hide-menu">Pengguna Premium</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark <?php echo ($current_menu == 'panduan_umroh')?'sidebar-link active':'sidebar-link' ?>"
                                href="https://dev.jtindonesia.com/admin/panduanumroh" aria-expanded="false"><i class="mdi me-2 mdi-compass"></i><span
                                    class="hide-menu">Panduan Umroh</span></a></li>
                        <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark <?php echo ($current_menu == 'jadwal_sholat')?'sidebar-link active':'sidebar-link' ?>"
                                href="https://dev.jtindonesia.com/admin/jadwalsholat" aria-expanded="false"><i class="mdi me-2 mdi-calendar"></i><span
                                    class="hide-menu">Jadwal Sholat</span></a></li>
						<li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark <?php echo ($current_menu == 'premium')?'sidebar-link active':'sidebar-link' ?>"
													 href="https://dev.jtindonesia.com/admin/premium" aria-expanded="false"><i class="mdi me-2 mdi-shopping"></i><span
										class="hide-menu">Premium</span></a></li>
						<li class="sidebar-item"> <a href="https://dev.jtindonesia.com/admin/settings" class="sidebar-link waves-effect waves-dark sidebar-link"
													 aria-expanded="false"><i class="mdi me-2 mdi-settings"></i><span
										class="hide-menu">Konfigurasi</span></a></li>
                        <li class="sidebar-item"> <a href="https://dev.jtindonesia.com/admin/logout" class="sidebar-link waves-effect waves-dark sidebar-link"
                                aria-expanded="false"><i class="mdi me-2 mdi-exit-to-app"></i><span
                                    class="hide-menu">Keluar</span></a></li>
                    </ul>
                </nav>
