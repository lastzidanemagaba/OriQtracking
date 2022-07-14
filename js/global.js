const PROTOCOL = "http";
//const HOST = "dev.jtindonesia.com";
const HOST = "localhost";
const API_URL = PROTOCOL+"://"+HOST+"/admin";
const USERDATA_URL = PROTOCOL+"://"+HOST+"/admin/userdata/";
var USER_ID = 0;

function refreshAdminInfo() {
	var adminID = parseInt($("#admin-id").val().trim());
	var fd = new FormData();
	fd.append("id", adminID);
	fetch(API_URL+"/admin/get_admin_by_id", {
		method: 'POST',
		body: fd
	}).then(response => response.text())
		.then(async (response) => {
			var adminInfo = JSON.parse(response);
			$("#admin-profile-picture").attr("src", USERDATA_URL+adminInfo['photo'].toString());
			$("#admin-name").html(adminInfo['name'].toString());
		});
}
