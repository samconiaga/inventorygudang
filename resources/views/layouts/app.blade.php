<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Inventory Gudang</title>

  <!-- ✅ FAVICON -->
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

  <!-- ✅ Bootstrap + FontAwesome (CSS) -->
  <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">

  <!-- ✅ Template CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">

  <!-- ✅ Select2 (CSS) -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

  <!-- ✅ DataTables (CSS) -->
  <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.4.1/css/dataTables.dateTime.min.css">

  <!-- ======================================================
       THEME MERAH (CUSTOM)
       ====================================================== -->
  <style>
    :root{
      --ig-red:#c62828;
      --ig-red-dark:#8e0000;
      --ig-red-soft:#ffebee;
      --ig-red-border:#ef9a9a;
      --ig-active:#d32f2f;
      --ig-active-2:#b71c1c;
    }

    .navbar-bg{ background: var(--ig-red) !important; }
    .main-navbar{ background: transparent !important; }
    .main-navbar .nav-link,
    .main-navbar .nav-link i,
    .main-navbar .dropdown-toggle{ color:#fff !important; }

    .search-element input.form-control{
      border: 1px solid rgba(255,255,255,.35) !important;
    }
    .search-element input.form-control:focus{
      border-color: rgba(255,255,255,.6) !important;
      box-shadow: 0 0 0 .2rem rgba(255,255,255,.15) !important;
    }
    .search-element .btn i{ color:#fff !important; }

    .main-sidebar{ background: var(--ig-red-dark) !important; }

    body.sidebar-mini .main-sidebar,
    body.sidebar-mini .main-sidebar::after,
    body.sidebar-mini .main-sidebar.sidebar-style-2,
    body.sidebar-mini .main-sidebar.sidebar-style-2 #sidebar-wrapper{
      background: var(--ig-red-dark) !important;
    }

    .sidebar-brand a,
    .sidebar-brand-sm a{
      color:#fff !important;
      font-weight:700;
      letter-spacing:.5px;
    }

    .sidebar-brand.sidebar-brand-sm{
      background: var(--ig-red-dark) !important;
      border-bottom: 1px solid rgba(255,255,255,.12);
    }

    .sidebar-menu li a{
      color:#fff !important;
      opacity:.92;
    }
    .sidebar-menu li a.active,
    .sidebar-menu li a:hover{
      background: var(--ig-active) !important;
      opacity:1;
    }

    .card .card-header{
      background: var(--ig-red-soft) !important;
      border-bottom: 2px solid var(--ig-red-border) !important;
    }

    table thead th{
      background: var(--ig-red) !important;
      color:#fff !important;
    }

    .main-footer{
      border-top: 1px solid var(--ig-red-border) !important;
    }

    /* ===========================
       NOTIFICATION (UI kecil)
       =========================== */
    .notif-badge {
      position: absolute;
      top: 8px;
      right: 6px;
      font-size: 10px;
      padding: 3px 6px;
      border-radius: 12px;
      background: #fff;
      color: var(--ig-red);
      font-weight: 700;
      line-height: 1;
    }
    .dropdown-notif {
      width: 360px;
      padding: 0;
      overflow: hidden;
    }
    .dropdown-notif .dropdown-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      padding: 12px 14px;
      background: #fff;
      border-bottom: 1px solid #eee;
      font-weight: 700;
    }
    .dropdown-notif .notif-list{
      max-height: 360px;
      overflow-y: auto;
      background: #fff;
    }
    .dropdown-notif .notif-item{
      padding: 10px 14px;
      border-bottom: 1px solid #f2f2f2;
      cursor: pointer;
    }
    .dropdown-notif .notif-item:hover{
      background: #fafafa;
    }
    .dropdown-notif .notif-title{
      font-weight: 700;
      font-size: 13px;
      margin-bottom: 2px;
      color: #111;
    }
    .dropdown-notif .notif-msg{
      font-size: 12px;
      color: #444;
      margin-bottom: 4px;
    }
    .dropdown-notif .notif-time{
      font-size: 11px;
      color: #888;
    }
    .dropdown-notif .dropdown-footer{
      padding: 10px 14px;
      background: #fff;
      border-top: 1px solid #eee;
      text-align: center;
    }
    .btn-link-red{
      color: var(--ig-red);
      font-weight: 700;
    }
    .btn-link-red:hover{
      color: var(--ig-red-dark);
      text-decoration: none;
    }

    /* small helpers */
    .sidebar .menu-header { color: rgba(255,255,255,.7); padding: 8px 16px; font-size: 12px; text-transform: uppercase; }
  </style>

  @stack('styles')
