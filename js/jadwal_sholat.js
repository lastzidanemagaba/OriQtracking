var app = angular.module("qtracking", []);
app.controller("qtracking-controller", function ($scope, $http) {
	$http.get(API_URL+"/admin/get_jadwal_sholat").then(function (response) {
		$scope.schedules = response.data;
	});
	$scope.editJadwalSholat = function (id) {
		$.redirect(API_URL+"/jadwalsholat/edit", {
			'id': ""+id
		});
	};
	$scope.deleteJadwalSholat = function (id) {
		if (confirm("Apakah Anda yakin ingin menghapus panduan umroh berikut?")) {
			var fd = new FormData();
			fd.append("id", id);
			fetch(API_URL+"/admin/delete_jadwal_sholat", {
				method: 'POST',
				body: fd
			})
				.then(response => response.text())
				.then(async (response) => {
					window.location.reload();
				});
		}
	};
});
