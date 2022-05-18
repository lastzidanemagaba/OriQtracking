var app = angular.module("qtracking", []);
app.controller("qtracking-controller", function ($scope, $http) {
	$http.get(API_URL+"/admin/get_panduan_umroh_bab").then(function (response) {
		$scope.panduan = response.data;
	});
	$scope.viewPanduanUmroh = function(id) {
		$.redirect(API_URL+"/panduanumroh/view_subbab", {
			'bab_id': ""+id
		});
	};
	$scope.editPanduanUmroh = function(id) {
		$.redirect(API_URL+"/panduanumroh/edit_bab", {
			'id': ""+id
		});
	};
	$scope.deletePanduanUmroh = function(id) {
		if (confirm("Apakah Anda yakin ingin menghapus panduan umroh berikut?")) {
			var fd = new FormData();
			fd.append("bab_id", id);
			fetch(API_URL+"/admin/delete_panduan_umroh_bab", {
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

$(document).ready(function() {
	refreshAdminInfo();
});
