<?php
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!-- Tell the browser to be responsive to screen width -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="keywords"
		  content="wrappixel, admin dashboard, html css dashboard, web dashboard, bootstrap 5 admin, bootstrap 5, css3 dashboard, bootstrap 5 dashboard, materialpro admin bootstrap 5 dashboard, frontend, responsive bootstrap 5 admin template, materialpro admin lite design, materialpro admin lite dashboard bootstrap 5 dashboard template">
	<meta name="description"
		  content="Material Pro Lite is powerful and clean admin dashboard template, inpired from Bootstrap Framework">
	<meta name="robots" content="noindex,nofollow">
	<title>Pengaturan</title>
	<!-- Favicon icon -->
	<link rel="icon" type="image/png" sizes="16x16" href="http://localhost/admin/assets/images/logo.png">
	<!-- Custom CSS -->
	<link href="http://localhost/admin/css/style.min.css" rel="stylesheet">
	<script src="http://localhost/admin/assets/plugins/jquery/dist/jquery.min.js"></script>
	<script src="http://localhost/admin/js/global.js"></script>
	<script src="http://localhost/admin/js/angular.js"></script>
	<script src="http://localhost/admin/js/settings.js"></script>
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body>
<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ============================================================== -->
<div class="preloader">
	<div class="lds-ripple">
		<div class="lds-pos"></div>
		<div class="lds-pos"></div>
	</div>
