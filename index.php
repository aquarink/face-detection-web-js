<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Face Scan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <style type="text/css">
        .canvas-container {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .canvas-container canvas {
            position: absolute;
            top: 0;
            left: 14%;
            width: 70%;
        }

    </style>
</head>
<body>

    <main>
        <section class="py-5 text-center container">
            <div class="row py-lg-5">
                <div class="col-lg-6 col-md-8 mx-auto">
                    <h1 class="fw-light">Face Detection</h1>
                    <p class="lead text-muted">Arahkan wajah anda agar terdeteksi.</p>
                    
                    <p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#faceDetector">Face Detection</button>
                    </p>
                </div>
            </div>
      </section>
    </main>

    <!-- Modal -->
    <div class="modal fade" id="faceDetector" tabindex="-1" aria-labelledby="labelFace" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="labelFace">Arahkan Wajah Anda Ke Kamera</h1>
                </div>
                
                <div class="modal-body">
                    <div class="canvas-container"></div>
                    <video id="video_face" width="100%" height="100%" autoplay muted></video>

                    <label id="faceIdLabel"></label>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="face-api.js-master/dist/face-api.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

    <script type="text/javascript">
        const videoFace = document.getElementById("video_face")

        var weights_model = 'face-api.js-master/weights'

        const detectedFaces = []

        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(weights_model),
            faceapi.nets.faceLandmark68Net.loadFromUri(weights_model),
            faceapi.nets.faceLandmark68TinyNet.loadFromUri(weights_model),
            faceapi.nets.faceRecognitionNet.loadFromUri(weights_model),
            faceapi.nets.faceExpressionNet.loadFromUri(weights_model),
        ]).then(startVideoFace)

        function startVideoFace() {
            navigator.getUserMedia(
                { video: {} },
                stream => videoFace.srcObject = stream,
                err => console.error(err)
            )
        }

        videoFace.addEventListener("play", () => {

            const cnv = faceapi.createCanvasFromMedia(videoFace)
            const canvasContainer = document.querySelector(".canvas-container");

            if (canvasContainer) {
                canvasContainer.innerHTML = ''; 
                canvasContainer.appendChild(cnv);
            }

            const displaySize = { 
                width: videoFace.width, 
                height: videoFace.height,
            }

            // Set ukuran elemen canvas sesuai ukuran video
            cnv.setAttribute('width', displaySize.width);
            cnv.setAttribute('height', displaySize.height);

            faceapi.matchDimensions(cnv, displaySize)

            setInterval(async () => {
                const detectionFace = await faceapi.detectAllFaces(videoFace, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceExpressions().withFaceDescriptors()

                cnv.getContext("2d").clearRect(0, 0, cnv.width, cnv.height)

                if (detectionFace.length > 0) {
                    const resizeDetection = faceapi.resizeResults(detectionFace, displaySize);
                    faceapi.draw.drawDetections(cnv, resizeDetection);
                    faceapi.draw.drawFaceLandmarks(cnv, resizeDetection);
                    faceapi.draw.drawFaceExpressions(cnv, resizeDetection);

                    detectionFace.forEach(fd => {
                        const faceScore = fd.detection.score;

                        if (faceScore > 0.97) {
                            const floatArrayString = Array.from(fd.descriptor).toString();
                            const encoder = new TextEncoder();
                            const data = encoder.encode(floatArrayString);

                            console.log(data)
                        }
                    })
                } else {
                     faceIdLabel.textContent = ''
                }

                await new Promise((resolve) => setTimeout(resolve, 100));
            }, 100)
        })
    </script>
</body>
</html>