var id = 0;

$(document).ready(function () {
	refreshAdminInfo();
	id = parseInt($("#edited-jadwal-sholat-id").val().trim());
	var fd = new FormData();
	fd.append("id", id);
	fetch(API_URL+"/admin/get_jadwal_sholat_by_id", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			var jadwalSholat = JSON.parse(response);
			$("#description").val(jadwalSholat['description'].toString());
		});
});

function save() {
	var description = $("#description").val().trim();
	if (description == "") {
		alert("Mohon masukkan deskripsi");
		return;
	}
	var fd = new FormData();
	fd.append("id", id);
	fd.append("description", description);
	fetch(API_URL+"/admin/edit_jadwal_sholat", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			window.location.href = "https://dev.jtindonesia.com/admin/jadwalsholat";
		});
}
