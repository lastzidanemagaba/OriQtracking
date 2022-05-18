$(document).ready(function() {
	refreshAdminInfo();
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
	var babID = parseInt($("#bab-id").val().trim());
	var subbabID = parseInt($("#subbab-id").val().trim());
	var fd = new FormData();
	fd.append("bab_id", babID);
	fd.append("subbab_id", subbabID);
	fd.append("title", title);
	fd.append("arabic_text", arabicText);
	fd.append("spelling", spelling);
	fd.append("meaning", meaning);
	fetch(API_URL+"/admin/add_panduan_umroh", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			$.redirect(API_URL+"/panduanumroh/view", {
				'bab_id': ""+babID,
				"subbab_id": ""+subbabID
			});
		});
}
