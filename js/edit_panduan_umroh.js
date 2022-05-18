var babID = 0;
var subbabID = 0;
var id = 0;

$(document).ready(function () {
	refreshAdminInfo();
	id = parseInt($("#edited-id").val().trim());
	babID = parseInt($("#edited-bab-id").val().trim());
	subbabID= parseInt($("#edited-subbab-id").val().trim());
	var fd = new FormData();
	fd.append("id", id);
	fetch(API_URL+"/admin/get_panduan_umroh_by_id", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			var panduanUmroh = JSON.parse(response);
			$("#title").val(panduanUmroh['title'].toString());
			$("#arabic-text").val(panduanUmroh['arabic_text'].toString());
			$("#spelling").val(panduanUmroh['spelling'].toString());
			$("#meaning").val(panduanUmroh['meaning'].toString());
		});
});

function save() {
	var title = $("#title").val().trim();
	var arabicText = $("#arabic-text").val().trim();
	var spelling = $("#spelling").val().trim();
	var meaning = $("#meaning").val().trim();
	if (title == "") {
		alert("Mohon masukkan judul");
		return;
	}
	if (arabicText == "") {
		alert("Mohon masukkan teks Arab");
		return;
	}
	// if (spelling == "") {
	// 	alert("Mohon masukkan ejaan");
	// 	return;
	// }
	if (meaning == "") {
		alert("Mohon masukkan arti");
		return;
	}
	var fd = new FormData();
	fd.append("id", id);
	fd.append("title", title);
	fd.append("arabic_text", arabicText);
	fd.append("spelling", spelling);
	fd.append("meaning", meaning);
	fetch(API_URL+"/admin/edit_panduan_umroh", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			$.redirect(API_URL+"/panduanumroh/view", {
				'id': ""+id,
				'bab_id': babID,
				'subbab_id': subbabID
			});
		});
}