</div>
<!-- ============================================================== -->
<!-- Main wrapper - style you can find in pages.scss -->
<!-- ============================================================== -->
<div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
	 data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
	<!-- ============================================================== -->
	<!-- Topbar header - style you can find in pages.scss -->
	<!-- ============================================================== -->
	<header class="topbar" data-navbarbg="skin6">
		<nav class="navbar top-navbar navbar-expand-md navbar-dark">
			<div class="navbar-header" data-logobg="skin6">
				<!-- ============================================================== -->
				<!-- Logo -->
				<!-- ============================================================== -->
				<a class="navbar-brand ms-4" href="http://localhost/admin">
					<!-- Logo icon -->
					<b class="logo-icon">
						<!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
						<!-- Dark Logo icon -->
						<img src="http://localhost/admin/assets/images/logo.png" alt="homepage" class="dark-logo" width="30px" height="30px" />

					</b>
					<!--End Logo icon -->
					<!-- Logo text -->
					<span class="logo-text">
                            <!-- dark Logo text -->
                            <img src="http://localhost/admin/assets/images/title.png" alt="homepage" class="dark-logo" width="120px" height="18px" style="margin-left: 5px;" />

                        </span>
				</a>
				<!-- ============================================================== -->
				<!-- End Logo -->
				<!-- ============================================================== -->
				<!-- ============================================================== -->
				<!-- toggle and nav items -->
				<!-- ============================================================== -->
				<a class="nav-toggler waves-effect waves-light text-white d-block d-md-none"
				   href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
			</div>
			<!-- ============================================================== -->
			<!-- End Logo -->
			<!-- ============================================================== -->
			<div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
				<ul class="navbar-nav d-lg-none d-md-block ">
					<li class="nav-item">
						<a class="nav-toggler nav-link waves-effect waves-light text-white "
						   href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
					</li>
				</ul>
				<!-- ============================================================== -->
				<!-- toggle and nav items -->
				<!-- ============================================================== -->
				<ul class="navbar-nav me-auto mt-md-0 ">
					<!-- ============================================================== -->
					<!-- Search -->
					<!-- ============================================================== -->

					<li class="nav-item search-box">
						<a class="nav-link text-muted" href="javascript:void(0)"><i class="ti-search"></i></a>
						<form class="app-search" style="display: none;">
							<input type="text" class="form-control" placeholder="Search &amp; enter"> <a
								class="srh-btn"><i class="ti-close"></i></a> </form>
					</li>
				</ul>

				<!-- ============================================================== -->
				<!-- Right side toggle and nav items -->
				<!-- ============================================================== -->
				<ul class="navbar-nav">
					<!-- ============================================================== -->
					<!-- User profile and search -->
					<!-- ============================================================== -->
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<img id="admin-profile-picture" width="40px" height="40px" alt="user" style="border-radius: 20px;">
							<span id="admin-name"></span>
						</a>
						<ul class="dropdown-menu" aria-labelledby="navbarDropdown"></ul>
					</li>
				</ul>
			</div>
		</nav>
	</header>
	<!-- ============================================================== -->
	<!-- End Topbar header -->
	<!-- ============================================================== -->
	<!-- ============================================================== -->
	<!-- Left Sidebar - style you can find in sidebar.scss  -->
	<!-- ============================================================== -->
	<aside class="left-sidebar" data-sidebarbg="skin6">
		<!-- Sidebar scroll-->
		<div class="scroll-sidebar">
			<!-- Sidebar navigation-->
			<?php $this->load->view('sidebar', array('current_menu' => 'settings')); ?>
			<!-- End Sidebar navigation -->
		</div>
		<!-- End Sidebar scroll-->
		<?php $this->load->view('sidebar_footer', array('current_menu' => 'admin')); ?>
	</aside>
	<!-- ============================================================== -->
	<!-- End Left Sidebar - style you can find in sidebar.scss  -->
	<!-- ============================================================== -->
	<!-- ============================================================== -->
	<!-- Page wrapper  -->
	<!-- ============================================================== -->
	<div class="page-wrapper">
		<!-- ============================================================== -->
		<!-- Bread crumb and right sidebar toggle -->
		<!-- ============================================================== -->
		<div class="page-breadcrumb">
			<div class="row align-items-center">
				<div class="col-md-6 col-8 align-self-center">
					<h3 class="page-title mb-0 p-0">Pengaturan</h3>
					<div class="d-flex align-items-center">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">Pengaturan</li>
							</ol>
						</nav>
					</div>
				</div>
			</div>
		</div>
		<!-- ============================================================== -->
		<!-- End Bread crumb and right sidebar toggle -->
		<!-- ============================================================== -->
		<!-- ============================================================== -->
		<!-- Container fluid  -->
		<!-- ============================================================== -->
		<div class="container-fluid">
			<!-- ============================================================== -->
			<!-- Start Page Content -->
			<!-- ============================================================== -->
			<div class="row">
				<!-- column -->
				<div class="col-lg-8 col-xlg-9 col-md-7">
					<div ng-app="qtracking" ng-controller="qtracking-controller" class="card">
						<div class="card-body">
							<div class="form-horizontal form-material mx-2">
								<div class="form-group">
									<label class="col-md-12 mb-0">Delay Update Lokasi:</label>
									<div class="col-md-12">
										<input id="locationUpdateDelay" ng-model="locationUpdateDelay" type="number"
											   class="form-control ps-0 form-control-line">
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-12 mb-0">Delay Refresh Map:</label>
									<div class="col-md-12">
										<input id="mapUpdateDelay" ng-model="mapUpdateDelay" type="number"
											   class="form-control ps-0 form-control-line">
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-12 mb-0">Delay Minta Update Lokasi:</label>
									<div class="col-md-12">
										<input id="minLocationUpdateDelay" ng-model="minLocationUpdateDelay" type="number"
											   class="form-control ps-0 form-control-line">
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-12 mb-0">Akurasi Update Lokasi:</label>
									<select id="locationUpdateAccuracy" ng-model="locationUpdateAccuracy" class="form-select shadow-none ps-0 border-0 form-control-line">
										<option value="lowest">Terendah</option>
										<option value="low">Rendah</option>
										<option value="medium">Medium</option>
										<option value="high">Tinggi</option>
										<option value="best">Terbaik</option>
										<option value="bestForNavigation">Terbaik untuk Navigasi</option>
										<option value="reduced">Dikurangi</option>
									</select>
								</div>
								<div class="form-group">
									<label class="col-md-12 mb-0">Deskripsi Barang Rekomendasi:</label>
									<div class="col-md-12">
										<textarea id="recommendedItemDescription" ng-model="recommendedItemDescription" rows="5" class="form-control ps-0 form-control-line"></textarea>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-12 mb-0">Teks Share (Bahasa Indonesia):</label>
									<div class="col-md-12">
										<textarea id="shareTextId" ng-model="shareTextId" rows="5" class="form-control ps-0 form-control-line"></textarea>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-12 mb-0">Teks Share (Bahasa Inggris):</label>
									<div class="col-md-12">
										<textarea id="shareTextEn" ng-model="shareTextEn" rows="5" class="form-control ps-0 form-control-line"></textarea>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-12 mb-0">Radius Tempat Minimum:</label>
									<div class="col-md-12">
										<input id="minPlaceRadius" ng-model="minPlaceRadius" type="text"
											   class="form-control ps-0 form-control-line">
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-12 mb-0">Radius Tempat Maksimum:</label>
									<div class="col-md-12">
										<input id="maxPlaceRadius" ng-model="maxPlaceRadius" type="text"
											   class="form-control ps-0 form-control-line">
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-12 mb-0">Server key FCM:</label>
									<div class="col-md-12">
										<input id="fcmServerKey" ng-model="fcmServerKey" type="text"
											   class="form-control ps-0 form-control-line">
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-12 mb-0">API key Pushy:</label>
									<div class="col-md-12">
										<input id="pushyApiKey" ng-model="pushyApiKey" type="text"
											   class="form-control ps-0 form-control-line">
									</div>
								</div>
								<div class="form-group">
									<div class="col-sm-12 d-flex">
										<button id="save" onclick="save()" class="btn btn-success mx-auto mx-md-0 text-white">Simpan</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- ============================================================== -->
			<!-- End PAge Content -->
			<!-- ============================================================== -->
			<!-- ============================================================== -->
			<!-- Right sidebar -->
			<!-- ============================================================== -->
			<!-- .right-sidebar -->
			<!-- ============================================================== -->
			<!-- End Right sidebar -->
			<!-- ============================================================== -->
		</div>
		<!-- ============================================================== -->
		<!-- End Container fluid  -->
		<!-- ============================================================== -->
		<!-- ============================================================== -->
		<!-- footer -->
		<!-- ============================================================== -->
		<!-- ============================================================== -->
		<!-- End footer -->
		<!-- ============================================================== -->
	</div>
	<!-- ============================================================== -->
	<!-- End Page wrapper  -->
	<!-- ============================================================== -->
</div>
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- All Jquery -->
<!-- ============================================================== -->
<!-- Bootstrap tether Core JavaScript -->
<script src="http://localhost/admin/assets/plugins/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="http://localhost/admin/js/jquery.redirect.js"></script>
<script src="js/app-style-switcher.js"></script>
<!--Wave Effects -->
<script src="js/waves.js"></script>
<!--Menu sidebar -->
<script src="js/sidebarmenu.js"></script>
<!--Custom JavaScript -->
<script src="js/custom.js"></script>
<input id="admin-id" type="text" style="width: 0; height: 0; visibility: hidden;" value="<?php echo $adminID; ?>">
</body>

</html>
