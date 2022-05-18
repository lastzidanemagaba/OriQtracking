var adminID = 0;
var prevEmail = "";
var profilePictureChanged = false;
var profilePictureFile = null;

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

$(document).ready(function() {
	refreshAdminInfo();
	adminID = parseInt($("#edited-admin-id").val().trim());
	var fd = new FormData();
	fd.append("id", adminID);
	fetch(API_URL+"/admin/get_admin_by_id", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			var admin = JSON.parse(response);
			prevEmail = admin['email'].toString();
			var photo = admin['photo'];
			if (photo != null && photo.toString().trim() != "null" && photo.toString().trim() != "") {
				$("#profile-picture").attr("src", USERDATA_URL+photo.toString().trim());
			}
			$("#name").val(admin['name'].toString());
			$("#email").val(prevEmail);
			$("#password").val(admin['password'].toString());
		});
});

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
	fd.append("id", adminID);
	fd.append("name", name);
	fd.append("email", email);
	fd.append("profile_picture_changed", profilePictureChanged?"1":"0");
	fd.append("profile_picture", profilePictureFile);
	fd.append("email_changed", prevEmail==email?"0":"1");
	fd.append("password", password);
	fetch(API_URL+"/admin/edit_admin", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			var obj = JSON.parse(response);
			var responseCode = parseInt(obj['response_code'].toString());
			if (responseCode == 1) {
				window.location.href = "https://dev.jtindonesia.com/admin/admin";
			} else if (responseCode == -1) {
				alert("Email sudah digunakan oleh admin lain.");
			}
		});
}
