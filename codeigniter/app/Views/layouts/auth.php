<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="description" content="ระบบบริหารจัดการเว็บแอพพลิเคชัน หน่วยงานภาครัฐทางด้านกฎหมาย">
  <meta name="author" content="สำนักงานปลัดกระทรวงสาธารณสุข">
  <title><?= $title ?? 'ระบบบริหารจัดการเว็บแอพพลิเคชัน' ?> - หน่วยงานภาครัฐทางด้านกฎหมาย</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="<?= base_url('favicon.ico') ?>">

  <!-- CSP ป้องกันการโจมตีแบบ XSS -->
  <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;">

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Cookie Consent สำหรับ PDPA -->
  <!-- <link rel="stylesheet" href="<?= base_url('assets/js/cookieconsent.min.css') ?>"> -->
  <!-- <script src="<?= base_url('assets/js/cookieconsent.min.js') ?>"></script> -->

  <!-- Custom CSS -->
  <style>
    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #555;
    }
  </style>
</head>

<body class="font-sans antialiased">
  <!-- Header -->
  <header class="bg-white shadow-sm fixed w-full top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <div class="flex-shrink-0 flex items-center">
            <img class="h-8 w-auto" src="<?= base_url('assets/images/logo.png') ?>" alt="Logo">
            <span class="ml-2 text-lg font-semibold">หน่วยงานภาครัฐทางด้านกฎหมาย</span>
          </div>
        </div>
        <div class="flex items-center">
          <a href="<?= site_url('/') ?>" class="text-sm font-medium text-gray-700 hover:text-gray-900">
            กลับไปยังหน้าหลัก
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="mt-16">
    <?= $this->renderSection('content') ?>
  </main>

  <!-- Footer -->
  <footer class="bg-white">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
      <div class="flex justify-center space-x-6 md:order-2">
        <a href="#" class="text-gray-400 hover:text-gray-500">
          <span class="sr-only">Facebook</span>
          <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
          </svg>
        </a>
        <a href="#" class="text-gray-400 hover:text-gray-500">
          <span class="sr-only">YouTube</span>
          <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.255.417-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" clip-rule="evenodd" />
          </svg>
        </a>
      </div>
      <div class="mt-8 md:mt-0 md:order-1">
        <p class="text-center text-base text-gray-400">
          &copy; <?= date('Y') ?> สำนักงานปลัดกระทรวงสาธารณสุข. สงวนลิขสิทธิ์.
        </p>
      </div>
    </div>
  </footer>

  <!-- Cookie Consent Script -->
  <script>
    window.addEventListener("load", function() {
      window.cookieconsent.initialise({
        "palette": {
          "popup": {
            "background": "#237afc",
            "text": "#ffffff"
          },
          "button": {
            "background": "#ffffff",
            "text": "#237afc"
          }
        },
        "theme": "classic",
        "position": "bottom-right",
        "content": {
          "message": "เว็บไซต์นี้ใช้คุกกี้เพื่อให้คุณได้รับประสบการณ์ที่ดีที่สุด",
          "dismiss": "ยอมรับ",
          "link": "เรียนรู้เพิ่มเติม",
          "href": "/privacy-policy"
        }
      });
    });
  </script>

  <!-- Security Headers ป้องกันการโจมตี -->
  <script nonce="<?= csrf_hash() ?>">
    // Lock down the window.opener to prevent Tabnabbing attacks
    if (window.opener) {
      window.opener = null;
    }

    // Restrict features
    document.addEventListener('DOMContentLoaded', function() {
      // Disable paste in password fields
      document.querySelectorAll('input[type=password]').forEach(function(input) {
        input.addEventListener('paste', function(e) {
          e.preventDefault();
          return false;
        });
      });
    });
  </script>
</body>

</html>