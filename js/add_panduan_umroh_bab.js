$(document).ready(function() {
	refreshAdminInfo();
});

function save() {
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
	fd.append("title_id", titleID);
	fd.append("title_ar", titleAR);
	fetch(API_URL+"/admin/add_panduan_umroh_bab", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			window.location.href = "https://dev.jtindonesia.com/admin/panduanumroh";
		});
}
