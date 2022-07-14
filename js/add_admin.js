var profilePictureChanged = false;
var profilePictureFile = null;

$(document).ready(function() {
	refreshAdminInfo();
});

function selectProfilePicture() {
	$("#select-image").on("change", function(e) {
		profilePictureFile = e.target.files[0];
		var reader = new FileReader();
		reader.onload = function(e) {
			profilePictureChanged = true;
			$("#profile-picture").attr("src", e.target.result);
		};
		reader.readAsDataURL(this.files[0]);
	});
	$("#select-image").click();
}

function save() {
	var name = $("#name").val().trim();
	var email = $("#email").val().trim();
	var password = $("#password").val();
	if (name == "") {
		alert("Mohon masukkan nama");
		return;
	}
	if (email == "") {
		alert("Mohon masukkan email");
		return;
	}
	if (password.trim() == "") {
		alert("Mohon masukkan kata sandi");
		return;
	}
	var fd = new FormData();
	fd.append("name", name);
	fd.append("email", email);
	fd.append("password", password);
	fd.append("profile_picture_changed", profilePictureChanged?"1":"0");
	fd.append("profile_picture", profilePictureFile);
	fetch(API_URL+"/admin/add_admin", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			var obj = JSON.parse(response);
			var responseCode = parseInt(obj['response_code'].toString());
			if (responseCode == 1) {
				window.location.href = "http://localhost/admin/admin";
			} else if (responseCode == -1) {
				alert("Email sudah digunakan oleh admin lain.");
			}
		});
}
