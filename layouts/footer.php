        </div> <!-- closes <div class="container mt-4"> from header -->
        
        <footer class="footer bg-dark text-light py-3 mt-auto">
            <div class="container text-center">
                <small>
                    &copy; <span id="year"></span>
                    <a href="https://www.geekinstitute.org" class="text-light text-decoration-none fw-semibold">
                        The Geek Institute of Cyber Security
                    </a> — All rights reserved.
                </small>
            </div>
        </footer>

        <script>
            // Auto-update year in footer
            document.getElementById('year').innerText = new Date().getFullYear();
        </script>

        <!-- Bootstrap Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
   

    </body>
</html>
