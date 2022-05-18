var id = 0;

$(document).ready(function () {
	refreshAdminInfo();
	id = parseInt($("#edited-id").val().trim());
	var fd = new FormData();
	fd.append("id", id);
	fetch(API_URL+"/admin/get_panduan_umroh_bab_by_id", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			var panduanUmroh = JSON.parse(response);
			$("#title-id").val(panduanUmroh['title_id'].toString());
			$("#title-ar").val(panduanUmroh['title_ar'].toString());
		});
});

function save() {
	var titleID = $("#title-id").val().trim();
	var titleAR = $("#title-ar").val().trim();
	if (titleID == "") {
		alert("Mohon masukkan judul (Bahasa Indonesia)");
		return;
	}
	if (titleAR == "") {
		alert("Mohon masukkan judul (Bahasa Arab)");
		return;
	}
	var fd = new FormData();
	fd.append("id", id);
	fd.append("title_id", titleID);
	fd.append("title_ar", titleAR);
	fetch(API_URL+"/admin/edit_panduan_umroh_bab", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			$.redirect(API_URL+"/panduanumroh");
		});
}
