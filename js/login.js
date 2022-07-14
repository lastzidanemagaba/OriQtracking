$(document).ready(function() {
	refreshAdminInfo();
});

function login() {
	var email = $("#email").val().trim();
	var password = $("#password").val().trim();
	let fd = new FormData();
	fd.append("email", email);
	fd.append("password", password);
	fetch(API_URL+"/admin/login", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			var adminInfo = JSON.parse(response);
			var responseCode = parseInt(adminInfo['response_code'].toString());
			if (responseCode == 1) {
				window.location.href = "http://localhost/admin/admin";
			} else if (responseCode == -1) {
				alert("Kombinasi email dan kata sandi salah.");
			}
		});
}
