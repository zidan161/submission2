<!DOCTYPE html>
<html>
<head>
    <title>Upload File</title>
<style>
table {
  width:100%;
}
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
th, td {
  padding: 15px;
  text-align: left;
}
</style>
</head>
<body>
    <h1> Upload File! </h1>
    <form action="" method="post" enctype="multipart/form-data">
        Pilih file: <input type="file" name="berkas"/>
        <input type="submit" name="submit" value="upload"/>
        <br/>
        <p><button type="submit" name="list">List</button></p>
    </form>
    <?php
        require_once 'vendor/autoload.php';
        require_once "./random_string.php";

        use MicrosoftAzure\Storage\Blob\BlobRestProxy;
        use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
        use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
        use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
        use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
  
        $accountName = "zidanstorage";
        $accountKey = "BAZ5xbwvB6GmxpztApG4wNBUpaeEk7A74cY6v17D7ScigLOakGNJEmkuIhXswZ3ljyY7f1Ay6ObuAOQbgVZcgg==";

        $connectionString = "DefaultEndpointsProtocol=https;AccountName=".$accountName.";AccountKey=".$accountKey;
   
        $containerName = "blockblobs";
    
    function getList() {
           // List blobs.
           $listBlobsOptions = new ListBlobsOptions();

           do{
               echo "<table>
                     <tr>
                        <th>Name</th>
                        <th>Url</th>
                     </tr>";
               $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
             
               foreach ($result->getBlobs() as $blob)
               {
                 $name = $blob->getName();
                 $url = $blob->getUrl();
                 echo "<form action='vision.php' method='post' enctype='multipart/form-data'>
                        <tr>
                          <td>$name</td>
                          <td>$url</td>
                          <td><button type='submit' name='button' value='$url'/>Analize!</td>
                        </tr>
                       </form>";
               }
               echo "</table>";
        
               $listBlobsOptions->setContinuationToken($result->getContinuationToken());
           } while($result->getContinuationToken());
          echo "<br/>";
       } 

        // Membuat blob client.
        $blobClient = BlobRestProxy::createBlobService($connectionString);
    
        getList();

        if (isset($_POST['submit']) && $_POST['submit'] == 'upload') {

            if (isset($_FILES['berkas'])) {

                $fileName = $_FILES['berkas']['name'];
                $fileDirectory = $_FILES['berkas']['tmp_name'];
                $fileToUpload = $fileName;
                move_uploaded_file($fileDirectory,"image/$fileName");
 
               try {

                    // Getting local file so that we can upload it to Azure
                    $myfile = fopen("image/$fileName", "r") or die("Unable to open file!");
                    fclose($myfile);
        
                    # Upload file as a block blob
                    echo "Uploading BlockBlob: ".PHP_EOL;
                    echo $fileToUpload;
                    echo "<br />";
            
                    $content = fopen("image/$fileName", "r");

                    //Upload blob
                    $blobClient->createBlockBlob($containerName, $fileName, $content);
                   
                   getList();

                }
                catch(ServiceException $e){
                    // Handle exception based on error codes and messages.
                    // Error codes and messages are here:
                    // http://msdn.microsoft.com/library/azure/dd179439.aspx
                    $code = $e->getCode();
                    $error_message = $e->getMessage();
                    echo $code.": ".$error_message."<br />";
                }
                catch(InvalidArgumentTypeException $e){
                    // Handle exception based on error codes and messages.
                    // Error codes and messages are here:
                    // http://msdn.microsoft.com/library/azure/dd179439.aspx
                    $code = $e->getCode();
                    $error_message = $e->getMessage();
                    echo $code.": ".$error_message."<br />";
                }
            }
          else echo "ERROR";
       } else echo "Dont to work";
       
          
    ?>
</body>
</html>
