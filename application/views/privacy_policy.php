<?php
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Kebijakan Privasi</title>
</head>
<body>

<div id="pdf" style="width: 100%; display: flex; flex-direction: column; align-items: center;"></div>

<script src="http://localhost/admin/js/pdf.js"></script>

<script id="script">
  //
  // If absolute URL from the remote server is provided, configure the CORS
  // header on that server.
  //
  const url = 'http://localhost/admin/userdata/privacy_policy.pdf';
  var currPage = 1; //Pages are 1-based not 0-based
  var numPages = 0;
  var pdf;

  //
  // Asynchronous download PDF
  //
  const loadingTask = pdfjsLib.getDocument(url);
  (async () => {
    pdf = await loadingTask.promise;
    numPages = pdf.numPages;
    //
    // Fetch the first page
    //
    pdf.getPage( 1 ).then( handlePages );
  })();
  
  function handlePages(page)
{
    const scale = 1.5;
    const viewport = page.getViewport({ scale });
    // Support HiDPI-screens.
    const outputScale = window.devicePixelRatio || 1;

    //
    // Prepare canvas using PDF page dimensions
    //
    var canvas = document.createElement( "canvas" );
    const context = canvas.getContext("2d");

    canvas.width = Math.floor(viewport.width * outputScale);
    canvas.height = Math.floor(viewport.height * outputScale);
    canvas.style.width = Math.floor(viewport.width) + "px";
    canvas.style.height = Math.floor(viewport.height) + "px";

    const transform = outputScale !== 1 
      ? [outputScale, 0, 0, outputScale, 0, 0] 
      : null;

    //
    // Render PDF page into canvas context
    //
    const renderContext = {
      canvasContext: context,
      transform,
      viewport,
    };
    page.render(renderContext);
    document.getElementById("pdf").appendChild( canvas );

    //Move to next page
    currPage++;
    if ( pdf !== null && currPage <= numPages )
    {
        pdf.getPage( currPage ).then( handlePages );
    }
}

</script>
</body>
</html>

