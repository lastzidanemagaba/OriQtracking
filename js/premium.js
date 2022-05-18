var app = angular.module("qtracking", []);
app.controller("qtracking-controller", function ($scope, $http) {
	$http.get(API_URL+"/admin/get_premium_subscriptions").then(function (response) {
		$scope.premiums = response.data;
	});
	$scope.getBenefits = function (benefitsString) {
		var benefits = "";
		var benefitsJSON = JSON.parse(benefitsString);
		for (var j=0; j<benefitsJSON.length; j++) {
			benefits += ("• "+benefitsJSON[j]+"<br/>");
		}
		return benefits;
	};
	$scope.editPremium = function (id) {
		$.redirect(API_URL+"/premium/edit", {
			'id': ""+id
		});
	};
	$scope.deletePremium = function (id) {
		if (confirm("Apakah Anda yakin ingin menghapus panduan umroh berikut?")) {
			var fd = new FormData();
			fd.append("id", id);
			fetch(API_URL+"/admin/delete_premium_subscription", {
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
