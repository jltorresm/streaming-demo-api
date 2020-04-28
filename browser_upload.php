<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Camera Demo</title>
	<style type="text/css">
		#video {
			width: 350px;
			height: 197px;
			cursor: pointer;
		}

		#snap {
			display: block;
			font-size: 2em;
			padding: 10px;
			margin: auto;
		}

		#canvas {
			display: none;
		}
	</style>
</head>
<body>
<video id="video" autoplay></video>
<button id="snap">Snap Photo</button>
<canvas id="canvas"></canvas>
<img id="image" src="">

<input id="file" type="file" accept="image/*;capture=camera">

<script type="text/javascript">
	// Elements for taking the snapshot
	const canvas = document.getElementById('canvas');
	const video = document.getElementById('video');
	const image = document.getElementById('image');
	const file = document.getElementById('file');

	// Get access to the camera!
	if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
		const hdConstraints = {
			video: {width: {min: 1280}, height: {min: 720}}
		};
		navigator.mediaDevices.getUserMedia(hdConstraints).then(function (stream) {
			video.srcObject = stream;
			video.play();
		});
	}

	// Trigger photo take using API
	document.getElementById("snap").onclick = video.onclick = function () {
		canvas.width = video.videoWidth;
		canvas.height = video.videoHeight;
		canvas.getContext('2d').drawImage(video, 0, 0);
		image.src = canvas.toDataURL('image/png');
	};

	// This is an <input> demo solution
	const reader = new FileReader();
	reader.addEventListener("load", function () {
		image.src = reader.result;
	}, false);
	file.onchange = function () {
		if (file.files[0]) {
			reader.readAsDataURL(file.files[0]);
		}
	};

	function toHexString(byteArray) {
		return byteArray.reduce(
			(output, elem) => (output + ('0' + elem.toString(16)).slice(-2)), ''
		);
	}
</script>
</body>
</html>