</head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>

      {{-- NAVBAR --}}
      <nav class="navbar navbar-expand-lg main-navbar">
        <form class="form-inline mr-auto">
          <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
            <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
          </ul>
          <div class="search-element">
            <input class="form-control" type="search" placeholder="Search" aria-label="Search" data-width="250">
            <button class="btn" type="submit"><i class="fas fa-search"></i></button>
            <div class="search-backdrop"></div>
          </div>
        </form>

        <ul class="navbar-nav navbar-right">

          {{-- 🔔 NOTIFIKASI --}}
          <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link nav-link-lg position-relative" id="notifBell">
              <i class="far fa-bell"></i>
              <span class="notif-badge d-none" id="notifBadge">0</span>
            </a>

            <div class="dropdown-menu dropdown-menu-right dropdown-notif">
              <div class="dropdown-header">
                <span>Notifikasi</span>

                <form id="notifReadAllForm" method="POST" action="{{ route('notifications.read-all') }}" class="m-0">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-link btn-link-red p-0">
                    Tandai semua dibaca
                  </button>
                </form>
              </div>

              <div class="notif-list" id="notifList">
                <div class="p-3 text-center text-muted">Memuat notifikasi...</div>
              </div>

              <div class="dropdown-footer">
                <a class="btn-link-red" href="{{ route('notifications.index') }}">
                  Lihat semua
                </a>
              </div>
            </div>
          </li>

          {{-- USER --}}
          <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
              <img alt="image" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="rounded-circle mr-1" width="36" height="36">
              <div class="d-sm-none d-lg-inline-block">Hi, {{ auth()->user()->name }}</div>
            </a>

            <div class="dropdown-menu dropdown-menu-right">
              <a href="{{ url('/ubah-password') }}" class="dropdown-item has-icon">
                <i class="fas fa-lock"></i> Ubah Password
              </a>

              <div class="dropdown-divider"></div>

              <a class="dropdown-item" href="{{ route('logout') }}"
                 onclick="event.preventDefault();
                          Swal.fire({
                              title: 'Konfirmasi Keluar',
                              text: 'Apakah Anda yakin ingin keluar?',
                              icon: 'warning',
                              showCancelButton: true,
                              confirmButtonColor: '#c62828',
                              cancelButtonColor: '#6c757d',
                              confirmButtonText: 'Ya, Keluar!'
                            }).then((result) => {
                              if (result.isConfirmed) {
                                document.getElementById('logout-form').submit();
                              }
                            });">
                <i class="fas fa-sign-out-alt"></i> {{ __('Keluar') }}
              </a>

              <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
              </form>
            </div>
          </li>
        </ul>
      </nav>

      {{-- SIDEBAR --}}
      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">

          <div class="sidebar-brand">
            <a href="{{ url('/') }}">INVENTORY GUDANG</a>
          </div>

          <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ url('/') }}">IG</a>
          </div>

          <ul class="sidebar-menu">

            {{-- ================== KEPALA GUDANG ================== --}}
            @if (optional(auth()->user()->role)->role === 'kepala gudang')
              <li class="sidebar-item">
                <a class="nav-link {{ Request::is('/') || Request::is('dashboard') ? 'active' : '' }}" href="/">
                  <i class="fas fa-fire"></i> <span class="align-middle">Dashboard</span>
                </a>
              </li>

              <li class="menu-header">LAPORAN</li>
              <li><a class="nav-link {{ Request::is('laporan-stok') ? 'active' : '' }}" href="/laporan-stok"><i class="far fa-file"></i><span>Stok</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-masuk') ? 'active' : '' }}" href="/laporan-barang-masuk"><i class="fas fa-file-import"></i><span>Barang Masuk</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-keluar') ? 'active' : '' }}" href="/laporan-barang-keluar"><i class="fas fa-file-export"></i><span>Barang Keluar</span></a></li>

              <li class="menu-header">MANAJEMEN USER</li>
              <li><a class="nav-link {{ Request::is('aktivitas-user') ? 'active' : '' }}" href="/aktivitas-user"><i class="fas fa-list"></i><span>Aktivitas User</span></a></li>
            @endif

            {{-- ================== SUPERADMIN ================== --}}
            @if (optional(auth()->user()->role)->role === 'superadmin')
              <li class="sidebar-item">
                <a class="nav-link {{ Request::is('/') || Request::is('dashboard') ? 'active' : '' }}" href="/">
                  <i class="fas fa-fire"></i> <span class="align-middle">Dashboard</span>
                </a>
              </li>

              <li class="menu-header">DATA MASTER</li>
              <li class="dropdown">
                <a href="#" class="nav-link has-dropdown {{ Request::is('barang*') || Request::is('jenis-barang*') || Request::is('satuan-barang*') ? 'active' : '' }}" data-toggle="dropdown">
                  <i class="fas fa-cubes"></i><span>Data Barang</span>
                </a>
                <ul class="dropdown-menu">
                  <li><a class="nav-link {{ Request::is('barang') ? 'active' : '' }}" href="/barang"><i class="fas fa-circle fa-xs"></i> Nama Barang</a></li>
                  <li><a class="nav-link {{ Request::is('jenis-barang') ? 'active' : '' }}" href="/jenis-barang"><i class="fas fa-circle fa-xs"></i> Jenis</a></li>
                  <li><a class="nav-link {{ Request::is('satuan-barang') ? 'active' : '' }}" href="/satuan-barang"><i class="fas fa-circle fa-xs"></i> Satuan</a></li>
                </ul>
              </li>

              <li class="dropdown">
                <a href="#" class="nav-link has-dropdown {{ Request::is('supplier*') || Request::is('customer*') ? 'active' : '' }}" data-toggle="dropdown">
                  <i class="fas fa-building"></i><span>Data Utama</span>
                </a>
                <ul class="dropdown-menu">
                  <li><a class="nav-link {{ Request::is('supplier') ? 'active' : '' }}" href="/supplier"><i class="fas fa-circle fa-xs"></i> Supplier</a></li>
                  <li><a class="nav-link {{ Request::is('customer') ? 'active' : '' }}" href="/customer"><i class="fas fa-circle fa-xs"></i> Department</a></li>
                </ul>
              </li>

              <li class="menu-header">TRANSAKSI</li>
              <li>
                <a class="nav-link {{ Request::is('purchase-orders*') ? 'active' : '' }}" href="/purchase-orders">
                  <i class="fas fa-file-invoice"></i><span>Purchase Order (PO)</span>
                </a>
              </li>
              <li><a class="nav-link {{ Request::is('barang-masuk*') ? 'active' : '' }}" href="/barang-masuk"><i class="fas fa-arrow-right"></i><span>Barang Masuk</span></a></li>
              <li><a class="nav-link {{ Request::is('barang-keluar*') ? 'active' : '' }}" href="/barang-keluar"><i class="fas fa-arrow-left"></i> <span>Barang Keluar</span></a></li>

              {{-- PERMINTAAN BARANG (ditambahkan) --}}
              <li><a class="nav-link {{ Request::is('permintaan') || Request::is('permintaan/*') ? 'active' : '' }}" href="{{ route('permintaan.index') }}"><i class="fas fa-box"></i><span>Permintaan Barang</span></a></li>
              <li><a class="nav-link {{ Request::is('permintaan-admin') ? 'active' : '' }}" href="{{ route('permintaan.admin') }}"><i class="fas fa-history"></i><span>Histori Permintaan</span></a></li>

              <li class="menu-header">LAPORAN</li>
              <li><a class="nav-link {{ Request::is('laporan-stok') ? 'active' : '' }}" href="/laporan-stok"><i class="far fa-file"></i><span>Stok</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-masuk') ? 'active' : '' }}" href="/laporan-barang-masuk"><i class="fas fa-file-import"></i><span>Barang Masuk</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-keluar') ? 'active' : '' }}" href="/laporan-barang-keluar"><i class="fas fa-file-export"></i><span>Barang Keluar</span></a></li>

              <li class="menu-header">MANAJEMEN USER</li>
              <li><a class="nav-link {{ Request::is('data-pengguna') ? 'active' : '' }}" href="/data-pengguna"><i class="fas fa-users"></i><span>Data Pengguna</span></a></li>
              <li><a class="nav-link {{ Request::is('hak-akses') ? 'active' : '' }}" href="/hak-akses"><i class="fas fa-user-lock"></i><span>Hak Akses/Role</span></a></li>
              <li><a class="nav-link {{ Request::is('aktivitas-user') ? 'active' : '' }}" href="/aktivitas-user"><i class="fas fa-list"></i><span>Aktivitas User</span></a></li>
            @endif

            {{-- ================== ADMIN GUDANG ================== --}}
            @if (optional(auth()->user()->role)->role === 'admin gudang')
              <li class="sidebar-item">
                <a class="nav-link {{ Request::is('/') || Request::is('dashboard') ? 'active' : '' }}" href="/">
                  <i class="fas fa-fire"></i> <span class="align-middle">Dashboard</span>
                </a>
              </li>

              <li class="menu-header">DATA MASTER</li>
              <li class="dropdown">
                <a href="#" class="nav-link has-dropdown {{ Request::is('barang*') || Request::is('jenis-barang*') || Request::is('satuan-barang*') ? 'active' : '' }}" data-toggle="dropdown">
                  <i class="fas fa-cubes"></i><span>Data Barang</span>
                </a>
                <ul class="dropdown-menu">
                  <li><a class="nav-link {{ Request::is('barang') ? 'active' : '' }}" href="/barang"><i class="fas fa-circle fa-xs"></i> Nama Barang</a></li>
                  <li><a class="nav-link {{ Request::is('jenis-barang') ? 'active' : '' }}" href="/jenis-barang"><i class="fas fa-circle fa-xs"></i> Jenis</a></li>
                  <li><a class="nav-link {{ Request::is('satuan-barang') ? 'active' : '' }}" href="/satuan-barang"><i class="fas fa-circle fa-xs"></i> Satuan</a></li>
                </ul>
              </li>

              <li class="dropdown">
                <a href="#" class="nav-link has-dropdown {{ Request::is('supplier*') || Request::is('customer*') ? 'active' : '' }}" data-toggle="dropdown">
                  <i class="fas fa-building"></i><span>Perusahaan</span>
                </a>
                <ul class="dropdown-menu">
                  <li><a class="nav-link {{ Request::is('supplier') ? 'active' : '' }}" href="/supplier"><i class="fas fa-circle fa-xs"></i> Supplier</a></li>
                  <li><a class="nav-link {{ Request::is('customer') ? 'active' : '' }}" href="/customer"><i class="fas fa-circle fa-xs"></i> Department</a></li>
                </ul>
              </li>

              <li class="menu-header">TRANSAKSI</li>
              <li>
                <a class="nav-link {{ Request::is('purchase-orders*') ? 'active' : '' }}" href="/purchase-orders">
                  <i class="fas fa-file-invoice"></i><span>Purchase Order (PO)</span>
                </a>
              </li>
              <li><a class="nav-link {{ Request::is('barang-masuk*') ? 'active' : '' }}" href="/barang-masuk"><i class="fas fa-arrow-right"></i><span>Barang Masuk</span></a></li>
              <li><a class="nav-link {{ Request::is('barang-keluar*') ? 'active' : '' }}" href="/barang-keluar"><i class="fas fa-arrow-left"></i> <span>Barang Keluar</span></a></li>

              {{-- PERMINTAAN BARANG --}}
              <li><a class="nav-link {{ Request::is('permintaan') || Request::is('permintaan/*') ? 'active' : '' }}" href="{{ route('permintaan.index') }}"><i class="fas fa-box"></i><span>Permintaan Barang</span></a></li>
              <li><a class="nav-link {{ Request::is('permintaan-admin') ? 'active' : '' }}" href="{{ route('permintaan.admin') }}"><i class="fas fa-history"></i><span>Histori Permintaan</span></a></li>

              <li class="menu-header">LAPORAN</li>
              <li><a class="nav-link {{ Request::is('laporan-stok') ? 'active' : '' }}" href="/laporan-stok"><i class="far fa-file"></i><span>Stok</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-masuk') ? 'active' : '' }}" href="/laporan-barang-masuk"><i class="fas fa-file-import"></i><span>Barang Masuk</span></a></li>
              <li><a class="nav-link {{ Request::is('laporan-barang-keluar') ? 'active' : '' }}" href="/laporan-barang-keluar"><i class="fas fa-file-export"></i><span>Barang Keluar</span></a></li>
            @endif

          </ul>
        </aside>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          @yield('content')
          <div class="section-body"></div>
        </section>
      </div>

      <footer class="main-footer">
        <div class="footer-left">
          Copyright &copy; PT. Samco Farma
        </div>
        <div class="footer-right"></div>
      </footer>
    </div>
  </div>

  <!-- ======================================================
       ✅ JS (PENTING: JANGAN LOAD JQUERY 2x)
       Urutan: jQuery -> Popper -> Bootstrap -> plugin -> stisla scripts -> stack
       ====================================================== -->

  <!-- ✅ jQuery (LOCAL STISLA) -->
  <script src="{{ asset('assets/modules/jquery.min.js') }}"></script>

  <!-- ✅ Bootstrap 4 dependencies -->
  <script src="{{ asset('assets/modules/popper.js') }}"></script>
  <script src="{{ asset('assets/modules/tooltip.js') }}"></script>
  <script src="{{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>

  <!-- ✅ Stisla deps -->
  <script src="{{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
  <script src="{{ asset('assets/modules/moment.min.js') }}"></script>
  <script src="{{ asset('assets/js/stisla.js') }}"></script>

  <!-- ✅ Plugins -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <!-- kalau ga butuh jQuery UI, boleh hapus biar aman -->
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"
          integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0="
          crossorigin="anonymous"></script>

  <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

  <!-- SweetAlert -->
  @include('sweetalert::alert')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

  <!-- DayJs -->
  <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

  <!-- ✅ Template JS -->
  <script src="{{ asset('assets/js/scripts.js') }}"></script>
  <script src="{{ asset('assets/js/custom.js') }}"></script>

  <!-- =========================
       NOTIFIKASI SCRIPT (AJAX)
       ========================= -->
  <script>
    const NOTIF_UNREAD_URL  = @json(route('notifications.unread'));
    const NOTIF_READ_URL    = @json(route('notifications.read', ['id' => '___ID___']));
    const CSRF_TOKEN        = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function escapeHtml(str){
      return String(str ?? '')
        .replaceAll('&','&amp;')
        .replaceAll('<','&lt;')
        .replaceAll('>','&gt;')
        .replaceAll('"','&quot;')
        .replaceAll("'",'&#039;');
    }

    function setBadge(count){
      const badge = $('#notifBadge');
      if (count && Number(count) > 0){
        badge.text(count > 99 ? '99+' : count);
        badge.removeClass('d-none');
      } else {
        badge.addClass('d-none');
      }
    }

    function renderNotif(items){
      const wrap = $('#notifList');

      if (!items || items.length === 0){
        wrap.html('<div class="p-3 text-center text-muted">Tidak ada notifikasi baru.</div>');
        return;
      }

      let html = '';
      items.forEach(it => {
        const id    = it.id;
        const title = escapeHtml(it.title || 'Notifikasi');
        const msg   = escapeHtml(it.message || '');
        const time  = escapeHtml(it.time || '');
        const url   = escapeHtml(it.url || '#');

        html += `
          <div class="notif-item" data-id="${id}" data-url="${url}">
            <div class="notif-title">${title}</div>
            <div class="notif-msg">${msg}</div>
            <div class="notif-time">${time}</div>
          </div>
        `;
      });

      wrap.html(html);
    }

    async function fetchUnread(){
      try{
        const res = await fetch(NOTIF_UNREAD_URL, { headers: { 'Accept': 'application/json' }});
        if (!res.ok) return;
        const json = await res.json();
        setBadge(json.count || 0);
        renderNotif(json.items || []);
      }catch(e){
        // console.error(e);
      }
    }

    async function markAsRead(id){
      const url = NOTIF_READ_URL.replace('___ID___', id);
      return fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json'
        }
      });
    }

    $(document).on('click', '.notif-item', async function(){
      const id  = $(this).data('id');
      const url = $(this).data('url') || '#';

      try{ await markAsRead(id); }catch(e){}
      fetchUnread();
      if (url && url !== '#') window.location.href = url;
    });

    $('#notifReadAllForm').on('submit', async function(e){
      e.preventDefault();
      try{
        await fetch(this.action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
          }
        });
      }catch(e){}
      fetchUnread();
    });

    $(document).ready(function(){
      fetchUnread();
      setInterval(fetchUnread, 8000);
    });
  </script>

  @stack('scripts')
</body>
</html>