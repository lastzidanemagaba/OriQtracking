$(document).ready(function() {
	refreshAdminInfo();
});

function save() {
	var babID = parseInt($("#bab-id").val().trim());
	var titleID = $("#title-id").val().trim();
	var titleAR = $("#title-ar").val().trim();
	// if (titleID == "") {
	// 	alert("Mohon masukkan judul (Bahasa Indonesia)");
	// 	return;
	// }
	// if (titleAR == "") {
	// 	alert("Mohon masukkan deskripsi (Bahasa Arab)");
	// 	return;
	// }
	var fd = new FormData();
	fd.append("bab_id", babID);
	fd.append("title_id", titleID);
	fd.append("title_ar", titleAR);
	fetch(API_URL+"/admin/add_panduan_umroh_subbab", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			window.history.back();
		});
}
