var babID = 0;
var subbabID = 0;

var app = angular.module("qtracking", []);
app.controller("qtracking-controller", function ($scope, $http) {
	refreshAdminInfo();
	babID = parseInt($("#bab-id").val().trim());
	subbabID = parseInt($("#subbab-id").val().trim());
	$http({
		url: API_URL+"/admin/get_panduan_umroh_by_bab_subbab",
		method: 'POST',
		data: "bab_id="+babID+"&subbab_id="+subbabID,
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then(function (response) {
		$scope.panduan = response.data;
	});
	$scope.addPanduanUmroh = function(id) {
		$.redirect(API_URL+"/panduanumroh/add_panduan_umroh", {
			'id': ""+id,
			'bab_id': ""+babID,
			"subbab_id": ""+subbabID
		});
	};
	$scope.backToSub = function(babId) {
		$.redirect(API_URL+"/panduanumroh/view_subbab", {
			'bab_id': ""+babId
		});
	};
	$scope.viewPanduanUmroh = function(id) {
		$.redirect(API_URL+"/panduanumroh/view_panduan_umroh", {
			'id': ""+id
		});
	};
	$scope.editPanduanUmroh = function(id) {
		$.redirect(API_URL+"/panduanumroh/edit", {
			'id': ""+id,
			"bab_id": ""+babID,
			"subbab_id": ""+subbabID
		});
	};
	$scope.deletePanduanUmroh = function(id) {
		if (confirm("Apakah Anda yakin ingin menghapus panduan umroh berikut?")) {
			var fd = new FormData();
			fd.append("id", id);
			fetch(API_URL+"/admin/delete_panduan_umroh", {
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
