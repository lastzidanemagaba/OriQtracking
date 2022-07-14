$(document).ready(function() {
	refreshAdminInfo();
});

function save() {
	var productID = $("#product-id").val().trim();
	var productName = $("#product-name").val().trim();
	var productDescription = $("#product-description").val().trim();
	var benefitsString = $("#benefits").val().trim();
	var months = $("#months").val().trim();
	if (productID == "") {
		alert("Mohon masukkan ID produk");
		return;
	}
	if (productName == "") {
		alert("Mohon masukkan nama produk");
		return;
	}
	if (productDescription == "") {
		alert("Mohon masukkan deskripsi produk");
		return;
	}
	if (benefitsString == "") {
		alert("Mohon masukkan benefit");
		return;
	}
	if (months == "") {
		alert("Mohon masukkan masa berlaku (dalam bulan)");
		return;
	}
	var benefits = [];
	var benefitsArray = benefitsString.split(",");
	for (var i=0; i<benefitsArray.length; i++) {
		benefits.push(benefitsArray[i]);
	}
	var fd = new FormData();
	fd.append("product_id", productID);
	fd.append("product_name", productName);
	fd.append("product_description", productDescription);
	fd.append("benefits", JSON.stringify(benefits));
	fd.append("months", months);
	fetch(API_URL+"/admin/add_premium", {
		method: 'POST',
		body: fd
	})
		.then(response => response.text())
		.then(async (response) => {
			window.location.href = "http://localhost/admin/premium";
		});
}
