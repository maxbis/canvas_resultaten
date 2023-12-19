<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Maintenance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f0f0f0;
            color: #333;
            padding: 50px;
        }

        .maintenance-message {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .maintenance-header {
            max-width: 640px;
            justify-content: center;
        }

        h1 {
            color: #2a88bd;
        }

        p {
            font-size: 18px;
            color:#606060;
        }

        .progress-bar {
            width: 100%;
            background-color: #ddd;
            overflow: hidden;
            /* Ensures the progress bar stays within bounds */
        }

        .progress {
            width: 0;
            /* Start with 0 width */
            height: 20px;
            background-color: #2a88bd;
            animation: fillProgress 180s linear forwards;
            /* 180 seconds = 3 minutes */
        }

        @keyframes fillProgress {
            from {
                width: 0;
            }

            to {
                width: 100%;
            }
        }

        .data-stream {
            max-width: 640px;
            justify-content: center;
            white-space: nowrap;
            overflow: hidden;
            color:#d0d0d0;
            font-size: 12px;
        }


    </style>

    <script>
        function generateDataStream() {
            let dataStream = document.getElementById('dataStream');
            let dataString = '';
            for (let i = 0; i < 70; i++) {
                dataString += Math.round(Math.random()) + ' ';
            }
            dataStream.textContent = dataString;
        }

        setInterval(generateDataStream, Math.random()*00+500); // Regenerate data every second
    </script>

</head>

<body>
    <div class="maintenance-message">
        <div class="maintenance-header">
            <img src="/canvas-bar.png" alt="Canvas Image" width="600" style="opacity:0.7">
        </div>
        
        <script src="script.js"></script>
        <div class="maintenance-message">

            <h1>Oops, wrong moment...</h1>

            <img src="/maintenance-image.webp" alt="Maintenance Image" width="200" style="opacity:0.8">
            <p>We're busy updating data from the Canvas database.</p>
            <p>Please check back soon!</p>
            <div class="progress-bar">
                <div class="progress"></div>
            </div>
            <div id="dataStream" class="data-stream">
                &nbsp;
            </div>

        </div>
    </div>
</body>

</html>