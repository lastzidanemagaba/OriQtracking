var configs = [];

$(document).ready(function() {
	refreshAdminInfo();
});

var app = angular.module("qtracking", []);
app.controller("qtracking-controller", function ($scope, $http) {
	$http.get("https://dev.jtindonesia.com/admin/index.php/admin/get_settings").then(function (response) {
		$scope.configs = response.data;
		configs = $scope.configs;
	});
	$scope.deleteSetting = function (id) {
		if (confirm("Apakah Anda yakin ingin menghapus konfigurasi berikut?")) {
			var fd = new FormData();
			fd.append("id", id);
			fetch(API_URL+"/admin/delete_setting", {
				body: fd,
				method: 'POST'
			})
				.then(response => response.text())
				.then(async (response) => {
					window.location.href = "https://dev.jtindonesia.com/admin/settings";
				});
		}
	};
});

function save() {
	var ids = [];
	var values = [];
	for (var i=0; i<configs.length; i++) {
		var config = configs[i];
		ids.push(config['id'].toString());
		values.push($("#"+config['name'].toString()).val());
	}
	var fd = new FormData();
	fd.append("ids", JSON.stringify(ids));
	fd.append("values", JSON.stringify(values));
	fetch(API_URL+"/admin/save_settings", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			window.location.href = "https://dev.jtindonesia.com/admin/settings";
		});
}
