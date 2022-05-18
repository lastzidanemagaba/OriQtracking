var id = 0;

var app = angular.module("qtracking", []);
app.controller("qtracking-controller", function ($scope, $http) {
	id = parseInt($("#id").val().trim());
	$http({
		url: API_URL+"/admin/get_panduan_umroh_by_id",
		method: "POST",
		data: "id="+id,
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then(function (response) {
		var obj = response.data;
		$("#title").val(obj['title'].toString());
		$("#arabic-text").val(obj['arabic_text'].toString());
		$("#spelling").val(obj['spelling'].toString());
		$("#meaning").val(obj['meaning'].toString());
	});
	$scope.viewPanduanUmroh = function(id) {
		$.redirect(API_URL+"/panduanumroh/view_subbab", {
			'bab_id': ""+id
		});
	};
	$scope.editPanduanUmroh = function(id) {
		$.redirect(API_URL+"/panduanumroh/edit", {
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
