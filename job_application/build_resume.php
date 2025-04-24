<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $resumeHTML = $_POST['resumeHTML'] ?? '';

    header("Content-type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=My_Resume.doc");

    echo "<html><body>";
    echo $resumeHTML;
    echo "</body></html>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Resume Builder</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f3f0fa;
      padding: 20px;
      max-width: 960px;
      margin: auto;
      color: #333;

    }

    h1 {
      color: #6a0dad;
      text-align: center;
      margin-bottom: 20px;
      font-size: 26px;
    }

    .note {
      text-align: center;
      font-size: 13px;
      color: #777;
      margin-bottom: 10px;
    }

    .resume-section {
      background: #fff;
      padding: 15px 20px;
      margin-bottom: 15px;
      border-left: 4px solid #6a0dad;
      border-radius: 6px;
      box-shadow: 0 1px 5px rgba(0,0,0,0.05);
    }

    .resume-section h2 {
      color: #6a0dad;
      margin-bottom: 8px;
      font-size: 17px;
      user-select: none;
    }

    .content {
      border: 1px dashed #bbb;
      padding: 8px;
      min-height: 40px;
      max-height: 150px;
      overflow-y: auto;
      background-color: #fafafa;
      border-radius: 4px;
      font-size: 14px;
    }

    button {
      display: block;
      margin: 25px auto 0;
      padding: 10px 24px;
      font-size: 16px;
      background-color: #6a0dad;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    button:hover {
      background-color: #5800a5;
    }
  </style>
</head>
<body>

  <h1>Build Resume</h1>
  <form method="POST" onsubmit="prepareHTML()">
    <div id="resume">
      <div class="resume-section">
        <h2 contenteditable="false">Full Name & Contact Info</h2>
        <div class="content" contenteditable="true" data-key="contact_info"></div>
      </div>

      <div class="resume-section">
        <h2 contenteditable="false">Profile Summary</h2>
        <div class="content" contenteditable="true" data-key="summary"></div>
      </div>

      <div class="resume-section">
        <h2 contenteditable="false">Skills</h2>
        <div class="content" contenteditable="true" data-key="skills"></div>
      </div>

      <div class="resume-section">
        <h2 contenteditable="false">Experience</h2>
        <div class="content" contenteditable="true" data-key="experience"></div>
      </div>

      <div class="resume-section">
        <h2 contenteditable="false">Education</h2>
        <div class="content" contenteditable="true" data-key="education"></div>
      </div>

      <div class="resume-section">
        <h2 contenteditable="false">Projects</h2>
        <div class="content" contenteditable="true" data-key="projects"></div>
      </div>
    </div>

    <input type="hidden" name="resumeHTML" id="resumeHTML">
    <button type="submit">Save as Docs</button>
  </form>

  <script>
    // Load saved content from localStorage
    document.querySelectorAll('.content').forEach(el => {
      const key = el.dataset.key;

      // Load saved content if exists
      const saved = localStorage.getItem(key);
      if (saved) el.innerHTML = saved;

      // Save new content on input
      el.addEventListener('input', () => {
        localStorage.setItem(key, el.innerHTML);
      });
    });

    function prepareHTML() {
      const resumeContent = document.getElementById('resume').innerHTML;
      document.getElementById('resumeHTML').value = resumeContent;
    }
  </script>

</body>
</html>
