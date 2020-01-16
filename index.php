<?php
require_once "vendor/autoload.php";
require_once "settings.php";

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\Blob\Models\Block;
use WindowsAzure\Blob\Models\BlockList;
use WindowsAzure\Blob\Models\BlobBlockType;

$connectionString = "DefaultEndpointsProtocol=" . $settings["protocol"] .
    ";AccountName=" . $settings["account_name"] .
    ";AccountKey=" . $settings["account_key"] . ";";

$blobRestProxy = ServicesBuilder::getInstance()->createBlobService($connectionString);

if(isset($_POST["submit"]))
{
    $fileToUpload = basename($_FILES['uploaded_file']['name']);

    $content = file_get_contents($_FILES['uploaded_file']['tmp_name']);

    try {
        //Upload blob
        $blobRestProxy->createBlockBlob($settings["container"], $fileToUpload, $content);
    } catch(ServiceException $e){
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Submission 2 - ACD</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</head>
<body>

<!-- Analyze -->
<script type="text/javascript">
    function processImage() {
        var subscriptionKey = "c62e302874cb45f6890b0bb014a5028f";
        var uriBase =
            "https://lukmanlabvision.cognitiveservices.azure.com/vision/v2.0/analyze";
 
        // Request parameters.
        var params = {
            "visualFeatures": "Categories,Description,Color",
            "details": "",
            "language": "en",
        };
 
        // Display the image.
        var sourceImageUrl = document.getElementById("inputImage").value;
        document.querySelector("#sourceImage").src = sourceImageUrl;
 
        // Make the REST API call.
        $.ajax({
            url: uriBase + "?" + $.param(params),
 
            // Request headers.
            beforeSend: function(xhrObj){
                xhrObj.setRequestHeader("Content-Type","application/json");
                xhrObj.setRequestHeader(
                    "Ocp-Apim-Subscription-Key", subscriptionKey);
            },
 
            type: "POST",
 
            // Request body.
            data: '{"url": ' + '"' + sourceImageUrl + '"}',
        })
 
        .done(function(data) {
            // Show formatted JSON on webpage.
            $("#responseTextArea").val(JSON.stringify(data, null, 2));
        })
 
        .fail(function(jqXHR, textStatus, errorThrown) {
            // Display error message.
            var errorString = (errorThrown === "") ? "Error. " :
                errorThrown + " (" + jqXHR.status + "): ";
            errorString += (jqXHR.responseText === "") ? "" :
                jQuery.parseJSON(jqXHR.responseText).message;
            alert(errorString);
        });
    };
</script>
<!-- End Analyze -->

<div class="container">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">Submission 2 - Azure Cloud Developer @ Computer Vision</a>
    </nav>
    <br>
  <form enctype="multipart/form-data" action="" method="POST">
    <h3>Upload your File:</h3>
    
    <input type="file" name="uploaded_file"></input>
    <input type="submit" name="submit" value="Upload"></input>
  </form>
  <hr>

  <h2>Uploaded Images: </h2>
  <p>Silahkan pilih gambar untuk di Analisa</p>  
  <div class="row">
    <div class="col-6">
                <table class="table">
                    <thead>
                        <tr>
                        <th>Nama</th>
                        <th>URL</th>
                        <th>Action</th>
                        </tr>
                    </thead>
            <?php
                try {
                    // List blobs.
                    $blob_list = $blobRestProxy->listBlobs($settings["container"]);
                    $blobs = $blob_list->getBlobs();
                
                    foreach($blobs as $blob)
                    {
                    //   echo $blob->getName().": ".$blob->getUrl()."<br />";
                    echo '
                    <tbody>
                        <tr>
                        <td>'.$blob->getName().'</td>
                        <td><input type="text" name="inputImage" id="inputImage"
                        value="'.$blob->getUrl().'"/></td>
                        <td><button onclick="processImage()">Analyze</button></td>
                        </tr>
                    </tbody>
                    ';
                    }
                } catch(ServiceException $e){
                    $code = $e->getCode();
                    $error_message = $e->getMessage();
                    echo $code.": ".$error_message."<br />";
                }
            ?>
            </table>
        </div>
        <div id="imageDiv" style="width:420px; display:table-cell;">
            Source image:
            <br>
            <img id="sourceImage" width="400" />
        </div>
    </div>
    <hr>
    <div class="row">
    <div class="col-6">
        <div id="jsonOutput">
           <h1>Response:</h1>
            <textarea id="responseTextArea" class="form-control" rows="20"></textarea>
        </div>
    </div>
    </div>
</body>
</html>
