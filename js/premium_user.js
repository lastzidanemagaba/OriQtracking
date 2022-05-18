var app = angular.module("qtracking", []);
app.controller("qtracking-controller", function ($scope, $http) {
	$http.get(API_URL+"/admin/get_premium_users").then(function (response) {
		$scope.users = response.data;
	});
	$scope.downgrade = function (userID) {
		if (confirm("Apakah Anda yakin ingin menurunkan status pengguna berikut menjadi free?")) {
			var fd = new FormData();
			fd.append("id", userID);
			fetch(API_URL+"/admin/downgrade_user", {
				method: 'POST',
				body: fd
			})
				.then(response => response.text())
				.then(async (response) => {
					window.location.href = "https://dev.jtindonesia.com/admin/user";
				});
		}
	};
});

$(document).ready(function() {
	refreshAdminInfo();
});
