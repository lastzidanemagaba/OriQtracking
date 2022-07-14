$(document).ready(function() {
	refreshAdminInfo();
});

function save() {
	var description = $("#description").val().trim();
	if (description == "") {
		alert("Mohon masukkan deskripsi");
		return;
	}
	var fd = new FormData();
	fd.append("description", description);
	fetch(API_URL+"/admin/add_jadwal_sholat", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			window.location.href = "http://localhost/admin/jadwalsholat";
		});
}
