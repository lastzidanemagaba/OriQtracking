var babID = 0;

var app = angular.module("qtracking", []);
app.controller("qtracking-controller", function ($scope, $http) {
	refreshAdminInfo();
	babID = parseInt($("#bab-id").val().trim());
	$http({
		url: API_URL+"/admin/get_panduan_umroh_subbab",
		method: 'POST',
		data: "bab_id="+babID,
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then(function (response) {
		$scope.panduan = response.data;
	});
	$scope.addSubBab = function() {
		$.redirect(API_URL+"/panduanumroh/add_subbab", {
			'bab_id': ""+babID
		});
	};
	$scope.viewPanduanUmroh = function(id) {
		$.redirect(API_URL+"/panduanumroh/view", {
			'bab_id': ""+babID,
			'subbab_id': ""+id
		});
	};
	$scope.editPanduanUmroh = function(id) {
		$.redirect(API_URL+"/panduanumroh/edit_subbab", {
			'id': ""+id
		});
	};
	$scope.deletePanduanUmroh = function(id) {
		if (confirm("Apakah Anda yakin ingin menghapus panduan umroh berikut?")) {
			var fd = new FormData();
			fd.append("subbab_id", id);
			fetch(API_URL+"/admin/delete_panduan_umroh_subbab", {
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
