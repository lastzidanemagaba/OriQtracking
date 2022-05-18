var admins = [];

$(document).ready(function() {
	refreshAdminInfo();
});

var app = angular.module("qtracking", []);
app.controller("qtracking-controller", function ($scope, $http) {
	$http.get(API_URL+"/admin/get_admins").then(function (response) {
		admins = response.data;
		console.log("Admins:");
		console.log(admins);
		$scope.admins = admins;
		$scope.editAdmin = function (adminID) {
			$.redirect(API_URL+"/admin/edit", {
				'id': ""+adminID
			});
		};
		$scope.getContent = function() {
			return "<span style='color: blue;'>Test</span>";
		};
		$scope.deleteAdmin = function (adminID) {
			if (confirm("Apakah Anda yakin ingin menghapus admin berikut?")) {
				if (admins.length <= 1) {
					alert("Tidak bisa menghapus admin terakhir");
					return;
				}
				var fd = new FormData();
				fd.append("id", adminID);
				fetch(API_URL+"/admin/delete_admin", {
					body: fd,
					method: 'POST'
				})
					.then(response => response.text())
					.then(async (response) => {
						window.location.href = "https://dev.jtindonesia.com/admin/admin";
					});
			}
		};
	});
});
