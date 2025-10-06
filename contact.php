<?php
session_start();

// Show modal if redirected after successful submission
$show_modal = isset($_GET['success']) && $_GET['success'] === 'true' && isset($_SESSION['form_submitted']);
if ($show_modal) {
    unset($_SESSION['form_submitted']); // Clear the flag
}

// Database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "contactbw";

// Initialize variables for form data
$name = $phone = $message = "";
$success_message = "";
$error_message = "";

// Create connection
try {
    $conn = new mysqli($servername, $username, $password);
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === FALSE) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($dbname);
    
} catch (Exception $e) {
    $error_message = "Database connection error. Please try again later.";
    error_log("Connection failed: " . $e->getMessage());
}

// Check connection
if (isset($conn) && $conn->connect_error) {
    $error_message = "Service temporarily unavailable. Please try again later.";
    error_log("Connection failed: " . $conn->connect_error);
}

// Create table if not exists
$create_table_sql = "CREATE TABLE IF NOT EXISTS contact (
    Contact_id INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Phone_No VARCHAR(20) NOT NULL,
    Message TEXT NOT NULL,
    Date DATETIME NOT NULL
)";

if (!$conn->query($create_table_sql)) {
    $error_message = "Error creating table: " . $conn->error;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Check if database connection is available
        if (!isset($conn) || $conn->connect_error) {
            throw new Exception("Database connection is not available");
        }

        // Validate required fields with more specific messages
        if (empty(trim($_POST['name']))) {
            throw new Exception("Please enter your name");
        }
        if (empty(trim($_POST['phone']))) {
            throw new Exception("Please enter your phone number");
        }
        if (empty(trim($_POST['message']))) {
            throw new Exception("Please enter your message");
        }

        // Get form data and sanitize
        $name = isset($_POST['name']) ? $conn->real_escape_string(trim($_POST['name'])) : '';
        $phone = isset($_POST['phone']) ? $conn->real_escape_string(trim($_POST['phone'])) : '';
        $message = isset($_POST['message']) ? $conn->real_escape_string(trim($_POST['message'])) : '';
        $date = date('Y-m-d H:i:s');

        // Validate name length
        if (strlen($name) < 2 || strlen($name) > 100) {
            throw new Exception("Name must be between 2 and 100 characters");
        }

        // Validate phone with more comprehensive regex
        if (!preg_match("/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/", $phone)) {
            throw new Exception("Please enter a valid phone number (e.g., +1234567890 or 123-456-7890)");
        }

        try {
            // Prepare statement
            $stmt = $conn->prepare("INSERT INTO contact (Name, Phone_No, Message, Date) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }

            // Bind parameters
            if (!$stmt->bind_param("ssss", $name, $phone, $message, $date)) {
                throw new Exception("Error binding parameters: " . $stmt->error);
            }

            // Execute the statement
            if (!$stmt->execute()) {
                throw new Exception("Error saving your message: " . $stmt->error);
            }

            $_SESSION['form_submitted'] = true;
            $success_message = "Message sent successfully!";
            
            // Clear form data after successful submission
            $name = $phone = $message = "";
            
            // Log successful submission
            error_log("New contact form submission from: " . $name);
            
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=true");
            exit();

        } catch (Exception $e) {
            // Log the detailed error
            error_log("Contact form error: " . $e->getMessage());
            throw new Exception("We couldn't process your request. Please try again later.");
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contact Us — Belwise</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      :root{--primary:#FF6B00;--secondary:#233554}
      html,body{font-family:'Poppins',system-ui,Segoe UI,Roboto,Helvetica,Arial,sans-serif; height: 100%; background-color: transparent;}
      .shadow-soft{box-shadow:0 10px 25px rgba(35,53,84,0.08)}.fade-in{animation:fadeIn .5s ease-in-out}
      @keyframes fadeIn{from{opacity:0}to{opacity:1}}
      
      /* Modal Styles */
      .modal {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 100;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
      }
      .modal.show {
        display: flex;
        opacity: 1;
      }
      .modal-content {
        transform: translateY(-20px);
        transition: transform 0.3s ease-in-out;
      }
      .modal.show .modal-content {
        transform: translateY(0);
      }
      @keyframes checkmark {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
      }
      .checkmark {
        animation: checkmark 0.5s ease-in-out forwards;
      }
      
      body::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        background: url('images/HomeBG.jpg') center/cover no-repeat;
        opacity: 0.08;
        pointer-events: none;
      }
    </style>
  </head>
  <body class="antialiased text-gray-800 fade-in">
    <header class="fixed top-0 left-0 right-0 bg-white/90 backdrop-blur z-50 border-b">
      <div class="max-w-6xl mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <a href="index.html" class="text-xl font-bold text-[var(--secondary)] flex items-center gap-2" aria-label="Belwise Home">
            <img src="images/belwise.png" alt="Belwise Logo" class="h-12 w-24 object-contain" />
          </a>
          <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-700">
            <a href="index.html">Home</a>
            <a href="about.html">About Us</a>
            <a href="services.html">Services</a>
            <a href="contact.php">Contact Us</a>
          </nav>
          <div class="md:hidden">
            <button id="mobile-toggle" aria-label="Open menu" class="p-2 rounded-md text-gray-700 hover:bg-gray-100">☰</button>
          </div>
        </div>
      </div>
    </header>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: <?php echo $show_modal ? 'flex' : 'none'; ?>">
      <div class="bg-white rounded-xl p-8 max-w-md w-[90%] relative">
        <button type="button" onclick="closeModal()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 p-2">
          <span class="sr-only">Close</span>
          <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
        <div class="text-center">
          <div class="w-16 h-16 bg-[#E8F5E9] rounded-full mx-auto mb-4 flex items-center justify-center">
            <svg class="w-8 h-8 text-[#4CAF50]" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Message Sent Successfully!</h3>
          <p class="text-gray-600 mb-6">Thank you for contacting us. We'll get back to you soon.</p>
          <button type="button" onclick="closeModal()" class="w-full bg-[var(--primary)] text-white py-3 px-6 rounded-full font-semibold hover:bg-opacity-90 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary)]">
            Close
          </button>
        </div>
      </div>
    </div>

    <main class="pt-20">
      <!-- Hero (Contact) -->
      <section class="bg-white/80 backdrop-blur-sm">
        <div class="max-w-6xl mx-auto px-6 lg:px-8 py-16 lg:py-24 grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
          <div>
            <p class="text-sm text-[var(--secondary)] font-semibold">We Are Here to Help</p>
            <h1 class="mt-4 text-4xl lg:text-5xl font-extrabold leading-tight text-gray-900">Contact Us</h1>
            <p class="mt-6 text-gray-600 max-w-xl">Have a project or question? Fill out the form and our team will reach out to discuss how Belwise can support your goals.</p>
          </div>
          <div class="flex justify-center lg:justify-end">
            <img src="images/Con.jpg" alt="People working illustration" class="w-full max-w-lg shadow-soft rounded-lg">
          </div>
        </div>
      </section>

      <!-- Contact Form + Info -->
      <section class="bg-gray-50/80 backdrop-blur-sm">
        <div class="max-w-6xl mx-auto px-6 lg:px-8 py-16 lg:py-24 grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
          <div>
            <?php if ($success_message): ?>
            <div class="mb-6 p-4 rounded-lg bg-green-50 text-green-800">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-800 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h4 class="font-semibold">Error</h4>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                    <button type="button" onclick="this.parentElement.parentElement.style.display='none'" class="mt-2 text-sm text-red-600 hover:text-red-800">Dismiss</button>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="contactForm" class="bg-white p-8 rounded-lg shadow-soft space-y-6">
              <h3 class="text-2xl font-bold text-center text-[var(--secondary)]">Get in Touch</h3>
              <div class="mb-6">
                <label class="block">
                  <span class="text-sm font-medium text-gray-700">Full Name</span>
                  <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[var(--primary)] focus:border-[var(--primary)] transition" placeholder="John Doe">
                </label>
              </div>
              <label class="block">
                <span class="text-sm font-medium text-gray-700">Phone Number</span>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>" pattern="[0-9+()\-\s]{6,}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[var(--primary)] focus:border-[var(--primary)] transition" placeholder="+91 1800-572-6513">
              </label>
              <label class="block">
                <span class="text-sm font-medium text-gray-700">Message</span>
                <textarea name="message" rows="5" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-[var(--primary)] focus:border-[var(--primary)] transition" placeholder="How can we help you?"><?php echo htmlspecialchars($message); ?></textarea>
              </label>
              <div class="flex items-center justify-end gap-4">
                <button type="reset" class="px-6 py-2 rounded-full text-sm font-semibold text-gray-600 hover:bg-gray-100 transition-colors">Reset</button>
                <button type="submit" class="px-8 py-3 rounded-full bg-[var(--primary)] text-white font-bold shadow-lg hover:bg-opacity-90 transition-all transform hover:scale-105">Send Message</button>
              </div>
            </form>
          </div>

          <aside>
            <div class="bg-white p-6 rounded-lg shadow-sm">
              <h4 class="font-semibold text-[var(--secondary)]">Contact Information</h4>
              <p class="mt-3 text-gray-600">Email: <a href="mailto:info@belwise.com" class="text-[var(--primary)]">info@belwise.com</a></p>
              <p class="mt-2 text-gray-600">Phone: +91 1800-572-6513</p>
              <div class="mt-6">
                <h5 class="font-medium">Office Hours</h5>
                <p class="text-sm text-gray-600">Mon - Fri 9:00 AM - 6:00 PM</p>
              </div>
            </div>
          </aside>
        </div>
      </section>

      <!-- Footer -->
      <footer class="bg-[var(--secondary)] text-white">
        <div class="max-w-6xl mx-auto px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <h5 class="font-bold text-lg">Belwise</h5>
            <p class="mt-2 text-gray-200">Perfect Solutions for Your Business.</p>
          </div>
          <div>
            <h6 class="font-semibold">Quick Links</h6>
            <ul class="mt-3 space-y-2 text-gray-200">
              <li><a href="index.html">Home</a></li>
              <li><a href="about.html">About Us</a></li>
              <li><a href="services.html">Services</a></li>
              <li><a href="contact.php">Contact Us</a></li>
            </ul>
          </div>
          <div>
            <h6 class="font-semibold">Contact</h6>
            <p class="mt-3 text-gray-200">Email: <a href="mailto:info@belwise.com" class="underline">info@belwise.com</a></p>
            <p class="mt-2 text-gray-200">Phone: +91 1800-572-6513</p>
          </div>
        </div>
        <div class="border-t border-white/10">
          <div class="max-w-6xl mx-auto px-6 lg:px-8 py-6 text-sm text-gray-300">© 2025 Belwise. All Rights Reserved.</div>
        </div>
      </footer>
    </main>

    <script>
      // Mobile toggle
      document.getElementById('mobile-toggle')?.addEventListener('click', () => {
        document.querySelector('nav')?.classList.toggle('hidden');
      });

      // Smooth scroll for in-page links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          document.querySelector(this.getAttribute('href'))?.scrollIntoView({ behavior: 'smooth' });
        });
      });

      // Modal functionality
      document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('successModal');
        
        // Initialize modal if it exists
        if (modal) {
          // Function to close modal
          window.closeModal = function() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            // Remove success parameter from URL
            const url = new URL(window.location);
            url.searchParams.delete('success');
            window.history.replaceState({}, '', url);
          };

          // Close modal when clicking outside
          modal.addEventListener('click', (e) => {
            if (e.target === modal) {
              closeModal();
            }
          });

          // Close modal with Escape key
          document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
              closeModal();
            }
          });

          // If modal is shown, prevent body scroll
          if (modal.style.display === 'flex') {
            document.body.style.overflow = 'hidden';
          }
        }
      });
    </script>
  </body>
</html>
<?php $conn->close(); ?>  