<!-- CSS -->
<style>
    #my_camera{
        width: 320px;
        height: 240px;
        border: 1px solid black;
    }
</style>

<!-- -->
<div id="my_camera"></div>
<fieldset>
    <legend>Webcam settings</legend>
    <label>Resolution</label>
    <select name="resolution" id="res">
        <option value="320:240">320:240</option>
        <option value="640:480">640:480</option>
    </select>
    <label>File type</label>
    <select name="type" id="type">
        <option value="jpeg">jpeg</option>
        <option value="png">png</option>
    </select>

    <label>Quality</label>
    <input type="number" max="100", min="20" value="90" id="quality"/>
    <input type=button value="Configure" onClick="configure()">

</fieldset>

<input type=button value="Take Snapshot" onClick="take_snapshot()">
<input type=button value="Save Snapshot" onClick="saveSnap()">

<div id="results" ></div>
<div><a href="/images/">Images</a> </div>
<!-- Script -->
<script type="text/javascript" src="webcam/webcam.min.js"></script>

<!-- Code to handle taking the snapshot and displaying it locally -->
<script language="JavaScript">
    var type = 'jpeg';
    // Configure a few settings and attach camera
    function configure(){
        Webcam.reset();
        var width = parseInt(document.getElementById('res').value.split(':')[0]);
        var height = parseInt(document.getElementById('res').value.split(':')[1]);
        type = document.getElementById('type').value;
        var quality = parseInt(document.getElementById('quality').value);
        Webcam.set({
            width: width,
            height: height,
            dest_width: width,
            dest_height: height,
            image_format: type,
            jpeg_quality: quality
        });
        Webcam.attach( '#my_camera' );
    }
    // A button for taking snaps


    // preload shutter audio clip
    var shutter = new Audio();
    shutter.autoplay = false;
    shutter.src = navigator.userAgent.match(/Firefox/) ? '/sound/shutter.ogg' : '/sound/shutter.mp3';

    function take_snapshot() {
        // play sound effect
        shutter.play();

        // take snapshot and get image data
        Webcam.snap( function(data_uri) {
            // display results in page
            document.getElementById('results').innerHTML =
                '<img id="imageprev" src="'+data_uri+'"/>';
        } );

    }

    function saveSnap(){
        // Get base64 value from <img id='imageprev'> source
        var base64image = document.getElementById("imageprev").src;

        Webcam.upload( base64image, '/upload?type=' + type, function(code, text) {
            console.log(code);
            console.log(text);
        });

    }
</script>

