$(document).ready(function() {
	refreshAdminInfo();
});

function save() {
	var name = $("#name").val().trim();
	var description = $("#description").val().trim();
	var dataType = $("#data-type").val().trim();
	var value = $("#value").val().trim();
	if (name == "") {
		alert("Mohon masukkan nama");
		return;
	}
	if (description == "") {
		alert("Mohon masukkan deskripsi");
		return;
	}
	if (dataType == "") {
		alert("Mohon masukkan tipe data");
		return;
	}
	if (value == "") {
		alert("Mohon masukkan nilai");
		return;
	}
	var fd = new FormData();
	fd.append("name", name);
	fd.append("description", description);
	fd.append("data_type", dataType);
	fd.append("value", value);
	fetch(API_URL+"/admin/add_setting", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			window.location.href = "http://localhost/admin/settings";
		});
}
