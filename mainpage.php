<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.html');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>MAIN PAGE</title>
    <link rel="stylesheet" type="text/css" href="mainpage.css">
</head>
<body>
    <div class="content">
        <!--sidebar menu design -->
        <div class="sidebar">
            <ul>
                <li><a href="#dashboard">DASHBOARD</a></li>
                <li><a href="#add-device">ADD DEVICE</a></li>
                <li><a href="#report">REPORT</a></li>
                <li><a href="#support">WARRANTY & SUPPORT</a></li>
            </ul>
            <input type="button" id="logout-btn" value="LOG OUT" onclick="window.location.href='login.html'">
        </div>

        <!--Dashboard design -->
        <section id="dashboard">
            <h1>ADMIN DASHBOARD</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['email']); ?>!</p>
            <form id="Device-form">
                <label for="devices">SELECT DEVICE:</label>
                <select id="devices" name="laptop">
                    <option value="laptop 1">LAPTOP 1</option>
                    <option value="laptop 2">LAPTOP 2</option>
                </select>
                <br>
                <!--text area for device-->
                <textarea id="details" rows="4" cols="7" readonly>
DEVICE MODEL/NAME:
SERIAL NUMBER:
CONDITION:
PRICE:
                </textarea>
                <!--laptop's image-->
                <div class="image-catalogue">
                    <img id="catalogue-img" src="laptop.png" alt="Device Image">
                    
                    <div class="controls">
                        <button type="button" onclick="prevImage()">Prev</button>
                        <button type="button" onclick="nextImage()">Next</button>
                    </div>
                </div>
            </form>
            <script>
                // Array of images
                const images = ["laptop.png", "laptop2.png", "laptop3.png"];
                let currentIndex = 0;

                function showImage(index) {
                    document.getElementById("catalogue-img").src = images[index];
                }

                function nextImage() {
                    currentIndex = (currentIndex + 1) % images.length;
                    showImage(currentIndex);
                }

                function prevImage() {
                    currentIndex = (currentIndex - 1 + images.length) % images.length;
                    showImage(currentIndex);
                }
            </script>
            <br>
            <!--buttons-->
            <div class="button-container">
                <input type="button" id="Generate-btn" value="Generate QR">
            </div>
            <br>
        </section>

        <!-- Rest of your existing HTML sections (add-device, report, support) -->
        <!-- Copy all the other sections from your original mainpage.html here -->

    </div>

    <script>
        // run when page loads
        document.addEventListener("DOMContentLoaded", function () {
            const links = document.querySelectorAll(".sidebar a");
            const sections = document.querySelectorAll("section");

            links.forEach(link => {
                link.addEventListener("click", function (e) {
                    e.preventDefault();
                    sections.forEach(sec => sec.style.display = "none");
                    const target = this.getAttribute("href");
                    document.querySelector(target).style.display = "block";
                });
            });
        });
    </script>
</body>
</html>