var app = angular.module("qtracking", []);
app.controller("qtracking-controller", function ($scope, $http) {
	$http.get(API_URL+"/admin/get_users").then(function (response) {
		$scope.users = response.data;
	});
	$scope.upgrade = function (userID) {
		if (confirm("Apakah Anda yakin ingin meningkatkan status pengguna berikut menjadi premium?")) {
			var fd = new FormData();
			fd.append("id", userID);
			fetch(API_URL+"/admin/upgrade_user", {
				method: 'POST',
				body: fd
			})
				.then(response => response.text())
				.then(async (response) => {
					window.location.href = "http://localhost/admin/premiumuser";
				});
		}
	};
});

$(document).ready(function() {
	refreshAdminInfo();
});
